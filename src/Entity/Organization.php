<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrganizationRepository::class)
 */
class Organization
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $slug;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, OrganizationMember>
	 * @ORM\OneToMany(targetEntity=OrganizationMember::class, mappedBy="organization", orphanRemoval=true)
	 */
	private $members;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, Project>
	 * @ORM\OneToMany(targetEntity=Project::class, mappedBy="ownerOrganization")
	 */
	private $projects;

	public function __construct()
	{
		$this->members = new ArrayCollection();
		$this->projects = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
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

	public function getSlug(): ?string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;

		return $this;
	}

	/**
	 * @return Collection<int, OrganizationMember>
	 */
	public function getMembers(): Collection
	{
		return $this->members;
	}

	public function addMember(OrganizationMember $member): self
	{
		if (!$this->members->contains($member)) {
			$this->members[] = $member;
			$member->setOrganization($this);
		}

		return $this;
	}

	public function removeMember(OrganizationMember $member): self
	{
		if ($this->members->removeElement($member)) {
			// set the owning side to null (unless already changed)
			if ($member->getOrganization() === $this) {
				$member->setOrganization(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, Project>
	 */
	public function getProjects(): Collection
	{
		return $this->projects;
	}

	public function addProject(Project $project): self
	{
		if (!$this->projects->contains($project)) {
			$this->projects[] = $project;
			$project->setOwnerOrganization($this);
		}

		return $this;
	}

	public function removeProject(Project $project): self
	{
		if ($this->projects->removeElement($project)) {
			// set the owning side to null (unless already changed)
			if ($project->getOwnerOrganization() === $this) {
				$project->setOwnerOrganization(null);
			}
		}

		return $this;
	}
}
