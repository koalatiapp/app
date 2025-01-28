<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactPreferencesController extends AbstractController
{
	#[Route(path: '/account/contact-preferences', name: 'manage_contact_preferences')]
	public function contactPreferences(Request $request): Response
	{
		$user = $this->getUser();
		$form = $this->createForm(UserProfileType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->addFlash('success', $this->translator->trans('user_settings.profile.flash.success'));
		}

		return $this->render('app/user/profile.html.twig', [
			'form' => $form->createView(),
		]);
	}
}
