<?php

namespace App\Entity\Checklist;

use App\Entity\Comment;
use App\Entity\MercureEntityInterface;
use App\Mercure\TopicBuilder;
use App\Repository\Checklist\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

	/**
	 * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="checklistItem", orphanRemoval=true)
	 * @ORM\OrderBy({"dateCreated" = "ASC"})
	 * @Groups({"comments"})
	 *
	 * @var Collection<int,Comment>
	 */
	private Collection $comments;

	public function __construct()
	{
		$this->comments = new ArrayCollection();
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

	/**
	 * @return Collection<int,Comment>
	 */
	public function getComments(): Collection
	{
		return $this->comments->filter(function (Comment $comment) {
			return !$comment->getThread();
		});
	}

	public function addComment(Comment $comment): self
	{
		if (!$this->comments->contains($comment)) {
			$this->comments[] = $comment;
			$comment->setChecklistItem($this);
		}

		return $this;
	}

	public function removeComment(Comment $comment): self
	{
		if ($this->comments->removeElement($comment)) {
			// set the owning side to null (unless already changed)
			if ($comment->getChecklistItem() === $this) {
				$comment->setChecklistItem(null);
			}
		}

		return $this;
	}

	/**
	 * @Groups({"default"})
	 */
	public function getCommentCount(): int
	{
		return $this->comments->count();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getUnresolvedCommentCount(): int
	{
		return $this->comments->filter(function (Comment $comment) {
			return !$comment->isResolved() && !$comment->getThread();
		})->count();
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
