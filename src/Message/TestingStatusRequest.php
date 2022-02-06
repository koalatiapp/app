<?php

namespace App\Message;

class TestingStatusRequest
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private int $projectId
	) {
	}

	public function getProjectId(): int
	{
		return $this->projectId;
	}
}
