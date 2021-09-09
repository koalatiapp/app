<?php

namespace App\Message;

class TestingRequest
{
	private int $projectId;

	/**
	 * Tool(s) to run.
	 *
	 * @var string|array<mixed,string>|null
	 */
	private $tools;

	/**
	 * ID(s) of the pages on which to run the tools.
	 *
	 * @var int|array<mixed,int>|null
	 */
	private $pageIds = null;

	/**
	 * Undocumented function.
	 *
	 * @param string|array<mixed,string>|null $tools
	 * @param int|array<mixed,int>|null       $pageIds
	 */
	public function __construct(int $projectId, mixed $tools = null, mixed $pageIds = null)
	{
		$this->projectId = $projectId;
		$this->tools = $tools;
		$this->pageIds = $pageIds;
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
