<?php

namespace App\Entity\Checklist;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Entity\Comment;
use App\Mercure\MercureEntityInterface;
use App\Repository\Checklist\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
	openapiContext: ["tags" => ['Checklist Item']],
	normalizationContext: ["groups" => "checklist_item.list"],
	uriTemplate: '/checklists/{checklistId}/items',
	uriVariables: ['checklistId' => new Link(fromClass: Checklist::class, fromProperty: 'items')],
	operations: [new GetCollection()],
)]
#[ApiResource(
	openapiContext: ["tags" => ['Checklist Item']],
	normalizationContext: ["groups" => "checklist_item.read"],
	operations: [
		new Get(
			security: "is_granted('checklist_view', object.getChecklist())",
			uriTemplate: '/checklist_items/{id}',
		),
		new Patch(
			denormalizationContext: ["groups" => "checklist_item.write"],
			security: "is_granted('checklist_view', object.getChecklist())",
			uriTemplate: '/checklist_items/{id}',
		),
	],
)]
#[ApiFilter(BooleanFilter::class, properties: ['isCompleted'])]
#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item implements MercureEntityInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist.read', 'checklist_item_group.read'])]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\ManyToOne(targetEntity: Checklist::class, inversedBy: 'items')]
	#[ORM\JoinColumn(name: 'checklist_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	private ?Checklist $checklist = null;

	#[ORM\ManyToOne(targetEntity: ItemGroup::class, inversedBy: 'items')]
	#[ORM\JoinColumn(name: 'parent_group_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: false)]
	private ?ItemGroup $parentGroup = null;

	#[ORM\Column(type: 'text')]
	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist.read', 'checklist_item_group.read'])]
	private ?string $title = null;

	#[ORM\Column(type: 'text')]
	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist.read', 'checklist_item_group.read'])]
	private ?string $description = null;

	/**
	 * @var array<int,string>
	 */
	#[ORM\Column(type: 'array', nullable: true)]
	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist.read', 'checklist_item_group.read'])]
	private ?array $resourceUrls = [];

	#[ORM\Column(type: 'boolean')]
	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist_item.write', 'checklist.read', 'checklist_item_group.read'])]
	private ?bool $isCompleted = false;

	/**
	 * @var Collection<int,Comment>
	 */
	#[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'checklistItem', orphanRemoval: true)]
	#[ORM\OrderBy(['isResolved' => 'ASC', 'dateCreated' => 'ASC'])]
	#[Groups(['checklist_item.read'])]
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
		return $this->comments->filter(fn (Comment $comment = null) => !$comment->getThread());
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

	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist.read', 'checklist_item_group.read'])]
	public function getCommentCount(): int
	{
		return $this->comments->count();
	}

	#[Groups(['checklist_item.list', 'checklist_item.read', 'checklist.read', 'checklist_item_group.read'])]
	public function getUnresolvedCommentCount(): int
	{
		return $this->comments->filter(fn (Comment $comment = null) => !$comment->isResolved() && !$comment->getThread())->count();
	}
}
