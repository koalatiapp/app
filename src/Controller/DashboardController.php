<?php

namespace App\Controller;

use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Security\ProjectVoter;
use App\Subscription\PlanManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
	use SuggestUpgradeControllerTrait;

	#[Route(path: '/', name: 'dashboard')]
	public function overview(): Response
	{
		return $this->redirectToRoute('projects');

		// @TODO: Implement dashboard and remove redirection
		/*
		$projects = $this->getUser()->getAllProjects();

		return $this->render('app/dashboard/index.html.twig', [
			'projects' => $projects->slice(0, 3),
		]);
		*/
	}

	#[Route(path: '/projects', name: 'projects')]
	public function projects(PlanManager $planManager): Response
	{
		if (!$this->isGranted(ProjectVoter::VIEW, null)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.project');
		}

		$projects = $this->getUser()->getAllProjects();

		if ($projects->isEmpty()) {
			return $this->redirectToRoute("onboarding");
		}

		return $this->render('app/dashboard/projects.html.twig', [
				'projects' => $projects,
			]);
	}

	#[Route(path: '/inbox', name: 'inbox')]
	public function inbox(): Response
	{
		return $this->render('app/dashboard/index.html.twig', [
			]);
	}

	#[Route(path: '/whats-new', name: 'koalati_news')]
	public function whatsNew(): Response
	{
		return $this->render('app/dashboard/index.html.twig', [
			]);
	}
}
