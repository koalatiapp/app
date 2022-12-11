<?php

namespace App\Api\State;

use App\Api\Model\Team;
use App\Entity\Organization;

/**
 * @extends AbstractDoctrineStateWrapper<Organization,Team>
 */
final class TeamState extends AbstractDoctrineStateWrapper
{
	public static function getEntityClass(): string
	{
		return Organization::class;
	}

	public static function getApiResourceClass(): string
	{
		return Team::class;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Organization $entity
	 * @param Team         $resource
	 */
	protected function updateEntityFromResource(object &$entity, object $resource): object
	{
		return parent::updateEntityFromResource($entity, $resource);
	}
}
