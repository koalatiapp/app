<?php

namespace App\Api\State;

use App\Api\Model\ChecklistItem;
use App\Entity\Checklist\Item;

/**
 * @extends AbstractDoctrineStateWrapper<Item,ChecklistItem>
 */
final class ChecklistItemState extends AbstractDoctrineStateWrapper
{
	public static function getEntityClass(): string
	{
		return Item::class;
	}

	public static function getApiResourceClass(): string
	{
		return ChecklistItem::class;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Item          $entity
	 * @param ChecklistItem $resource
	 */
	protected function updateEntityFromResource(object &$entity, object $resource): object
	{
		return parent::updateEntityFromResource($entity, $resource);
	}
}
