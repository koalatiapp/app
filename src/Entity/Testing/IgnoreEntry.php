<?php

namespace App\Entity\Testing;

use App\Entity\Organization;
use App\Entity\Page;
use App\Entity\Project;
use App\Entity\User;
use App\Mercure\MercureEntityInterface;
use App\Repository\Testing\IgnoreEntryRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=IgnoreEntryRepository::class)
 */
class IgnoreEntry implements MercureEntityInterface
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private ?int $id = null;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private ?DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $tool;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $test;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private ?string $recommendationUniqueName;

	/**
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="ignoreEntries")
	 * @ORM\JoinColumn(name="target_organization_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?Organization $targetOrganization = null;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ignoreEntries")
	 * @ORM\JoinColumn(name="target_user_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?User $targetUser = null;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="ignoreEntries")
	 * @ORM\JoinColumn(name="target_project_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?Project $targetProject = null;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(nullable=false)
	 * @Groups({"default"})
	 */
	private ?User $createdBy;

	/**
	 * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="ignoreEntries")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 * @Groups({"default"})
	 */
	private ?Page $targetPage = null;

	/**
	 * @ORM\Column(type="string", length=512)
	 * @Groups({"default"})
	 */
	private ?string $recommendationTitle;

	public function __construct(string $tool, string $test, string $recommendationUniqueName, string $recommendationTitle, null | Organization | User | Project | Page $scopeTarget = null, ?User $createdBy = null)
	{
		$this->dateCreated = new DateTime();
		$this->setTool($tool);
		$this->setTest($test);
		$this->setRecommendationUniqueName($recommendationUniqueName);
		$this->setRecommendationTitle($recommendationTitle);

		if ($scopeTarget) {
			$this->setScope($scopeTarget);
		}

		if ($createdBy) {
			$this->setCreatedBy($createdBy);
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

	/**
	 * @Groups({"default"})
	 */
	public function getScopeType(): string
	{
		if ($this->getTargetUser()) {
			return 'user';
		}

		if ($this->getTargetOrganization()) {
			return 'organization';
		}

		if ($this->getTargetProject()) {
			return 'project';
		}

		return 'page';
	}

	public function getRecommendationTitle(): ?string
	{
		return $this->recommendationTitle;
	}

	public function setRecommendationTitle(string $recommendationTitle): self
	{
		$this->recommendationTitle = $recommendationTitle;

		return $this;
	}
}
