<?php

namespace App\Entity\Checklist;

use App\Repository\Checklist\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
class Item
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Checklist::class, inversedBy="items")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private ?Checklist $checklist;

	/**
	 * @ORM\ManyToOne(targetEntity=ItemGroup::class, inversedBy="items")
	 */
	private ?ItemGroup $parentGroup;

	/**
	 * @ORM\Column(type="text")
	 */
	private ?string $title;

	/**
	 * @ORM\Column(type="text")
	 */
	private ?string $description;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 *
	 * @var array<int,string>
	 */
	private ?array $resourceUrls = [];

	/**
	 * @ORM\Column(type="boolean")
	 */
	private ?bool $isCompleted = false;

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

	public function getParentGroup(): ?ItemGroup
	{
		return $this->parentGroup;
	}

	public function setParentGroup(?ItemGroup $parentGroup): self
	{
		$this->parentGroup = $parentGroup;

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * @return array<int,string>|null
	 */
	public function getResourceUrls(): ?array
	{
		return $this->resourceUrls;
	}

	/**
	 * @param array<int,string>|null $resourceUrls
	 */
	public function setResourceUrls(?array $resourceUrls): self
	{
		$this->resourceUrls = $resourceUrls;

		return $this;
	}

	public function getIsCompleted(): ?bool
	{
		return $this->isCompleted;
	}

	public function setIsCompleted(bool $isCompleted): self
	{
		$this->isCompleted = $isCompleted;

		return $this;
	}
}
