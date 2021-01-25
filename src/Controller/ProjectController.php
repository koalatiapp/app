<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\NewProjectType;
use App\Message\SitemapRequest;
use App\Util\Url;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
	 * @Route("/project/create", name="project_creation")
	 */
	public function projectCreation(Url $urlHelper, Request $request, TranslatorInterface $translator): Response
	{
		$project = new Project();
		$project->setOwnerUser($this->getUser());

		$form = $this->createForm(NewProjectType::class, $project);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$websiteUrl = $urlHelper->standardize($project->getUrl());

			// Check if the provided website URL exists
			if (!$urlHelper->exists($websiteUrl)) {
				$form->get('url')->addError(new FormError('This URL is invalid or unreachable.'));
			}

			if ($form->isValid()) {
				$project->setUrl($websiteUrl);
				$em = $this->getDoctrine()->getManager();
				$em->persist($project);
				$em->flush();

				$this->dispatchMessage(new SitemapRequest($websiteUrl, $project->getId()));
				$this->addFlash('success', $translator->trans('project_creation.flash.created_successfully', ['name' => $project->getName()]));

				return $this->redirectToRoute('project_dashboard', ['id' => $project->getId()]);
			}
		}

		return $this->render('app/project/creation.html.twig', [
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/project/{id}", name="project_dashboard")
	 */
	public function projectDashboard(int $id): Response
	{
		$project = $this->getProject($id);

		foreach ($project->getActivePages() as $page) {
			dump($page);
		}

		return new Response('Not yet implemented; project '.$project->getName());
	}
}
