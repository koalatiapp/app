<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
	use TargetPathTrait;

	final public const LOGIN_ROUTE = 'login';

	public function __construct(
		private readonly UrlGeneratorInterface $urlGenerator,
		private readonly EntityManagerInterface $entityManager,
		private readonly EmailVerifier $emailVerifier,
		private readonly TokenStorageInterface $tokenStorage,
		private readonly RateLimiterFactory $appAuthenticationLimiter,
	) {
	}

	public function authenticate(Request $request): Passport
	{
		$email = $request->request->get('email', '');

		$this->checkRateLimiting($request, $email);

		$request->getSession()->set(Security::LAST_USERNAME, $email);

		return new Passport(
			new UserBadge($email),
			new PasswordCredentials($request->request->get('password', '')),
			[
				new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
				new RememberMeBadge(),
			]
		);
	}

	private function checkRateLimiting(Request $request, string $email): void
	{
		$rateLimiter = $this->appAuthenticationLimiter->create(strtolower(trim($email))."-".$request->getClientIp());
		$limit = $rateLimiter->consume(1);

		if ($limit->isAccepted() === false) {
			throw new TooManyLoginAttemptsAuthenticationException(threshold: (int) ceil(($limit->getRetryAfter()->getTimestamp() - time()) / 60));
		}
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
	{
		$user = $token->getUser();

		if ($user instanceof User) {
			$dateLastLoggedIn = $user->getDateLastLoggedIn();

			$user->setDateLastLoggedIn(new \DateTime());
			$this->entityManager->persist($user);
			$this->entityManager->flush();

			// Check if email has been verified before logging in the user...
			if (!$user->isVerified()) {
				// Send a new confirmation link to the user if the last one expired
				if ($dateLastLoggedIn < new \DateTime("-1 hour")) {
					$this->emailVerifier->sendEmailConfirmation($user);
				}

				// Force logout
				$this->tokenStorage->setToken(null);

				return new RedirectResponse($this->urlGenerator->generate('verify_email_pending'));
			}
		}

		if ($organizationInvitationUrl = $request->getSession()->get("pre_redirect_organization_invitation_url")) {
			$request->getSession()->remove("pre_redirect_organization_invitation_url");

			return new RedirectResponse($organizationInvitationUrl);
		}

		if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
			if (!str_contains($targetPath, "/internal-api/")) {
				return new RedirectResponse($targetPath);
			}
		}

		return new RedirectResponse($this->urlGenerator->generate('dashboard'));
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function getLoginUrl(Request $request): string
	{
		return $this->urlGenerator->generate(self::LOGIN_ROUTE);
	}
}
