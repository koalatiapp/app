<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Api\State\ProjectProcessor;
use App\Entity\Checklist\Checklist;
use App\Entity\Testing\IgnoreEntry;
use App\Entity\Testing\Recommendation;
use App\Mercure\MercureEntityInterface;
use App\Repository\ProjectRepository;
use App\Util\Testing\RecommendationGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @SuppressWarnings("PHPMD.ExcessiveClassComplexity")
 * @SuppressWarnings("PHPMD.ExcessivePublicCount")
 */
#[ApiResource(
	processor: ProjectProcessor::class,
	normalizationContext: ["groups" => "project.read"],
	denormalizationContext: ["groups" => "project.write"],
	operations: [
		new Get(security: "is_granted('project_view', object)"),
		new GetCollection(normalizationContext: ["groups" => "project.list"]),
		new Post(security: "is_granted('project_edit', object)"),
		new Put(security: "is_granted('project_edit', object)"),
		new Patch(security: "is_granted('project_edit', object)"),
		new Delete(security: "is_granted('project_edit', object)"),
	],
)]
#[ApiFilter(OrderFilter::class, properties: ['dateCreated', 'name'])]
#[ApiFilter(SearchFilter::class, properties: ['ownerOrganization' => 'exact', 'ownerUser' => 'exact', 'tags' => 'partial'])]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements MercureEntityInterface
{
	final public const STATUS_NEW = 'NEW';
	final public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
	final public const STATUS_MAINTENANCE = 'MAINTENANCE';
	final public const STATUS_COMPLETED = 'COMPLETED';

	final public const ROLE_ADMIN = 'ROLE_ADMIN';
	final public const ROLE_MANAGE = 'ROLE_MANAGE';
	final public const ROLE_USER = 'ROLE_USER';
	final public const ROLE_INVITED = 'ROLE_INVITED';

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['project.list', 'project.read'])]
	private ?int $id = null;

	#[Assert\NotBlank]
	#[Assert\Length(max: 255)]
	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['project.list', 'project.read', 'project.write'])]
	private ?string $name = null;

	#[ORM\Column(type: 'datetime')]
	#[Groups(['project.list', 'project.read'])]
	private \DateTimeInterface $dateCreated;

	#[Assert\NotBlank]
	#[Assert\Url(relativeProtocol: true)]
	#[ORM\Column(type: 'string', length: 512)]
	#[Groups(['project.list', 'project.read', 'project.write'])]
	private ?string $url = null;

	/**
	 * @var Collection<int, Page>
	 */
	#[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'project')]
	#[Groups(['project.read'])]
	#[MaxDepth(1)]
	private Collection $pages;

	/**
	 * @var Collection<int, ProjectMember>
	 */
	#[ORM\OneToMany(targetEntity: ProjectMember::class, mappedBy: 'project')]
	#[MaxDepth(1)]
	private Collection $teamMembers;

	/**
	 * @var Collection<int,Recommendation>
	 */
	#[ORM\OneToMany(targetEntity: Recommendation::class, mappedBy: 'project')]
	private Collection $recommendations;

	/**
	 * @var Collection<int, IgnoreEntry>
	 */
	#[ORM\OneToMany(targetEntity: IgnoreEntry::class, mappedBy: 'targetProject')]
	private Collection $ignoreEntries;
	/**
	 * @var array<int,string>|null
	 */
	#[ORM\Column(type: 'array', nullable: true)]
	#[Groups(['project.list', 'project.read', 'project.write'])]
	private ?array $disabledTools = [];

	#[ORM\OneToOne(targetEntity: Checklist::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
	private ?Checklist $checklist = null;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'personalProjects')]
	#[ORM\JoinColumn(name: 'owner_user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	#[Groups(['project.list', 'project.read'])]
	private ?User $ownerUser = null;

	#[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'projects')]
	#[ORM\JoinColumn(name: 'owner_organization_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	#[Groups(['project.list', 'project.read', 'project.write'])]
	private ?Organization $ownerOrganization = null;

	/**
	 * @var Collection<int,Comment>
	 */
	#[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'project')]
	#[ORM\OrderBy(['isResolved' => 'ASC', 'dateCreated' => 'ASC'])]
	#[Groups(['comments'])]
	private Collection $comments;

	/**
	 * @var array<int,string>
	 */
	#[ORM\Column(type: 'array', nullable: true)]
	#[Groups(['project.list', 'project.read'])]
	private ?array $tags = [];

	/**
	 * Whether the crawler should use the pages' canonical URL as the standard
	 * page URL or not. Defaults to true.
	 */
	#[ORM\Column(options: ["default" => true])]
	#[Groups(['project.list', 'project.read', 'project.write'])]
	private bool $useCanonicalPageUrls = true;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
		$this->pages = new ArrayCollection();
		$this->teamMembers = new ArrayCollection();
		$this->ignoreEntries = new ArrayCollection();
		$this->comments = new ArrayCollection();
		$this->tags = [];
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
		$this->name = strip_tags($name);

		return $this;
	}

	public function getDateCreated(): \DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(\DateTimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getUrl(): ?string
	{
		return $this->url;
	}

	public function setUrl(string $url): self
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return ArrayCollection<int, Page>
	 */
	public function getPages(): Collection
	{
		return $this->pages;
	}

	public function addPage(Page $page): self
	{
		if (!$this->pages->contains($page)) {
			$this->pages->add($page);
			$page->setProject($this);
		}

		return $this;
	}

	public function removePage(Page $page): self
	{
		$this->pages->removeElement($page);

		return $this;
	}

	/**
	 * @return Collection<int, Page>
	 */
	public function getActivePages(): Collection
	{
		$criteria = Criteria::create()->where(Criteria::expr()->eq('isIgnored', false));

		return $this->getPages()->matching($criteria);
	}

	/**
	 * Returns the priority of this project's processing requests.
	 * The higher the number, the higher the priority.
	 * The default priority is `1`.
	 *
	 * @TODO: Check the project/user/organization's subscription plan (replacing this method stub)
	 */
	public function getPriority(): int
	{
		return 1;
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

	public function getOwner(): Organization|User|null
	{
		return $this->getOwnerOrganization() ?: $this->getOwnerUser();
	}

	/**
	 * @return User|null the user who owns this project (or who owns the organization that owns this project)
	 */
	public function getTopLevelOwner(): User|null
	{
		if ($organization = $this->getOwnerOrganization()) {
			return $organization->getOwner();
		}

		return $this->getOwnerUser();
	}

	#[Groups(['project.list', 'project.read'])]
	public function getOwnerOrganizationName(): ?string
	{
		return $this->getOwnerOrganization()?->getName();
	}

	/**
	 * @return Collection<int, ProjectMember>
	 */
	public function getTeamMembers(): Collection
	{
		return $this->teamMembers;
	}

	public function addTeamMember(ProjectMember $teamMember): self
	{
		if (!$this->teamMembers->contains($teamMember)) {
			$this->teamMembers[] = $teamMember;
			$teamMember->setProject($this);
		}

		return $this;
	}

	public function removeTeamMember(ProjectMember $teamMember): self
	{
		if ($this->teamMembers->removeElement($teamMember)) {
			// set the owning side to null (unless already changed)
			if ($teamMember->getProject() === $this) {
				$teamMember->setProject(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int,Recommendation>
	 */
	public function getRecommendations(): Collection
	{
		return $this->recommendations;
	}

	/**
	 * @param Collection<int,Recommendation> $recommendations
	 */
	public function setRecommendations(Collection $recommendations): self
	{
		$this->recommendations = $recommendations;

		return $this;
	}

	/**
	 * Returns all recommendations that are linked to active pages,
	 * that are not ignored and that are not completed.
	 *
	 * @return ArrayCollection<int,Recommendation>
	 */
	public function getActiveRecommendations(): ArrayCollection
	{
		$recommendations = $this->recommendations->filter(
			function (Recommendation $recommendation = null) {
				return !$recommendation->getIsCompleted()
							 && !$recommendation->isIgnored()
							 && !$recommendation->getRelatedPage()->getIsIgnored();
			}
		);

		// Sort the recommendations by priority level
		/** @var \ArrayIterator<int, Recommendation> $recommendationIterator */
		$recommendationIterator = $recommendations->getIterator();
		$recommendationIterator->uasort(function (Recommendation $a, Recommendation $b) {
			$priorities = Recommendation::TYPE_PRIORITIES;

			if ($priorities[$a->getType()] > $priorities[$b->getType()]) {
				return 1;
			} elseif ($priorities[$a->getType()] < $priorities[$b->getType()]) {
				return -1;
			}

			return strnatcasecmp($a->getTitle(), $b->getTitle());
		});

		return new ArrayCollection(iterator_to_array($recommendationIterator));
	}

	/**
	 * @return ArrayCollection<string,RecommendationGroup>
	 */
	public function getActiveRecommendationGroups(): ArrayCollection
	{
		$groups = RecommendationGroup::fromLooseRecommendations($this->getActiveRecommendations());

		return new ArrayCollection($groups);
	}

	/**
	 * @return Collection<int,IgnoreEntry>
	 */
	public function getIgnoreEntries(): Collection
	{
		return $this->ignoreEntries;
	}

	public function addIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		if (!$this->ignoreEntries->contains($ignoreEntry)) {
			$this->ignoreEntries[] = $ignoreEntry;
			$ignoreEntry->setTargetProject($this);
		}

		return $this;
	}

	public function removeIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		if ($this->ignoreEntries->removeElement($ignoreEntry)) {
			// set the owning side to null (unless already changed)
			if ($ignoreEntry->getTargetProject() === $this) {
				$ignoreEntry->setTargetProject(null);
			}
		}

		return $this;
	}

	/**
	 * Returns the list of Koalati automated tools that are disabled for this project.
	 *
	 * @TODO: Also check the user/organization's settings to get the list of tools
	 *
	 * @return array<int,string>
	 */
	public function getDisabledTools(): ?array
	{
		return $this->disabledTools ?: [];
	}

	/**
	 * @param array<int,string>|null $disabledTools
	 */
	public function setDisabledTools(?array $disabledTools): self
	{
		$this->disabledTools = $disabledTools ?: [];

		return $this;
	}

	public function enableTool(string $tool): self
	{
		$this->disabledTools = array_filter($this->getDisabledTools(), fn ($disabledTool) => strcasecmp($tool, $disabledTool) != 0);

		return $this;
	}

	public function disableTool(string $tool): self
	{
		if ($this->disabledTools === null) {
			$this->disabledTools = [];
		}

		$this->disabledTools[] = strtolower(trim($tool));

		return $this;
	}

	public function hasToolEnabled(string $tool): bool
	{
		return !in_array(strtolower(trim($tool)), $this->getDisabledTools());
	}

	public function getChecklist(): ?Checklist
	{
		return $this->checklist;
	}

	public function setChecklist(Checklist $checklist): self
	{
		// set the owning side of the relation if necessary
		if ($checklist->getProject() !== $this) {
			$checklist->setProject($this);
		}

		$this->checklist = $checklist;

		return $this;
	}

	public function getChecklistProgress(): float
	{
		return $this->getChecklist()?->getCompletionPercentage() ?: 0;
	}

	#[Groups(['project.list', 'project.read'])]
	public function getStatus(): string
	{
		$checklist = $this->getChecklist();
		$checklistCompletion = $checklist?->getCompletionPercentage() ?: 0;

		if (!$checklist || !$checklistCompletion) {
			return self::STATUS_NEW;
		}

		if ($checklistCompletion == 1) {
			if ($this->getActiveRecommendations()->count() == 0) {
				return self::STATUS_COMPLETED;
			}

			return self::STATUS_MAINTENANCE;
		}

		return self::STATUS_IN_PROGRESS;
	}

	/**
	 * @return Collection<int,Comment>
	 */
	public function getComments(): Collection
	{
		return $this->comments->filter(fn (Comment $comment = null) => !$comment->getThread());
	}

	public function getCommentCount(): int
	{
		return $this->comments->count();
	}

	public function getUnresolvedCommentCount(): int
	{
		return $this->comments->filter(fn (Comment $comment = null) => !$comment->isResolved() && !$comment->getThread())->count();
	}

	/**
	 * @return array<int,string>
	 */
	public function getTags(): array
	{
		return $this->tags ?? [];
	}

	/**
	 * @param array<int,string> $tags
	 */
	public function setTags(?array $tags): self
	{
		$this->tags = $tags ?: [];

		return $this;
	}

	public function addTag(string $tag): self
	{
		if (!in_array($tag, $this->getTags())) {
			$this->tags[] = $tag;
		}

		return $this;
	}

	public function hasTag(string $tag): bool
	{
		return in_array($tag, $this->getTags());
	}

	public function getMercureSerializationGroup(): string
	{
		return "project.read";
	}

	public function useCanonicalPageUrls(): bool
	{
		return $this->useCanonicalPageUrls;
	}

	public function setUseCanonicalPageUrls(bool $useCanonicalPageUrls): self
	{
		$this->useCanonicalPageUrls = $useCanonicalPageUrls;

		return $this;
	}
}
