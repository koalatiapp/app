<?php

namespace App\Mercure;

use App\Mercure\EntityHandlerInterface;
use App\Mercure\MercureEntityInterface;
use App\Util\ClientMessageSerializer;
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
		private HashidsInterface $idHasher
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
	 */
	public function prepare(MercureEntityInterface $entity, UpdateType $type): void
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
	 * @return array<int,\Symfony\Component\Messenger\Envelope>
	 */
	public function dispatch(MercureEntityInterface $entity, UpdateType $type): array
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
	 * @return array<int,\Symfony\Component\Mercure\Update>
	 */
	private function generateUpdates(MercureEntityInterface $entity, UpdateType $type): array
	{
		$handler = $this->entityHandlers[$entity::class];
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
			$userId = $this->idHasher->encode($user->getId());
			$topic = "http://koalati/$userId/";
			$updates[] = new Update($topic, $jsonData);
		}

		return $updates;
	}

	private function getTypeString(UpdateType $type): string
	{
		return match ($type) {
			UpdateType::CREATE => "create",
			UpdateType::UPDATE => "update",
			UpdateType::DELETE => "delete",
		};
	}
}
