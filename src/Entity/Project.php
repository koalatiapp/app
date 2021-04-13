<?php

namespace App\Entity;

use App\Entity\Testing\Recommendation;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
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
	 * @ORM\OneToMany(targetEntity=Page::class, mappedBy="project")
	 */
	private $pages;

	/**
	 * @var Collection<int, ProjectMember>
	 * @ORM\OneToMany(targetEntity=ProjectMember::class, mappedBy="project")
	 */
	private $teamMembers;

	/**
	 * @var Collection<int, Recommendation>
	 * @ORM\OneToMany(targetEntity=Recommendation::class, mappedBy="project", orphanRemoval=true)
	 */
	private Collection $recommendations;

	public function __construct()
	{
		$this->dateCreated = new \DateTime();
		$this->status = self::STATUS_NEW;
		$this->pages = new ArrayCollection();
		$this->teamMembers = new ArrayCollection();
		$this->recommendations = new ArrayCollection();
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
	 * @return ArrayCollection<int, Page>
	 */
	public function getPages(): Collection
	{
		return $this->pages;
	}

	public function addPage(Page $page): self
	{
		if (!$this->pages->contains($page)) {
			$this->pages->add($page);
			$page->setProject($this);
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
	public function getActivePages(): Collection
	{
		$criteria = Criteria::create()->where(Criteria::expr()->eq('isIgnored', false));

		return $this->getPages()->matching($criteria);
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
			$recommendation->setProject($this);
		}

		return $this;
	}

	public function removeRecommendation(Recommendation $recommendation): self
	{
		if ($this->recommendations->removeElement($recommendation)) {
			// set the owning side to null (unless already changed)
			if ($recommendation->getProject() === $this) {
				$recommendation->setProject(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int,Recommendation>
	 */
	public function getSortedRecommendations(): Collection
	{
		/**
		 * @var \ArrayIterator<int, Recommendation> $recommendationIterator
		 */
		$recommendationIterator = $this->recommendations->getIterator();
		$recommendationIterator->uasort(function ($a, $b) {
			$priorities = Recommendation::TYPE_PRIORITIES;

			return $priorities[$a->getType()] > $priorities[$b->getType()] ? 1 : -1;
		});

		return new ArrayCollection(iterator_to_array($recommendationIterator));
	}

	/**
	 * @return Collection<int,Recommendation>
	 */
	public function getActiveRecommendations(): Collection
	{
		return $this->getSortedRecommendations()->filter(function ($recommendation) {
			return !$recommendation->getIsCompleted()
				&& !$recommendation->getIsIgnored()
				&& !$recommendation->getRelatedPage()->getIsIgnored();
		});
	}
}
