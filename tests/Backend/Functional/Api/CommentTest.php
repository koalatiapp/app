<?php

namespace App\Tests\Backend\Functional\Api;

class CommentTest extends AbstractApiTestCase
{
	public function testUserCanListCommentsOfProjectItsAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/comments",
			user: self::USER_TEST
		);
		$responseContent = $response->getContent();
		$this->assertSame(200, $response->getStatusCode());
		$this->assertEquals("hydra:Collection", $responseContent["@type"]);
		$this->assertGreaterThan(0, $responseContent["hydra:totalItems"]);
		$this->assertIsArray($responseContent["hydra:member"]);
		$this->assertArrayHasKey("@id", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("content", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("text_content", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("author", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("author_name", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("date_created", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("project", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("checklist_item", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("thread", $responseContent["hydra:member"][0]);
		$this->assertArrayHasKey("is_resolved", $responseContent["hydra:member"][0]);
	}

	public function testUserCannotListCommentsOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/projects/ew8BEeB2PO/comments",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanViewComment()
	{
		$response = $this->apiRequest(
			url: "/api/comments/ew8BEeB2PO",
			method: "GET",
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotViewCommentOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/comments/ew8BEeB2PO",
			method: "GET",
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanResolveComment()
	{
		$response = $this->apiRequest(
			url: "/api/comments/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_resolved" => true],
			user: self::USER_TEST
		);
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testUserCannotResolveCommentOfProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/pages/ew8BEeB2PO",
			method: "PATCH",
			payload: ["is_resolved" => true],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanPublishComment()
	{
		$response = $this->apiRequest(
			url: "/api/comments",
			method: "POST",
			payload: [
				"content" => "<p>Some <strong>HTML!</strong> content submitted via the API.</p>",
				"checklist_item" => "/api/checklist_items/ew8BEeB2PO",
			],
			user: self::USER_TEST
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testUserCannotPublishCommentInProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/comments",
			method: "POST",
			payload: [
				"content" => "<p>Some <strong>HTML!</strong> content submitted via the API.</p>",
				"checklist_item" => "/api/checklist_items/ew8BEeB2PO",
			],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testUserCanReplyToComment()
	{
		$response = $this->apiRequest(
			url: "/api/comments",
			method: "POST",
			payload: [
				"content" => "<p>This is a reply to an existing comment.</p>",
				"thread" => "/api/comments/ew8BEeB2PO",
			],
			user: self::USER_TEST
		);
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testUserCannotReplyToCommentInProjectItsNotAMemberOf()
	{
		$response = $this->apiRequest(
			url: "/api/comments",
			method: "POST",
			payload: [
				"content" => "<p>This is a reply to an existing comment.</p>",
				"thread" => "/api/comments/ew8BEeB2PO",
			],
			user: self::USER_SMALL_TEAM_PLAN
		);
		$this->assertSame(403, $response->getStatusCode());
	}

	public function testSubmittedCommentContentIsSanitized()
	{
		$response = $this->apiRequest(
			url: "/api/comments",
			method: "POST",
			payload: [
				"content" => "<p>This is a comment with Javascript <script>alert('hacked!!!')</script> and <strong onclick='alert(\"hacked again!\")'>dodgy attributes</strong>.</p>",
				"thread" => "/api/comments/ew8BEeB2PO",
			],
			user: self::USER_TEST
		);
		$this->assertSame("<p>This is a comment with Javascript  and <strong>dodgy attributes</strong>.</p>", $response->getContent()['content']);
	}
}
