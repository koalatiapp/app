<?php

namespace App\Entity\Checklist;

use App\Entity\Trait\OwnedEntity;
use App\Repository\Checklist\ChecklistTemplateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChecklistTemplateRepository::class)
 */
class ChecklistTemplate
{
	use OwnedEntity;

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
	 * @ORM\Column(type="text", nullable=true)
	 */
	private ?string $description;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private ?bool $isPublic;

	/**
	 * @ORM\Column(type="json")
	 *
	 * @var array<mixed,mixed>
	 */
	private ?array $checklistContent = [];

	/**
	 * @ORM\OneToMany(targetEntity=Checklist::class, mappedBy="template")
	 *
	 * @var Collection<int,Checklist>
	 */
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
}