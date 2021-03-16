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
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="personalProjects")
	 */
	private $ownerUser;

	/**
	 * @var \App\Entity\Organization|null
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="projects")
	 */
	private $ownerOrganization;

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

	/**
	 * @var Collection<int, ProjectMember>
	 * @ORM\OneToMany(targetEntity=ProjectMember::class, mappedBy="project")
	 */
	private $teamMembers;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
		$this->status = self::STATUS_NEW;
		$this->pages = new ArrayCollection();
		$this->ignoredPages = new ArrayCollection();
		$this->teamMembers = new ArrayCollection();
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

	/**
	 * Returns the list of Koalati automated tools to run for this project.
	 *
	 * @TODO: Check the project/user/organization's settings to get the list of tools (replacing this method stub)
	 *
	 * @return string[]
	 */
	public function getEnabledAutomatedTools(): array
	{
		return [
			'@koalati/tool-seo',
			'@koalati/tool-accessibility',
			'@koalati/tool-console',
			'@koalati/tool-loading-speed',
			'@koalati/tool-responsive',
			'@koalati/tool-social',
		];
	}

	/**
	 * Returns the priority of this project's processing requests.
	 * The higher the number, the higher the priority.
	 * The default priority for free users is `1`.
	 *
	 * @TODO: Check the project/user/organization's subscription plan (replacing this method stub)
	 */
	public function getPriority(): int
	{
		return 1;
	}

	public function getOwnerOrganization(): ?Organization
	{
		return $this->ownerOrganization;
	}

	public function setOwnerOrganization(?Organization $ownerOrganization): self
	{
		$this->ownerOrganization = $ownerOrganization;

		return $this;
	}

	/**
	 * @return Collection<int, ProjectMember>
	 */
	public function getTeamMembers(): Collection
	{
		return $this->teamMembers;
	}

	public function addTeamMember(ProjectMember $teamMember): self
	{
		if (!$this->teamMembers->contains($teamMember)) {
			$this->teamMembers[] = $teamMember;
			$teamMember->setProject($this);
		}

		return $this;
	}

	public function removeTeamMember(ProjectMember $teamMember): self
	{
		if ($this->teamMembers->removeElement($teamMember)) {
			// set the owning side to null (unless already changed)
			if ($teamMember->getProject() === $this) {
				$teamMember->setProject(null);
			}
		}

		return $this;
	}
}
