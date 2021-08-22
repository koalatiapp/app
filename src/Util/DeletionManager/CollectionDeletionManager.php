<?php

namespace App\Util\DeletionManager;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class CollectionDeletionManager
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private EntityManagerInterface $em
	) {
	}

	/**
	 * @param Collection<mixed,mixed> $collection
	 */
	public function deleteItems(Collection $collection): EntityManagerInterface
	{
		foreach ($collection as $item) {
			$this->em->remove($item);
		}

		return $this->em;
	}
}
