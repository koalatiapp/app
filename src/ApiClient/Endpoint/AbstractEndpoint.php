<?php

namespace App\ApiClient\Endpoint;

use App\ApiClient\ClientInterface;

abstract class AbstractEndpoint
{
	/**
	 * @var ClientInterface;
	 */
	protected $client;

	public function __construct(ClientInterface $client)
	{
		$this->client = $client;
	}
}
