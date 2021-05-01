<?php

namespace App\Tests\Unit\Util;

use App\Util\Config;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \Config::
 */
class ConfigTest extends WebTestCase
{
	private Config $configHelper;

	public function setup()
	{
		self::bootKernel();
		$this->configHelper = self::$container->get('App\\Util\\Config');
	}

	/**
	 * Checks if a URL exists and returns a valid HTTP code (2xx).
	 *
	 * @covers \Config::get
	 */
	public function testGet()
	{
		$this->assertArrayHasKey(200, $this->configHelper->get('http_codes'), 'Loads JSON configuration files successfully.');
	}
}
