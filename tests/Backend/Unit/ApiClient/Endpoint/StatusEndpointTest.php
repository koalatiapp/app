<?php

namespace App\Tests\Unit\ApiClient\Endpoint;

use App\ToolsService\Endpoint\StatusEndpoint;
use App\ToolsService\MockClient;
use App\ToolsService\MockServerlessClient;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \StatusEndpoint::
 */
class StatusEndpointTest extends WebTestCase
{
	private StatusEndpoint $statusEndpoint;

	public function setup(): void
	{
		self::bootKernel();

		$this->statusEndpoint = new StatusEndpoint(
			new MockClient(),
			new MockServerlessClient(),
			$this->createStub(LoggerInterface::class)
		);
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
			'@koalati/tool-seo' => [
				'processing_time' => 3000,
			],
		], $this->statusEndpoint->timeEstimates());
	}
}
