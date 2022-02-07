<?php

namespace App\Controller\Webhook;

use App\Controller\AbstractController;
use App\Entity\Project;
use App\Entity\Testing\Recommendation;
use App\Entity\Testing\TestResult;
use App\Entity\Testing\ToolResponse;
use App\Exception\WebhookException;
use App\Mercure\UpdateDispatcher;
use App\Message\TestingStatusRequest;
use App\Repository\PageRepository;
use App\Repository\ProjectRepository;
use App\Repository\Testing\RecommendationRepository;
use App\Util\ClientMessageSerializer;
use App\Util\Testing\RecommendationGroup;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TestResultController extends AbstractController
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

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private RecommendationRepository $recommendationRepository,
		private ProjectRepository $projectRepository,
		private PageRepository $pageRepository,
		private UpdateDispatcher $updateDispatcher,
		private ClientMessageSerializer $serializer,
		private MessageBusInterface $bus,
	) {
	}

	/**
	 * @Route("/webhook/test-result", name="webhook_test_result")
	 */
	public function testResult(Request $request): Response
	{
		$payload = $this->getPayload($request);

		// Handle payload errors
		if ($this->requestIsErrorNotice($payload)) {
			$this->dispatchProjectStatusUpdates($payload);

			return new Response('The webhook request was handled successfully.');
		}

		// @TODO: add handling of tool responses where `$payload["success"]` is false
		// @TODO: add a security check to webhook to ensure it comes from a legit source. This should probably be done via the firewall.
		$this->processToolResponse($payload);

		// Communicate the new data to subscribed clients
		$this->dispatchClientUpdates();

		// Send project status update
		$this->dispatchProjectStatusUpdates($payload);

		return new Response('The webhook request was handled successfully.');
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
		$em = $this->getDoctrine()->getManager();

		foreach ($this->updatedProjectsByPageId as $project) {
			$em->refresh($project);
			$completedRecommendations = $this->completedRecommendations[$project->getId()] ?? [];

			// Process updates and creations
			foreach ($project->getActiveRecommendationGroups() as $group) {
				if ($group->getTool() != $this->tool) {
					continue;
				}

				$this->updateDispatcher->prepare($group, ['id' => $group->getId(), 'data' => $this->serializer->serialize($group)]);
				unset($completedRecommendations[$group->getUniqueName()]);
			}

			// Process completions
			foreach ($completedRecommendations as $completedRecommendation) {
				$group = new RecommendationGroup(new ArrayCollection([$completedRecommendation]));
				$this->updateDispatcher->prepare($group, ['id' => $group->getId()]);
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
		$now = new DateTime();
		$em = $this->getDoctrine()->getManager();

		// Generate the base tool response
		$toolResponse = $this->createToolResponse($payload);
		$this->tool = $toolResponse->getTool();

		$matchingPages = $this->pageRepository->findByUrls([$toolResponse->getUrl()]);
		$existingRecommendations = $this->getExistingRecommendations($toolResponse);

		foreach ($payload['results'] as $rawResult) {
			$testResult = $this->createTestResult($rawResult);
			$toolResponse->addTestResult($testResult);
			$em->persist($testResult);

			$rawRecommendations = (array) ($rawResult['recommendations'] ?? []);

			foreach ($rawRecommendations ?? [] as $rawRecommendation) {
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
					$em->persist($recommendation);

					$this->updatedProjectsByPageId[$page->getId()] = $page->getProject();

					unset($existingRecommendations[$matchingIdentifier]);
				}
			}
		}

		// Mark recommendations that aren't present anymore as completed
		foreach ($existingRecommendations as $oldRecommendation) {
			$oldRecommendation->setDateCompleted($now);
			$em->persist($oldRecommendation);
			$this->completedRecommendations[$oldRecommendation->getProject()->getId()][$oldRecommendation->getUniqueName()] = $oldRecommendation;
		}

		$em->persist($toolResponse);
		$em->flush();

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
	private function standardizeRawRecommendation(string | array $rawRecommendation): array
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
				'uniqueName' => substr($rawRecommendation[0], 0, 255),
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
	 * Extracts, deserializes and validates the payload from the request.
	 *
	 * @return array<string,mixed>
	 */
	private function getPayload(Request $request): array
	{
		$payload = json_decode($request->request->get('payload'), true);

		if (!$payload) {
			throw new WebhookException('An invalid payload was sent to the test results webhook.');
		}

		$this->handlePayloadErrors($payload);

		return $payload;
	}

	/**
	 * Detects errors in the request's payload and handles logging.
	 *
	 * @param array<string,mixed> $payload
	 */
	private function handlePayloadErrors(array $payload): void
	{
		switch ($payload['type']) {
			case 'developerError':
				$this->logDeveloperError($payload);

			// no break
			case 'toolError':
				$this->logToolError($payload);
		}
	}

	/**
	 * @param array<string,mixed> $payload
	 */
	private function requestIsErrorNotice(array $payload): bool
	{
		return in_array($payload['type'], ['developerError', 'toolError']);
	}

	/**
	 * Logs an error for the Koalati developers to handle.
	 *
	 * @param array<mixed> $payload
	 */
	private function logDeveloperError(array $payload): void
	{
		$this->logger->error(
			new WebhookException(
				sprintf("An error (developerError) occured on the Tools API: %s.\n
				Request: %s\n
				Error details: %s", $payload['message'] ?? 'no error message', json_encode($payload['request'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), json_encode($payload['error'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))
			)
		);
	}

	/**
	 * Logs an error for the tool developers to handle.
	 *
	 * @param array<mixed> $payload
	 */
	private function logToolError(array $payload): void
	{
		// @TODO: Handle toolError in tools result webhook (submit bug to tool developer)
		$this->logger->error(
			new WebhookException(
				sprintf("An error (toolError) occured on the Tools API for tool %s.\n
				Request: %s\n
				Error details: %s", $payload['request']['tool'], json_encode($payload['request'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), json_encode($payload['error'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT))
			)
		);
	}
}
