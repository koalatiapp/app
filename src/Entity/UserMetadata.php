<?php

namespace App\Entity;

use App\Repository\UserMetadataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserMetadataRepository::class)]
class UserMetadata
{
	final public const TRIAL_ENDING_NOTICE_SENT = 'trial_ending_notice_sent';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	public function __construct(#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'metadata')]
		#[ORM\JoinColumn(nullable: false)]
		private User $user, #[ORM\Column(type: 'string', length: 255)]
		private string $name, #[ORM\Column(type: 'string', length: 255)]
		private string $value = '')
	{
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

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setValue(string $value): self
	{
		$this->value = $value;

		return $this;
	}
}
