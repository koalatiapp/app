<?php

namespace App\Twig;

use App\Entity\Project;
use App\Storage\ProjectStorage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StorageExtension extends AbstractExtension
{
	public function __construct(private readonly ProjectStorage $projectStorage)
	{
	}

	public function getFilters(): array
	{
		return [
			new TwigFilter('favicon', $this->projectFavicon(...)),
			new TwigFilter('thumbnail', $this->projectThumbnail(...)),
		];
	}

	/**
	 * Return the URL of a project's favicon.
	 */
	public function projectFavicon(Project $project): string
	{
		return $this->projectStorage->faviconUrl($project);
	}

	/**
	 * Return the URL of a project's thumbnail.
	 */
	public function projectThumbnail(Project $project): string
	{
		return $this->projectStorage->thumbnailUrl($project);
	}
}
