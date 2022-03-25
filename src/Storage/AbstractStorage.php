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
	public function __construct(
		protected Filesystem $filesystem,
		protected string $cdnBaseUrl,
	)
	{
	}

	/**
	 * Generates a URL to a provided object path in the external storage.
	 *
	 * @return string|null
	 */
	protected function generateUrl(string $path, bool $returnNullWhenMissing)
	{
		if ($returnNullWhenMissing && !$this->filesystem->fileExists($path)) {
			return null;
		}

		return rtrim($this->cdnBaseUrl, '/').'/'.ltrim($path, '/');
	}
}
