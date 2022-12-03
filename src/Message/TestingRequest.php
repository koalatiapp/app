<?php

namespace App\Message;

class TestingRequest
{
	/**
	 * Undocumented function.
	 *
	 * @param string|array<mixed,string>|null $tools
	 * @param int|array<mixed,int>|null       $pageIds
	 */
	public function __construct(
		private readonly int $projectId,
		/**
		 * Tool(s) to run.
		 */
		private $tools = null,
		/**
		 * ID(s) of the pages on which to run the tools.
		 */
		private $pageIds = null
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
