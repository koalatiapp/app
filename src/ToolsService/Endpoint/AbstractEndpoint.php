<?php

namespace App\ToolsService\Endpoint;

use App\ToolsService\ClientInterface;
use App\ToolsService\ServerlessClient;
use Psr\Log\LoggerInterface;

abstract class AbstractEndpoint
{
	public function __construct(
		protected ClientInterface $client,
		protected ServerlessClient $serverlessClient,
		protected LoggerInterface $logger,
	) {
	}
}
