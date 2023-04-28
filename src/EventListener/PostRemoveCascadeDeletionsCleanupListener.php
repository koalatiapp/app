<?php

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * When entities are deleted, their relations may be deleted automatically by
 * database-level on delete constraints. When this happens, if the relation
 * entities were managed by Doctrine, they will remain managed, which can cause
 * issues of "non-persisted entity found in association graph" in later flushes.
 *
 * This post-remove listener checks to remove any such relation entities,
 * ensuring that Doctrine's managed state matches the state of the database and
 * that issues such as the one above do not occur.
 */
#[AsDoctrineListener(event: Events::postRemove)]
class PostRemoveCascadeDeletionsCleanupListener
{
	public function __construct(
		private PropertyAccessorInterface $propertyAccessor,
	) {
	}

	public function postRemove(LifecycleEventArgs $args): void
	{
		$deletedEntity = $args->getObject();
		$entityManager = $args->getObjectManager();

		if (!($entityManager instanceof EntityManagerInterface)) {
			return;
		}

		foreach ($entityManager->getUnitOfWork()->getIdentityMap() as $className => $managedEntities) {
			$relevantProperties = $this->getRelevantPropertiesWithOnDeleteCascade($className, $deletedEntity::class);

			if (!$relevantProperties) {
				continue;
			}

			foreach ($managedEntities as $managedEntity) {
				foreach ($relevantProperties as $relevantPropertyName) {
					$value = $this->propertyAccessor->getValue($managedEntity, $relevantPropertyName);

					if ($value === $deletedEntity) {
						$entityManager->detach($managedEntity);
						break;
					}
				}
			}
		}
	}

	/**
	 * Finds and returns the names of properties on `$entityClass` that are
	 * relations to `$removedEntityClass` and that have `onDelete="CASCADE"` set.
	 *
	 * @return array<int,class-string>
	 */
	private function getRelevantPropertiesWithOnDeleteCascade(string $entityClass, string $removedEntityClass): array
	{
		static $cache = [];

		if (!isset($cache[$entityClass])) {
			$reflectedClass = new \ReflectionClass($entityClass);

			foreach ($reflectedClass->getProperties() as $property) {
				$joinColumnAttribute = $property->getAttributes(JoinColumn::class)[0] ?? null;
				$onDeleteArgument = $joinColumnAttribute?->getArguments()['onDelete'] ?? null;

				if ($onDeleteArgument != "CASCADE") {
					// Property does not have onDelelete="CASCADE" set - we don't care about it.
					continue;
				}

				$relationAttribute = $property->getAttributes(ManyToOne::class)[0] ?? null;
				if (!$relationAttribute) {
					$relationAttribute = $property->getAttributes(OneToOne::class)[0] ?? null;
				}

				$targetEntityClass = $relationAttribute?->getArguments()['targetEntity'] ?? null;

				if (!$targetEntityClass) {
					// No ManyToOne or OneToOne relations were found - we don't care about it.
					continue;
				}

				$cache[$entityClass][$targetEntityClass][] = $property->getName();
			}
		}

		return $cache[$entityClass][$removedEntityClass] ?? [];
	}
}
