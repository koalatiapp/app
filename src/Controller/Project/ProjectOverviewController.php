<?php

namespace App\Controller\Project;

use App\Subscription\UsageManager;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProjectOverviewController extends AbstractProjectController
{
	/**
	 * Redirects to the overview page for the last project opened.
	 * If no project was last opened, redirect to the Projects tab of the dashboaard.
	 */
	#[Route(path: '/project/current', name: 'project_shortcut')]
	public function projectShortcut(Request $request, HashidsInterface $idHasher): Response
	{
		if ($currentProjectId = $request->getSession()->get(static::getCurrentProjectSessionKey())) {
			try {
				$project = $this->getProject($currentProjectId);
			} catch (NotFoundHttpException) {
				$project = null;
			}

			if ($project) {
				return $this->redirectToRoute('project_dashboard', ['id' => $idHasher->encode($project->getId())]);
			}
		}

		return $this->redirectToRoute('projects');
	}

	#[Route(path: '/project/{id}', name: 'project_dashboard', options: ['expose' => true])]
	public function projectDashboard(int $id, UsageManager $usageManager): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/dashboard.html.twig', [
				'project' => $project,
				'usageManager' => $usageManager->withUser($project->getTopLevelOwner()),
			]);
	}
}
