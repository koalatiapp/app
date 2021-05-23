<?php

namespace App\Controller\Project;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectTestingController extends AbstractProjectController
{
	/**
	 * @Route("/project/{id}/testing", name="project_testing")
	 */
	public function projectTesting(int $id): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/testing/index.html.twig', [
			'project' => $project,
		]);
	}
}
