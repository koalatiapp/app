<?php

namespace App\Entity\Testing;

use App\Repository\Testing\ToolResponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table]
#[ORM\Index(name: 'page_url_index', columns: ['tool', 'url'])]
#[ORM\Entity(repositoryClass: ToolResponseRepository::class)]
class ToolResponse
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['default'])]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 255)]
	#[Groups(['default'])]
	private string $tool;

	#[ORM\Column(type: 'string', length: 510)]
	#[Groups(['default'])]
	private string $url;

	#[ORM\Column(type: 'datetime')]
	#[Groups(['default'])]
	private \DateTimeInterface $dateReceived;

	#[ORM\Column(type: 'integer')]
	#[Groups(['default'])]
	private int $processingTime;

	/**
	 * @var Collection<int,TestResult>
	 */
	#[ORM\OneToMany(targetEntity: TestResult::class, mappedBy: 'parentResponse', orphanRemoval: true)]
	private Collection $testResults;

	public function __construct()
	{
		$this->testResults = new ArrayCollection();
		$this->dateReceived = new \DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTool(): ?string
	{
		return $this->tool;
	}

	public function setTool(string $tool): self
	{
		$this->tool = $tool;

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

	public function getDateReceived(): ?\DateTimeInterface
	{
		return $this->dateReceived;
	}

	public function setDateReceived(\DateTimeInterface $dateReceived): self
	{
		$this->dateReceived = $dateReceived;

		return $this;
	}

	public function getProcessingTime(): ?int
	{
		return $this->processingTime;
	}

	public function setProcessingTime(int $processingTime): self
	{
		$this->processingTime = $processingTime;

		return $this;
	}

	/**
	 * @return Collection<int,TestResult>
	 */
	public function getTestResults(): Collection
	{
		return $this->testResults;
	}

	public function addTestResult(TestResult $testResult): self
	{
		if (!$this->testResults->contains($testResult)) {
			$this->testResults[] = $testResult;
			$testResult->setParentResponse($this);
		}

		return $this;
	}

	public function removeTestResult(TestResult $testResult): self
	{
		if ($this->testResults->removeElement($testResult)) {
			// set the owning side to null (unless already changed)
			if ($testResult->getParentResponse() === $this) {
				$testResult->setParentResponse(null);
			}
		}

		return $this;
	}
}
