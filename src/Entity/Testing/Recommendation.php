<?php

namespace App\Entity\Testing;

use App\Entity\Page;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\Testing\RecommendationRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
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
	 * @Groups({"default"})
	 */
	private string $template;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 * @Groups({"default"})
	 *
	 * @var array<mixed,mixed>
	 */
	private ?array $parameters = [];

	/**
	 * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="recommendations")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 * @Groups({"recommendation"})
	 * @MaxDepth(1)
	 */
	private Page $relatedPage;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private string $type;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Groups({"default"})
	 */
	private string $uniqueName;

	/**
	 * @ORM\ManyToOne(targetEntity=TestResult::class, inversedBy="recommendations")
	 * @ORM\JoinColumn(name="parent_result_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 * @Groups({"recommendation"})
	 * @MaxDepth(1)
	 */
	private TestResult $parentResult;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private DateTimeInterface $dateCreated;

	/**
	 * @ORM\Column(type="datetime")
	 * @Groups({"default"})
	 */
	private DateTimeInterface $dateLastOccured;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Groups({"default"})
	 */
	private ?DatetimeInterface $dateCompleted;

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

	public function __construct()
	{
		$this->dateCreated = new DateTime();
		$this->dateLastOccured = new DateTime();
		$this->dateCompleted = null;
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
		return $this->parameters ?: [];
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

	/**
	 * @Groups({"default"})
	 */
	public function getHtmlTitle(): string
	{
		$htmlTemplate = $this->getTemplate();

		foreach (array_keys($this->getParameters()) as $key) {
			$htmlTemplate = str_replace($key, "<span class='parameter'>$key</span>", $htmlTemplate);
		}

		return strtr($htmlTemplate, $this->getParameters());
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
			throw new Exception(sprintf('%s is not a valid recommendation type. Accecpted types are %s', $type, implode(', ', $allowedTypes)));
		}

		$this->type = $type;

		return $this;
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

	public function getParentResult(): TestResult
	{
		return $this->parentResult;
	}

	public function setParentResult(TestResult $parentResult): self
	{
		$this->parentResult = $parentResult;

		return $this;
	}

	public function getDateCreated(): ?DatetimeInterface
	{
		return $this->dateCreated;
	}

	public function setDateCreated(DatetimeInterface $dateCreated): self
	{
		$this->dateCreated = $dateCreated;

		return $this;
	}

	public function getDateLastOccured(): ?DatetimeInterface
	{
		return $this->dateLastOccured;
	}

	public function setDateLastOccured(DatetimeInterface $dateLastOccured): self
	{
		$this->dateLastOccured = $dateLastOccured;

		return $this;
	}

	public function getDateCompleted(): ?DatetimeInterface
	{
		return $this->dateCompleted;
	}

	public function setDateCompleted(?DatetimeInterface $dateCompleted): self
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

	public function isIgnored(): ?bool
	{
		$ignoreEntries = new ArrayCollection(
			array_merge(
				$this->getRelatedPage()->getIgnoreEntries()->toArray(),
				$this->getRelatedPage()->getProject()->getIgnoreEntries()->toArray(),
				$this->getRelatedPage()->getProject()->getOwner()->getIgnoreEntries()->toArray()
				)
			);

		$recommendation = $this;
		$matchingIgnoreEntries = $ignoreEntries->filter(function (IgnoreEntry $entry) use ($recommendation) {
			return $entry->getRecommendationUniqueName() == $recommendation->getUniqueName()
				&& $entry->getTest() == $recommendation->getParentResult()->getUniqueName()
				&& $entry->getTool() == $recommendation->getParentResult()->getParentResponse()->getTool();
		});

		return $matchingIgnoreEntries->count() > 0;
	}

	public function getProject(): Project
	{
		return $this->getRelatedPage()->getProject();
	}

	/**
	 * Returns an app-wide unique identifier composed of the related page's ID,
	 * the tool's name and the recommendation's unique name.
	 *
	 * This identifier can be used to easily match new recommendation results to
	 * existing recommendations.
	 *
	 * The resulting value is a simple MD5 hash of the serialized values.
	 */
	public function getUniqueMatchingIdentifier(): string
	{
		$parentResponse = $this->getParentResult()->getParentResponse();

		return static::generateUniqueMatchingIdentifier(
			$this->getRelatedPage()->getId(),
			$parentResponse->getTool(),
			$this->getUniqueName()
		);
	}

	/**
	 * Returns an app-wide unique identifier composed of the related page's ID,
	 * the tool's name and the recommendation's unique name.
	 *
	 * This identifier can be used to easily match new recommendation results to
	 * existing recommendations.
	 *
	 * The resulting value is a simple MD5 hash of the serialized values.
	 */
	public static function generateUniqueMatchingIdentifier(int $pageId, string $toolName, string $recommendationUniqueName): string
	{
		return md5(serialize([
			$pageId,
			$toolName,
			$recommendationUniqueName,
		]));
	}

	public function complete(User $user): static
	{
		$this->setCompletedBy($user)
			->setDateCompleted(new DateTime())
			->setIsCompleted(true);

		return $this;
	}
}
