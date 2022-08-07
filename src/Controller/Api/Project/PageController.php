<?php

namespace App\Controller\Api\Project;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Entity\Page;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/project/pages", name="api_project_pages_")
 */
class PageController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * @Route("", methods={"GET","HEAD"}, name="list", options={"expose": true})
	 */
	public function list(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);
		$pages = $project->getPages()->toArray();

		// Sort pages by relevance (shortest URLs first)
		usort($pages, function (Page $pageA, Page $pageB) {
			return strlen($pageA->getUrl()) <=> strlen($pageB->getUrl());
		});

		return $this->apiSuccess($pages);
	}

	/**
	 * @Route("", methods={"POST", "PUT"}, name="toggle", options={"expose": true})
	 */
	public function togglePage(Request $request): JsonResponse
	{
		$projectId = $request->request->get('project_id');
		$pageId = $request->request->get('page_id');
		$enabled = $request->request->get('enabled');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		if (!$pageId) {
			return $this->apiError('You must provide a valid value for `page_id`.');
		}

		if ($enabled === null) {
			return $this->apiError('You must provide a valid value for `enabled`.');
		}

		$page = $this->getPage($projectId, $pageId);

		if (!$page) {
			return $this->notFound('The page you requested could not be found in this project.');
		}

		if ($enabled && $page->getIsIgnored()) {
			$page->setIsIgnored(false);
		} elseif (!$enabled && !$page->getIsIgnored()) {
			$page->setIsIgnored(true);
		}

		$em = $this->getDoctrine()->getManager();
		$em->persist($page);
		$em->flush();

		return $this->apiSuccess([
			'enabled' => !$page->getIsIgnored(),
		]);
	}

	private function getPage(int|string $projectId, int|string $pageId): ?Page
	{
		$project = $this->getProject($projectId);

		if (!is_numeric($pageId)) {
			$pageId = $this->idHasher->decode($pageId)[0];
		}

		/** @var Page|null */
		$page = $project->getPages()->filter(function (Page $page) use ($pageId) {
			return $page->getId() == $pageId;
		})->first();

		return $page ?: null;
	}
}
