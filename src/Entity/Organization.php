<?php

namespace App\Entity;

use App\Entity\Checklist\ChecklistTemplate;
use App\Entity\Testing\IgnoreEntry;
use App\Mercure\MercureEntityInterface;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization implements MercureEntityInterface, \Stringable
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['default'])]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['default'])]
	private ?string $name = null;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['default'])]
	private ?string $slug = null;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, OrganizationMember>
	 */
	#[ORM\OneToMany(targetEntity: OrganizationMember::class, mappedBy: 'organization', orphanRemoval: true)]
	#[Groups(['members'])]
	#[MaxDepth(1)]
	private \Doctrine\Common\Collections\Collection $members;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, Project>
	 */
	#[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'ownerOrganization')]
	#[Groups(['projects'])]
	#[MaxDepth(1)]
	private \Doctrine\Common\Collections\Collection $projects;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, IgnoreEntry>
	 */
	#[ORM\OneToMany(targetEntity: IgnoreEntry::class, mappedBy: 'targetOrganization')]
	private Collection $ignoreEntries;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, OrganizationInvitation>
	 */
	#[ORM\OneToMany(targetEntity: OrganizationInvitation::class, mappedBy: 'organization', orphanRemoval: true)]
	private Collection $organizationInvitations;

	/**
	 * @var \Doctrine\Common\Collections\Collection<int, ChecklistTemplate>
	 */
	#[ORM\OneToMany(targetEntity: ChecklistTemplate::class, mappedBy: 'ownerOrganization')]
	private ?Collection $checklistTemplates;

	public function __construct()
	{
		$this->members = new ArrayCollection();
		$this->projects = new ArrayCollection();
		$this->ignoreEntries = new ArrayCollection();
		$this->organizationInvitations = new ArrayCollection();
		$this->checklistTemplates = new ArrayCollection();
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
		$this->name = strip_tags($name);

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

	/**
	 * @return Collection<int, OrganizationMember>
	 */
	public function getMembersSortedByRole(): Collection
	{
		$membersArray = $this->getMembers()->toArray();
		usort($membersArray, function (OrganizationMember $memberA, OrganizationMember $memberB) {
			if ($memberA->calculateRoleValue() != $memberB->calculateRoleValue()) {
				return $memberA->calculateRoleValue() > $memberB->calculateRoleValue() ? -1 : 1;
			}

			return strnatcasecmp($memberA->getUser()->getFullName(), $memberB->getUser()->getFullName());
		});

		return new ArrayCollection($membersArray);
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

	/**
	 * @return Collection<int,OrganizationInvitation>
	 */
	public function getOrganizationInvitations(): Collection
	{
		return $this->organizationInvitations;
	}

	public function addOrganizationInvitation(OrganizationInvitation $organizationInvitation): self
	{
		if (!$this->organizationInvitations->contains($organizationInvitation)) {
			$this->organizationInvitations[] = $organizationInvitation;
			$organizationInvitation->setOrganization($this);
		}

		return $this;
	}

	public function removeOrganizationInvitation(OrganizationInvitation $organizationInvitation): self
	{
		if ($this->organizationInvitations->removeElement($organizationInvitation)) {
			// set the owning side to null (unless already changed)
			if ($organizationInvitation->getOrganization() === $this) {
				$organizationInvitation->setOrganization(null);
			}
		}

		return $this;
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
		if (!$this->checklistTemplates->contains($checklistTemplate)) {
			$this->checklistTemplates[] = $checklistTemplate;
			$checklistTemplate->setOwnerOrganization($this);
		}

		return $this;
	}

	public function removeChecklistTemplate(ChecklistTemplate $checklistTemplate): self
	{
		if ($this->checklistTemplates->removeElement($checklistTemplate)) {
			// set the owning side to null (unless already changed)
			if ($checklistTemplate->getOwnerOrganization() === $this) {
				$checklistTemplate->setOwnerOrganization(null);
			}
		}

		return $this;
	}

	public function getOwner(): ?User
	{
		foreach ($this->getMembers() as $membership) {
			if ($membership->getHighestRole() == OrganizationMember::ROLE_OWNER) {
				return $membership->getUser();
			}
		}

		return null;
	}

	public function __toString(): string
	{
		return (string) $this->getName();
	}
}
