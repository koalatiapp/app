<?php

namespace App\Entity\Checklist;

use App\Repository\Checklist\ItemGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemGroupRepository::class)
 */
class ItemGroup
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Checklist::class, inversedBy="itemGroups")
	 * @ORM\JoinColumn(name="checklist_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private ?Checklist $checklist;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $name;

	/**
	 * @ORM\OneToMany(targetEntity=Item::class, mappedBy="parentGroup")
	 *
	 * @var Collection<int,Item>
	 */
	private Collection $items;

	public function __construct()
	{
		$this->items = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getChecklist(): ?Checklist
	{
		return $this->checklist;
	}

	public function setChecklist(?Checklist $checklist): self
	{
		$this->checklist = $checklist;

		return $this;
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
			$item->setParentGroup($this);
		}

		return $this;
	}

	public function removeItem(Item $item): self
	{
		if ($this->items->removeElement($item)) {
			// set the owning side to null (unless already changed)
			if ($item->getParentGroup() === $this) {
				$item->setParentGroup(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int,Item>
	 */
	public function getCompletedItems(): Collection
	{
		return $this->getItems()->filter(fn (Item $item) => $item->getIsCompleted());
	}

	public function getCompletionPercentage(): float
	{
		$completedItems = $this->getCompletedItems();
		$items = $this->getItems();

		return $completedItems->count() / max($items->count(), 1);
	}

	public function isCompleted(): bool
	{
		return (bool) $this->getItems()->filter(fn (Item $item) => !$item->getIsCompleted())->count();
	}
}
