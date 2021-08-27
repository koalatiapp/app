<?php

namespace App\Entity\Testing;

use App\Repository\Testing\TestResultRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=TestResultRepository::class)
 */
class TestResult
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private int $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private string $uniqueName;

	/**
	 * @ORM\Column(type="string", length=512)
	 * @Groups({"default"})
	 */
	private string $title;

	/**
	 * @ORM\Column(type="text")
	 * @Groups({"default"})
	 */
	private string $description;

	/**
	 * @ORM\Column(type="float", nullable=true)
	 * @Groups({"default"})
	 */
	private ?float $weight;

	/**
	 * @ORM\Column(type="float")
	 * @Groups({"default"})
	 */
	private float $score;

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @Groups({"default"})
	 *
	 * @var array<int,mixed>|null
	 */
	private ?array $snippets = [];

	/**
	 * @ORM\Column(type="array", nullable=true)
	 * @Groups({"default"})
	 *
	 * @var array<int,array>
	 */
	private ?array $dataTable = [];

	/**
	 * @ORM\ManyToOne(targetEntity=ToolResponse::class, inversedBy="testResults")
	 * @ORM\JoinColumn(name="parent_response_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private ToolResponse $parentResponse;

	/**
	 * @ORM\OneToMany(targetEntity=Recommendation::class, mappedBy="parentResult")
	 * @MaxDepth(1)
	 *
	 * @var Collection<int,Recommendation>
	 */
	private Collection $recommendations;

	public function __construct()
	{
		$this->recommendations = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getUniqueName(): ?string
	{
		return $this->uniqueName;
	}

	public function setUniqueName(string $uniqueName): self
	{
		$this->uniqueName = $uniqueName;

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

	public function getWeight(): ?float
	{
		return $this->weight;
	}

	public function setWeight(?float $weight): self
	{
		$this->weight = $weight;

		return $this;
	}

	public function getScore(): ?float
	{
		return $this->score;
	}

	public function setScore(float $score): self
	{
		$this->score = $score;

		return $this;
	}

	/**
	 * @return array<int,mixed>|null
	 */
	public function getSnippets(): ?array
	{
		return $this->snippets;
	}

	/**
	 * @param array<int,mixed>|null $snippets
	 */
	public function setSnippets(?array $snippets): self
	{
		$this->snippets = $snippets;

		return $this;
	}

	/**
	 * @return array<int,array>|null
	 */
	public function getDataTable(): ?array
	{
		return $this->dataTable;
	}

	/**
	 * @param array<int,mixed>|null $dataTable
	 */
	public function setDataTable(?array $dataTable): self
	{
		$this->dataTable = $dataTable;

		return $this;
	}

	public function getParentResponse(): ?ToolResponse
	{
		return $this->parentResponse;
	}

	public function setParentResponse(?ToolResponse $parentResponse): self
	{
		$this->parentResponse = $parentResponse;

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
			$recommendation->setParentResult($this);
		}

		return $this;
	}
}
