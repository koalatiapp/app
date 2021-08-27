<?php

namespace App\Entity;

use App\Entity\Testing\IgnoreEntry;
use App\Entity\Testing\Recommendation;
use App\Repository\PageRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="unique_url", columns={"url"})})
 */
class Page
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private int $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Groups({"default"})
	 */
	private ?string $title;

	/**
	 * @ORM\Column(type="string", length=510)
	 * @Groups({"default"})
	 */
	private string $url;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private \DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private \DateTimeInterface $dateUpdated;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @Groups({"default"})
	 */
	private ?int $httpCode;

	/**
	 * @ORM\OneToMany(targetEntity=Recommendation::class, mappedBy="relatedPage", orphanRemoval=true)
	 * @Groups({"page"})
	 * @MaxDepth(1)
	 *
	 * @var Collection<int,Recommendation>
	 */
	private Collection $recommendations;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="pages")
	 * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"page"})
	 * @MaxDepth(1)
	 */
	private Project $project;

	/**
	 * @ORM\Column(type="boolean")
	 * @Groups({"default"})
	 */
	private bool $isIgnored = false;

	/**
	 * @ORM\OneToMany(targetEntity=IgnoreEntry::class, mappedBy="targetPage")
	 *
	 * @var \Doctrine\Common\Collections\Collection<int, IgnoreEntry>
	 */
	private $ignoreEntries;

	public function __construct(Project $project, string $url, ?string $title = null)
	{
		$this->url = $url;
		$this->title = $title;
		$this->dateCreated = new DateTime();
		$this->dateUpdated = new DateTime();
		$this->recommendations = new ArrayCollection();
		$this->setProject($project);
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
		$this->title = $title;

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
}
