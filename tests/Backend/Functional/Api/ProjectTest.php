<?php

namespace App\Tests\Backend\Functional\Api;

class ProjectTest extends AbstractApiTestCase
{
	public function testUserCanListProjects()
	{
		$response = $this->apiRequest(
			url: "/api/projects",
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

	public function testUserCanListPersonalProjects()
	{
		$response = $this->apiRequest(
			url: "/api/projects?organizationOwner=null",
			user: self::USER_TEST,
		);
		$this->assertSame(200, $response->getStatusCode());

		$responseContent = $response->getContent();
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(1, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("name", $responseContent["hydra:member"][0]);
	}

	public function testUserCanListProjectsForOrganization()
	{
		$response = $this->apiRequest(
			url: "/api/projects?organizationOwner=ew8BEeB2PO",
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

	public function testUserCanAccessPersonalProject()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO",
			user: self::USER_TEST,
		);
		$this->assertSame(200, $response->getStatusCode());

		$responseContent = $response->getContent();
		$this->assertArrayHasKey("@id", $responseContent);
		$this->assertArrayHasKey("name", $responseContent);
		$this->assertArrayHasKey("url", $responseContent);
		$this->assertArrayHasKey("owner_user", $responseContent);
		$this->assertArrayHasKey("owner_organization", $responseContent);
	}

	public function testUserCannotAccessProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/0YpbRqXLl2",
			user: self::USER_TEST,
		);
		$this->assertSame(403, $response->getStatusCode());
		$this->assertArrayNotHasKey("@id", $response->getContent());
	}

	public function testUserCanEditProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO",
			method: "PATCH",
			payload: ["name" => "Koalati"],
			user: self::USER_TEST,
		);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame("Koalati", $response->getContent()["name"]);
	}

	public function testUserCannotEditProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/0YpbRqXLl2",
			method: "PATCH",
			payload: ["name" => "Koalati"],
			user: self::USER_TEST,
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserWithPlanCanCreateProject()
	{
		$response = $this->apiRequest(
			url: "/api/projects",
			method: "POST",
			payload: [
				"name" => "The Small Team Project",
				"url" => "https://sample.koalati.com",
				"owner_organization" => null,
			],
			user: self::USER_SMALL_TEAM_PLAN,
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testUserWithoutPlanCannotCreateProject()
	{
		$response = $this->apiRequest(
			url: "/api/projects",
			method: "POST",
			payload: [
				"name" => "The Small Team Project",
				"url" => "https://sample.koalati.com",
				"owner_organization" => null,
			],
			user: self::USER_NO_PLAN,
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserWithoutPlanCanCreateProjectInOrganizationItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects",
			method: "POST",
			payload: [
				"name" => "The Small Team Project",
				"url" => "https://sample.koalati.com",
				"owner_organization" => "/api/organizations/ew8BEeB2PO",
			],
			user: self::USER_NO_PLAN,
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testUserCannotCreateProjectInOrganizationItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects",
			method: "POST",
			payload: [
				"name" => "The Small Team Project",
				"url" => "https://sample.koalati.com",
				"owner_organization" => "/api/organizations/0YpbRqXLl2",
			],
			user: self::USER_NO_PLAN,
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanDeleteProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/DyaBWNJNxq",
			method: "DELETE",
			user: self::USER_SOLO_PLAN,
		);
		$this->assertSame(204, $response->getStatusCode());
	}

	public function testUserCannotDeleteProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/DkYJ1dbvM3",
			method: "DELETE",
			user: self::USER_SOLO_PLAN,
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
