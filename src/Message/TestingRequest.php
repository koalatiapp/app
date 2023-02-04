<?php

namespace App\Message;

class TestingRequest
{
	/**
	 * @param array<int|string,string>|null $tools
	 * @param array<int|string,int>|null    $pageIds
	 */
	public function __construct(
		private ?int $projectId = null,

		/**
		 * Tool(s) to run.
		 */
		private ?array $tools = null,

		/**
		 * ID(s) of the pages on which to run the tools.
		 */
		private ?array $pageIds = null
	) {
	}

	public function getProjectId(): int
	{
		return $this->projectId;
	}

	/**
	 * @return array<int,string>
	 */
	public function getTools(): array
	{
		if (!$this->tools) {
			return [];
		}

		return array_values((array) $this->tools);
	}

	/**
	 * @return array<int,int>
	 */
	public function getPageIds(): array
	{
		if (!$this->pageIds) {
			return [];
		}

		return array_values((array) $this->pageIds);
	}
}
