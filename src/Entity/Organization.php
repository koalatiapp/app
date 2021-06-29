<?php

namespace App\Entity;

use App\Entity\Testing\IgnoreEntry;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=OrganizationRepository::class)
 */
class Organization
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
	private ?string $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $slug;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, OrganizationMember>
	 * @ORM\OneToMany(targetEntity=OrganizationMember::class, mappedBy="organization", orphanRemoval=true)
	 * @Groups({"default"})
	 * @MaxDepth(1)
	 */
	private $members;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, Project>
	 * @ORM\OneToMany(targetEntity=Project::class, mappedBy="ownerOrganization")
	 * @Groups({"default"})
	 * @MaxDepth(1)
	 */
	private $projects;

	/**
	 * @ORM\OneToMany(targetEntity=IgnoreEntry::class, mappedBy="targetOrganization")
	 *
	 * @var \Doctrine\Common\Collections\Collection<int, IgnoreEntry>
	 */
	private Collection $ignoreEntries;

	public function __construct()
	{
		$this->members = new ArrayCollection();
		$this->projects = new ArrayCollection();
		$this->ignoreEntries = new ArrayCollection();
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

	/**
	 * @return Collection<int,IgnoreEntry>
	 */
	public function getIgnoreEntries(): Collection
	{
		return $this->ignoreEntries;
	}

	public function addIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		if (!$this->ignoreEntries->contains($ignoreEntry)) {
			$this->ignoreEntries[] = $ignoreEntry;
			$ignoreEntry->setTargetOrganization($this);
		}

		return $this;
	}

	public function removeIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		if ($this->ignoreEntries->removeElement($ignoreEntry)) {
			// set the owning side to null (unless already changed)
			if ($ignoreEntry->getTargetOrganization() === $this) {
				$ignoreEntry->setTargetOrganization(null);
			}
		}

		return $this;
	}

	public function getMemberFromUser(User $user): ?OrganizationMember
	{
		foreach ($this->getMembers() as $member) {
			if ($member->getUser() == $user) {
				return $member;
			}
		}

		return null;
	}

	/**
	 * @return array<int,string>
	 */
	public function getUserRoles(User $user): array
	{
		$member = $this->getMemberFromUser($user);

		return $member?->getRoles() ?: [];
	}
}
