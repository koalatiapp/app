<?php

namespace App\Mercure;

use App\Mercure\EntityHandlerInterface;
use App\Mercure\MercureEntityInterface;
use App\Util\ClientMessageSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Exception;
use Hashids\HashidsInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles generation and dispatching of Mercure updates for
 * the different scopes of entities.
 */
class UpdateDispatcher
{
	/**
	 * @var array<int,\Symfony\Component\Mercure\Update>
	 */
	private array $pendingUpdates = [];

	/**
	 * @var array<string,EntityHandlerInterface>
	 */
	private array $entityHandlers = [];

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.bus)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.entityHandlers)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.idHasher)
	 * @param iterable<mixed,EntityHandlerInterface> $entityHandlers
	 */
	public function __construct(
		iterable $entityHandlers,
		private ClientMessageSerializer $serializer,
		private MessageBusInterface $bus,
		private HashidsInterface $idHasher,
		private UserTopicBuilder $topicBuilder,
		private EntityManagerInterface $entityManager,
	) {
		foreach ($entityHandlers as $entityHandler) {
			$this->entityHandlers[$entityHandler->getSupportedEntity()] = $entityHandler;
		}
	}

	/**
	 * Generates Mercure updates for every configured topic of an entity, and keeps
	 * them to be sent later.
	 *
	 * This is meant to be used in conjonction with `dispatchPreparedUpdates()`
	 *
	 * The main use case for this feature is preparing updates about an entity that
	 * is being deleted. Seeing as the update generation needs to access the entity
	 * prior to deletion, updates must be prepared before the deletion is flushed,
	 * and sent only afterwards.
	 *
	 * @param string $type One of the `App\Mercure\UpdateType::` constants
	 */
	public function prepare(MercureEntityInterface $entity, string $type): void
	{
		foreach ($this->generateUpdates($entity, $type) as $update) {
			$this->pendingUpdates[] = $update;
		}
	}

	/**
	 * Dispatches all previously prepared but not yet sent updates.
	 *
	 * @return array<int,\Symfony\Component\Messenger\Envelope>
	 */
	public function dispatchPreparedUpdates(): array
	{
		$envelopes = [];

		foreach ($this->pendingUpdates as $update) {
			$envelopes[] = $this->bus->dispatch($update);
		}

		$this->pendingUpdates = [];

		return $envelopes;
	}

	/**
	 * Generates and sends Mercure updates for an entity.
	 *
	 * @param string $type One of the `App\Mercure\UpdateType::` constants
	 *
	 * @return array<int,\Symfony\Component\Messenger\Envelope>
	 */
	public function dispatch(MercureEntityInterface $entity, string $type): array
	{
		$envelopes = [];

		foreach ($this->generateUpdates($entity, $type) as $update) {
			$envelopes[] = $this->bus->dispatch($update);
		}

		return $envelopes;
	}

	/**
	 * Generates an array of `Symfony\Component\Mercure\Update` for the entity.
	 *
	 * @param string $type One of the `App\Mercure\UpdateType::` constants
	 *
	 * @return array<int,\Symfony\Component\Mercure\Update>
	 */
	private function generateUpdates(MercureEntityInterface $entity, string $type): array
	{
		$entityClass = $entity::class;

		if ($entity instanceof Proxy) {
			$entityClass = $this->entityManager->getClassMetadata($entityClass)->rootEntityName;
		}

		$handler = $this->entityHandlers[$entityClass];

		$entityId = $entity->getId();
		$updates = [];

		$data = [
			"event" => $this->getTypeString($type),
			"timestamp" => time(),
			"type" => $handler->getType(),
			"data" => $this->serializer->serialize($entity),
			"id" => is_numeric($entityId) ? $this->idHasher->encode($entityId) : $entityId,
		];
		$jsonData = json_encode($data);

		$affectedUsers = $handler->getAffectedUsers($entity);

		foreach ($affectedUsers as $user) {
			$topic = $this->topicBuilder->getTopic($user);
			$updates[] = new Update($topic, $jsonData, true);
		}

		return $updates;
	}

	/**
	 * @param string $type One of the `App\Mercure\UpdateType::` constants
	 */
	private function getTypeString(string $type): string
	{
		return match ($type) {
			UpdateType::CREATE => "create",
			UpdateType::UPDATE => "update",
			UpdateType::DELETE => "delete",
			default => throw new Exception("Invalid update type"),
		};
	}
}
