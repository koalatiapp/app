<?php

namespace App\Storage;

use App\Entity\Project;

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

	/**
	 * Return the URL of a project's thumbnail.
	 */
	public function thumbnailUrl(Project $project): string
	{
		$thumbnailPath = implode('/', [
			self::DIRECTORY,
			'thumbnail',
			md5($project->getUrl()),
		]);

		return $this->generateUrl($thumbnailPath, false);
	}
}
