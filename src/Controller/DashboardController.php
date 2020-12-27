<?php

namespace App\Controller;

use App\Storage\ProjectStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
	/**
	 * @Route("/", name="dashboard")
	 */
	public function overview(ProjectStorage $projectStorage): Response
	{
		$projects = $this->getUser()->getProjects();

		return $this->render('app/dashboard/index.html.twig', [
			'projects' => $projects->slice(0, 3),
			'projectStorage' => $projectStorage,
		]);
	}

	/**
	 * @Route("/projects", name="projects")
	 */
	public function projects(ProjectStorage $projectStorage): Response
	{
		$projects = $this->getUser()->getProjects();

		return $this->render('app/dashboard/index.html.twig', [
			'projects' => $projects,
			'projectStorage' => $projectStorage,
		]);
	}

	/**
	 * @Route("/inbox", name="inbox")
	 */
	public function inbox(): Response
	{
		return $this->render('app/dashboard/index.html.twig', [
		]);
	}

	/**
	 * @Route("/whats-new", name="koalati_news")
	 */
	public function whatsNew(): Response
	{
		return $this->render('app/dashboard/index.html.twig', [
		]);
	}
}
