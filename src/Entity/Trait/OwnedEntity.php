<?php

namespace App\Entity\Trait;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

trait OwnedEntity
{
	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(name="owner_user_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?User $ownerUser;

	/**
	 * @ORM\ManyToOne(targetEntity=Organization::class)
	 * @ORM\JoinColumn(name="owner_organization_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?Organization $ownerOrganization;

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
