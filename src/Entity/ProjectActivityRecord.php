<?php

namespace App\Entity;

use App\Repository\ProjectActivityRecordRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjectActivityRecordRepository::class)
 * @ORM\Table(name="project_activity_record", indexes={
 *     @ORM\Index(name="project_activity_record_website_url", columns={"website_url"})
 * })
 */
class ProjectActivityRecord
{
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\ManyToOne(targetEntity=Project::class)
	 * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="SET NULL")
	 */
	private ?Project $project;

	/**
	 * @ORM\ManyToOne(targetEntity=User::class)
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private ?User $user;

	/**
	 * @ORM\Column(type="string", length=512)
	 */
	private ?string $websiteUrl;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private ?DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="string", length=512)
	 */
	private ?string $pageUrl;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $tool;

	public function __construct()
	{
		$this->dateCreated = new DateTime();
	}

	public function getId(): ?int
	{
		return $this->id;
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

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $user): self
	{
		$this->user = $user;

		return $this;
	}

	public function getWebsiteUrl(): ?string
	{
		return $this->websiteUrl;
	}

	public function setWebsiteUrl(string $websiteUrl): self
	{
		$this->websiteUrl = $websiteUrl;

		return $this;
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

	public function getPageUrl(): ?string
	{
		return $this->pageUrl;
	}

	public function setPageUrl(string $pageUrl): self
	{
		$this->pageUrl = $pageUrl;

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
}
