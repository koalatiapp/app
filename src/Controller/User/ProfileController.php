<?php

namespace App\Controller\User;

use App\Activity\Logger\UserLogger;
use App\Controller\AbstractController;
use App\Form\User\UserProfileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
	public function __construct(
		private readonly UserLogger $userActivityLogger,
	) {
	}

	#[Route(path: '/edit-profile', name: 'edit_profile')]
	public function editProfile(Request $request): Response
	{
		$user = $this->getUser();
		$form = $this->createForm(UserProfileType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->addFlash('success', $this->translator->trans('user_settings.profile.flash.success'));

			$this->userActivityLogger->updateProfile($user);
		}

		return $this->render('app/user/profile.html.twig', [
				'form' => $form->createView(),
			]);
	}
}
