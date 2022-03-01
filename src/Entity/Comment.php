<?php

namespace App\Entity;

use App\Entity\Checklist\Item;
use App\Mercure\TopicBuilder;
use App\Repository\CommentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment implements MercureEntityInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @Groups({"default"})
	 * @ORM\Column(type="integer")
	 */
	private ?int $id = null;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?User $author = null;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="comments")
	 * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
	 * @Groups({"default"})
	 * @MaxDepth(1)
	 */
	private Project $project;

	/**
	 * @ORM\Column(type="text")
	 * @Groups({"default"})
	 */
	private string $content;

	/**
	 * @ORM\ManyToOne(targetEntity=Comment::class, inversedBy="replies")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?Comment $thread = null;

	/**
	 * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="thread")
	 * @ORM\OrderBy({"isResolved" = "ASC"}, {"dateCreated" = "ASC"})
	 * @Groups({"default"})
	 *
	 * @var Collection<int,self>
	 */
	private Collection $replies;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Groups({"default"})
	 */
	private ?string $authorName = null;

	/**
	 * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="comments")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?Item $checklistItem = null;

	/**
	 * @ORM\Column(type="boolean")
	 * @Groups({"default"})
	 */
	private bool $isResolved = false;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private bool $isDeleted = false;

	public function __construct()
	{
		$this->dateCreated = new DateTime();
		$this->replies = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getAuthor(): ?User
	{
		return $this->author;
	}

	public function setAuthor(?User $author): self
	{
		$this->author = $author;
		$this->setAuthorName($author->getFullName());

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

	public function getContent(): ?string
	{
		return $this->content;
	}

	public function setContent(string $content): self
	{
		$this->content = $content;

		return $this;
	}

	public function getThread(): ?self
	{
		return $this->thread;
	}

	public function setThread(?self $thread): self
	{
		$this->thread = $thread;

		return $this;
	}

	/**
	 * @return Collection<int,self>
	 */
	public function getReplies(): Collection
	{
		return $this->replies;
	}

	public function addReply(self $reply): self
	{
		if (!$this->replies->contains($reply)) {
			$this->replies[] = $reply;
			$reply->setThread($this);
		}

		return $this;
	}

	public function removeReply(self $reply): self
	{
		if ($this->replies->removeElement($reply)) {
			// set the owning side to null (unless already changed)
			if ($reply->getThread() === $this) {
				$reply->setThread(null);
			}
		}

		return $this;
	}

	public function getDateCreated(): ?DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(DateTimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getAuthorName(): ?string
	{
		return $this->authorName;
	}

	public function setAuthorName(?string $authorName): self
	{
		$this->authorName = $authorName;

		return $this;
	}

	public function getChecklistItem(): ?Item
	{
		return $this->checklistItem;
	}

	public function setChecklistItem(?Item $checklistItem): self
	{
		$this->checklistItem = $checklistItem;

		return $this;
	}

	public function isResolved(): ?bool
	{
		return $this->isResolved;
	}

	/**
	 * Alias for `isResolved` that allows Symfony's serializer
	 * to properly handle boolean values with the "is" prefix.
	 */
	public function getIsResolved(): ?bool
	{
		return $this->isResolved;
	}

	public function setIsResolved(bool $isResolved): self
	{
		$this->isResolved = $isResolved;

		return $this;
	}

	public function isDeleted(): ?bool
	{
		return $this->isDeleted;
	}

	public function setIsDeleted(bool $isDeleted): self
	{
		$this->isDeleted = $isDeleted;

		return $this;
	}

	/*
	 * Mercure implementation (MercureEntityInterface)
	 */

	public static function getMercureTopics(): array
	{
		return [
			TopicBuilder::SCOPE_SPECIFIC => 'http://koalati/comment/{id}',
			TopicBuilder::SCOPE_PROJECT => 'http://koalati/{scope}/comment',
			TopicBuilder::SCOPE_CHECKLIST_ITEM => 'http://koalati/{scope}/comment',
		];
	}

	public function getMercureScope(string $scope): object | array | null
	{
		return match ($scope) {
			TopicBuilder::SCOPE_PROJECT => $this->getProject(),
			TopicBuilder::SCOPE_CHECKLIST_ITEM => $this->getChecklistItem(),
			default => null
		};
	}
}
