<?php

namespace App\Entity;

use App\Repository\OrganizationMemberRepository;
use DateTime;
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
	public const ROLE_VALUES = [
		self::ROLE_ADMIN => 100,
		self::ROLE_MEMBER => 10,
		self::ROLE_VISITOR => 1,
	];

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
	private ?Organization $organization;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organizationLinks")
	 * @ORM\JoinColumn(nullable=false)
	 * @Groups({"default"})
	 */
	private ?User $user;

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

	/**
	 * @param string|array<int,string> $roles
	 */
	public function __construct(Organization $organization, User $user, string | array $roles)
	{
		$this->setOrganization($organization);
		$this->setUser($user);
		$this->setRoles((array) $roles);
		$this->setDateCreated(new DateTime());
	}

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

	public function setHighestRole(string $highestRole): self
	{
		$includedRoles = [];
		$highestRoleValue = self::ROLE_VALUES[$highestRole];

		foreach (self::ROLE_VALUES as $role => $value) {
			if ($value <= $highestRoleValue) {
				$includedRoles[] = $role;
			}
		}

		$this->setRoles($includedRoles);

		return $this;
	}

	/**
	 * @Groups({"default"})
	 */
	public function getHighestRole(): ?string
	{
		$highestValue = 0;
		$highestRole = null;

		foreach ($this->getRoles() as $role) {
			if (self::ROLE_VALUES[$role] > $highestValue) {
				$highestValue = self::ROLE_VALUES[$role];
				$highestRole = $role;
			}
		}

		return $highestRole;
	}

	public function calculateRoleValue(): int
	{
		$highestRole = $this->getHighestRole();

		return self::ROLE_VALUES[$highestRole];
	}
}
