<?php

namespace App\Tests\Backend\Functional;

/**
 * Tests the availability of all of the internal API's routes.
 */
class ApiAvailabilityTest extends AbstractAppTestCase
{
	/**
	 * @dataProvider urlProvider
	 */
	public function testApiCallIsSuccessful(string $url, string $method)
	{
		$this->client->request($method, $url);

		$this->assertResponseIsSuccessful();
	}

	public function urlProvider()
	{
		yield ['/api/checklist/items', 'GET'];
		yield ['/api/link-metas?url=https://koalati.com', 'GET'];
		yield ['/api/organization/members', 'GET'];
		yield ['/api/organization/ew8BEeB2PO', 'GET'];
		yield ['/api/project/automated-testing-settings/tools', 'GET'];
		yield ['/api/projects', 'GET'];
		yield ['/api/testing/ignore-entries', 'GET'];
		yield ['/api/testing/ignore-entries/ew8BEeB2PO', 'GET'];
		yield ['/api/testing/recommendations/groups', 'GET'];
		yield ['/api/testing/recommendations/groups/ew8BEeB2PO', 'GET'];
		yield ['/api/testing/recommendations', 'GET'];
		yield ['/api/testing/recommendations/ew8BEeB2PO', 'GET'];
		yield ['/api/user/current', 'GET'];

		// @TODO: Add smoke testing for project testing status
		// yield ['/api/testing/request/project-status/ew8BEeB2PO', 'GET'];

		/*
		// @TODO: Add smoke test for POST/PUT/DELETE API routes
		yield ["/api/testing/request/create", "POST"];
		yield ["/api/testing/recommendations/groups/{id}/complete", "PUT"];
		yield ["/api/testing/ignore-entries/{id}", "DELETE"];
		yield ["/api/testing/ignore-entries", "POST"];
		yield ["/api/search", "POST"];
		yield ["/api/project/automated-testing-settings/tools", "POST"];
		yield ["/api/checklist/items/{id}/toggle", "POST"];
		yield ["/api/feedback/submit", "POST"];
		yield ["/api/organization/members/ew8BEeB2PO", "DELETE"];
		yield ["/api/organization/members/ew8BEeB2PO/role", "POST"];
		yield ["/api/organization/members/ew8BEeB2PO/invitation", "POST"];
		*/
	}
}
