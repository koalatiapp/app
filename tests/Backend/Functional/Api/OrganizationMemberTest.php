<?php

namespace App\Tests\Backend\Functional\Api;

class OrganizationMemberTest extends AbstractApiTestCase
{
	public function testUserCanListMembersOfOrganizationItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/organizations/ew8BEeB2PO/members",
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
			url: "/api/organizations/0YpbRqXLl2/members",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testAdminCanEditMemberRole()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/0YpbRqXLl2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_VISITOR"]],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testNonAdminCannotEditMemberRole()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/ew8BEeB2PO",
			method: "PATCH",
			payload: ["roles" => ["ROLE_MEMBER"]],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testAdminCanPromoteOtherMemberToAdmin()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/0YpbRqXLl2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_ADMIN"]],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testAdminCanRemoveTeamMember()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/Zv2B8pBzrY",
			method: "DELETE",
			user: self::USER_TEST
		);
		$this->assertSame(204, $response->getStatusCode());
	}

	public function testOwnerCanGiveTeamOwnershipToOtherMember()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/0YpbRqXLl2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_OWNER"]],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testAdminCannotDemoteOwner()
	{
		$response = $this->apiRequest(
			url: "/api/organization_members/0YpbRqXLl2",
			method: "PATCH",
			payload: ["roles" => ["ROLE_MEMBER"]],
			user: self::USER_TEST
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
