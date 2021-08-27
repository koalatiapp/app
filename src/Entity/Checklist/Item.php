<?php

namespace App\Entity\Checklist;

use App\Entity\MercureEntityInterface;
use App\Mercure\TopicBuilder;
use App\Repository\Checklist\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 */
class Item implements MercureEntityInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @Groups({"default"})
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Checklist::class, inversedBy="items")
	 * @ORM\JoinColumn(name="checklist_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private ?Checklist $checklist;

	/**
	 * @ORM\ManyToOne(targetEntity=ItemGroup::class, inversedBy="items")
	 * @ORM\JoinColumn(name="parent_group_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private ?ItemGroup $parentGroup;

	/**
	 * @ORM\Column(type="text")
	 * @Groups({"default"})
	 */
	private ?string $title;

	/**
	 * @ORM\Column(type="text")
	 * @Groups({"default"})
	 */
	private ?string $description;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @Groups({"default"})
	 *
	 * @var array<int,string>
	 */
	private ?array $resourceUrls = [];

	/**
	 * @ORM\Column(type="boolean")
	 * @Groups({"default"})
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

	/*
	 * Mercure implementation (MercureEntityInterface)
	 */

	public static function getMercureTopics(): array
	{
		return [
			TopicBuilder::SCOPE_SPECIFIC => 'http://koalati/checklist-item/{id}',
			TopicBuilder::SCOPE_PROJECT => 'http://koalati/{scope}/checklist-item/{id}',
		];
	}

	public function getMercureScope(string $scope): object | array | null
	{
		return match ($scope) {
			TopicBuilder::SCOPE_PROJECT => $this->getChecklist()->getProject(),
			default => null
		};
	}
}
