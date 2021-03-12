<?php

namespace App\ApiClient\Endpoint;

class ToolsEndpoint extends AbstractEndpoint
{
	/**
	 * Enqueues a processing request.
	 *
	 * @param string|string[] $url      URL(s) on which to run the provided tools
	 * @param string|string[] $tool     Tool(s) on which to run the provided tools. The tool names are usually npm package names.
	 * @param int             $priority Priority of the request. The higher the number, the higher the priority.
	 *
	 * @return bool whether the request has successfully been added to the queue
	 */
	public function request(string | array $url, string | array $tool, int $priority = 1): bool
	{
		$response = $this->client->request('POST', '/tools/request', [
			'url' => $url,
			'tool' => $tool,
			'priority' => $priority,
		]);

		return $response['success'];
	}
}
