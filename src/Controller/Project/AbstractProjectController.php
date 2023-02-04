<?php

namespace App\Controller\Project;

use App\Controller\AbstractController;
use App\Entity\Project;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractProjectController extends AbstractController
{
	protected RequestStack $requestStack;

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setRequestStack(RequestStack $requestStack): void
	{
		$this->requestStack = $requestStack;
	}

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
		$repository = $this->entityManager->getRepository(Project::class);
		$project = $repository->find($id);

		if (!$project) {
			throw $this->createNotFoundException('Project not found');
		}

		$this->denyAccessUnlessGranted(ProjectVoter::VIEW, $project);

		// Save the project to session as the "current project". This is used in the projectShortcut() method.
		$session = $this->requestStack->getSession();
		$session->set(static::getCurrentProjectSessionKey(), $project->getId());

		return $project;
	}
}
