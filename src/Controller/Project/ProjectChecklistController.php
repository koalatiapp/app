<?php

namespace App\Controller\Project;

use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Entity\Project;
use App\Security\ProjectVoter;
use App\Util\Checklist\Generator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectChecklistController extends AbstractProjectController
{
	use SuggestUpgradeControllerTrait;

	public function __construct(
		private readonly Generator $checklistGenerator,
	) {
	}

	private function autogenerateChecklist(Project $project): void
	{
		if ($project->getChecklist()) {
			return;
		}

		$checklist = $this->checklistGenerator->generateChecklist($project);

		$this->entityManager->persist($checklist);
		$this->entityManager->flush();
	}

	#[Route(path: '/project/{id}/checklist', name: 'project_checklist')]
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

	#[Route(path: '/project/{id}/checklist/step-by-step', name: 'project_checklist_detailed')]
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
