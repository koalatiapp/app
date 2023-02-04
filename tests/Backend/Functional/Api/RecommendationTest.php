<?php

namespace App\Tests\Backend\Functional\Api;

class RecommendationTest extends AbstractApiTestCase
{
	public function testUserCanListRecommendationsOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/recommendations",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("title", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("parameters", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("template", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("type", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("date_created", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("date_last_occured", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("date_completed", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("unique_matching_identifier", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("page_title", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("page_url", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("ignored", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("completed_by", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("is_completed", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("tool", $responseContent["hydra:member"][0]);
	}

	public function testUserCannotListRecommendationsOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/recommendations",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanViewRecommendation()
	{
		$response = $this->apiRequest(
			url: "/api/recommendations/ew8BEeB2PO",
			method: "GET",
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotViewRecommendationOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/recommendations/ew8BEeB2PO",
			method: "GET",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanMarkRecommendationAsCompleted()
	{
		$response = $this->apiRequest(
			url: "/api/recommendations/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_completed" => true],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotMarkRecommendationOfProjectItsNotAMemberOfAsComplete()
	{
		$response = $this->apiRequest(
			url: "/api/recommendations/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_completed" => true],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
