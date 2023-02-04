<?php

namespace App\Tests\Backend\Functional\Api;

class TestingStatusTest extends AbstractApiTestCase
{
	public function testUserCanViewTestingStatusOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/testing_status",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("page_count", $responseContent);
		$this->assertArrayHasKey("active_page_count", $responseContent);
		$this->assertArrayHasKey("time_estimate", $responseContent);
		$this->assertArrayHasKey("request_count", $responseContent);
		$this->assertArrayHasKey("pending", $responseContent);
	}

	public function testUserCannotViewTestingStatusOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/testing_status",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
