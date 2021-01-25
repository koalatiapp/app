<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
	public const STATUS_NEW = 'NEW';
	public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
	public const STATUS_COMPLETED = 'COMPLETED';

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @var string
	 * @Assert\NotBlank
	 * @Assert\Length(max = 255)
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

	/**
	 * @var \App\Entity\User|null
	 * @Assert\NotBlank
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projects")
	 */
	private $ownerUser;

	/**
	 * @var \DateTimeInterface
	 * @ORM\Column(type="datetime")
	 */
	private $dateCreated;

	/**
	 * @var string
	 * @Assert\NotBlank
	 * @Assert\Url(relativeProtocol = true)
	 * @ORM\Column(type="string", length=512)
	 */
	private $url;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=32)
	 */
	private $status;

	/**
	 * @var Collection<int, Page>
	 * @ORM\ManyToMany(targetEntity=Page::class)
	 */
	private $pages;

	/**
	 * @var Collection<int, Page>
	 * @ORM\ManyToMany(targetEntity=Page::class)
	 * @ORM\JoinTable(name="project_ignored_page")
	 */
	private $ignoredPages;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
		$this->status = self::STATUS_NEW;
		$this->pages = new ArrayCollection();
		$this->ignoredPages = new ArrayCollection();
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
		$this->name = $name;

		return $this;
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

	public function getStatus(): string
	{
		return $this->status;
	}

	public function setStatus(string $status): self
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @return Collection<int, Page>
	 */
	public function getPages(): Collection
	{
		return $this->pages;
	}

	public function addPage(Page $page): self
	{
		if (!$this->pages->contains($page)) {
			$this->pages->add($page);
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
	public function getIgnoredPages(): Collection
	{
		return $this->ignoredPages;
	}

	public function addIgnoredPage(Page $ignoredPage): self
	{
		if (!$this->ignoredPages->contains($ignoredPage)) {
			$this->ignoredPages->add($ignoredPage);
		}

		return $this;
	}

	public function removeIgnoredPage(Page $ignoredPage): self
	{
		$this->ignoredPages->removeElement($ignoredPage);

		return $this;
	}

	/**
	 * @return Collection<int, Page>
	 */
	public function getActivePages(): Collection
	{
		$pageArray = [];

		foreach ($this->getPages() as $page) {
			$pageArray[$page->getId()] = $page;
		}

		foreach ($this->getIgnoredPages() as $ignoredPage) {
			unset($pageArray[$ignoredPage->getId()]);
		}

		return new ArrayCollection(array_values($pageArray));
	}
}
