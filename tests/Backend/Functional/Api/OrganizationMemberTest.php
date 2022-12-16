<?php

namespace App\Tests\Backend\Functional\Api;

class OrganizationMemberTest extends AbstractApiTestCase
{
	public function testUserCanListMembersOfOrganizationItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/1/members",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("first_name", $responseContent["hydra:member"][0]);
	}

	public function testUserCannotListMembersOfOrganizationItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/2/members",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(404, $response->getStatusCode());
	}

	public function testAdminCanEditMemberRole()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_VISITOR"]],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testNonAdminCannotEditMemberRole()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/1",
			method: "PATCH",
			payload: ["roles" => ["ROLE_MEMBER"]],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testAdminCanPromoteOtherMemberToAdmin()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_ADMIN"]],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testAdminCanRemoveTeamMember()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/3",
			method: "DELETE",
			user: self::USER_TEST
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testOwnerCanGiveTeamOwnershipToOtherMember()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_OWNER"]],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testAdminCannotDemoteOwner()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_MEMBER"]],
			user: self::USER_TEST
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
