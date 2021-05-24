<?php

namespace App\Entity;

use App\Entity\Testing\Recommendation;
use App\Repository\ProjectRepository;
use App\Util\Testing\RecommendationGroup;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
	public const STATUS_NEW = 'NEW';
	public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
	public const STATUS_COMPLETED = 'COMPLETED';
	public const ROLE_ADMIN = 'ROLE_ADMIN';
	public const ROLE_MANAGE = 'ROLE_MANAGE';
	public const ROLE_USER = 'ROLE_USER';
	public const ROLE_INVITED = 'ROLE_INVITED';

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 * @Groups({"default"})
	 */
	private $id;

	/**
	 * @var string
	 * @Assert\NotBlank
	 * @Assert\Length(max = 255)
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private $name;

	/**
	 * @var \App\Entity\User|null
	 * @ORM\ManyToOne(targetEntity=User::class, inversedBy="personalProjects")
	 * @Groups({"default"})
	 */
	private $ownerUser;

	/**
	 * @var \App\Entity\Organization|null
	 * @ORM\ManyToOne(targetEntity=Organization::class, inversedBy="projects")
	 * @Groups({"default"})
	 */
	private $ownerOrganization;

	/**
	 * @var \DateTimeInterface
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private $dateCreated;

	/**
	 * @var string
	 * @Assert\NotBlank
	 * @Assert\Url(relativeProtocol = true)
	 * @ORM\Column(type="string", length=512)
	 * @Groups({"default"})
	 */
	private $url;

	/**
	 * @var string
	 * @ORM\Column(type="string", length=32)
	 * @Groups({"default"})
	 */
	private $status;

	/**
	 * @var Collection<int, Page>
	 * @ORM\OneToMany(targetEntity=Page::class, mappedBy="project")
	 * @Groups({"project"})
	 * @MaxDepth(1)
	 */
	private $pages;

	/**
	 * @var Collection<int, ProjectMember>
	 * @ORM\OneToMany(targetEntity=ProjectMember::class, mappedBy="project")
	 * @Groups({"default"})
	 * @MaxDepth(1)
	 */
	private $teamMembers;

	public function __construct()
	{
		$this->dateCreated = new DateTime();
		$this->status = self::STATUS_NEW;
		$this->pages = new ArrayCollection();
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
	 * @return ArrayCollection<int,Recommendation>
	 */
	public function getRecommendations(): ArrayCollection
	{
		$recommendations = new ArrayCollection();

		foreach ($this->getActivePages() as $page) {
			foreach ($page->getRecommendations() as $recommendation) {
				$recommendations->add($recommendation);
			}
		}

		return $recommendations;
	}

	/**
	 * @return ArrayCollection<int,Recommendation>
	 */
	public function getSortedRecommendations(): ArrayCollection
	{
		/**
		 * @var \ArrayIterator<int, Recommendation> $recommendationIterator
		 */
		$recommendationIterator = $this->getRecommendations()->getIterator();
		$recommendationIterator->uasort(function ($a, $b) {
			$priorities = Recommendation::TYPE_PRIORITIES;

			return $priorities[$a->getType()] > $priorities[$b->getType()] ? 1 : -1;
		});

		return new ArrayCollection(iterator_to_array($recommendationIterator));
	}

	/**
	 * @return ArrayCollection<int,Recommendation>
	 */
	public function getActiveRecommendations(): ArrayCollection
	{
		return $this->getSortedRecommendations()->filter(function ($recommendation) {
			return !$recommendation->getIsCompleted()
				&& !$recommendation->getIsIgnored()
				&& !$recommendation->getRelatedPage()->getIsIgnored();
		});
	}

	/**
	 * @return ArrayCollection<string,RecommendationGroup>
	 */
	public function getActiveRecommendationGroups(): ArrayCollection
	{
		$groups = RecommendationGroup::fromLooseRecommendations($this->getActiveRecommendations());

		return new ArrayCollection($groups);
	}
}
