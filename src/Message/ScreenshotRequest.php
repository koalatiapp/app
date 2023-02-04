<?php

namespace App\Message;

class ScreenshotRequest
{
	public function __construct(private readonly int $projectId)
	{
	}

	public function getProjectId(): ?int
	{
		return $this->projectId;
	}
}
