<?php

namespace App\Entity\Testing;

use App\Entity\Page;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\Testing\RecommendationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=RecommendationRepository::class)
 */
class Recommendation
{
	public const TYPE_ISSUE = 'ISSUE';
	public const TYPE_ESSENTIAL = 'ESSENTIAL';
	public const TYPE_OPTIMIZATION = 'OPTIMIZATION';
	public const TYPE_PRIORITIES = [
		Recommendation::TYPE_ISSUE => 5,
		Recommendation::TYPE_ESSENTIAL => 10,
		Recommendation::TYPE_OPTIMIZATION => 20,
	];

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
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
	 */
	private Page $relatedPage;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private string $type;

	/**
	 * @ORM\ManyToOne(targetEntity=TestResult::class, inversedBy="recommendations")
	 * @ORM\JoinColumn(nullable=false)
	 * @Groups({"default"})
	 * @MaxDepth(1)
	 */
	private TestResult $parentResult;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private \DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private \DateTimeInterface $dateLastOccured;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Groups({"default"})
	 */
	private \DateTimeInterface $dateCompleted;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @Groups({"default"})
	 */
	private ?User $completedBy;

	/**
	 * @ORM\Column(type="boolean")
	 * @Groups({"default"})
	 */
	private bool $isCompleted = false;

	/**
	 * @ORM\Column(type="boolean")
	 * @Groups({"default"})
	 */
	private bool $isIgnored = false;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="recommendations")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private Project $project;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
		$this->dateLastOccured = new \DateTime();
		$this->dateCompleted = new \DateTime();
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

	/**
	 * @Groups({"default"})
	 */
	public function getTitle(): string
	{
		return strtr($this->getTemplate(), $this->getParameters());
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
		$allowedTypes = array_keys(static::TYPE_PRIORITIES);

		if (!in_array($type, $allowedTypes)) {
			throw new \Exception(sprintf('%s is not a valid recommendation type. Accecpted types are %s', $type, implode(', ', $allowedTypes)));
		}

		$this->type = $type;

		return $this;
	}

	public function getParentResult(): TestResult
	{
		return $this->parentResult;
	}

	public function setParentResult(TestResult $parentResult): self
	{
		$this->parentResult = $parentResult;

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

	public function getProject(): ?Project
	{
		return $this->project;
	}

	public function setProject(?Project $project): self
	{
		$this->project = $project;

		return $this;
	}

	/**
	 * @Groups({"default"})
	 * @TODO: Implement the Recommendation::getUniqueName() method with an actual property,
	 * with a fallback on the template when it is missing or empty.
	 */
	public function getUniqueName(): ?string
	{
		return $this->template;
	}
}
