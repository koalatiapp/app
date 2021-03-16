<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
	/**
	 * @Route("/", name="dashboard")
	 */
	public function overview(): Response
	{
		$projects = $this->getUser()->getAllProjects();

		return $this->render('app/dashboard/index.html.twig', [
			'projects' => $projects->slice(0, 3),
		]);
	}

	/**
	 * @Route("/projects", name="projects")
	 */
	public function projects(): Response
	{
		$projects = $this->getUser()->getAllProjects();

		return $this->render('app/dashboard/projects.html.twig', [
			'projects' => $projects,
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
