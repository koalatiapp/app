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
	 * Path to the root of the project (without trailing slash).
	 *
	 * @var string
	 */
	protected $projectRootPath;

	/**
	 * Array containing the parsed configuration of every existing.
	 *
	 * @var array<mixed>
	 */
	private static array $loadedConfigurations = [];

	/**
	 * @param string $configDirectory the relative path of the directory containing the configurations
	 */
	public function __construct(ParameterBagInterface $params, string $configDirectory = '/config/app')
	{
		$this->projectRootPath = rtrim($params->get('kernel.project_dir'), '/');
		$this->setConfigDirectory($configDirectory);
	}

	public function setConfigDirectory(string $relativePath): self
	{
		$this->configDirPath = '/'.trim($relativePath, '/');

		return $this;
	}

	public function getConfigDirectory(): string
	{
		return $this->configDirPath;
	}

	public function getConfigDirectoryFullPath(): string
	{
		return $this->projectRootPath.$this->configDirPath;
	}

	/**
	 * Returns the configuration(s) for the specified filename and configuration key.
	 * The key is optional, and follows the dot access format (ex.: key.subkey.othersubkey).
	 * If the requested configuration cannot be found, NULL is returned.
	 */
	public function get(string $filename, ?string $key = null): mixed
	{
		if (!isset(self::$loadedConfigurations[$filename])) {
			self::$loadedConfigurations[$filename] = $this->loadDataFromFile($filename);
		}

		$data = self::$loadedConfigurations[$filename];

		if ($key) {
			$subkeys = explode('.', $key);

			foreach ($subkeys as $subkey) {
				if (!isset($data[$subkey])) {
					return null;
				}
				$data = $data[$subkey];
			}
		}

		return $data;
	}

	protected function loadDataFromFile(string $filename): mixed
	{
		$locator = new FileLocator([$this->getConfigDirectoryFullPath()]);
		$extensions = ['json', 'yml', 'yaml', 'php'];

		$fullFilename = null;
		foreach ($extensions as $extension) {
			try {
				$fullFilename = $locator->locate($filename.'.'.$extension, null, true);
				break;
			} catch (\Exception $exception) {
				// Nothing to do, just keep moving along and try to other extensions.
			}
		}

		if (!$fullFilename) {
			return null;
		}

		return match ($extension) {
			'json' => json_decode(file_get_contents($fullFilename), true, 512, JSON_THROW_ON_ERROR),
			'php' => include $fullFilename,
			'yml', 'yaml' => Yaml::parse(file_get_contents($fullFilename)),
		};
	}
}
