<?php

namespace App\ToolsService\Endpoint;

final class MetasEndpoint extends AbstractEndpoint
{
	/**
	 * Scrapes the meta data from a given URL.
	 *
	 * @return array<string,string>
	 */
	public function getMetas(string $url): array
	{
		$response = $this->serverlessClient->request('POST', '/metas', ['url' => $url]);

		return $response['metas'] ?? [];
	}
}
