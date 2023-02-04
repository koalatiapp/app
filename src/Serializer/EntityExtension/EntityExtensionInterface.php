<?php

namespace App\Serializer\EntityExtension;

interface EntityExtensionInterface
{
	/**
	 * @return bool whether this entity normalizer supports the provided entity
	 */
	public function supports(object $entity): bool;

	/**
	 * @param array<string,mixed> $normalizedData
	 *
	 * @return array<string,mixed>
	 */
	public function extendNormalization(object $entity, array $normalizedData): array;
}
