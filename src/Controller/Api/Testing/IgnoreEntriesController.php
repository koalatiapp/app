<?php

namespace App\Controller\Api\Testing;

use App\Controller\Api\AbstractApiController;
use App\Entity\Testing\IgnoreEntry;
use App\Mercure\TopicBuilder;
use App\Repository\Testing\IgnoreEntryRepository;
use App\Repository\Testing\RecommendationRepository;
use App\Security\IgnoreEntryVoter;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/testing/ignore-entries", name="api_testing_ignore_entry_")
 */
class IgnoreEntriesController extends AbstractApiController
{
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
		$ignoreEntries = $project->getIgnoreEntries();

		foreach ($project->getOwner()->getIgnoreEntries() as $ownerIgnoreEntry) {
			$ignoreEntries->add($ownerIgnoreEntry);
		}

		if ($projectId) {
			$this->setSuggestedMercureTopic($this->topicBuilder->getEntityGenericTopic(IgnoreEntry::class, TopicBuilder::SCOPE_PROJECT, $projectId));
		}

		return $this->apiSuccess($ignoreEntries);
	}

	/**
	 * @Route("", methods={"POST","PUT"}, name="create", options={"expose": true})
	 */
	public function create(Request $request, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$scope = $request->request->get('scope');
		$recommendationId = $request->request->get('recommendation_id');
		$recommendation = $recommendationRepository->find($recommendationId);

		if (!$recommendation) {
			// @TODO: replace this error message with a translation message
			return $this->notFound('The recommendation you are attempting to ignore does not seem to exist anymore.');
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$testResult = $recommendation->getParentResult();
		$toolResponse = $testResult->getParentResponse();
		$ignoreEntry = new IgnoreEntry($toolResponse->getTool(), $testResult->getUniqueName(), $recommendation->getUniqueName(), $recommendation->getTitle());
		$ignoreEntry->setCreatedBy($this->getUser());

		switch ($scope) {
			case 'organization':
				$ignoreEntry->setTargetOrganization($project->getOwnerOrganization());
				break;
			case 'user':
				$ignoreEntry->setTargetUser($this->getUser());
				break;
			case 'project':
				$ignoreEntry->setTargetProject($project);
				break;
			case 'page':
				$ignoreEntry->setTargetPage($recommendation->getRelatedPage());
				break;
		}

		$em->persist($ignoreEntry);
		$em->flush();

		$this->updateDispatcher->dispatch($ignoreEntry, ['id' => $ignoreEntry->getId(), 'data' => $this->serializer->serialize($ignoreEntry)]);

		return $this->apiSuccess($ignoreEntry);
	}

	/**
	 * @Route("/{id}", methods={"GET","HEAD"}, name="details", options={"expose": true})
	 */
	public function details(int $id, IgnoreEntryRepository $ignoreEntryRepository): JsonResponse
	{
		$entry = $ignoreEntryRepository->find($id);

		if (!$this->isGranted(IgnoreEntryVoter::VIEW, $entry)) {
			return $this->accessDenied();
		}

		$this->setSuggestedMercureTopic($this->topicBuilder->getEntityTopic($entry, TopicBuilder::SCOPE_SPECIFIC));

		return $this->apiSuccess($entry);
	}

	/**
	 * @Route("/{id}", methods={"DELETE"}, name="delete", options={"expose": true})
	 */
	public function delete(int $id, IgnoreEntryRepository $ignoreEntryRepository): JsonResponse
	{
		$entry = $ignoreEntryRepository->find($id);

		if (!$this->isGranted(IgnoreEntryVoter::DELETE, $entry)) {
			return $this->accessDenied();
		}

		$this->updateDispatcher->prepare($entry, ['id' => $id]);

		$em = $this->getDoctrine()->getManager();
		$em->remove($entry);
		$em->flush();

		$this->updateDispatcher->dispatchPreparedUpdates();

		return $this->apiSuccess();
	}
}
