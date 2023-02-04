<?php

namespace App\Tests\Backend\Functional\Api;

class TestResultTest extends AbstractApiTestCase
{
	public function testUserCanViewTestResultOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/test_results/ew8BEeB2PO",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("@id", $responseContent);
		$this->assertArrayHasKey("unique_name", $responseContent);
		$this->assertArrayHasKey("title", $responseContent);
		$this->assertArrayHasKey("description", $responseContent);
		$this->assertArrayHasKey("snippets", $responseContent);
		$this->assertArrayHasKey("data_table", $responseContent);
		$this->assertArrayHasKey("score", $responseContent);
		$this->assertArrayHasKey("weight", $responseContent);
	}

	public function testUserCannotViewTestResultOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/testing_status",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
