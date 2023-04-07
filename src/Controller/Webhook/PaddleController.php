<?php

namespace App\Controller\Webhook;

use App\Activity\Logger\UserLogger;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Subscription\Plan\NoPlan;
use App\Subscription\PlanManager;
use Paddle\API as PaddleAPI;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaddleController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly PaddleAPI $paddleApi,
		private readonly PlanManager $planManager,
		private readonly UserRepository $userRepository,
		private readonly MailerInterface $mailer,
		private readonly UserLogger $userActivityLogger,
	) {
	}

	#[Route(path: '/webhook/paddle', name: 'webhook_paddle')]
	public function receiveAlert(Request $request): Response
	{
		if (!$this->verifySignature($request)) {
			throw new \Exception('Paddle webhook has failed security verification', 401);
		}

		$alertType = $request->request->get('alert_name');

		// Paused subscriptions are treated like cancelled subscriptions
		if ($request->request->get('paused_at')) {
			$alertType = 'subscription_paused';
		}

		return match ($alertType) {
			'subscription_created', 'subscription_updated' => $this->updateUserSubscription($request),
			'subscription_paused', 'subscription_cancelled' => $this->cancelUserSubscription($request),
			'subscription_payment_success' => $this->handleSubscriptionSuccessfulPayment($request),
			default => throw new \Exception(sprintf('The "%s" Paddle alert is not handled by the application at the moment.', $alertType)),
		};
	}

	/**
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function updateUserSubscription(Request $request): Response
	{
		$user = $this->getTargetUser($request);
		$originalPlan = $this->planManager->getPlanFromEntity($user);
		$planId = $request->request->get('subscription_plan_id');
		$newPlan = $this->planManager->getPlanFromPaddleId($planId);
		$paddleUserId = $request->request->get('user_id');
		$paddleSubscriptionId = $request->request->get('subscription_id');

		$user->setPaddleUserId((string) $paddleUserId)
			->setPaddleSubscriptionId($paddleSubscriptionId);
		$paddleUser = $this->paddleApi->subscription()->listUsers($paddleSubscriptionId)[0] ?? null;
		$nextPaymentDate = new \DateTimeImmutable($paddleUser['next_payment']['date']);

		if ($newPlan->isDowngradeComparedTo($originalPlan)) {
			/*
			 * If the user is downgrading, this shouldn't be effective immediately.
			 * Instead, the downgrade should be scheduled for the next payment date.
			 */
			$user->setUpcomingSubscriptionPlan($newPlan)
				->setSubscriptionChangeDate($nextPaymentDate)
				->setSubscriptionRenewalDate($nextPaymentDate);

			$this->handleDowngradeSideEffects($user);
		} else {
			/*
			 * Upgrades are effective immediately, and should clear any upcoming
			 * cancellations or downgrade.
			 */
			$user->setSubscriptionPlan($newPlan)
				->setUpcomingSubscriptionPlan(null)
				->setSubscriptionChangeDate(null)
				->setSubscriptionRenewalDate($nextPaymentDate);
		}

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		$this->userActivityLogger->updateSubscription($user, $originalPlan->getUniqueName(), $newPlan->getUniqueName());

		return new Response('ok');
	}

	private function cancelUserSubscription(Request $request): Response
	{
		$user = $this->getTargetUser($request);
		$endDateString = $request->request->get('cancellation_effective_date');

		$user->setUpcomingSubscriptionPlan(NoPlan::UNIQUE_NAME)
			->setSubscriptionChangeDate(new \DateTime($endDateString))
			->setSubscriptionRenewalDate(null)
			->setPaddleSubscriptionId(null);
		$this->handleDowngradeSideEffects($user);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		$this->userActivityLogger->cancelSubscription($user);

		return new Response('ok');
	}

	private function handleDowngradeSideEffects(User $user): void
	{
		// @TODO: Implement an automatic team ownership transfer / reattribution system when a team owner cancels their account.

		$plan = $this->planManager->getPlanFromEntity($user);
		$organization = $user->getOwnedOrganization();

		if ($organization && $organization->getMembers()->count() > 1 && !$plan->getMaxTeamOwned()) {
			$email = (new TemplatedEmail())
			->to(new Address($user->getEmail(), $user->getFirstName()))
			->subject($this->translator->trans('email.organization_ownership_transfer.subject', ['%organization%' => $organization]))
			->htmlTemplate('email/organization_ownership_transfer.html.twig')
			->context([
				'user' => $user,
				'plan' => $plan,
				'organization' => $organization,
			]);
			$this->mailer->send($email);
		}
	}

	private function handleSubscriptionSuccessfulPayment(Request $request): Response
	{
		$user = $this->getTargetUser($request);
		$nextPaymentDate = new \DateTime($request->request->get('next_bill_date'));

		$user->setPreviousBillingDate(new \DateTime('now'))
			->setNextBillingDate($nextPaymentDate)
			->setSubscriptionRenewalDate($nextPaymentDate);

		$this->entityManager->persist($user);
		$this->entityManager->flush();

		return new Response('ok');
	}

	private function getTargetUser(Request $request): User
	{
		$paddleUserId = $request->request->get('user_id');
		$user = $this->userRepository->findOneByPaddleUserId($paddleUserId);

		if (!$user) {
			// Fallback on the email address...
			$email = $request->request->get('email');
			$user = $this->userRepository->findOneByEmail($email);

			if (!$user) {
				throw new \Exception('No user was found for the provided Paddle ID and email address.');
			}
		}

		return $user;
	}

	/**
	 * Validates the origin of the webhook request via the
	 * signature provided by Padddle.
	 *
	 * For mor information, read https://developer.paddle.com/webhook-reference/verifying-webhooks.
	 */
	private function verifySignature(Request $request): bool
	{
		$payload = $request->request->all();
		$encodedSignature = $payload['p_signature'] ?? null;

		if (!$encodedSignature) {
			return false;
		}

		$signature = base64_decode($encodedSignature);
		unset($payload['p_signature']);

		ksort($payload);
		foreach ($payload as $key => $value) {
			if (!in_array(gettype($value), ['object', 'array'])) {
				$payload[$key] = "$value";
			}
		}

		$publicKey = openssl_get_publickey($this->getParameter('paddle_public_key'));
		$verificationResult = openssl_verify(serialize($payload), $signature, $publicKey, OPENSSL_ALGO_SHA1);

		return $verificationResult == 1;
	}
}
