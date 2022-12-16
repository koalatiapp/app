<?php

namespace App\Tests\Backend\Functional\Api;

class OrganizationTest extends AbstractApiTestCase
{
	public function testUserCanListOrganizationItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/organizations",
			user: self::USER_TEST,
		);
		$this->assertSame(200, $response->getStatusCode());

		$responseContent = $response->getContent();
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("name", $responseContent["hydra:member"][0]);
	}

	public function testUserCanAccessOrganizationItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/ew8BEeB2PO",
			user: self::USER_TEST,
		);
		$this->assertSame(200, $response->getStatusCode());

		$responseContent = $response->getContent();
		$this->assertArrayHasKey("@id", $responseContent);
		$this->assertArrayHasKey("name", $responseContent);
		$this->assertArrayHasKey("members", $responseContent);
		$this->assertIsArray($responseContent["members"]);
	}

	public function testUserCannotAccessOrganizationItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/0YpbRqXLl2",
			user: self::USER_BUSINESS_PLAN,
		);
		$this->assertSame(403, $response->getStatusCode());
		$this->assertArrayNotHasKey("@id", $response->getContent());
	}

	public function testAdminMemberCanEditOrganization()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/ew8BEeB2PO",
			method: "PATCH",
			payload: ["name" => "Koalati Inc.!"],
			user: self::USER_TEST,
		);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame("Koalati Inc.!", $response->getContent()["name"]);
	}

	public function testNonAdminMemberCannotEditOrganization()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/ew8BEeB2PO",
			method: "PATCH",
			payload: ["name" => "Team I Don't Manage"],
			user: self::USER_BUSINESS_PLAN,
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testSmallTeamPlanUserCanCreateOrganization()
	{
		$response = $this->apiRequest(
			url: "/api/organizations",
			method: "POST",
			payload: ["name" => "The Small Team"],
			user: self::USER_SMALL_TEAM_PLAN,
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testBusinessPlanUserCanCreateOrganization()
	{
		$response = $this->apiRequest(
			url: "/api/organizations",
			method: "POST",
			payload: ["name" => "The Business Team"],
			user: self::USER_BUSINESS_PLAN,
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testSoloPlanUserCannotCreateOrganization()
	{
		$response = $this->apiRequest(
			url: "/api/organizations",
			method: "POST",
			payload: ["name" => "Solo Team"],
			user: self::USER_SOLO_PLAN,
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
