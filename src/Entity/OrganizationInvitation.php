<?php

namespace App\Entity;

use App\Repository\OrganizationInvitationRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=OrganizationInvitationRepository::class)
 */
class OrganizationInvitation
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $firstName;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $email;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $hash;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private ?DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private ?DateTimeInterface $dateExpired;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Groups({"default"})
	 */
	private ?DateTimeInterface $dateUsed;

	/**
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="organizationInvitations")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private Organization $organization;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(nullable=false)
	 */
	private User $inviter;

	public function __construct(string $firstName, string $email, Organization $organization, User $inviter)
	{
		$this->dateCreated = new DateTime();
		$this->dateExpired = new DateTime('+7 days');
		$this->dateUsed = null;
		$this->hash = bin2hex(random_bytes(16));
		$this->setFirstName($firstName);
		$this->setEmail($email);
		$this->setOrganization($organization);
		$this->setInviter($inviter);
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getHash(): ?string
	{
		return $this->hash;
	}

	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	public function setFirstName(string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	public function getDateCreated(): DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(DateTimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getDateExpired(): DateTimeInterface
	{
		return $this->dateExpired;
	}

	public function setDateExpired(DateTimeInterface $dateExpired): self
	{
		$this->dateExpired = $dateExpired;

		return $this;
	}

	public function getDateUsed(): ?DateTimeInterface
	{
		return $this->dateUsed;
	}

	public function setDateUsed(DateTimeInterface $dateUsed): self
	{
		$this->dateUsed = $dateUsed;

		return $this;
	}

	public function markAsUsed(): self
	{
		$this->setDateUsed(new DateTime());

		return $this;
	}

	public function isUsed(): bool
	{
		return $this->getDateUsed() !== null;
	}

	public function hasExpired(): bool
	{
		return new DateTime() >= $this->getDateExpired();
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

	public function getInviter(): ?User
	{
		return $this->inviter;
	}

	public function setInviter(?User $inviter): self
	{
		$this->inviter = $inviter;

		return $this;
	}
}
