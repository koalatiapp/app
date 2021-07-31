<?php

namespace App\Entity\Trait;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

trait OwnedEntity
{
	/**
	 * @var \App\Entity\User|null
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="personalProjects")
	 * @Groups({"default"})
	 */
	private $ownerUser;

	/**
	 * @var \App\Entity\Organization|null
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="projects")
	 * @Groups({"default"})
	 */
	private $ownerOrganization;

	public function getOwnerUser(): ?User
	{
		return $this->ownerUser;
	}

	public function setOwnerUser(?User $ownerUser): self
	{
		$this->ownerUser = $ownerUser;

		return $this;
	}

	public function getOwnerOrganization(): ?Organization
	{
		return $this->ownerOrganization;
	}

	public function setOwnerOrganization(?Organization $ownerOrganization): self
	{
		$this->ownerOrganization = $ownerOrganization;

		return $this;
	}

	public function getOwner(): Organization | User
	{
		return $this->getOwnerOrganization() ?: $this->getOwnerUser();
	}
}

/// @TODO: Figure out a good structure for traitts & interfaces
/// @TODO: Move the owner-related methods of Project to this trait
/// @TODO: Create an interface to match this trait
