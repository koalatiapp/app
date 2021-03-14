<?php

namespace App\Tests\ApiClient\Endpoint;

use App\ApiClient\Endpoint\ToolsEndpoint;
use App\ApiClient\MockClient;
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

	public function setup()
	{
		self::bootKernel();
		$mockApiClient = new MockClient();
		$this->toolsEndpoint = new ToolsEndpoint($mockApiClient);
	}

	/**
	 * @covers \ToolsEndpoint::request
	 */
	public function testRequest()
	{
		$this->assertTrue($this->toolsEndpoint->request('http://domain.com', '@koalati/tool-name'));
	}
}
