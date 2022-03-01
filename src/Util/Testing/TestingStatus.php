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
	public bool $pending;

	/**
	 * @Groups({"default"})
	 */
	public int $requestCount;

	/**
	 * @Groups({"default"})
	 */
	public int $timeEstimate;

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
}
