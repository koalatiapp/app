<?php

namespace App\Storage;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\Asset\Packages;

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
		protected Packages $packages,
		protected FilesystemManager $filesystemManager,
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

		if ($this->filesystemManager->isLocalFilesystem()) {
			return $this->packages->getUrl("storage/".ltrim($path, '/'));
		}

		return rtrim($this->cdnBaseUrl, '/').'/'.ltrim($path, '/');
	}
}
