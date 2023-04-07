<?php

namespace App\Entity;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ActivityLogRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
	openapiContext: ["tags" => ['Activity Log']],
	operations: [
		new Get(controller: NotFoundAction::class, read: false, status: 404, openapi: false),
	]
)]
#[ApiResource(
	openapiContext: ["tags" => ['Activity Log']],
	normalizationContext: ["groups" => "activity_log.list"],
	operations: [new GetCollection()],
	order: ['dateCreated' => 'DESC'],
)]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact', 'organization' => 'exact', 'project' => 'exact', 'type' => 'exact', 'target' => 'exact'])]
#[ApiFilter(RangeFilter::class, properties: ['dateCreated'])]
#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
class ActivityLog
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\ManyToOne]
	#[Groups(['activity_log.list'])]
	private ?User $user = null;

	#[ORM\ManyToOne]
	#[Groups(['activity_log.list'])]
	private ?Organization $organization = null;

	#[ORM\ManyToOne]
	#[Groups(['activity_log.list'])]
	private ?Project $project = null;

	#[ORM\Column(length: 255)]
	#[Groups(['activity_log.list'])]
	private ?string $type = null;

	/** @var array<string,mixed> */
	#[ORM\Column(nullable: true)]
	#[Groups(['activity_log.list'])]
	private ?array $data = [];

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	#[Groups(['activity_log.list'])]
	private ?\DateTimeInterface $dateCreated = null;

	/**
	 * IRI of the main entity this activity log is about.
	 */
	#[ORM\Column(length: 512, nullable: true)]
	#[Groups(['activity_log.list'])]
	private ?string $target = null;

	/**
	 * Whether this activity log should be displayed in shared log listings,
	 * or whether it should be kept for internal purposes only.
	 *
	 * Public ex.: User X left a comment on a task Y in project Z.
	 * Internal ex.: User X logged in.
	 */
	#[ORM\Column(type: Types::BOOLEAN)]
	private bool $isInternal = true;

	public function __construct()
	{
		$this->dateCreated = new DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $user): self
	{
		$this->user = $user;

		return $this;
	}

	public function getOrganization(): ?Organization
	{
		return $this->organization;
	}

	public function setOrganization(?Organization $organization): self
	{
		$this->organization = $organization;

		return $this;
	}

	public function getProject(): ?Project
	{
		return $this->project;
	}

	public function setProject(?Project $project): self
	{
		$this->project = $project;

		return $this;
	}

	public function getType(): ?string
	{
		return $this->type;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	/** @return array<string,mixed> */
	public function getData(): array
	{
		return $this->data ?: [];
	}

	/** @param null|array<string,mixed> $data */
	public function setData(?array $data): self
	{
		$this->data = $data;

		return $this;
	}

	public function getDateCreated(): ?\DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(\DateTimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getTarget(): ?string
	{
		return $this->target;
	}

	public function setTarget(?string $target): self
	{
		$this->target = $target;

		return $this;
	}

	public function isInternal(): bool
	{
		return $this->isInternal;
	}

	public function isPublic(): bool
	{
		return !$this->isInternal;
	}

	public function makeInternal(): self
	{
		$this->isInternal = true;

		return $this;
	}

	public function makePublic(): self
	{
		$this->isInternal = false;

		return $this;
	}
}
