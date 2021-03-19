<?php

namespace App\Message;

class ScreenshotRequest
{
	/**
	 * @var int
	 */
	private $projectId;

	public function __construct(int $projectId)
	{
		$this->projectId = $projectId;
	}

	public function getProjectId(): ?int
	{
		return $this->projectId;
	}
}
