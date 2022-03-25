<?php

namespace App\Controller\Api\Project;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Util\Testing\AvailableToolsFetcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/project/automated-testing-settings", name="api_project_automated_testing_settings_")
 */
class AutomatedTestingSettingsController extends AbstractController
{
	use ApiControllerTrait;

	/**
	 * @Route("/tools", methods={"GET","HEAD"}, name="tools_list", options={"expose": true})
	 */
	public function listTools(Request $request, AvailableToolsFetcher $availableToolsFetcher): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);
		$availableTools = $availableToolsFetcher->getTools();
		$list = [];

		foreach ($availableTools as $tool) {
			$list[] = [
				'id' => $tool->name,
				'enabled' => $project->hasToolEnabled($tool->name),
				'tool' => $tool,
			];
		}

		return $this->apiSuccess($list);
	}

	/**
	 * @Route("/tools", methods={"POST", "PUT"}, name="tools_toggle", options={"expose": true})
	 */
	public function toggleTool(Request $request): JsonResponse
	{
		$projectId = $request->request->get('project_id');
		$toolName = $request->request->get('tool');
		$enabled = $request->request->get('enabled');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		if (!$toolName) {
			return $this->apiError('You must provide a valid value for `tool`.');
		}

		if ($enabled === null) {
			return $this->apiError('You must provide a valid value for `enabled`.');
		}

		$project = $this->getProject($projectId);

		if ($enabled && !$project->hasToolEnabled($toolName)) {
			$project->enableTool($toolName);
		} elseif (!$enabled && $project->hasToolEnabled($toolName)) {
			$project->disableTool($toolName);
		}

		$em = $this->getDoctrine()->getManager();
		$em->persist($project);
		$em->flush();

		return $this->apiSuccess([
			'tool' => $toolName,
			'enabled' => $project->hasToolEnabled($toolName),
		]);
	}
}
