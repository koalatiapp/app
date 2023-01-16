<?php

namespace App\Tests\Backend\Functional\Api;

class TestingRequestTest extends AbstractApiTestCase
{
	public function testUserCanRequestTestingForProject()
	{
		$response = $this->apiRequest(
			url: "/api/testing_requests",
			method: "POST",
			payload: ["project" => "/api/projects/ew8BEeB2PO"],
			user: self::USER_TEST
		);
		$this->assertSame(202, $response->getStatusCode());
	}

	public function testUserCannotRequestTestingForProjecttItsNotAMemberOfAsComplete()
	{
		$response = $this->apiRequest(
			url: "/api/testing_requests",
			method: "POST",
			payload: ["project" => "/api/projects/ew8BEeB2PO"],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanRequestTestingForSpecificPages()
	{
		$response = $this->apiRequest(
			url: "/api/testing_requests",
			method: "POST",
			payload: ["project" => "/api/projects/ew8BEeB2PO", "pages" => ["/api/pages/ew8BEeB2PO"]],
			user: self::USER_TEST
		);
		$this->assertSame(202, $response->getStatusCode());
	}

	public function testUserCanRequestTestingForSpecificTools()
	{
		$response = $this->apiRequest(
			url: "/api/testing_requests",
			method: "POST",
			payload: ["project" => "/api/projects/ew8BEeB2PO", "tools" => ["@koalati/tool-seo"]],
			user: self::USER_TEST
		);
		$this->assertSame(202, $response->getStatusCode());
	}
}
