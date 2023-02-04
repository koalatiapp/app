<?php

namespace App\Serializer\EntityExtension;

use App\Entity\Project;
use App\Storage\ProjectStorage;

class ProjectExtension implements EntityExtensionInterface
{
	public function __construct(
		private ProjectStorage $projectStorage
	) {
	}

	/**
	 * @return bool whether this entity normalizer supports the provided entity
	 */
	public function supports(object $entity): bool
	{
		return $entity instanceof Project;
	}

	/**
	 * @param array<string,mixed> $normalizedData
	 *
	 * @return array<string,mixed>
	 */
	public function extendNormalization(object $entity, array $normalizedData): array
	{
		$normalizedData['favicon_url'] = $this->projectStorage->faviconUrl($entity);
		$normalizedData['thumbnail_url'] = $this->projectStorage->thumbnailUrl($entity);

		return $normalizedData;
	}
}
