<?php

namespace App\Util\Testing;

use App\Entity\Project;
use App\Mercure\MercureEntityInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class TestingStatus implements MercureEntityInterface
{
	/**
	 * @Groups({"default"})
	 */
	private bool $pending;

	/**
	 * @Groups({"default"})
	 */
	private ?int $requestCount;

	/**
	 * @Groups({"default"})
	 */
	private ?int $timeEstimate;

	/**
	 * @param array<string,mixed> $data
	 */
	public function __construct(
		private Project $project,
		array $data)
	{
		$this->pending = $data["pending"];
		$this->requestCount = $data["requestCount"];
		$this->timeEstimate = $data["timeEstimate"];
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

}
