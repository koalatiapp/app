<?php

namespace App\Entity\Checklist;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\Checklist\ChecklistTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChecklistTemplateRepository::class)]
class ChecklistTemplate
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 255)]
	private ?string $name = null;

	#[ORM\Column(type: 'text', nullable: true)]
	private ?string $description = null;

	#[ORM\Column(type: 'boolean')]
	private ?bool $isPublic = null;

	/**
	 * @var array<mixed,mixed>
	 */
	#[ORM\Column(type: 'json')]
	private ?array $checklistContent = [];
	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'checklistTemplates')]
	#[ORM\JoinColumn(name: 'owner_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	private ?User $ownerUser = null;

	#[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'checklistTemplates')]
	#[ORM\JoinColumn(name: 'owner_organization_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	private ?Organization $ownerOrganization = null;

	/**
	 * @var Collection<int,Checklist>
	 */
	#[ORM\OneToMany(targetEntity: Checklist::class, mappedBy: 'template')]
	private Collection $childChecklists;

	public function __construct()
	{
		$this->childChecklists = new ArrayCollection();
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

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getIsPublic(): ?bool
	{
		return $this->isPublic;
	}

	public function isPublic(): ?bool
	{
		return $this->getIsPublic();
	}

	public function setIsPublic(bool $isPublic): self
	{
		$this->isPublic = $isPublic;

		return $this;
	}

	/**
	 * @return array<int,mixed>|null
	 */
	public function getChecklistContent(): ?array
	{
		return $this->checklistContent;
	}

	/**
	 * @param array<int,mixed> $checklistContent
	 */
	public function setChecklistContent(array $checklistContent): self
	{
		$this->checklistContent = $checklistContent;

		return $this;
	}

	/**
	 * @return Collection<int,Checklist>
	 */
	public function getChildChecklists(): Collection
	{
		return $this->childChecklists;
	}

	public function addChildChecklist(Checklist $childChecklist): self
	{
		if (!$this->childChecklists->contains($childChecklist)) {
			$this->childChecklists[] = $childChecklist;
			$childChecklist->setTemplate($this);
		}

		return $this;
	}

	public function removeChildChecklist(Checklist $childChecklist): self
	{
		if ($this->childChecklists->removeElement($childChecklist)) {
			// set the owning side to null (unless already changed)
			if ($childChecklist->getTemplate() === $this) {
				$childChecklist->setTemplate(null);
			}
		}

		return $this;
	}

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

	public function getOwner(): Organization|User
	{
		return $this->getOwnerOrganization() ?: $this->getOwnerUser();
	}
}
