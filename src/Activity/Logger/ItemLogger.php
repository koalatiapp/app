<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\Checklist\Item;

/**
 * @extends AbstractEntityActivityLogger<Item>
 */
class ItemLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return Item::class;
	}

	public function postPersist(object &$item, ?array $originalData): void
	{
		if ($item->getIsCompleted() != $originalData['isCompleted']) {
			$this->log(
				type: $item->getIsCompleted() ? "checklist_item_complete" : "checklist_item_uncomplete",
				organization: $item->getChecklist()->getProject()->getOwnerOrganization(),
				project: $item->getChecklist()->getProject(),
				target: $item,
			);
		}
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function postRemove(object &$item): void
	{
	}
}
