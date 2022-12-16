<?php

namespace App\Tests\Backend\Functional\Api;

class AuthenticationTest extends AbstractApiTestCase
{
	public function testSoloPlanUserCanAuthenticate()
	{
		$response = $this->authenticate(self::USER_SOLO_PLAN);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("token", $response->getContent());
		$this->assertArrayHasKey("refresh_token", $response->getContent());
	}

	public function testSmallTeamPlanUserCanAuthenticate()
	{
		$response = $this->authenticate(self::USER_SMALL_TEAM_PLAN);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("token", $response->getContent());
		$this->assertArrayHasKey("refresh_token", $response->getContent());
	}

	public function testBusinessPlanUserCanAuthenticate()
	{
		$response = $this->authenticate(self::USER_BUSINESS_PLAN);
		$this->assertSame(200, $response->getStatusCode());
		$this->assertArrayHasKey("token", $response->getContent());
		$this->assertArrayHasKey("refresh_token", $response->getContent());
	}

	public function testUserWithoutPlanCannotAuthenticate()
	{
		$response = $this->authenticate(self::USER_NO_PLAN);
		$this->assertSame(403, $response->getStatusCode());
		$this->assertArrayNotHasKey("token", $response->getContent());
		$this->assertArrayNotHasKey("refresh_token", $response->getContent());
	}
}
