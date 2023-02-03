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

		$this->assertResponseIsSuccessful("$method - $url");
	}

	public function urlProvider()
	{
		yield ['/internal-api/public/link-metas?url=https://koalati.com', 'GET'];
		yield ['/internal-api/project/automated-testing-settings/tools', 'GET'];
		yield ['/internal-api/user/current', 'GET'];

		/*
		// @TODO: Add smoke test for POST/PUT/DELETE API routes
		yield ["/internal-api/search", "POST"];
		yield ["/internal-api/feedback/submit", "POST"];
		*/
	}
}
