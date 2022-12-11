<?php

namespace App\Api\Model;

/**
 * @template T of object
 */
interface EntityFacadeInterface
{
	/**
	 * Generates the API resource from an existing Doctrine entity.
	 *
	 * @param T $entity
	 *
	 * @return self<T>
	 */
	public static function fromEntity(object $entity): self;
}
