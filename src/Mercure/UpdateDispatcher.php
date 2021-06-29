<?php

namespace App\Mercure;

use App\Entity\MercureEntityInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Handles generation and dispatching of Mercure updates for
 * the different scopes of entities.
 */
class UpdateDispatcher
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.bus)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.topicBuilder)
	 */
	public function __construct(private TopicBuilder $topicBuilder, private MessageBusInterface $bus)
	{
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

			$updates[] = new Update($topic, json_encode($data));
		}

		return $updates;
	}
}
