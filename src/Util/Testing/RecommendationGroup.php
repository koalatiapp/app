<?php

namespace App\Util\Testing;

use App\Entity\Testing\Recommendation;
use App\Exception\WrongRecommendationTypeException;
use Countable;
use Doctrine\Common\Collections\ArrayCollection;
use IteratorAggregate;
use Traversable;

/**
 * A group of recommendations of the same type.
 *
 * @implements IteratorAggregate<int, Recommendation>
 */
class RecommendationGroup implements Countable, IteratorAggregate
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

	public function getSample(): ?Recommendation
	{
		return $this->recommendations->first() ?: null;
	}

	public function getType(): ?string
	{
		return $this->getSample()?->gettype();
	}

	public function getUniqueName(): ?string
	{
		return $this->getSample()?->getUniqueName();
	}

	public function getTemplate(): ?string
	{
		return $this->getSample()?->getTemplate();
	}

	public function getTitle(): ?string
	{
		return $this->getSample()?->getTitle();
	}

	public function count(): int
	{
		return $this->recommendations->count();
	}

	/**
	 * @return Traversable<int, Recommendation>
	 */
	public function getIterator(): Traversable
	{
		return $this->recommendations->getIterator();
	}

	/**
	 * Groups the provided recommendations by type.
	 *
	 * @param ArrayCollection<int, Recommendation> $recommendations
	 *
	 * @return RecommendationGroup[]
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
