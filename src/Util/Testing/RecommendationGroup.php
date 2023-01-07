<?php

namespace App\Util\Testing;

use App\Entity\Project;
use App\Entity\Testing\Recommendation;
use App\Entity\User;
use App\Mercure\MercureEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A group of recommendations of the same type.
 */
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
	#[Groups(['recommendation_group'])]
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

	#[Groups(['default'])]
	public function getSampleId(): ?int
	{
		return $this->getSample()?->getId() ?: null;
	}

	#[Groups(['recommendation_group'])]
	public function getSample(): ?Recommendation
	{
		if (!$this->isSorted) {
			$this->sort();
		}

		return $this->getRecommendations()->first() ?: null;
	}

	#[Groups(['default'])]
	public function getProjectId(): ?int
	{
		return $this->getSample()?->getProject()->getId();
	}

	public function getProject(): ?Project
	{
		return $this->getSample()?->getProject();
	}

	#[Groups(['default'])]
	public function getType(): ?string
	{
		return $this->getSample()?->getType();
	}

	#[Groups(['default'])]
	public function getUniqueName(): ?string
	{
		return $this->getSample()?->getUniqueName();
	}

	#[Groups(['default'])]
	public function getTemplate(): ?string
	{
		return $this->getSample()?->getTemplate();
	}

	#[Groups(['default'])]
	public function getTitle(): ?string
	{
		return $this->getSample()?->getTitle();
	}

	#[Groups(['default'])]
	public function getHtmlTitle(): ?string
	{
		return $this->getSample()?->getHtmlTitle();
	}

	#[Groups(['default'])]
	public function getProjectOwnerType(): ?string
	{
		$projectOwner = $this->getSample()?->getProject()->getOwner();

		if ($projectOwner instanceof User) {
			return 'user';
		}

		return 'organization';
	}

	#[Groups(['default'])]
	public function getProjectOwnerId(): ?int
	{
		$projectOwner = $this->getSample()?->getProject()->getOwner();

		return $projectOwner->getId();
	}

	#[Groups(['default'])]
	public function getTool(): ?string
	{
		return $this->getSample()?->getParentResult()?->getParentResponse()?->getTool();
	}

	public function count(): int
	{
		return $this->recommendations->count();
	}

	#[Groups(['default'])]
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

			return $priorities[$a->getType()] > $priorities[$b->getType()] ? 1 : -1;
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

	#[Groups(['default'])]
	public function getId(): string
	{
		return $this->getUniqueMatchingIdentifier();
	}

	/**
	 * Returns an identifier can be used to easily group together identical recommendations
	 * that affect different pages within a project.
	 *
	 * The resulting value is a simple MD5 hash of the serialized values.
	 */
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
		return md5(serialize([
			$projectId,
			$toolName,
			$recommendationUniqueName,
		]));
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
		return "recommendation_group.read";
	}
}
