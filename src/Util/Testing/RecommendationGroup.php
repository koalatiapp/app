<?php

namespace App\Util\Testing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Api\State\RecommendationGroupProcessor;
use App\Api\State\RecommendationGroupProvider;
use App\Entity\Project;
use App\Entity\Testing\Recommendation;
use App\Entity\User;
use App\Mercure\MercureEntityInterface;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A group of recommendations of the same type.
 */
#[ApiResource(
	provider: RecommendationGroupProvider::class,
	openapiContext: ["tags" => ['Recommendation Group']],
	normalizationContext: ["groups" => "recommendation.list"],
	uriTemplate: '/projects/{projectId}/recommendation_groups',
	uriVariables: ['projectId' => new Link(fromClass: Project::class)],
	operations: [new GetCollection()],
)]
#[ApiResource(
	provider: RecommendationGroupProvider::class,
	openapiContext: ["tags" => ['Recommendation Group']],
	normalizationContext: ["groups" => "recommendation.read"],
	processor: RecommendationGroupProcessor::class,
	operations: [
		new Get(
			security: "is_granted('project_view', object.getProject())",
		),
		new Patch(
			security: "is_granted('project_participate', object.getProject())",
			denormalizationContext: ["groups" => "recommendation.write"],
		),
	],
)]
class RecommendationGroup implements \Countable, MercureEntityInterface
{
	/**
	 * Indicates whether the recommendation collection is sorted by priority or not.
	 */
	private bool $isSorted = false;

	/**
	 * @param ArrayCollection<int, Recommendation> $recommendations
	 */
	public function __construct(private ArrayCollection $recommendations)
	{
	}

	/**
	 * @return ArrayCollection<int, Recommendation>
	 */
	#[Groups(['recommendation.read'])]
	public function getRecommendations(): ArrayCollection
	{
		return $this->recommendations;
	}

	public function add(Recommendation $recommendation): self
	{
		$this->recommendations->add($recommendation);
		$this->isSorted = false;

		return $this;
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getSampleId(): ?int
	{
		return $this->getSample()?->getId() ?: null;
	}

	#[Groups(['recommendation.read'])]
	public function getSample(): ?Recommendation
	{
		if (!$this->isSorted) {
			$this->sort();
		}

		return $this->getRecommendations()->first() ?: null;
	}

	public function getProjectId(): ?int
	{
		return $this->getSample()?->getProject()->getId();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getProject(): ?Project
	{
		return $this->getSample()?->getProject();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getType(): ?string
	{
		return $this->getSample()?->getType();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getUniqueName(): ?string
	{
		return $this->getSample()?->getUniqueName();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getTemplate(): ?string
	{
		return $this->getSample()?->getTemplate();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getTitle(): ?string
	{
		return $this->getSample()?->getTitle();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getProjectOwnerType(): ?string
	{
		$projectOwner = $this->getSample()?->getProject()->getOwner();

		if ($projectOwner instanceof User) {
			return 'user';
		}

		return 'organization';
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getProjectOwnerId(): ?int
	{
		$projectOwner = $this->getSample()?->getProject()->getOwner();

		return $projectOwner->getId();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getTool(): ?string
	{
		return $this->getSample()?->getParentResult()?->getParentResponse()?->getTool();
	}

	#[Groups(['recommendation.read'])]
	public function getIsCompleted(): bool
	{
		foreach ($this->getRecommendations() as $recommendation) {
			if (!$recommendation->getIsCompleted()) {
				return false;
			}
		}

		return true;
	}

	#[Groups(['recommendation.write'])]
	public function setIsCompleted(bool $isCompleted): self
	{
		foreach ($this->getRecommendations() as $recommendation) {
			$recommendation->setIsCompleted($isCompleted);
		}

		return $this;
	}

	public function count(): int
	{
		return $this->recommendations->count();
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getCount(): int
	{
		return $this->count();
	}

	private function sort(): self
	{
		/**
		 * @var \ArrayIterator<int, Recommendation> $recommendationIterator
		 */
		$recommendationIterator = $this->getRecommendations()->getIterator();
		$recommendationIterator->uasort(function ($a, $b) {
			$priorities = Recommendation::TYPE_PRIORITIES;

			if ($priorities[$a->getType()] > $priorities[$b->getType()]) {
				return 1;
			} elseif ($priorities[$a->getType()] < $priorities[$b->getType()]) {
				return -1;
			}

			return strnatcasecmp($a->getTitle(), $b->getTitle());
		});

		$this->recommendations = new ArrayCollection(iterator_to_array($recommendationIterator));
		$this->isSorted = true;

		return $this;
	}

	/**
	 * Groups the provided recommendations by type.
	 *
	 * @param ArrayCollection<int, Recommendation> $recommendations
	 *
	 * @return array<string,RecommendationGroup>
	 */
	public static function fromLooseRecommendations(ArrayCollection $recommendations): array
	{
		$groups = [];

		foreach ($recommendations as $recommendation) {
			$uniqueName = $recommendation->getUniqueName();

			if (!isset($groups[$uniqueName])) {
				$groups[$uniqueName] = new RecommendationGroup(new ArrayCollection());
			}

			$groups[$uniqueName]->add($recommendation);
		}

		uasort($groups, function (RecommendationGroup $groupA, RecommendationGroup $groupB) {
			$priorities = Recommendation::TYPE_PRIORITIES;

			if ($priorities[$groupA->getType()] != $priorities[$groupB->getType()]) {
				return $priorities[$groupA->getType()] > $priorities[$groupB->getType()] ? 1 : -1;
			}

			$countA = $groupA->count();
			$countB = $groupB->count();

			return $countA < $countB ? 1 : ($countA == $countB ? 0 : -1);
		});

		return $groups;
	}

	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getId(): ?string
	{
		return $this->getUniqueMatchingIdentifier();
	}

	/**
	 * Returns an identifier can be used to easily group together identical recommendations
	 * that affect different pages within a project.
	 *
	 * The resulting value is a simple MD5 hash of the serialized values.
	 */
	#[Groups(['recommendation.list', 'recommendation.read'])]
	public function getUniqueMatchingIdentifier(): string
	{
		$sample = $this->getSample();
		$parentResponse = $sample->getParentResult()->getParentResponse();

		return static::generateGroupMatchingIdentifier(
			$sample->getProject()->getId(),
			$parentResponse->getTool(),
			$sample->getUniqueName()
		);
	}

	/**
	 * Returns an identifier can be used to easily group together identical recommendations
	 * that affect different pages within a project.
	 *
	 * The resulting value is a simple MD5 hash of the serialized values.
	 */
	public static function generateGroupMatchingIdentifier(int $projectId, string $toolName, string $recommendationUniqueName): string
	{
		return $projectId.'Fm'.md5(serialize([
			$toolName,
			$recommendationUniqueName,
		]));
	}

	public static function loadFromGroupMatchingIdentifier(string $identifier, ProjectRepository $projectRepository): ?self
	{
		$projectId = explode('Fm', $identifier)[0] ?? null;
		$project = $projectRepository->find($projectId);

		if (!$project) {
			return null;
		}

		foreach ($project->getActiveRecommendationGroups() as $recommendationGroup) {
			if ($recommendationGroup->getUniqueMatchingIdentifier() == $identifier) {
				return $recommendationGroup;
			}
		}

		return null;
	}

	/**
	 * Returns an identifier can be used to easily group together identical recommendations
	 * that affect different pages within a project.
	 *
	 * The resulting value is a simple MD5 hash of the serialized values.
	 */
	public static function generateGroupMatchingIdentifierFromRecommendation(Recommendation $recommendation): string
	{
		$parentResponse = $recommendation->getParentResult()->getParentResponse();

		return static::generateGroupMatchingIdentifier(
			$recommendation->getProject()->getId(),
			$parentResponse->getTool(),
			$recommendation->getUniqueName()
		);
	}

	public function getMercureSerializationGroup(): string
	{
		return "recommendation.read";
	}
}
