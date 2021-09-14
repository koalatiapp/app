<?php

namespace App\Mercure;

use App\Entity\MercureEntityInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException;
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
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.bus)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.topicBuilder)
	 */
	public function __construct(private TopicBuilder $topicBuilder, private MessageBusInterface $bus, private LoggerInterface $logger)
	{
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
	 * @param array<mixed,mixed> $data
	 * @param string|null        $specificScope Specific scope for which to dispatch the update. Uses all scopes when `null`.
	 */
	public function prepare(MercureEntityInterface $entity, array $data, ?string $specificScope = null): void
	{
		foreach ($this->generateUpdates($entity, $data, $specificScope) as $update) {
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
			try {
				$envelopes[] = $this->bus->dispatch($update);
			} catch (RuntimeException $exception) {
				$this->logger->error(
					implode("\n\t", [
						$exception->getMessage(),
						$exception->getTraceAsString(),
						$exception->getPrevious()?->getMessage(),
						$exception->getPrevious()?->getTraceAsString(),
					])
				);
			}
		}

		$this->pendingUpdates = [];

		return $envelopes;
	}

	/**
	 * Generates and sends Mercure updates to every configured topic of an entity.
	 *
	 * @param array<mixed,mixed> $data
	 * @param string|null        $specificScope Specific scope for which to dispatch the update. Uses all scopes when `null`.
	 *
	 * @return array<int,\Symfony\Component\Messenger\Envelope>
	 */
	public function dispatch(MercureEntityInterface $entity, array $data, ?string $specificScope = null): array
	{
		$envelopes = [];

		foreach ($this->generateUpdates($entity, $data, $specificScope) as $update) {
			$envelopes[] = $this->bus->dispatch($update);
		}

		return $envelopes;
	}

	/**
	 * Generates an array of `Symfony\Component\Mercure\Update` for the entity's changes.
	 *
	 * @param array<mixed,mixed> $data
	 * @param string|null        $specificScope Specific scope for which to dispatch the update. Uses all scopes when `null`.
	 *
	 * @return array<int,\Symfony\Component\Mercure\Update>
	 */
	private function generateUpdates(MercureEntityInterface $entity, array $data, ?string $specificScope = null): array
	{
		$updates = [];

		foreach (array_keys($entity::getMercureTopics()) as $scope) {
			if ($specificScope && $specificScope != $scope) {
				continue;
			}

			$topic = $this->topicBuilder->getEntityTopic($entity, $scope);

			if (!$topic) {
				continue;
			}

			$topics = (array) $topic;
			foreach ($topics as $topic) {
				$updates[] = new Update($topic, json_encode($data));
			}
		}

		return $updates;
	}
}
