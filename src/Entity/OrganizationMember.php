<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Repository\OrganizationMemberRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
	normalizationContext: ["groups" => "organization.read"],
	uriTemplate: '/organizations/{organizationId}/members',
	uriVariables: ['organizationId' => new Link(fromClass: Organization::class, fromProperty: 'members')],
	operations: [new GetCollection()],
)]
#[ApiResource(
	normalizationContext: ["groups" => "member.read"],
	denormalizationContext: ["groups" => "member.write"],
	operations: [
		new Get(security: "is_granted('view', object)"),
		new Patch(security: "is_granted('edit', object)"),
		new Delete(security: "is_granted('edit', object)"),
	],
)]
#[ORM\Entity(repositoryClass: OrganizationMemberRepository::class)]
class OrganizationMember
{
	final public const ROLE_OWNER = 'ROLE_OWNER';
	final public const ROLE_ADMIN = 'ROLE_ADMIN';
	final public const ROLE_MEMBER = 'ROLE_MEMBER';
	final public const ROLE_VISITOR = 'ROLE_VISITOR';
	final public const ROLE_VALUES = [
		self::ROLE_OWNER => 1000,
		self::ROLE_ADMIN => 100,
		self::ROLE_MEMBER => 10,
		self::ROLE_VISITOR => 1,
	];

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['member.read', 'organization.read'])]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'members')]
	#[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	#[Groups(['member.read'])]
	private ?Organization $organization;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'organizationLinks')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	#[Groups(['member.read', 'organization.read'])]
	private ?User $user;

	/**
	 * Available roles are:
	 * - `ROLE_OWNER`
	 * - `ROLE_ADMIN`
	 * - `ROLE_MEMBER`
	 * - `ROLE_VISITOR`.
	 *
	 * @var array<string>
	 */
	#[ORM\Column(type: 'json')]
	#[Groups(['member.read', 'member.write', 'organization.read'])]
	private array $roles = [];

	#[ORM\Column(type: 'datetime')]
	#[Groups(['member.read', 'organization.read'])]
	private \DateTimeInterface $dateCreated;

	/**
	 * @param string|array<int,string> $roles
	 */
	public function __construct(Organization $organization, User $user, string|array $roles)
	{
		$this->setOrganization($organization);
		$this->setUser($user);
		$this->setRoles((array) $roles);
		$this->setDateCreated(new \DateTime());
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

	#[Groups(['member.read', 'organization.read'])]
	public function getFirstName(): string
	{
		return $this->getUser()->getFirstName();
	}

	#[Groups(['member.read', 'organization.read'])]
	public function getLastName(): string
	{
		return $this->getUser()->getLastName();
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
	 * `ROLE_OWNER, `ROLE_ADMIN`, `ROLE_MEMBER`, `ROLE_VISITOR`.
	 *
	 * @param string[] $roles
	 */
	public function setRoles(array $roles): self
	{
		$this->roles = array_filter($roles, fn ($role) => in_array($role, [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MEMBER, self::ROLE_VISITOR]));

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

	#[Groups(['default'])]
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
