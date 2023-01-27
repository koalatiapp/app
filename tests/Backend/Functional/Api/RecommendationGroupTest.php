<?php

namespace App\Tests\Backend\Functional\Api;

class RecommendationGroupTest extends AbstractApiTestCase
{
	public function testUserCanListRecommendationGroupsOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/recommendation_groups",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("title", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("template", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("type", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("unique_matching_identifier", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("tool", $responseContent["hydra:member"][0]);
	}

	public function testUserCannotListRecommendationGroupsOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/recommendation_groups",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanViewRecommendationGroup()
	{
		$response = $this->apiRequest(
			url: "/api/recommendation_groups/1Fme97835bbe2c468778abccfe77d4bb36b",
			method: "GET",
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotViewRecommendationGroupOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/recommendation_groups/1Fme97835bbe2c468778abccfe77d4bb36b",
			method: "GET",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCannotMarkRecommendationGroupOfProjectItsNotAMemberOfAsComplete()
	{
		$response = $this->apiRequest(
			url: "/api/recommendation_groups/1Fme97835bbe2c468778abccfe77d4bb36b",
			method: "PATCH",
			payload: ["is_completed" => true],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanMarkRecommendationGroupAsCompleted()
	{
		$response = $this->apiRequest(
			url: "/api/recommendation_groups/1Fme97835bbe2c468778abccfe77d4bb36b",
			method: "PATCH",
			payload: ["is_completed" => true],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}
}
