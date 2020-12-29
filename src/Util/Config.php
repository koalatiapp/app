<?php

namespace App\Util;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * The Config service facilitates access to application-specific configurations,
 * which are stored in the /config/app directory.
 */
class Config
{
	/**
	 * Path to the configuration directory.
	 *
	 * @var string
	 */
	protected $configDirPath;

	/**
	 * Array containing the parsed configuration of every existing.
	 *
	 * @var array<mixed>
	 */
	private static $loadedConfigurations = [];

	public function __construct(ParameterBagInterface $params)
	{
		$this->configDirPath = rtrim($params->get('kernel.project_dir'), '/').'/config/app';
	}

	/**
	 * Returns the configuration(s) for the specified filename and configuration key.
	 * The key is optional, and follows the dot access format (ex.: key.subkey.othersubkey).
	 * If the requested configuration cannot be found, NULL is returned.
	 *
	 * @return mixed
	 */
	public function get(string $filename, ?string $key = null)
	{
		if (isset(static::$loadedConfigurations[$filename])) {
			$data = static::$loadedConfigurations[$filename];
		} else {
			$locator = new FileLocator([$this->configDirPath]);
			$extensions = ['json', 'yml', 'php'];

			$filename = null;
			foreach ($extensions as $extension) {
				try {
					$filename = $locator->locate($filename.'.'.$extension, null, true);
					break;
				} catch (\Exception $exception) {
					// Nothing to do, just keep moving along and try to other extensions.
				}
			}

			if (!$filename) {
				return null;
			} else {
				switch ($extension) {
					case 'json':
						$data = json_decode(file_get_contents($filename), true);
						break;
					case 'php':
						$data = include $filename;
						break;
					case 'yml':
						$data = Yaml::parse(file_get_contents($filename));
						break;
					default:
						$data = null;
				}

				static::$loadedConfigurations[$filename] = $data;
			}
		}

		if ($key) {
			$subkeys = explode('.', $key);

			foreach ($subkeys as $subkey) {
				if (isset($data[$subkey])) {
					$data = $data[$subkey];
				} else {
					return null;
				}
			}
		}

		return $data;
	}
}
