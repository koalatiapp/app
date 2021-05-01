<?php

namespace App\Controller\Webhook;

use App\Entity\Testing\Recommendation;
use App\Entity\Testing\TestResult;
use App\Entity\Testing\ToolResponse;
use App\Exception\WebhookException;
use App\Repository\PageRepository;
use App\Repository\Testing\RecommendationRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestResultController extends AbstractController
{
	/**
	 * @Route("/webhook/test-result", name="webhook_test_result")
	 */
	public function testResult(Request $request, MessageBusInterface $bus, RecommendationRepository $recommendationRepository, PageRepository $pageRepository): Response
	{
		$payload = $this->getPayload($request);
		// @TODO: add handling of tool responses where `$payload["success"]` is false
		// @TODO: add a security check to webhook to ensure it comes from a legit source. This should probably be done via the firewall.
		$toolResponse = $this->processToolResponse($payload, $recommendationRepository, $pageRepository);

		// Communicate the new data to subscribed clients
		$mercureUpdate = $this->generateClientUpdate($toolResponse);
		$bus->dispatch($mercureUpdate);

		return new Response('The webhook request was handled successfully.');
	}

	private function generateClientUpdate(ToolResponse $toolResponse): Update
	{
		// @TODO: Implement Mercure update generation for tool results

		return new Update(
			'http://koalati.com/test-result/',
			json_encode($toolResponse->getId())
		);
	}

	/**
	 * Processes the test results and recommendations in the provided payload.
	 *
	 * @param array<string,mixed> $payload
	 *
	 * @return ToolResponse toolResponse instance containing all of the extracted test results and recommendations
	 */
	private function processToolResponse(array $payload, RecommendationRepository $recommendationRepository, PageRepository $pageRepository): ToolResponse
	{
		$now = new DateTime();
		$em = $this->getDoctrine()->getManager();

		// Generate the base tool response
		$toolResponse = $this->createToolResponse($payload);
		$matchingPages = $pageRepository->findByUrls([$toolResponse->getUrl()]);
		$existingRecommendations = $this->getExistingRecommendations($toolResponse, $recommendationRepository);

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
						->setDateLastOccured($now);
					$em->persist($recommendation);

					unset($existingRecommendations[$matchingIdentifier]);
				}
			}
		}

		// Mark recommendations that aren't present anymore as completed
		foreach ($existingRecommendations as $oldRecommendation) {
			$oldRecommendation->setDateCompleted($now);
			$em->persist($oldRecommendation);
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
	private function getExistingRecommendations(ToolResponse $toolResponse, RecommendationRepository $recommendationRepository): array
	{
		$existingRecommendations = $recommendationRepository->findFromToolResponse($toolResponse);
		$recommendations = [];

		foreach ($existingRecommendations as $recommendation) {
			$recommendations[$recommendation->getUniqueMatchingIdentifier()] = $recommendation;
		}

		return $recommendations;
	}

	/**
	 * Standardizes the raw recommendation into the expected array format.
	 *
	 * @param string|array<string,mixed> $rawRecommendation
	 *
	 * @return array<string,mixed>
	 */
	private function standardizeRawRecommendation(mixed $rawRecommendation): array
	{
		// Fallback for tools still using the old format of string recommendations
		if (is_string($rawRecommendation)) {
			$rawRecommendation = [
				'template' => $rawRecommendation,
				'params' => null,
				'type' => Recommendation::TYPE_ISSUE,
				'uniqueName' => substr($rawRecommendation, 0, 255),
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
	 * @throws WebhookException
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
	 * Detects errors in the request's payload and throws appropriate exceptions.
	 *
	 * @param array<string,mixed> $payload
	 *
	 * @throws WebhookException
	 */
	private function handlePayloadErrors(array $payload): void
	{
		switch ($payload['type']) {
			case 'developerError':
				$this->throwDeveloperError($payload);

			// no break
			case 'toolError':
				$this->throwToolError($payload);
		}
	}

	/**
	 * Throws an error for the Koalati developers to handle.
	 *
	 * @param array<mixed> $payload
	 *
	 * @throws WebhookException
	 */
	private function throwDeveloperError(array $payload): void
	{
		throw new WebhookException(sprintf("An error (developerError) occured on the Tools API: %s.\n
				 Request: %s\n
				 Error details: %s", $payload['message'] ?? 'no error message', json_encode($payload['request'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), json_encode($payload['error'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
	}

	/**
	 * Throws an error for the tool developers to handle.
	 *
	 * @param array<mixed> $payload
	 *
	 * @throws WebhookException
	 */
	private function throwToolError(array $payload): void
	{
		// @TODO: Handle toolError in tools result webhook (submit bug to tool developer)
		throw new WebhookException(sprintf("An error (toolError) occured on the Tools API for tool %s.\n
				 Request: %s\n
				 Error details: %s", $payload['request']['tool'], json_encode($payload['request'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), json_encode($payload['error'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)));
	}
}
