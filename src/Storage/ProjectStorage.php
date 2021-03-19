<?php

namespace App\Storage;

use App\Entity\Project;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Visibility;

/**
 * Storage service for project-related storage operations (favicons, screenshots, etc.).
 */
class ProjectStorage extends AbstractStorage
{
	/**
	 * Defines the name of the root diretory in which this type of storage takes place.
	 *
	 * @var string
	 */
	protected const DIRECTORY = 'project';

	/**
	 * Return the URL of a project's favicon.
	 */
	public function faviconUrl(Project $project): string
	{
		$faviconPath = implode('/', [
			self::DIRECTORY,
			'favicon',
			md5($project->getUrl()),
		]);

		return $this->generateUrl($faviconPath, false);
	}

	private function getThumbnailPath(Project $project): string
	{
		return implode('/', [
			self::DIRECTORY,
			'thumbnail',
			md5($project->getUrl()),
		]);
	}

	/**
	 * Return the URL of a project's thumbnail.
	 */
	public function thumbnailUrl(Project $project): string
	{
		return $this->generateUrl($this->getThumbnailPath($project), false);
	}

	/**
	 * Uploads a project thumbnail.
	 */
	public function uploadThumbnail(Project $project, string $contents): void
	{
		$this->filesystem->write(
			$this->getThumbnailPath($project),
			$contents,
			[FlysystemConfig::OPTION_VISIBILITY => Visibility::PUBLIC]
		);
	}
}
