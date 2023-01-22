<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Checklist\ChecklistTemplate;
use App\Entity\Testing\IgnoreEntry;
use App\Entity\Trait\CollectionManagingEntity;
use App\Entity\Trait\UserQuotaPreferencesTrait;
use App\Entity\Trait\UserSubscriptionTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Pyrrah\GravatarBundle\GravatarApi;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
#[ApiResource(
	normalizationContext: ["groups" => "user.read"],
	operations: [
		new Get(security: "is_granted('user_view', object)"),
	],
)]
#[ORM\Table(name: '`user`')]
#[ORM\Index(name: 'user_paddle_user_id', columns: ['paddle_user_id'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: 'email', message: 'user.error.unique_email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Stringable
{
	use CollectionManagingEntity;
	use UserSubscriptionTrait;
	use UserQuotaPreferencesTrait;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['default'])]
	protected int $id;

	#[ORM\Column(type: 'string', length: 180, unique: true)]
	#[Groups(['self'])]
	#[Assert\NotBlank]
	protected ?string $email = null;

	/**
	 * @var array<string>
	 */
	#[ORM\Column(type: 'json')]
	protected array $roles = [];

	/**
	 * @var string The hashed password
	 */
	#[ORM\Column(type: 'string')]
	#[Assert\NotBlank]
	protected string $password;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['user.read'])]
	#[Assert\NotBlank]
	protected ?string $firstName = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['user.read'])]
	protected ?string $lastName = null;

	#[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
	protected ?\DateTimeInterface $dateCreated;

	#[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
	protected ?\DateTimeInterface $dateLastLoggedIn;

	/**
	 * @var Collection<int, Project>
	 */
	#[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'ownerUser')]
	#[ORM\OrderBy(['dateCreated' => 'DESC'])]
	protected Collection $personalProjects;

	/**
	 * @var Collection<int, OrganizationMember>
	 */
	#[ORM\OneToMany(targetEntity: OrganizationMember::class, mappedBy: 'user', orphanRemoval: true)]
	protected Collection $organizationLinks;

	/**
	 * @var Collection<int, ProjectMember>
	 */
	#[ORM\OneToMany(targetEntity: ProjectMember::class, mappedBy: 'user')]
	protected Collection $projectLinks;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, ChecklistTemplate>
	 */
	#[ORM\OneToMany(targetEntity: ChecklistTemplate::class, mappedBy: 'ownerUser')]
	protected ?Collection $checklistTemplates;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, IgnoreEntry>
	 */
	#[ORM\OneToMany(targetEntity: IgnoreEntry::class, mappedBy: 'targetUser')]
	protected ?Collection $ignoreEntries;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, UserMetadata>
	 */
	#[ORM\OneToMany(targetEntity: UserMetadata::class, mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
	protected Collection $metadata;

	#[ORM\Column(type: 'boolean')]
	private bool $isVerified = false;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
		$this->dateLastLoggedIn = new \DateTime();
		$this->personalProjects = new ArrayCollection();
		$this->organizationLinks = new ArrayCollection();
		$this->projectLinks = new ArrayCollection();
		$this->ignoreEntries = new ArrayCollection();
		$this->checklistTemplates = new ArrayCollection();
		$this->metadata = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
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

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string
	{
		return (string) $this->email;
	}

	/**
	 * @deprecated 5.3 Deprecated in Symfony 5.3: use `getUserIdentifier` instead
	 */
	public function getUsername(): string
	{
		return $this->getUserIdentifier();
	}

	/**
	 * @deprecated 5.3 Deprecated in Symfony 5.3. Can be removed starting from 6.0
	 */
	public function getSalt(): ?string
	{
		return null;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}

	/**
	 * @param array<string> $roles
	 */
	public function setRoles(array $roles): self
	{
		$this->roles = $roles;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials(): void
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	public function setFirstName(string $firstName): self
	{
		$this->firstName = strip_tags($firstName);

		return $this;
	}

	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	public function setLastName(?string $lastName): self
	{
		$this->lastName = strip_tags($lastName);

		return $this;
	}

	public function getFullName(): string
	{
		return $this->getFirstName().' '.$this->getLastName();
	}

	public function getDateCreated(): \DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(\DateTimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getDateLastLoggedIn(): \DateTimeInterface
	{
		return $this->dateLastLoggedIn;
	}

	public function setDateLastLoggedIn(\DateTimeInterface $dateLastLoggedIn): self
	{
		$this->dateLastLoggedIn = $dateLastLoggedIn;

		return $this;
	}

	/**
	 * @return Collection<int, Project>
	 */
	public function getAllProjects(): Collection
	{
		// Start by adding personal projects
		$projects = $this->getPersonalProjects();

		// Add all projects in which the user is a member
		foreach ($this->getProjectLinks() as $projectLink) {
			$projects->add($projectLink->getProject());
		}

		// Add all projects of organizations in which the user is a member
		foreach ($this->getOrganizationLinks() as $organizationLink) {
			$organization = $organizationLink->getOrganization();
			foreach ($organization->getProjects() as $organizationProject) {
				if (!$projects->contains($organizationProject)) {
					$projects->add($organizationProject);
				}
			}
		}

		// Sort projects by date
		$projectArray = $projects->toArray();
		usort($projectArray, function (Project $projectA, Project $projectB) {
			return $projectA->getDateCreated()->getTimestamp() > $projectB->getDateCreated()->getTimestamp() ? -1 : 1;
		});

		return new ArrayCollection($projectArray);
	}

	/**
	 * @return Collection<int, Project>
	 */
	public function getPersonalProjects(): Collection
	{
		return $this->personalProjects;
	}

	public function addPersonalProject(Project $project): self
	{
		return $this->addCollectionElement('personalProjects', $project, 'OwnerUser');
	}

	public function removePersonalProject(Project $project): self
	{
		return $this->removeCollectionElement('personalProjects', $project, 'OwnerUser');
	}

	/**
	 * @return Collection<int, OrganizationMember>
	 */
	public function getOrganizationLinks(): Collection
	{
		return $this->organizationLinks;
	}

	public function addOrganizationLink(OrganizationMember $organizationLink): self
	{
		return $this->addCollectionElement('organizationLinks', $organizationLink, 'User');
	}

	public function removeOrganizationLink(OrganizationMember $organizationLink): self
	{
		return $this->removeCollectionElement('organizationLinks', $organizationLink, 'User');
	}

	/**
	 * @return Collection<int, ProjectMember>
	 */
	public function getProjectLinks(): Collection
	{
		return $this->projectLinks;
	}

	public function addProjectLink(ProjectMember $projectLink): self
	{
		return $this->addCollectionElement('projectLinks', $projectLink, 'User');
	}

	public function removeProjectLink(ProjectMember $projectLink): self
	{
		return $this->removeCollectionElement('projectLinks', $projectLink, 'User');
	}

	/**
	 * @return Collection<int,IgnoreEntry>
	 */
	public function getIgnoreEntries(): Collection
	{
		return $this->ignoreEntries;
	}

	public function addIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		return $this->addCollectionElement('ignoreEntries', $ignoreEntry, 'TargetUser');
	}

	public function removeIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		return $this->removeCollectionElement('ignoreEntries', $ignoreEntry, 'TargetUser');
	}

	#[Groups(['default'])]
	public function getAvatarUrl(int $size = 80): string
	{
		$gravatarApi = new GravatarApi(['default' => 'retro']);

		return $gravatarApi->getUrl($this->getEmail(), $size);
	}

	/**
	 * @return Collection<int, ChecklistTemplate>
	 */
	public function getChecklistTemplates(): Collection
	{
		return $this->checklistTemplates;
	}

	public function addChecklistTemplate(ChecklistTemplate $checklistTemplate): self
	{
		return $this->addCollectionElement('checklistTemplates', $checklistTemplate, 'OwnerUser');
	}

	public function removeChecklistTemplate(ChecklistTemplate $checklistTemplate): self
	{
		return $this->removeCollectionElement('checklistTemplates', $checklistTemplate, 'OwnerUser');
	}

	public function getDefaultOrganization(): ?Organization
	{
		$organizationLink = $this->getOrganizationLinks()->first() ?: null;

		return $organizationLink?->getOrganization();
	}

	public function getOwnedOrganization(): ?Organization
	{
		foreach ($this->getOrganizationLinks() as $organizationLink) {
			if ($organizationLink->getHighestRole() == OrganizationMember::ROLE_OWNER) {
				return $organizationLink->getOrganization();
			}
		}

		return null;
	}

	public function getMetadata(string $key): ?UserMetadata
	{
		foreach ($this->metadata as $metadata) {
			if ($metadata->getName() == $key) {
				return $metadata;
			}
		}

		return null;
	}

	public function getMetadataValue(string $key): string
	{
		return $this->getMetadata($key)?->getValue() ?: '';
	}

	public function setMetadata(string $key, string $value): self
	{
		$metadata = $this->getMetadata($key);

		if (!$metadata) {
			$metadata = new UserMetadata($this, $key, $value);
			$this->metadata->add($metadata);
		}

		$metadata->setValue($value);

		return $this;
	}

	public function __toString(): string
	{
		return (string) $this->getId();
	}

	public function isVerified(): bool
	{
		return $this->isVerified;
	}

	public function setIsVerified(bool $isVerified): self
	{
		$this->isVerified = $isVerified;

		return $this;
	}

	/**
	 * @return Collection<int,?Organization>
	 */
	public function getOrganizations(): Collection
	{
		return $this->getOrganizationLinks()->map(fn (?OrganizationMember $membership = null) => $membership->getOrganization());
	}
}
