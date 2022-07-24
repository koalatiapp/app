<?php

namespace App\Controller\Project;

use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Entity\Checklist\Checklist;
use App\Entity\Project;
use App\Repository\Checklist\ChecklistTemplateRepository;
use App\Security\ProjectVoter;
use App\Util\Checklist\TemplateHydrator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectChecklistController extends AbstractProjectController
{
	use SuggestUpgradeControllerTrait;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.templateRepository)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.templateHydrator)
	 */
	public function __construct(
		private ChecklistTemplateRepository $templateRepository,
		private TemplateHydrator $templateHydrator,
	) {
	}

	private function autogenerateChecklist(Project $project): void
	{
		if ($project->getChecklist()) {
			return;
		}

		// @TODO: add an interface to let users select a template
		$template = current($this->templateRepository->findAll());
		$checklist = $this->templateHydrator->hydrate($template, $project);
		$em = $this->getDoctrine()->getManager();
		$em->persist($checklist);

		foreach ($checklist->getItemGroups() as $group) {
			$em->persist($group);
		}

		foreach ($checklist->getItems() as $item) {
			$em->persist($item);
		}

		$em->flush();
		$em->refresh($project);
	}

	/**
	 * @Route("/project/{id}/checklist", name="project_checklist")
	 */
	public function overview(int $id): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::CHECKLIST, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.checklist');
		}

		if (!$project->getChecklist()) {
			$this->autogenerateChecklist($project);
		}

		return $this->render('app/project/checklist/index.html.twig', [
			'project' => $project,
		]);
	}

	/**
	 * @Route("/project/{id}/checklist/step-by-step", name="project_checklist_detailed")
	 */
	public function stepByStep(int $id): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::CHECKLIST, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.checklist');
		}

		return $this->render('app/project/checklist/index.html.twig', [
			'project' => $project,
		]);
	}
}
