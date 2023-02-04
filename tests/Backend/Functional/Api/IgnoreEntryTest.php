<?php

namespace App\Tests\Backend\Functional\Api;

class IgnoreEntryTest extends AbstractApiTestCase
{
	public function testUserCanListIgnoreEntriesOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/ignore_entries",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("date_created", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("tool", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("test", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("recommendation_unique_name", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("target_project", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("created_by", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("recommendation_title", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("scope_type", $responseContent["hydra:member"][0]);
	}

	public function testUserCannotListIgnoreEntriesOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/ignore_entries",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanViewIgnoreEntries()
	{
		$response = $this->apiRequest(
			url: "/api/ignore_entries/ew8BEeB2PO",
			method: "GET",
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotViewIgnoreEntriesOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/ignore_entries/ew8BEeB2PO",
			method: "GET",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCreateIgnoreEntryInProjectsTheyAreAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/ignore_entries",
			method: "POST",
			payload: ["recommendation" => "/api/recommendations/ew8BEeB2PO", "scope" => "project"],
			user: self::USER_TEST
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testUserCannotCreateIgnoreEntryInProjectsTheyAreNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/ignore_entries",
			method: "POST",
			payload: ["recommendation" => "/api/recommendations/ew8BEeB2PO", "scope" => "project"],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanDeleteIgnoreEntry()
	{
		$response = $this->apiRequest(
			url: "/api/ignore_entries/K1aJjAb3oj",
			method: "DELETE",
			user: self::USER_TEST
		);
		$this->assertSame(204, $response->getStatusCode());
	}
}
