<?php

namespace App\Mercure;

use App\Entity\User;

/**
 * An entity handler provides methods to extract information
 * that is revelant for creating Mercure updates for a specific
 * entity or class impleeting the `MercureEntityInterface`.
 */
interface EntityHandlerInterface
{
	/**
	 * Returns the class of the entity this handler supports.
	 */
	public function getSupportedEntity(): string;

	/**
	 * Returns a string identifying the type of data this handler supports.
	 * Types should be in UpperCamelCase.
	 *
	 * Ex.: a handler that manages Comments might define its type as `"Comment"`.
	 */
	public function getType(): string;

	/**
	 * Returns the list of users who should receive a Mercure update when
	 * the provided entity changes.
	 *
	 * This list should include every user who may have read access to the
	 * provided entity.
	 *
	 * @return array<int,User>
	 */
	public function getAffectedUsers(MercureEntityInterface $entity): array;
}
