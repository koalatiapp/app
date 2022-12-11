<?php

namespace App\Api\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Api\Model\EntityFacadeInterface;
use App\Exception\NotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Base class for API state providers and processors that act as a wrapper
 * around the default Doctrine state providers and processors.
 *
 * @template TEntity of object
 * @template TResource of object
 *
 * @implements ProviderInterface<TResource>
 */
abstract class AbstractDoctrineStateWrapper implements ProviderInterface, ProcessorInterface
{
	/** @var ProviderInterface<TEntity> */
	protected ProviderInterface $doctrineCollectionProvider;
	protected ProcessorInterface $persistProcessor;
	protected ProcessorInterface $removeProcessor;
	protected EntityManagerInterface $entityManager;
	protected HashidsInterface $idHasher;

	/** @param ProviderInterface<TEntity> $doctrineCollectionProvider */
	#[Required]
	public function setDoctrineDependencies(
		ProviderInterface $doctrineCollectionProvider,
		ProcessorInterface $persistProcessor,
		ProcessorInterface $removeProcessor,
		EntityManagerInterface $entityManager,
	): void {
		$this->doctrineCollectionProvider = $doctrineCollectionProvider;
		$this->persistProcessor = $persistProcessor;
		$this->removeProcessor = $removeProcessor;
		$this->entityManager = $entityManager;
	}

	#[Required]
	public function setIdHasher(HashidsInterface $idHasher): void
	{
		$this->idHasher = $idHasher;
	}

	/**
	 * Returns the class name of the Doctrine entity that backs this API
	 * resource.
	 *
	 * @psalm-return class-string<TEntity> Doctrine entity class name
	 */
	abstract public static function getEntityClass(): string;

	/**
	 * Returns the class name of the API resources that this provider manages.
	 *
	 * @psalm-return class-string<EntityFacadeInterface<TEntity>> API resource class name
	 */
	abstract public static function getApiResourceClass(): string;

	/**
	 * {@inheritDoc}
	 *
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 *
	 * @return TResource|array<TResource>|null
	 */
	public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
	{
		$doctrineEntityClass = static::getEntityClass();
		$apiResourceClass = static::getApiResourceClass();

		if (!is_a($apiResourceClass, EntityFacadeInterface::class, true)) {
			throw new \LogicException("Using AbstractDoctrineProvider with a resource class that does not implement EntityFacadeInterface is not allowed.");
		}

		if ($operation instanceof CollectionOperationInterface) {
			// Overwrite API Model to fetch entities from Doctrine
			$operation = $operation->withClass($doctrineEntityClass);
			$context["resource_class"] = $doctrineEntityClass;
			$result = $this->doctrineCollectionProvider->provide($operation, $uriVariables, $context);

			// @TODO: Just changed $doctrineProvider to $doctrineCollectionProvider - gotta figure out how to convert entities in paginator to resources
			// @TODO: Test if the [...$result] fucks up the pagination features

			return array_map(fn (mixed $organization = null) => $this->createResourceFromEntity($organization), [...$result]);
		}

		// For single item fetching operations, simply fetch from the repository
		$id = $uriVariables["id"] ?? null;

		if (!$id) {
			throw new BadRequestException("Missing resource ID.");
		}

		$entity = $this->fetchEntity($id);

		if (!$entity) {
			return null;
		}

		// @TODO: Check if the user has access to this resource via a voter or perhaps a Doctrine filter

		return $this->createResourceFromEntity($entity);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param mixed               $data
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 *
	 * @return TResource|null
	 */
	public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?object
	{
		$id = $uriVariables["id"] ?? null;
		$createMode = false;

		if (!$id) {
			if (!($operation instanceof Post || $operation instanceof Put)) {
				throw new BadRequestException("Missing resource ID.");
			}

			$createMode = true;
		}

		$entityClass = static::getEntityClass();
		$entity = $createMode ? new $entityClass() : $this->fetchEntity($id);

		if (!$entity) {
			throw new NotFoundException();
		}

		// DELETE: use Doctrine's removal processor on the entity
		if ($operation instanceof DeleteOperationInterface) {
			$this->removeProcessor->process($entity, $operation, $uriVariables, $context);

			return null;
		}

		// Other operations: update the data and use Doctrine's persistence processor
		$updatedEntity = $this->updateEntityFromResource($entity, $data);
		$this->persistProcessor->process($updatedEntity, $operation, $uriVariables, $context);

		return $this->createResourceFromEntity($updatedEntity);
	}

	/**
	 * Updates the entity from the data contained in the API resource.
	 *
	 * @param TEntity   $entity
	 * @param TResource $resource
	 *
	 * @return TEntity
	 */
	protected function updateEntityFromResource(object &$entity, object $resource): object
	{
		$propertyAccessor = PropertyAccess::createPropertyAccessor();

		foreach (get_object_vars($resource) as $property => $value) {
			if (!$propertyAccessor->isWritable($entity, $property)) {
				continue;
			}

			// @TODO: Check if value needs to be converted from a resource to an entity
			$propertyAccessor->setValue($entity, $property, $value);
		}

		return $entity;
	}

	/**
	 * @return TEntity|null
	 */
	protected function fetchEntity(int|string $id): ?object
	{
		if ($id && !is_numeric($id)) {
			$id = $this->idHasher->decode($id)[0] ?? null;
		}

		if (!$id) {
			throw new BadRequestException("Missing resource ID in API item request.");
		}

		$doctrineEntityClass = static::getEntityClass();
		$repository = $this->entityManager->getRepository($doctrineEntityClass);

		return $repository->find($id);
	}

	/**
	 * @param TEntity $entity
	 *
	 * @return TResource
	 */
	protected function createResourceFromEntity(object $entity): object
	{
		$apiResourceClass = static::getApiResourceClass();

		return $apiResourceClass::fromEntity($entity);
	}
}
