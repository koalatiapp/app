<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
	/**
	 * @Route("/account/subscription", name="manage_subscription")
	 */
	public function manageSubscription(Request $request): Response
	{
		$user = $this->getUser();
		/**
		 * @var \Symfony\Component\Form\Form $form
		 */
		$form = $this->createForm(UserProfileType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash('success', $this->translator->trans('user_settings.profile.flash.success'));
		}

		return $this->render('app/user/profile.html.twig', [
			'form' => $form->createView(),
		]);
	}
}
