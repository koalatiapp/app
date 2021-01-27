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
	protected const CURRENT_PROJECT_SESSION_KEY = 'koalati_current_project_id';

	/**
	 * Loads the target project and checks for user privileges.
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

	/**
	 * Redirects to the overview page for the last project opened.
	 * If no project was last opened, redirect to the Projects tab of the dashboaard.
	 *
	 * @Route("/project/current", name="project_shortcut")
	 */
	public function projectShortcut(Request $request): Response
	{
		if ($currentProjectId = $request->getSession()->get(static::CURRENT_PROJECT_SESSION_KEY)) {
			$project = $this->getProject($currentProjectId);

			if ($project) {
				return $this->redirectToRoute('project_dashboard', ['id' => $project->getId()]);
			}
		}

		return $this->redirectToRoute('projects');
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
				$this->addFlash('success', $translator->trans('project_creation.flash.created_successfully', ['%name%' => $project->getName()]));

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

		return $this->render('app/project/dashboard.html.twig', [
			'project' => $project,
		]);
	}
}
