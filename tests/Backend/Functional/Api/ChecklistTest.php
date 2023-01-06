<?php

namespace App\Tests\Backend\Functional\Api;

class ChecklistTest extends AbstractApiTestCase
{
	public function testUserCanViewChecklist()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/checklist",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("@id", $responseContent);
		$this->assertArrayHasKey("project", $responseContent);
		$this->assertArrayHasKey("date_updated", $responseContent);
		$this->assertArrayHasKey("item_groups", $responseContent);
		$this->assertArrayHasKey("completed", $responseContent);
		$this->assertArrayHasKey("completion_percentage", $responseContent);

		$response = $this->apiRequest(
			url: "/api/checklists/ew8BEeB2PO",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("@id", $responseContent);
		$this->assertArrayHasKey("project", $responseContent);
		$this->assertArrayHasKey("date_updated", $responseContent);
		$this->assertArrayHasKey("item_groups", $responseContent);
		$this->assertArrayHasKey("completed", $responseContent);
		$this->assertArrayHasKey("completion_percentage", $responseContent);
	}

	public function testUserCannotViewChecklistOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/checklist",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode(), "Cannot access it through project endpoint.");
		$response = $this->apiRequest(
			url: "/api/checklists/ew8BEeB2PO",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode(), "Cannot access it through checklist endpoint.");
	}

	public function testUserCanViewItemGroup()
	{
		$response = $this->apiRequest(
			url: "/api/checklist_item_groups/ew8BEeB2PO",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("@id", $responseContent);
		$this->assertArrayHasKey("name", $responseContent);
		$this->assertArrayHasKey("checklist", $responseContent);
		$this->assertArrayHasKey("items", $responseContent);
		$this->assertArrayHasKey("completed", $responseContent);
	}

	public function testUserCannotViewItemGroupOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/checklist_item_groups/ew8BEeB2PO",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanViewItems()
	{
		$response = $this->apiRequest(
			url: "/api/checklists/ew8BEeB2PO/items",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("title", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("description", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("resource_urls", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("is_completed", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("comment_count", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("unresolved_comment_count", $responseContent["hydra:member"][0]);
	}

	public function testUserCanCheckItem()
	{
		$response = $this->apiRequest(
			url: "/api/checklist_items/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_completed" => true],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame(true, $response->getContent()['is_completed']);
	}

	public function testUserCanUncheckItem()
	{
		$response = $this->apiRequest(
			url: "/api/checklist_items/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_completed" => false],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertSame(false, $response->getContent()['is_completed']);
	}

	public function testUserCannotCheckItemOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/checklist_items/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_completed" => true],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}
}
