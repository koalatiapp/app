<?php

namespace App\Controller\Project;

use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractProjectController extends AbstractController
{
	protected const CURRENT_PROJECT_SESSION_KEY = 'koalati_current_project_id';

	/**
	 * Loads the target project and checks for user privileges.
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function getProject(int $id): ?Project
	{
		/**
		 * @var \App\Repository\ProjectRepository
		 */
		$repository = $this->getDoctrine()->getRepository(Project::class);
		$project = $repository->findById($id, $this->getUser());

		if (!$project) {
			throw $this->createNotFoundException('Project not found');
		}

		// Save the project to session as the "current project". This is used in the projectShortcut() method.
		$this->get('session')->set(static::CURRENT_PROJECT_SESSION_KEY, $project->getId());

		return $project;
	}
}
