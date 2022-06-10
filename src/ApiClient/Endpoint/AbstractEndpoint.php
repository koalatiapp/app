<?php

namespace App\ApiClient\Endpoint;

use App\ApiClient\ClientInterface;
use App\ApiClient\ServerlessClient;
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
