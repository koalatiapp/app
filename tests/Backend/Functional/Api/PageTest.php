<?php

namespace App\Tests\Backend\Functional\Api;

class PageTest extends AbstractApiTestCase
{
	public function testUserCanListPagesOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/pages",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("title", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("url", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("date_updated", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("http_code", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("project", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("is_ignored", $responseContent["hydra:member"][0]);
	}

	public function testUserCannotListPagesOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/0YpbRqXLl2/pages",
			user: self::USER_TEST
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanEditPageIgnoredStatus()
	{
		$response = $this->apiRequest(
			url: "/api/pages/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_ignored" => true],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotEditPageIgnoredStatusOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/pages/WYVJD8JZoK",
			method: "PATCH",
			payload: ["is_ignored" => true],
			user: self::USER_TEST
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
