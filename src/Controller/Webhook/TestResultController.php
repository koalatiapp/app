<?php

namespace App\Controller\Webhook;

use App\Controller\AbstractController;
use App\Exception\WebhookException;
use App\Message\TestingResultRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestResultController extends AbstractController
{
	/**
	 * @Route("/webhook/test-result", name="webhook_test_result")
	 */
	public function testResult(Request $request, MessageBusInterface $bus): Response
	{
		$payload = $this->getPayload($request);
		$resultProcessingRequest = new TestingResultRequest($payload);

		$bus->dispatch($resultProcessingRequest);

		return new Response('The webhook request was handled successfully.');
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
	 * Logs an error for the Koalati developers to handle.
	 *
	 * @param array<mixed> $payload
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function logDeveloperError(array $payload): void
	{
		// @TODO: Handle developerError in tools result webhook (submit bug to Koalati developers)
	}

	/**
	 * Logs an error for the tool developers to handle.
	 *
	 * @param array<mixed> $payload
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function logToolError(array $payload): void
	{
		// @TODO: Handle toolError in tools result webhook (submit bug to tool developer)
	}
}
