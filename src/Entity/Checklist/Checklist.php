<?php

namespace App\Entity\Checklist;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use App\Entity\Project;
use App\Repository\Checklist\ChecklistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
	openapiContext: ["tags" => ['Checklist']],
	normalizationContext: ["groups" => "checklist.read"],
	uriTemplate: '/projects/{projectId}/checklist',
	uriVariables: ['projectId' => new Link(fromClass: Project::class, fromProperty: 'checklist')],
	operations: [new Get(security: "is_granted('checklist_view', object)")],
)]
#[ApiResource(
	openapiContext: ["tags" => ['Checklist']],
	normalizationContext: ["groups" => "checklist.read"],
	operations: [
		new Get(security: "is_granted('checklist_view', object)"),
	],
)]
#[ORM\Entity(repositoryClass: ChecklistRepository::class)]
class Checklist
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['checklist.read'])]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: ChecklistTemplate::class, inversedBy: 'childChecklists')]
	private ?ChecklistTemplate $template = null;

	#[ORM\OneToOne(targetEntity: Project::class, inversedBy: 'checklist', cascade: ['persist', 'remove'])]
	#[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	#[Groups(['checklist.read'])]
	private ?Project $project = null;

	#[ORM\Column(type: 'datetime')]
	#[Groups(['checklist.read'])]
	private ?\DateTimeInterface $dateUpdated;

	/**
	 * @var Collection<int,ItemGroup>
	 */
	#[Groups(['checklist.read'])]
	#[ORM\OneToMany(targetEntity: ItemGroup::class, mappedBy: 'checklist', cascade: ['persist', 'remove'])]
	private Collection $itemGroups;

	/**
	 * @var Collection<int,Item>
	 */
	#[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'checklist', cascade: ['persist', 'remove'])]
	private Collection $items;

	public function __construct()
	{
		$this->itemGroups = new ArrayCollection();
		$this->items = new ArrayCollection();
		$this->dateUpdated = new \DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTemplate(): ?ChecklistTemplate
	{
		return $this->template;
	}

	public function setTemplate(?ChecklistTemplate $template): self
	{
		$this->template = $template;

		return $this;
	}

	public function getProject(): ?Project
	{
		return $this->project;
	}

	public function setProject(?Project $project): self
	{
		$this->project = $project;

		return $this;
	}

	public function getDateUpdated(): ?\DateTimeInterface
	{
		return $this->dateUpdated;
	}

	public function setDateUpdated(\DateTimeInterface $dateUpdated): self
	{
		$this->dateUpdated = $dateUpdated;

		return $this;
	}

	/**
	 * @return Collection<int,ItemGroup>
	 */
	public function getItemGroups(): Collection
	{
		return $this->itemGroups;
	}

	public function addItemGroup(ItemGroup $itemGroup): self
	{
		if (!$this->itemGroups->contains($itemGroup)) {
			$this->itemGroups[] = $itemGroup;
			$itemGroup->setChecklist($this);
		}

		return $this;
	}

	public function removeItemGroup(ItemGroup $itemGroup): self
	{
		if ($this->itemGroups->removeElement($itemGroup)) {
			// set the owning side to null (unless already changed)
			if ($itemGroup->getChecklist() === $this) {
				$itemGroup->setChecklist(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int,Item>
	 */
	public function getItems(): Collection
	{
		return $this->items;
	}

	public function addItem(Item $item): self
	{
		if (!$this->items->contains($item)) {
			$this->items[] = $item;
			$item->setChecklist($this);
		}

		return $this;
	}

	public function removeItem(Item $item): self
	{
		if ($this->items->removeElement($item)) {
			// set the owning side to null (unless already changed)
			if ($item->getChecklist() === $this) {
				$item->setChecklist(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int,Item>
	 */
	public function getCompletedItems(): Collection
	{
		return $this->getItems()->filter(fn (Item $item = null) => $item->getIsCompleted());
	}

	#[Groups(['checklist.read'])]
	public function getCompletionPercentage(): float
	{
		$completedItems = $this->getCompletedItems();
		$items = $this->getItems();

		return $completedItems->count() / max($items->count(), 1);
	}

	#[Groups(['checklist.read'])]
	public function isCompleted(): bool
	{
		return !$this->getItems()->filter(fn (Item $item = null) => !$item->getIsCompleted())->count();
	}
}
