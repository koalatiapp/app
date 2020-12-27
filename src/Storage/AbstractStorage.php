<?php

namespace App\Storage;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

/**
 * Storage service that acts as a base for all storage operations.
 *
 * @see https://flysystem.thephpleague.com/v2/docs/usage/filesystem-api/
 */
abstract class AbstractStorage
{
	/**
	 * Defines the name of the root diretory in which this type of storage takes place.
	 *
	 * @var string
	 */
	protected const DIRECTORY = '';

	/**
	 * @var \League\Flysystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * Base URL of the CDN.
	 * This is used to generate links to objects & files.
	 *
	 * @var string
	 */
	protected $cdnBaseUrl;

	public function __construct(string $region, string $version, string $endpoint, string $key, string $secret, string $bucket, string $cdnBaseUrl)
	{
		if (!static::DIRECTORY) {
			throw new \Exception(sprintf('Storage class %s has not implemented the required DIRECTORY constant.', static::class));
		}

		$client = new S3Client([
			'credentials' => [
				'key' => $key,
				'secret' => $secret,
			],
			'region' => $region,
			'version' => $version,
			'endpoint' => $endpoint,
		]);
		$adapter = new AwsS3V3Adapter($client, $bucket);
		$this->filesystem = new Filesystem($adapter);
		$this->cdnBaseUrl = $cdnBaseUrl;
	}

	/**
	 * Generates a URL to a provided object path in the external storage.
	 *
	 * @return string|null
	 */
	protected function generateUrl(string $path, bool $returnNullWhenMissing = true)
	{
		if ($returnNullWhenMissing && !$this->filesystem->fileExists($path)) {
			return null;
		}

		return rtrim($this->cdnBaseUrl, '/').'/'.ltrim($path, '/');
	}
}
