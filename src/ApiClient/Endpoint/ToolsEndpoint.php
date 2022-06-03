<?php

namespace App\ApiClient\Endpoint;

use Exception;

final class ToolsEndpoint extends AbstractEndpoint
{
	/**
	 * Enqueues a processing request.
	 *
	 * @param string|string[] $url      URL(s) on which to run the provided tools
	 * @param string|string[] $tool     Tool(s) on which to run the provided tools. The tool names are usually npm package names.
	 * @param int             $priority Priority of the request. The higher the number, the higher the priority.
	 */
	public function request(string|array $url, string|array $tool, int $priority = 1): bool
	{
		try {
			$response = $this->serverlessClient->request('POST', '/request', [
				'urls' => (array) $url,
				'tools' => (array) $tool,
				'priority' => $priority,
			]);
		} catch (Exception $exception) {
			$this->logger->error($exception->getMessage(), $exception->getTrace());

			$response = $this->client->request('POST', '/tools/request', [
				'url' => $url,
				'tool' => $tool,
				'priority' => $priority,
			]);
		}

		return (bool) $response['success'];
	}
}
