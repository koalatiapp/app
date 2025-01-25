<?php

namespace App\Api\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Activity\ActivityLogger;
use App\Entity\User;
use App\Mercure\MercureEntityInterface;
use App\Mercure\UpdateDispatcher;
use App\Mercure\UpdateType;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\HashidsInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Base class for API state processors that act as a wrapper
 * around the default Doctrine state providers and processors.
 *
 * @template T of object
 *
 * @implements ProcessorInterface<T,?T>
 */
abstract class AbstractDoctrineStateWrapper implements ProcessorInterface
{
	protected Security $security;
	/** @var ProcessorInterface<T,T> */
	protected ProcessorInterface $persistProcessor;
	/** @var ProcessorInterface<T,T> */
	protected ProcessorInterface $removeProcessor;
	protected EntityManagerInterface $entityManager;
	protected HashidsInterface $idHasher;
	protected MessageBusInterface $bus;
	protected UpdateDispatcher $mercureUpdateDispatcher;
	protected ActivityLogger $activityLogger;

	/**
	 * @param ProcessorInterface<T,T> $persistProcessor
	 * @param ProcessorInterface<T,T> $removeProcessor
	 */
	#[Required]
	public function setDependencies(
		Security $security,
		ProcessorInterface $persistProcessor,
		ProcessorInterface $removeProcessor,
		EntityManagerInterface $entityManager,
		HashidsInterface $idHasher,
		MessageBusInterface $bus,
		UpdateDispatcher $mercureUpdateDispatcher,
		ActivityLogger $activityLogger,
	): void {
		$this->security = $security;
		$this->persistProcessor = $persistProcessor;
		$this->removeProcessor = $removeProcessor;
		$this->entityManager = $entityManager;
		$this->idHasher = $idHasher;
		$this->bus = $bus;
		$this->mercureUpdateDispatcher = $mercureUpdateDispatcher;
		$this->activityLogger = $activityLogger;
	}

	public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?object
	{
		// DELETE: use Doctrine's removal processor on the entity
		if ($operation instanceof DeleteOperationInterface) {
			if ($data instanceof MercureEntityInterface) {
				$this->mercureUpdateDispatcher->prepare($data, UpdateType::DELETE);
			}

			$this->preRemove($data);
			$this->removeProcessor->process($data, $operation, $uriVariables, $context);
			$this->postRemove($data);
			$this->activityLogger->postRemove($data);

			if ($data instanceof MercureEntityInterface) {
				$this->mercureUpdateDispatcher->dispatchPreparedUpdates();
			}

			return null;
		}

		$originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
		$this->prePersist($data, $originalData);
		$this->persistProcessor->process($data, $operation, $uriVariables, $context);
		$this->postPersist($data, $originalData);
		$this->activityLogger->postPersist($data, $originalData);

		if ($data instanceof MercureEntityInterface) {
			$this->mercureUpdateDispatcher->dispatch($data, ($originalData['id'] ?? null) ? UpdateType::UPDATE : UpdateType::CREATE);
		}

		return $data;
	}

	protected function getUser(): User
	{
		/** @var User */
		$user = $this->security->getUser();

		return $user;
	}

	/**
	 * Hook before the removal of a resource in the database.
	 *
	 * @param T $data
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.data)
	 */
	protected function preRemove(object &$data): void
	{
	}

	/**
	 * Hook after a resource has been removed in the database.
	 *
	 * @param T $data
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.data)
	 */
	protected function postRemove(object &$data): void
	{
	}

	/**
	 * Hook before the persistence of a resource in the database.
	 *
	 * @param T                   $data
	 * @param array<string,mixed> $originalData
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.data)
	 */
	protected function prePersist(object &$data, ?array $originalData): void
	{
	}

	/**
	 * Hook after a resource has been persisted in the database.
	 *
	 * @param T                   $data
	 * @param array<string,mixed> $originalData
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.data)
	 */
	protected function postPersist(object &$data, ?array $originalData): void
	{
	}
}
