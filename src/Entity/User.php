<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", length=180, unique=true)
	 */
	private $email;

	/**
	 * @var array<string>
	 * @ORM\Column(type="json")
	 */
	private $roles = [];

	/**
	 * @var string The hashed password
	 * @ORM\Column(type="string")
	 */
	private $password;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $firstName;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", length=255)
	 */
	private $lastName;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $jobTitle;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, Project>
	 * @ORM\OneToMany(targetEntity=Project::class, mappedBy="ownerUser")
	 * @ORM\OrderBy({"dateCreated" = "ASC"})
	 */
	private $personalProjects;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, OrganizationMember>
	 * @ORM\OneToMany(targetEntity=OrganizationMember::class, mappedBy="user", orphanRemoval=true)
	 */
	private $organizationLinks;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, ProjectMember>
	 * @ORM\OneToMany(targetEntity=ProjectMember::class, mappedBy="user")
	 */
	private $projectLinks;

	public function __construct()
	{
		$this->personalProjects = new ArrayCollection();
		$this->organizationLinks = new ArrayCollection();
		$this->projectLinks = new ArrayCollection();
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
	public function getUsername(): string
	{
		return (string) $this->email;
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
		return (string) $this->password;
	}

	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getSalt()
	{
		return null;
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

	public function setFirstName(?string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	public function setLastName(string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	public function getJobTitle(): ?string
	{
		return $this->jobTitle;
	}

	public function setJobTitle(?string $jobTitle): self
	{
		$this->jobTitle = $jobTitle;

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

		// Add all projects of organizations where the user is an admin
		foreach ($this->getOrganizationLinks() as $organizationLink) {
			$organization = $organizationLink->getOrganization();
			$isAdminWithinOrg = in_array(OrganizationMember::ROLE_ADMIN, $organizationLink->getRoles());

			if ($isAdminWithinOrg) {
				foreach ($organization->getProjects() as $organizationProject) {
					if (!$projects->contains($organizationProject)) {
						$projects->add($organizationProject);
					}
				}
			}
		}

		return $projects;
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
		if (!$this->personalProjects->contains($project)) {
			$this->personalProjects[] = $project;
			$project->setOwnerUser($this);
		}

		return $this;
	}

	public function removePersonalProject(Project $project): self
	{
		if ($this->personalProjects->removeElement($project)) {
			// set the owning side to null (unless already changed)
			if ($project->getOwnerUser() === $this) {
				$project->setOwnerUser(null);
			}
		}

		return $this;
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
		if (!$this->organizationLinks->contains($organizationLink)) {
			$this->organizationLinks[] = $organizationLink;
			$organizationLink->setUser($this);
		}

		return $this;
	}

	public function removeOrganizationLink(OrganizationMember $organizationLink): self
	{
		if ($this->organizationLinks->removeElement($organizationLink)) {
			// set the owning side to null (unless already changed)
			if ($organizationLink->getUser() === $this) {
				$organizationLink->setUser(null);
			}
		}

		return $this;
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
		if (!$this->projectLinks->contains($projectLink)) {
			$this->projectLinks[] = $projectLink;
			$projectLink->addUser($this);
		}

		return $this;
	}

	public function removeProjectLink(ProjectMember $projectLink): self
	{
		if ($this->projectLinks->removeElement($projectLink)) {
			$projectLink->setUser(null);
		}

		return $this;
	}
}
