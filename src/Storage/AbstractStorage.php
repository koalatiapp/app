<?php

namespace App\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;

/**
 * Storage service that acts as a base for all storage operations.
 *
 * @see https://flysystem.thephpleague.com/v2/docs/usage/filesystem-api/
 */
abstract class AbstractStorage
{
	protected FilesystemOperator $filesystem;

	public function __construct(
		protected string $cdnBaseUrl,
		FilesystemManager $filesystemManager,
	) {
		$this->filesystem = $filesystemManager->getFilesystem();
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
