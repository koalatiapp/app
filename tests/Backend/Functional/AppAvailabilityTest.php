<?php

namespace App\Tests\Backend\Functional;

/**
 * Tests the availability of all of the app's routes.
 */
class AppAvailabilityTest extends AbstractAppTestCase
{
	public function setup(): void
	{
		parent::setup();

		$this->loadUser(static::USER_TEST);
	}

	/**
	 * @dataProvider urlProvider
	 */
	public function testPublicPageIsSuccessful($url)
	{
		$this->client->request('GET', $url);

		$this->assertResponseIsSuccessful();
	}

	public function urlProvider()
	{
		yield ['/'];
		yield ['/projects'];
		yield ['/help'];
		yield ['/team/create'];
		yield ['/team/ew8BEeB2PO'];
		yield ['/team/ew8BEeB2PO/leave'];
		yield ['/team/ew8BEeB2PO/settings'];
		yield ['/team/invitation/ew8BEeB2PO/ad01bca12faaf7113230959d40f8cc12'];
		yield ['/project/ew8BEeB2PO/checklist'];
		yield ['/project/ew8BEeB2PO/checklist/step-by-step'];
		yield ['/project/create'];
		yield ['/project/current'];
		yield ['/project/ew8BEeB2PO/'];
		yield ['/project/ew8BEeB2PO/settings/team'];
		yield ['/project/ew8BEeB2PO/settings/checklist'];
		yield ['/project/ew8BEeB2PO/settings/automated-testing'];
		yield ['/project/ew8BEeB2PO/settings'];
		yield ['/project/ew8BEeB2PO/testing'];
		yield ['/account/contact-preferences'];
		yield ['/edit-profile'];
		yield ['/account/security'];
		yield ['/account/subscription'];
	}
}
