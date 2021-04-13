<?php

namespace App\Entity\Testing;

use App\Entity\Page;
use App\Entity\User;
use App\Repository\Testing\RecommendationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecommendationRepository::class)
 */
class Recommendation
{
	public const TYPE_ISSUE = 'ISSUE';
	public const TYPE_ESSENTIAL = 'ESSENTIAL';
	public const TYPE_OPTIMIZATION = 'OPTIMIZATION';
	public const TYPES = [
		Recommendation::TYPE_ISSUE,
		Recommendation::TYPE_ESSENTIAL,
		Recommendation::TYPE_OPTIMIZATION,
	];

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private int $id;

	/**
	 * @ORM\Column(type="text")
	 */
	private string $template;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 *
	 * @var array<mixed,mixed>
	 */
	private array $parameters = [];

	/**
	 * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="recommendations")
	 * @ORM\JoinColumn(nullable=false)
	 *
	 * @var Page
	 */
	private $relatedPage;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private string $type;

	/**
	 * @ORM\ManyToMany(targetEntity=TestResult::class, inversedBy="recommendations")
	 *
	 * @var Collection<int,TestResult>
	 */
	private $parentResults;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private \DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private \DateTimeInterface $dateLastOccured;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private \DateTimeInterface $dateCompleted;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 */
	private ?User $completedBy;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private bool $isCompleted;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private bool $isIgnored;

	public function __construct()
	{
		$this->parentResults = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTemplate(): ?string
	{
		return $this->template;
	}

	public function setTemplate(string $template): self
	{
		$this->template = $template;

		return $this;
	}

	/**
	 * @return array<mixed, mixed>
	 */
	public function getParameters(): ?array
	{
		return $this->parameters;
	}

	/**
	 * @param array<mixed,mixed> $parameters
	 */
	public function setParameters(?array $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	public function getRelatedPage(): ?Page
	{
		return $this->relatedPage;
	}

	public function setRelatedPage(?Page $relatedPage): self
	{
		$this->relatedPage = $relatedPage;

		return $this;
	}

	public function getType(): ?string
	{
		return $this->type;
	}

	public function setType(string $type): self
	{
		if (!in_array($type, static::TYPES)) {
			throw new \Exception(sprintf('%s is not a valid recommendation type. Accecpted types are %s', $type, implode(', ', static::TYPES)));
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * @return Collection<int,TestResult>
	 */
	public function getParentResults(): Collection
	{
		return $this->parentResults;
	}

	public function addParentResult(TestResult $parentResult): self
	{
		if (!$this->parentResults->contains($parentResult)) {
			$this->parentResults[] = $parentResult;
		}

		return $this;
	}

	public function removeParentResult(TestResult $parentResult): self
	{
		$this->parentResults->removeElement($parentResult);

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

	public function getDateLastOccured(): ?\DateTimeInterface
	{
		return $this->dateLastOccured;
	}

	public function setDateLastOccured(\DateTimeInterface $dateLastOccured): self
	{
		$this->dateLastOccured = $dateLastOccured;

		return $this;
	}

	public function getDateCompleted(): ?\DateTimeInterface
	{
		return $this->dateCompleted;
	}

	public function setDateCompleted(?\DateTimeInterface $dateCompleted): self
	{
		$this->dateCompleted = $dateCompleted;

		return $this;
	}

	public function getCompletedBy(): ?User
	{
		return $this->completedBy;
	}

	public function setCompletedBy(?User $completedBy): self
	{
		$this->completedBy = $completedBy;

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

	public function getIsIgnored(): ?bool
	{
		return $this->isIgnored;
	}

	public function setIsIgnored(bool $isIgnored): self
	{
		$this->isIgnored = $isIgnored;

		return $this;
	}
}
