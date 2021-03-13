<?php

namespace App\Tests\ApiClient\Endpoint;

use App\ApiClient\Endpoint\StatusEndpoint;
use App\ApiClient\MockClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \StatusEndpoint::
 */
class StatusEndpointTest extends WebTestCase
{
	/**
	 * @var StatusEndpoint
	 */
	private $statusEndpoint;

	public function setup()
	{
		self::bootKernel();
		$mockApiClient = new MockClient();
		$this->statusEndpoint = new StatusEndpoint($mockApiClient);
	}

	/**
	 * @covers \StatusEndpoint::up
	 */
	public function testUp()
	{
		$this->assertTrue($this->statusEndpoint->up());
	}

	/**
	 * @covers \StatusEndpoint::uptime
	 */
	public function testUptime()
	{
		$this->assertSame(15000, $this->statusEndpoint->uptime());
	}

	/**
	 * @covers \StatusEndpoint::queue
	 */
	public function testQueue()
	{
		$this->assertSame([
			'unassignedRequests' => 0,
			'pendingRequests' => 1,
		], $this->statusEndpoint->queue());
	}

	/**
	 * @covers \StatusEndpoint::pendingRequests
	 */
	public function testPendingRequests()
	{
		$this->assertSame(1, $this->statusEndpoint->pendingRequests());
	}

	/**
	 * @covers \StatusEndpoint::unassignedRequests
	 */
	public function testUnassignedRequests()
	{
		$this->assertSame(0, $this->statusEndpoint->unassignedRequests());
	}

	/**
	 * @covers \StatusEndpoint::timeEstimates
	 */
	public function testTimeEstimates()
	{
		$this->assertSame([
			'lowPriority' => [
				'@koalati/tool-seo' => [
					'processing_time' => 6000,
					'completion_time' => 6865,
				],
			],
			'highPriority' => [
				'@koalati/tool-seo' => [
					'processing_time' => 3000,
					'completion_time' => 3865,
				],
			],
		], $this->statusEndpoint->timeEstimates());
	}
}
