<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Entity\Testing\IgnoreEntry;
use App\Entity\Testing\Recommendation;
use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
	normalizationContext: ["groups" => "page.list"],
	uriTemplate: '/projects/{projectId}/pages',
	uriVariables: ['projectId' => new Link(fromClass: Project::class, fromProperty: 'pages')],
	operations: [new GetCollection()],
)]
#[ApiResource(
	normalizationContext: ["groups" => "page.read"],
	denormalizationContext: ["groups" => "page.write"],
	operations: [
		new Get(security: "is_granted('page_view', object)"),
		new Patch(security: "is_granted('page_edit', object)"),
	],
)]
#[ApiFilter(OrderFilter::class, properties: ['url', 'title', 'dateUpdated'])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'url' => 'partial', 'httpCode' => 'exact'])]
#[ApiFilter(NumericFilter::class, properties: ['httpCode'])]
#[ApiFilter(BooleanFilter::class, properties: ['isIgnored'])]
#[ORM\Index(name: 'page_url_index', columns: ['url'])]
#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['page.list', 'page.read'])]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['page.list', 'page.read'])]
	private ?string $title;

	#[ORM\Column(type: 'string', length: 510)]
	#[Groups(['page.list', 'page.read'])]
	private string $url;

	#[ORM\Column(type: 'datetime')]
	private \DateTimeInterface $dateCreated;

	#[ORM\Column(type: 'datetime')]
	#[Groups(['page.list', 'page.read'])]
	private \DateTimeInterface $dateUpdated;

	#[ORM\Column(type: 'integer', nullable: true)]
	#[Groups(['page.list', 'page.read'])]
	private ?int $httpCode = null;

	/**
	 * @var Collection<int,Recommendation>
	 */
	#[ORM\OneToMany(targetEntity: Recommendation::class, mappedBy: 'relatedPage', orphanRemoval: true)]
	private Collection $recommendations;

	#[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'pages')]
	#[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	#[Groups(['page.list', 'page.read'])]
	private Project $project;

	#[ORM\Column(type: 'boolean')]
	#[Groups(['page.list', 'page.read', 'page.write'])]
	private bool $isIgnored = false;

	/**
	 * @var Collection<int, IgnoreEntry>
	 */
	#[ORM\OneToMany(targetEntity: IgnoreEntry::class, mappedBy: 'targetPage')]
	private Collection $ignoreEntries;

	public function __construct(Project $project, string $url, ?string $title = null)
	{
		$this->setUrl($url);
		$this->setTitle($title);
		$this->setProject($project);
		$this->dateCreated = new \DateTime();
		$this->dateUpdated = new \DateTime();
		$this->recommendations = new ArrayCollection();
		$this->ignoreEntries = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	public function setTitle(?string $title): self
	{
		$this->title = mb_substr($title ?: '', 0, 255);
		$this->setDateUpdated(new \DateTime());

		return $this;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function setUrl(string $url): self
	{
		$this->url = $url;

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

	public function getDateUpdated(): ?\DateTimeInterface
	{
		return $this->dateUpdated;
	}

	public function setDateUpdated(\DateTimeInterface $dateUpdated): self
	{
		$this->dateUpdated = $dateUpdated;

		return $this;
	}

	public function getHttpCode(): ?int
	{
		return $this->httpCode;
	}

	public function setHttpCode(?int $httpCode): self
	{
		$this->httpCode = $httpCode;

		return $this;
	}

	/**
	 * @return Collection<int,Recommendation>
	 */
	public function getRecommendations(): Collection
	{
		return $this->recommendations;
	}

	public function addRecommendation(Recommendation $recommendation): self
	{
		if (!$this->recommendations->contains($recommendation)) {
			$this->recommendations[] = $recommendation;
			$recommendation->setRelatedPage($this);
		}

		return $this;
	}

	public function removeRecommendation(Recommendation $recommendation): self
	{
		if ($this->recommendations->removeElement($recommendation)) {
			// set the owning side to null (unless already changed)
			if ($recommendation->getRelatedPage() === $this) {
				$recommendation->setRelatedPage(null);
			}
		}

		return $this;
	}

	public function setProject(Project $project): self
	{
		$this->project = $project;
		$project->addPage($this);

		return $this;
	}

	public function getProject(): Project
	{
		return $this->project;
	}

	public function getIsIgnored(): ?bool
	{
		return $this->isIgnored;
	}

	public function setIsIgnored(bool $isIgnored): self
	{
		$this->isIgnored = $isIgnored;

		return $this;
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
			$ignoreEntry->setTargetPage($this);
		}

		return $this;
	}

	public function removeIgnoreEntry(IgnoreEntry $ignoreEntry): self
	{
		if ($this->ignoreEntries->removeElement($ignoreEntry)) {
			// set the owning side to null (unless already changed)
			if ($ignoreEntry->getTargetPage() === $this) {
				$ignoreEntry->setTargetPage(null);
			}
		}

		return $this;
	}

	/**
	 * Returns:
	 * - `true` if the page responds with an error code,
	 * - `false` if the page returns a 200 OK
	 * - `null` if the page hasn't been crawled yet.
	 */
	public function respondsWithError(): ?bool
	{
		$httpCode = $this->getHttpCode();

		if (!$httpCode) {
			return null;
		}

		return $httpCode >= 400;
	}
}
