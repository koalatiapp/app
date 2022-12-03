<?php

namespace App\MessageHandler;

use App\Entity\Project;
use App\Entity\Testing\Recommendation;
use App\Entity\Testing\TestResult;
use App\Entity\Testing\ToolResponse;
use App\Mercure\UpdateDispatcher;
use App\Mercure\UpdateType;
use App\Message\TestingResultRequest;
use App\Message\TestingStatusRequest;
use App\Repository\PageRepository;
use App\Repository\ProjectRepository;
use App\Repository\Testing\RecommendationRepository;
use App\Util\Testing\RecommendationGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TestingResultRequestHandler implements MessageHandlerInterface
{
	/**
	 * @var array<int,Project>
	 */
	private array $updatedProjectsByPageId = [];

	private ?string $tool = null;

	/**
	 * @var array<int,array<string,Recommendation>>
	 */
	private array $completedRecommendations = [];

	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly ProjectRepository $projectRepository,
		private readonly RecommendationRepository $recommendationRepository,
		private readonly PageRepository $pageRepository,
		private readonly UpdateDispatcher $updateDispatcher,
		private readonly MessageBusInterface $bus,
	) {
	}

	public function __invoke(TestingResultRequest $message): void
	{
		// Reset the state on each invocation
		$this->updatedProjectsByPageId = [];
		$this->completedRecommendations = [];
		$this->tool = null;

		$payload = $message->getPayload();

		// Handle payload errors
		if ($this->requestIsErrorNotice($payload)) {
			$this->dispatchProjectStatusUpdates($payload);

			return;
		}

		// @TODO: add handling of tool responses where `$payload["success"]` is false
		// @TODO: add a security check to webhook to ensure it comes from a legit source. This should probably be done via the firewall.
		$this->processToolResponse($payload);

		// Communicate the new data to subscribed clients
		$this->dispatchClientUpdates();

		// Send project status update
		$this->dispatchProjectStatusUpdates($payload);
	}

	/**
	 * Trigger a project status update request for each project
	 * that has been affected by the test result that was just processed.
	 *
	 * @param array<string,mixed> $payload
	 */
	private function dispatchProjectStatusUpdates(array $payload): void
	{
		$pageUrl = $payload['request']['url'];
		$projects = $this->projectRepository->findByPageUrl($pageUrl);

		foreach ($projects as $project) {
			$this->bus->dispatch(new TestingStatusRequest($project->getId()));
		}
	}

	private function dispatchClientUpdates(): void
	{
		foreach ($this->updatedProjectsByPageId as $project) {
			$this->entityManager->refresh($project);
			$completedRecommendations = $this->completedRecommendations[$project->getId()] ?? [];

			// Process updates and creations
			foreach ($project->getActiveRecommendationGroups() as $group) {
				if ($group->getTool() != $this->tool) {
					continue;
				}

				$this->updateDispatcher->prepare($group, UpdateType::UPDATE);
				unset($completedRecommendations[$group->getUniqueName()]);
			}

			// Process completions
			foreach ($completedRecommendations as $completedRecommendation) {
				$group = new RecommendationGroup(new ArrayCollection([$completedRecommendation]));
				$this->updateDispatcher->prepare($group, UpdateType::UPDATE);
			}
		}

		$this->updateDispatcher->dispatchPreparedUpdates();
	}

	/**
	 * Processes the test results and recommendations in the provided payload.
	 *
	 * @param array<string,mixed> $payload
	 *
	 * @return ToolResponse toolResponse instance containing all of the extracted test results and recommendations
	 */
	private function processToolResponse(array $payload): ToolResponse
	{
		$now = new \DateTime();

		// Generate the base tool response
		$toolResponse = $this->createToolResponse($payload);
		$this->tool = $toolResponse->getTool();

		$matchingPages = $this->pageRepository->findByUrls([$toolResponse->getUrl()]);
		$existingRecommendations = $this->getExistingRecommendations($toolResponse);

		foreach ($payload['results'] as $rawResult) {
			$testResult = $this->createTestResult($rawResult);
			$toolResponse->addTestResult($testResult);
			$this->entityManager->persist($testResult);

			$rawRecommendations = (array) ($rawResult['recommendations'] ?? []);

			foreach ($rawRecommendations as $rawRecommendation) {
				$rawRecommendation = $this->standardizeRawRecommendation($rawRecommendation);

				foreach ($matchingPages as $page) {
					$recommendationUniqueName = implode('_', [$testResult->getUniqueName(), $rawRecommendation['uniqueName']]);

					$matchingIdentifier = Recommendation::generateUniqueMatchingIdentifier(
						$page->getId(),
						$toolResponse->getTool(),
						$recommendationUniqueName
					);

					$recommendation = $existingRecommendations[$matchingIdentifier] ?? (new Recommendation());
					$recommendation->setTemplate($rawRecommendation['template'])
						->setParameters($rawRecommendation['params'])
						->setType($rawRecommendation['type'])
						->setUniqueName($recommendationUniqueName)
						->setParentResult($testResult)
						->setRelatedPage($page)
						->setIsCompleted(false)
						->setDateLastOccured($now);
					$this->entityManager->persist($recommendation);

					$this->updatedProjectsByPageId[$page->getId()] = $page->getProject();

					unset($existingRecommendations[$matchingIdentifier]);
				}
			}
		}

		// Mark recommendations that aren't present anymore as completed
		foreach ($existingRecommendations as $oldRecommendation) {
			$oldRecommendation->setDateCompleted($now);
			$this->entityManager->persist($oldRecommendation);
			$this->completedRecommendations[$oldRecommendation->getProject()->getId()][$oldRecommendation->getUniqueName()] = $oldRecommendation;
		}

		$this->entityManager->persist($toolResponse);
		$this->entityManager->flush();

		return $toolResponse;
	}

	/**
	 * Returns existing recommendations that match the provided ToolResponse's URL and tool.
	 * Results are indexed by their unique matching identifier.
	 *
	 * @return array<string,Recommendation>
	 */
	private function getExistingRecommendations(ToolResponse $toolResponse): array
	{
		$existingRecommendations = $this->recommendationRepository->findFromToolResponse($toolResponse);
		$recommendations = [];

		foreach ($existingRecommendations as $recommendation) {
			$recommendations[$recommendation->getUniqueMatchingIdentifier()] = $recommendation;
		}

		return $recommendations;
	}

	/**
	 * Standardizes the raw recommendation into the expected array format.
	 *
	 * @param string|array<int,mixed> $rawRecommendation
	 *
	 * @return array<string,mixed>
	 */
	private function standardizeRawRecommendation(string|array $rawRecommendation): array
	{
		// Fallback for tools still using the old format of string recommendations
		if (is_string($rawRecommendation)) {
			$rawRecommendation = [
				'template' => $rawRecommendation,
				'params' => null,
				'type' => Recommendation::TYPE_OPTIMIZATION,
				'uniqueName' => substr($rawRecommendation, 0, 255),
			];
		} elseif (is_array($rawRecommendation)) {
			$rawRecommendation = [
				'template' => $rawRecommendation[0],
				'params' => $rawRecommendation[1],
				'type' => ($rawRecommendation[2] ?? null) ?: Recommendation::TYPE_OPTIMIZATION,
				'uniqueName' => substr((string) $rawRecommendation[0], 0, 255),
			];
		}

		return $rawRecommendation;
	}

	/**
	 * Creates a TestResult instance from the raw test result array from a tool's response.
	 *
	 * @param array<string,mixed> $rawResult
	 */
	private function createTestResult(array $rawResult): TestResult
	{
		return (new TestResult())
			->setUniqueName($rawResult['uniqueName'])
			->setTitle($rawResult['title'])
			->setDescription($rawResult['description'])
			->setScore($rawResult['score'])
			->setWeight($rawResult['weight'] ?? null)
			->setSnippets($rawResult['snippets'] ?? [])
			->setDataTable($rawResult['table'] ?? []);
	}

	/**
	 * Creates a ToolResponse instance from the provided instance.
	 *
	 * @param array<string,mixed> $payload
	 */
	private function createToolResponse(array $payload): ToolResponse
	{
		$toolRequest = $payload['request'];
		$toolResponse = new ToolResponse();
		$toolResponse->setUrl($toolRequest['url'])
			->setTool($toolRequest['tool'])
			->setProcessingTime($payload['processingTime']);

		return $toolResponse;
	}

	/**
	 * @param array<string,mixed> $payload
	 */
	private function requestIsErrorNotice(array $payload): bool
	{
		return in_array($payload['type'], ['developerError', 'toolError']);
	}
}
