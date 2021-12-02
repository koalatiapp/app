<?php

namespace App\Tests\Backend\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the availability of all publicly accessible pages.
 */
class PublicAvailabilityTest extends WebTestCase
{
	/**
	 * @dataProvider publicUrlProvider
	 */
	public function testPublicPageIsSuccessful($url)
	{
		$client = self::createClient();
		$client->request('GET', $url);

		$this->assertResponseIsSuccessful();
	}

	public function publicUrlProvider()
	{
		yield ['/sign-up'];
		yield ['/reset-password'];
		yield ['/reset-password/check-email'];
		yield ['/login'];
	}

	/**
	 * @dataProvider redirectsUrlProvider
	 */
	public function testRedirectsAreSuccessful($url, $redirectLocation)
	{
		$client = self::createClient();
		$client->request('GET', $url);

		$this->assertResponseRedirects($redirectLocation, Response::HTTP_FOUND);
	}

	public function redirectsUrlProvider()
	{
		yield ['/logout', 'http://localhost/login'];
		yield ['/reset-password/reset/J3qZdnxB9SiyHy2hYAeoGmt7WhA0xrWx66C4cLgd', '/reset-password/reset'];
	}
}
