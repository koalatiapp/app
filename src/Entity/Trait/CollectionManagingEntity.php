<?php

namespace App\Entity\Trait;

trait CollectionManagingEntity
{
	private function addCollectionElement(string $property, mixed $item, ?string $itemOwnershipProperty = null): self
	{
		if (!$this->$property->contains($item)) {
			$this->$property[] = $item;

			if ($itemOwnershipProperty) {
				$setter = $this->getCollectionElementSetter($itemOwnershipProperty);
				$item->$setter($this);
			}
		}

		return $this;
	}

	private function removeCollectionElement(string $property, mixed $item, ?string $itemOwnershipProperty = null): self
	{
		if ($this->$property->removeElement($item)) {
			if ($itemOwnershipProperty) {
				$getter = $this->getCollectionElementGetter($itemOwnershipProperty);
				$setter = $this->getCollectionElementSetter($itemOwnershipProperty);
				// set the owning side to null (unless already changed)
				if ($item->$getter() === $this) {
					$item->$setter(null);
				}
			}
		}

		return $this;
	}

	private function getCollectionElementGetter(string $property): string
	{
		return 'get'.ucfirst($property);
	}

	private function getCollectionElementSetter(string $property): string
	{
		return 'set'.ucfirst($property);
	}
}
