<?php

namespace App\Storage;

use App\Util\SelfHosting;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;

class FilesystemManager
{
	private FilesystemOperator $filesystem;

	public function __construct(
		FilesystemAdapter $mainFilesystemAdapter,
		LocalFilesystemAdapter $localFilesystemAdapter,
		SelfHosting $selfHosting,
	) {
		$adapter = $mainFilesystemAdapter;

		if ($selfHosting->isSelfHosted()) {
			$adapter = $localFilesystemAdapter;
		}

		$this->filesystem = new Filesystem($adapter);
	}

	public function getFilesystem(): FilesystemOperator
	{
		return $this->filesystem;
	}
}
