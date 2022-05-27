<?php

namespace App\Tests\Unit\ApiClient\Endpoint;

use App\ApiClient\Endpoint\ToolsEndpoint;
use App\ApiClient\MockClient;
use App\ApiClient\MockServerlessClient;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \ToolsEndpoint::
 */
class ToolsEndpointTest extends WebTestCase
{
	/**
	 * @var ToolsEndpoint
	 */
	private $toolsEndpoint;

	public function setup(): void
	{
		self::bootKernel();

		$this->toolsEndpoint = new ToolsEndpoint(
			new MockClient(),
			new MockServerlessClient(),
			$this->createStub(LoggerInterface::class)
		);
	}

	/**
	 * @covers \ToolsEndpoint::request
	 */
	public function testRequest()
	{
		$this->assertTrue($this->toolsEndpoint->request('http://domain.com', '@koalati/tool-name'));
	}
}
