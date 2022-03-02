<?php

namespace App\Mercure\EntityHandler;

use App\Entity\Checklist\Item;
use App\Mercure\MercureEntityInterface;
use App\Mercure\EntityHandlerInterface;

class ChecklistItemHandler implements EntityHandlerInterface
{
	public function getSupportedEntity(): string
	{
		return Item::class;
	}

	public function getType(): string
	{
		return "ChecklistItem";
	}

	/**
	 * @param Item $item
	 */
	public function getAffectedUsers(MercureEntityInterface $item): array
	{
		$project = $item->getChecklist()->getProject();

		return (new ProjectHandler())->getAffectedUsers($project);
	}
}
