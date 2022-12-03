<?php

namespace App\Controller\Trait;

use App\Entity\Project;
use App\Security\ProjectVoter;

trait ProjectControllerTrait
{
	final protected static function getCurrentProjectSessionKey(): string
	{
		return 'koalati_current_project_id';
	}

	/**
	 * Loads the target project and checks for user privileges.
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function getProject(int $id): ?Project
	{
		/** @var \App\Repository\ProjectRepository */
		$repository = $this->getDoctrine()->getRepository(Project::class);
		$project = $repository->find($id);

		if (!$project) {
			throw $this->createNotFoundException('Project not found');
		}

		$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $project);

		// Save the project to session as the "current project". This is used in the projectShortcut() method.
		$session = $this->get('request_stack')->getSession();
		$session->set(static::getCurrentProjectSessionKey(), $project->getId());

		return $project;
	}
}
