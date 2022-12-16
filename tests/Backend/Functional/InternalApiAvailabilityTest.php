<?php

namespace App\Tests\Backend\Functional;

/**
 * Tests the availability of all of the internal API's routes.
 */
class InternalApiAvailabilityTest extends AbstractAppTestCase
{
	public function setup(): void
	{
		parent::setup();

		$this->loadUser(static::USER_TEST);
	}

	/**
	 * @dataProvider urlProvider
	 */
	public function testApiCallIsSuccessful(string $url, string $method)
	{
		$this->client->request($method, $url, [], [], ["HTTP_X-Requested-With" => "XMLHttpRequest"]);

		$this->assertResponseIsSuccessful();
	}

	public function urlProvider()
	{
		yield ['/internal-api/checklist/items', 'GET'];
		yield ['/internal-api/public/link-metas?url=https://koalati.com', 'GET'];
		yield ['/internal-api/organization/members', 'GET'];
		yield ['/internal-api/organization/ew8BEeB2PO', 'GET'];
		yield ['/internal-api/project/automated-testing-settings/tools', 'GET'];
		yield ['/internal-api/projects', 'GET'];
		yield ['/internal-api/testing/ignore-entries', 'GET'];
		yield ['/internal-api/testing/ignore-entries/ew8BEeB2PO', 'GET'];
		yield ['/internal-api/testing/recommendations/groups', 'GET'];
		yield ['/internal-api/testing/recommendations/groups/ew8BEeB2PO', 'GET'];
		yield ['/internal-api/testing/recommendations', 'GET'];
		yield ['/internal-api/testing/recommendations/ew8BEeB2PO', 'GET'];
		yield ['/internal-api/user/current', 'GET'];

		// @TODO: Add smoke testing for project testing status
		// yield ['/internal-api/testing/request/project-status/ew8BEeB2PO', 'GET'];

		/*
		// @TODO: Add smoke test for POST/PUT/DELETE API routes
		yield ["/internal-api/testing/request/create", "POST"];
		yield ["/internal-api/testing/recommendations/groups/{id}/complete", "PUT"];
		yield ["/internal-api/testing/ignore-entries/{id}", "DELETE"];
		yield ["/internal-api/testing/ignore-entries", "POST"];
		yield ["/internal-api/search", "POST"];
		yield ["/internal-api/project/automated-testing-settings/tools", "POST"];
		yield ["/internal-api/checklist/items/{id}/toggle", "POST"];
		yield ["/internal-api/feedback/submit", "POST"];
		yield ["/internal-api/organization/members/ew8BEeB2PO", "DELETE"];
		yield ["/internal-api/organization/members/ew8BEeB2PO/role", "POST"];
		yield ["/internal-api/organization/members/ew8BEeB2PO/invitation", "POST"];
		*/
	}
}
