<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\State\CommentProcessor;
use App\Entity\Checklist\Item;
use App\Mercure\MercureEntityInterface;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
	openapiContext: ["tags" => ['Comment']],
	normalizationContext: ["groups" => "comment.list"],
	uriTemplate: '/projects/{projectId}/comments',
	uriVariables: ['projectId' => new Link(fromClass: Project::class, fromProperty: 'comments')],
	operations: [new GetCollection()],
)]
#[ApiResource(
	openapiContext: ["tags" => ['Comment']],
	normalizationContext: ["groups" => "comment.read"],
	processor: CommentProcessor::class,
	operations: [
		new Get(
			security: "is_granted('comment_view', object)",
		),
		new Post(
			denormalizationContext: ["groups" => "comment.write"],
		),
		new Patch(
			security: "is_granted('comment_resolve', object)",
			denormalizationContext: ["groups" => "comment.resolve"],
		),
	],
)]
#[ApiFilter(SearchFilter::class, properties: ['checklistItem' => 'exact', 'author' => 'exact', 'authorName' => 'partial', 'content' => 'partial', 'textContent' => 'partial'])]
#[ApiFilter(BooleanFilter::class, properties: ['isResolved'])]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment implements MercureEntityInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[Groups(['comment.list', 'comment.read'])]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: User::class)]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	#[Groups(['comment.list', 'comment.read'])]
	private ?User $author = null;

	#[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'comments')]
	#[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
	#[Groups(['comment.list', 'comment.read', 'comment.write'])]
	private ?Project $project = null;

	#[ORM\Column(type: 'text')]
	#[Groups(['comment.list', 'comment.read', 'comment.write'])]
	private string $content;

	#[ORM\Column(type: 'text')]
	#[Groups(['comment.list', 'comment.read'])]
	private string $textContent;

	#[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: 'replies')]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	#[Groups(['comment.list', 'comment.read', 'comment.write'])]
	private ?Comment $thread = null;

	/**
	 * @var Collection<int,self>
	 */
	#[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'thread')]
	#[ORM\OrderBy(['isResolved' => 'ASC', 'dateCreated' => 'ASC'])]
	#[Groups(['comment.read'])]
	private Collection $replies;

	#[ORM\Column(type: 'datetime')]
	#[Groups(['comment.list', 'comment.read'])]
	private \DateTimeInterface $dateCreated;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['comment.list', 'comment.read'])]
	private ?string $authorName = null;

	#[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'comments')]
	#[ORM\JoinColumn(onDelete: 'CASCADE')]
	#[Groups(['comment.list', 'comment.read', 'comment.write'])]
	private ?Item $checklistItem = null;

	#[ORM\Column(type: 'boolean')]
	#[Groups(['comment.list', 'comment.read', 'comment.write', 'comment.resolve'])]
	private bool $isResolved = false;

	#[ORM\Column(type: 'boolean')]
	private bool $isDeleted = false;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
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

		// Create a text-only version of the content for prevewing and indexing purposes
		$this->textContent = htmlspecialchars_decode(strip_tags($content));

		return $this;
	}

	public function getTextContent(): ?string
	{
		return $this->textContent;
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

	public function getDateCreated(): ?\DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(\DateTimeInterface $dateCreated): self
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
}
