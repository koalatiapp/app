<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Form\Project\ProjectSettingsType;
use App\Message\FaviconRequest;
use App\Message\ScreenshotRequest;
use App\Message\SitemapRequest;
use App\Util\Testing\AvailableToolsFetcher;
use App\Util\Url;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectSettingsController extends AbstractProjectController
{
	/**
	 * @Route("/project/{id}/settings/team", name="project_settings_team")
	 */
	public function teamSettings(int $id): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/settings/team.html.twig', ['project' => $project]);
	}

	/**
	 * @Route("/project/{id}/settings/checklist", name="project_settings_checklist")
	 */
	public function checklistSettings(int $id): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/settings/checklist.html.twig', ['project' => $project]);
	}

	/**
	 * @Route("/project/{id}/settings/automated-testing", name="project_settings_automated_testing")
	 */
	public function automatedTestingSettings(int $id, AvailableToolsFetcher $availableToolsFetcher): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/settings/automated-testing.html.twig', ['project' => $project, 'tools' => $availableToolsFetcher->getTools()]);
	}

	/**
	 * @Route("/project/{id}/settings", name="project_settings")
	 */
	public function projectSettings(Request $request, Url $urlHelper, TranslatorInterface $translator, int $id): Response
	{
		$project = $this->getProject($id);
		$originalProject = clone $project;

		/**
		 * @var \Symfony\Component\Form\Form $form
		 */
		$form = $this->createForm(ProjectSettingsType::class, $project);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			// Handle deletion first without regular form validation
			if ($this->isRequestingDeletion($form, $request)) {
				if ($form->get('deleteConfirmation')->getData() === true) {
					$em = $this->getDoctrine()->getManager();
					$em->remove($project);
					$em->flush();

					$this->addFlash('success', $translator->trans('project_settings.project.flash.deleted_successfully', ['%name%' => $project->getName()]));

					return $this->redirectToRoute('dashboard');
				}

				$form->get('deleteConfirmation')->addError(new FormError($translator->trans('project_settings.project.delete.error_confirmation')));
			}
			// Handle the good old form settings form regularly
			elseif ($form->isValid()) {
				$this->processChanges($form, $project, $originalProject, $urlHelper, $translator);
			}
		}

		return $this->render('app/project/settings/project.html.twig', [
			'project' => $project,
			'form' => $form->createView(),
		]);
	}

	private function processChanges(Form $form, Project $project, Project $originalProject, Url $urlHelper, TranslatorInterface $translator): void
	{
		$websiteUrl = $urlHelper->standardize($project->getUrl(), false);
		$urlHasChanged = $originalProject->getUrl() != $project->getUrl();

		// Check if the provided website URL exists
		if ($urlHasChanged && !$urlHelper->exists($websiteUrl)) {
			$form->get('url')->addError(new FormError($translator->trans('project_settings.project.form.field.url.error_unreachable')));
		}

		if ($form->isValid()) {
			$project->setUrl($websiteUrl);
			$em = $this->getDoctrine()->getManager();
			$em->persist($project);
			$em->flush();

			$this->addFlash('success', $translator->trans('project_settings.project.flash.updated_successfully', ['%name%' => $project->getName()]));

			if ($urlHasChanged) {
				$this->dispatchMessage(new ScreenshotRequest($project->getId()));
				$this->dispatchMessage(new FaviconRequest($project->getId()));
				$this->dispatchMessage(new SitemapRequest($project->getId()));
			}
		}
	}

	private function isRequestingDeletion(Form $form, Request $request): bool
	{
		$clickedButtonName = $form->getClickedButton()->getName();
		$csrfToken = $request->request->get($form->getName())['_token'] ?? null;

		return $clickedButtonName == 'delete' && $this->isCsrfTokenValid($form->getName(), $csrfToken);
	}
}
