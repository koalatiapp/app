<?php

namespace App\Controller\Project;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectOverviewController extends AbstractProjectController
{
	/**
	 * Redirects to the overview page for the last project opened.
	 * If no project was last opened, redirect to the Projects tab of the dashboaard.
	 *
	 * @Route("/project/current", name="project_shortcut")
	 */
	public function projectShortcut(Request $request): Response
	{
		if ($currentProjectId = $request->getSession()->get(static::getCurrentProjectSessionKey())) {
			$project = $this->getProject($currentProjectId);

			if ($project) {
				return $this->redirectToRoute('project_dashboard', ['id' => $project->getId()]);
			}
		}

		return $this->redirectToRoute('projects');
	}

	/**
	 * @Route("/project/{id}/", name="project_dashboard", options={"expose": true})
	 */
	public function projectDashboard(int $id): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/dashboard.html.twig', [
			'project' => $project,
		]);
	}
}
