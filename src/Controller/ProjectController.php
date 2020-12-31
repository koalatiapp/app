<?php

namespace App\Controller;

use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
	/**
	 * Loads the target project and checks for user privileges.
	 *
	 * @return \App\Entity\Project
	 */
	protected function getProject(int $id)
	{
		/**
		 * @var \App\Repository\ProjectRepository
		 */
		$repository = $this->getDoctrine()->getRepository(Project::class);
		$project = $repository->findById($id, $this->getUser());

		if (!$project) {
			throw $this->createNotFoundException('Project not found');
		}

		return $project;
	}

	/**
	 * @Route("/project/{id}", name="project_dashboard")
	 */
	public function projectDashboard(int $id): Response
	{
		$project = $this->getProject($id);

		return new Response('Not yet implemented; project '.$project->getName());
	}
}
