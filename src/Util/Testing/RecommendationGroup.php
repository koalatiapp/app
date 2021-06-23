<?php

namespace App\Util\Testing;

use App\Entity\Testing\Recommendation;
use App\Entity\User;
use App\Exception\WrongRecommendationTypeException;
use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A group of recommendations of the same type.
 */
class RecommendationGroup implements Countable
{
	/**
	 * @var ArrayCollection<int, Recommendation>
	 */
	private ArrayCollection $recommendations;

	/**
	 * @param ArrayCollection<int, Recommendation> $recommendations
	 */
	public function __construct(ArrayCollection $recommendations)
	{
		$this->recommendations = $recommendations;
	}

	/**
	 * @Groups({"recommendation_group"})
	 *
	 * @return ArrayCollection<int, Recommendation>
	 */
	public function getRecommendations(): ArrayCollection
	{
		return $this->recommendations;
	}

	public function add(Recommendation $recommendation): self
	{
		if ($this->getType() != null && $recommendation->getType() != $this->getType()) {
			throw new WrongRecommendationTypeException();
		}

		$this->recommendations->add($recommendation);

		return $this;
	}

	/**
	 * @Groups({"default"})
	 */
	public function getSampleId(): ?int
	{
		return $this->getSample()?->getId() ?: null;
	}

	/**
	 * @Groups({"recommendation_group"})
	 */
	public function getSample(): ?Recommendation
	{
		return $this->recommendations->first() ?: null;
	}

	/**
	 * @Groups({"default"})
	 */
	public function getType(): ?string
	{
		return $this->getSample()?->gettype();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getUniqueName(): ?string
	{
		return $this->getSample()?->getUniqueName();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getTemplate(): ?string
	{
		return $this->getSample()?->getTemplate();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getTitle(): ?string
	{
		return $this->getSample()?->getTitle();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getHtmlTitle(): ?string
	{
		return $this->getSample()?->getHtmlTitle();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getProjectOwnerType(): ?string
	{
		$projectOwner = $this->getSample()?->getProject()->getOwner();

		if ($projectOwner instanceof User) {
			return 'user';
		}

		return 'organization';
	}

	/**
	 * @Groups({"default"})
	 */
	public function getTool(): ?string
	{
		return $this->getSample()?->getParentResult()?->getParentResponse()?->getTool();
	}

	public function count(): int
	{
		return $this->recommendations->count();
	}

	/**
	 * @Groups({"default"})
	 */
	public function getCount(): int
	{
		return $this->count();
	}

	/**
	 * Groups the provided recommendations by type.
	 *
	 * @param ArrayCollection<int, Recommendation> $recommendations
	 *
	 * @return Array<string,RecommendationGroup>
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

		return $groups;
	}
}
