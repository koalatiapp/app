<?php

namespace App\Storage;

use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Visibility;

/**
 * Storage service for user uploads.
 */
class UserUploadStorage extends AbstractStorage
{
	/**
	 * Defines the name of the root diretory in which this type of storage takes place.
	 *
	 * @var string
	 */
	protected const DIRECTORY = 'user_upload';

	/**
	 * Uploads the provided contents to a file on the CDN
	 * under a random name and returns the URL to that new file.
	 *
	 * @return string URL of the uploaded file
	 */
	public function upload(string $contents): string
	{
		$filename = uniqid().bin2hex(random_bytes(32));
		$filepath = self::DIRECTORY."/".$filename;

		$this->filesystem->write(
			$filepath,
			$contents,
			[FlysystemConfig::OPTION_VISIBILITY => Visibility::PUBLIC]
		);

		return $this->generateUrl($filepath, false);
	}
}
