<?php

namespace App\Controller\Project;

use App\Activity\ActivityLogger;
use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Entity\Project;
use App\Form\Project\ProjectSettingsType;
use App\Message\FaviconRequest;
use App\Message\ScreenshotRequest;
use App\Message\SitemapRequest;
use App\Security\ProjectVoter;
use App\Util\Testing\AvailableToolsFetcher;
use App\Util\Url;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjectSettingsController extends AbstractProjectController
{
	use SuggestUpgradeControllerTrait;

	public function __construct(
		private readonly MessageBusInterface $bus,
		private readonly ActivityLogger $activityLogger,
	) {
	}

	#[Route(path: '/project/{id}/settings/team', name: 'project_settings_team')]
	public function teamSettings(int $id): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::EDIT, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.project');
		}

		return $this->render('app/project/settings/team.html.twig', ['project' => $project]);
	}

	#[Route(path: '/project/{id}/settings/checklist', name: 'project_settings_checklist')]
	public function checklistSettings(int $id): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::EDIT, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.project');
		}

		return $this->render('app/project/settings/checklist.html.twig', ['project' => $project]);
	}

	#[Route(path: '/project/{id}/settings/automated-testing', name: 'project_settings_automated_testing')]
	public function automatedTestingSettings(int $id, AvailableToolsFetcher $availableToolsFetcher): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::EDIT, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.project');
		}

		return $this->render('app/project/settings/automated-testing.html.twig', ['project' => $project, 'tools' => $availableToolsFetcher->getTools()]);
	}

	#[Route(path: '/project/{id}/settings', name: 'project_settings')]
	public function projectSettings(int $id, Request $request, Url $urlHelper): Response
	{
		$project = $this->getProject($id);
		$originalProject = clone $project;

		if (!$this->isGranted(ProjectVoter::EDIT, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.project');
		}

		/**
		 * @var \Symfony\Component\Form\Form $form
		 */
		$form = $this->createForm(ProjectSettingsType::class, $project);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			// Handle deletion first without regular form validation
			if ($this->isRequestingDeletion($form, $request)) {
				if ($form->get('deleteConfirmation')->getData() === true) {
					$projectName = $project->getName();
					$this->entityManager->remove($project);
					$this->entityManager->flush();

					$this->addFlash('success', 'project_settings.project.flash.deleted_successfully', ['%name%' => $projectName]);

					$this->activityLogger->postRemove($project);

					return $this->redirectToRoute('dashboard');
				}

				$form->get('deleteConfirmation')->addError(new FormError($this->translator->trans('project_settings.project.delete.error_confirmation')));
			}
			// Handle the good old form settings form regularly
			elseif ($form->isValid()) {
				$this->processChanges($form, $project, $originalProject, $urlHelper);
			}
		}

		return $this->render('app/project/settings/project.html.twig', [
				'project' => $project,
				'form' => $form->createView(),
			]);
	}

	private function processChanges(Form $form, Project $project, Project $originalProject, Url $urlHelper): void
	{
		$websiteUrl = $urlHelper->standardize($project->getUrl(), false);
		$urlHasChanged = $originalProject->getUrl() != $project->getUrl();
		$useCanonicalUrlSettingHasChanged = $originalProject->useCanonicalPageUrls() != $project->useCanonicalPageUrls();

		// Check if the provided website URL exists
		if ($urlHasChanged && !$urlHelper->exists($websiteUrl)) {
			$form->get('url')->addError(new FormError($this->translator->trans('project_settings.project.form.field.url.error_unreachable')));
		}

		if ($form->isValid()) {
			$project->setUrl($websiteUrl);
			$this->entityManager->persist($project);
			$this->entityManager->flush();

			$this->addFlash('success', 'project_settings.project.flash.updated_successfully', ['%name%' => $project->getName()]);

			if ($urlHasChanged || $useCanonicalUrlSettingHasChanged) {
				$this->bus->dispatch(new ScreenshotRequest($project->getId()));
				$this->bus->dispatch(new FaviconRequest($project->getId()));
				$this->bus->dispatch(new SitemapRequest($project->getId()));
			}

			$this->activityLogger->postPersist($project, ['id' => $project->getId()]);
		}
	}

	private function isRequestingDeletion(Form $form, Request $request): bool
	{
		$clickedButtonName = $form->getClickedButton()->getName();
		$csrfToken = $request->request->all($form->getName())['_token'] ?? null;

		return $clickedButtonName == 'delete' && $this->isCsrfTokenValid($form->getName(), $csrfToken);
	}
}
