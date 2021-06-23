<?php

namespace App\Entity\Testing;

use App\Entity\Organization;
use App\Entity\Page;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\IgnoreEntryRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IgnoreEntryRepository::class)
 */
class IgnoreEntry
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private ?DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $tool;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $test;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $recommendationUniqueName;

	/**
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="ignoreEntries")
	 */
	private ?Organization $targetOrganization;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ignoreEntries")
	 */
	private ?User $targetUser;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="ignoreEntries")
	 */
	private ?Project $targetProject;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(nullable=false)
	 */
	private ?User $createdBy;

	/**
	 * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="ignoreEntries")
	 */
	private ?Page $targetPage;

	public function __construct(string $tool, string $test, string $recommendationUniqueName, null | Organization | User | Project | Page $scopeTarget = null)
	{
		$this->dateCreated = new DateTime();
		$this->setTool($tool);
		$this->setTest($test);
		$this->setRecommendationUniqueName($recommendationUniqueName);

		if ($scopeTarget) {
			$this->setScope($scopeTarget);
		}
	}

	public function getId(): ?int
	{
		return $this->id;
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

	public function getTool(): ?string
	{
		return $this->tool;
	}

	public function setTool(string $tool): self
	{
		$this->tool = $tool;

		return $this;
	}

	public function getTest(): ?string
	{
		return $this->test;
	}

	public function setTest(string $test): self
	{
		$this->test = $test;

		return $this;
	}

	public function getRecommendationUniqueName(): ?string
	{
		return $this->recommendationUniqueName;
	}

	public function setRecommendationUniqueName(string $recommendationUniqueName): self
	{
		$this->recommendationUniqueName = $recommendationUniqueName;

		return $this;
	}

	public function getTargetOrganization(): ?Organization
	{
		return $this->targetOrganization;
	}

	public function setTargetOrganization(?Organization $targetOrganization): self
	{
		$this->targetOrganization = $targetOrganization;

		return $this;
	}

	public function getTargetUser(): ?User
	{
		return $this->targetUser;
	}

	public function setTargetUser(?User $targetUser): self
	{
		$this->targetUser = $targetUser;

		return $this;
	}

	public function getTargetProject(): ?Project
	{
		return $this->targetProject;
	}

	public function setTargetProject(?Project $targetProject): self
	{
		$this->targetProject = $targetProject;

		return $this;
	}

	public function getCreatedBy(): ?User
	{
		return $this->createdBy;
	}

	public function setCreatedBy(?User $createdBy): self
	{
		$this->createdBy = $createdBy;

		return $this;
	}

	public function getTargetPage(): ?Page
	{
		return $this->targetPage;
	}

	public function setTargetPage(?Page $targetPage): self
	{
		$this->targetPage = $targetPage;

		return $this;
	}

	public function setScope(Organization | User | Project | Page $scopeTarget): self
	{
		switch (get_class($scopeTarget)) {
			case Organization::class:
				return $this->setTargetOrganization($scopeTarget);
			case User::class:
				return $this->setTargetUser($scopeTarget);
			case Project::class:
				return $this->setTargetProject($scopeTarget);
			case Page::class:
				return $this->setTargetPage($scopeTarget);
		}

		return $this;
	}
}
