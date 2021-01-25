<?php

namespace App\Message;

class SitemapRequest
{
	/**
	 * @var string
	 */
	private $websiteUrl;

	/**
	 * @var int|null
	 */
	private $projectId;

	public function __construct(string $websiteUrl, ?int $projectId = null)
	{
		$this->websiteUrl = $websiteUrl;
		$this->projectId = $projectId;
	}

	public function getWebsiteUrl(): string
	{
		return $this->websiteUrl;
	}

	public function getProjectId(): ?int
	{
		return $this->projectId;
	}
}
