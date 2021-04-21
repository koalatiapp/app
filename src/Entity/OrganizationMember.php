<?php

namespace App\Entity;

use App\Repository\OrganizationMemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrganizationMemberRepository::class)
 */
class OrganizationMember
{
	public const ROLE_ADMIN = 'ROLE_ADMIN';
	public const ROLE_MEMBER = 'ROLE_MEMBER';
	public const ROLE_VISITOR = 'ROLE_VISITOR';

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="members")
	 * @ORM\JoinColumn(nullable=false)
	 * @Groups({"default"})
	 */
	private Organization $organization;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organizationLinks")
	 * @ORM\JoinColumn(nullable=false)
	 * @Groups({"default"})
	 */
	private User $user;

	/**
	 * @var array<string>
	 * @ORM\Column(type="json")
	 * @Groups({"default"})
	 */
	private array $roles = [];

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private \DateTimeInterface $dateCreated;

	public function getId(): ?int
	{
		return $this->id;
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

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $user): self
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @return string[]|null
	 */
	public function getRoles(): ?array
	{
		return $this->roles;
	}

	/**
	 * Defines the role of the member within the organization.
	 * Allowed array values are the following \App\Entity\OrganizationMember constants:
	 * `ROLE_ADMIN`, `ROLE_MEMBER`, `ROLE_VISITOR`.
	 *
	 * @param string[] $roles
	 */
	public function setRoles(array $roles): self
	{
		$this->roles = array_filter($roles, function ($role) {
			return in_array($role, [self::ROLE_ADMIN, self::ROLE_MEMBER, self::ROLE_VISITOR]);
		});

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
}
