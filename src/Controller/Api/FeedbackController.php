<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/feedback", name="api_feedback_")
 */
class FeedbackController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * @Route("/submit", methods={"POST"}, name="submit", options={"expose": true})
	 */
	public function submitFeedback(Request $request, MailerInterface $mailer): Response
	{
		$type = $request->request->get('type');
		$message = $request->request->get('message');
		$url = $request->request->get('url');
		$user = $this->getUser();

		$email = (new TemplatedEmail())
			->to(new Address('info@koalati.com', 'Koalati'))
			->subject(sprintf('New feedback from %s', $user->getFullName()))
			->htmlTemplate('email/user_feedback.html.twig')
			->context([
				'user' => $user,
				'type' => $type,
				'message' => $message,
				'url' => $url,
			]);
		$mailer->send($email);

		return $this->apiSuccess();
	}
}
