<?php

namespace App\Tests\Backend\Unit\Util;

use App\Util\Config;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \Config::
 */
class ConfigTest extends WebTestCase
{
	private Config $configHelper;

	public function setup(): void
	{
		self::bootKernel();
		$this->configHelper = self::$container->get('App\\Util\\Config');
		$this->configHelper->setConfigDirectory('tests/stub/config');
	}

	/**
	 * Checks if a URL exists and returns a valid HTTP code (2xx).
	 *
	 * @covers \Config::get
	 */
	public function testGet()
	{
		$expectedConfiguration = [
			'scope' => [
				'name' => 'test',
				'values' => [1, 2, 3],
			],
		];
		$this->assertSame($expectedConfiguration, $this->configHelper->get('json'), 'Loads JSON configuration files successfully (.json extension).');
		$this->assertSame($expectedConfiguration, $this->configHelper->get('yaml'), 'Loads YAML configuration files successfully (.yaml extension).');
		$this->assertSame($expectedConfiguration, $this->configHelper->get('yml'), 'Loads YAML configuration files successfully (.yml extension).');
		$this->assertSame($expectedConfiguration, $this->configHelper->get('php'), 'Loads PHP configuration files successfully (.php extension).');
	}
}
