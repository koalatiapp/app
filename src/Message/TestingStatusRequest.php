<?php

namespace App\Message;

class TestingStatusRequest
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly int $projectId
	) {
	}

	public function getProjectId(): int
	{
		return $this->projectId;
	}
}
