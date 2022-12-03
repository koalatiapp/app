<?php

namespace App\Util\Testing;

use App\Entity\Project;
use App\Mercure\MercureEntityInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class TestingStatus implements MercureEntityInterface
{
	#[Groups(['default'])]
	private readonly bool $pending;

	#[Groups(['default'])]
	private readonly ?int $requestCount;

	#[Groups(['default'])]
	private readonly ?int $timeEstimate;

	#[Groups(['default'])]
	private readonly int $pageCount;

	#[Groups(['default'])]
	private readonly int $activePageCount;

	/**
	 * @param array<string,mixed> $data
	 */
	public function __construct(
		private readonly Project $project,
		array $data
	) {
		$this->pending = $data["pending"];
		$this->requestCount = $data["requestCount"];
		$this->timeEstimate = $data["timeEstimate"];

		// Add project pages data
		$this->pageCount = $project->getPages()->count();
		$this->activePageCount = $project->getActivePages()->count();
	}

	public function getId(): ?int
	{
		return $this->project->getId();
	}

	public function getProject(): Project
	{
		return $this->project;
	}

	public function getPending(): bool
	{
		return $this->pending;
	}

	public function getRequestCount(): ?int
	{
		return $this->requestCount;
	}

	public function getTimeEstimate(): ?int
	{
		return $this->timeEstimate;
	}

	public function getPageCount(): ?int
	{
		return $this->pageCount;
	}

	public function getActivePageCount(): ?int
	{
		return $this->activePageCount;
	}
}
