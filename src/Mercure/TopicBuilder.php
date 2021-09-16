<?php

namespace App\Mercure;

use App\Entity\MercureEntityInterface;
use Hashids\HashidsInterface;

/**
 * Handles generation and dispatching of Mercure updates for
 * the different scopes of entities.
 */
class TopicBuilder
{
	public const SCOPE_PUBLIC = 'public';
	public const SCOPE_SPECIFIC = 'specific';
	public const SCOPE_PROJECT = 'project';
	public const SCOPE_USER = 'user';
	public const SCOPE_ORGANIZATION = 'organization';

	protected HashidsInterface $idHasher;

	/**
	 * @required
	 */
	public function setIdHasher(HashidsInterface $idHasher): void
	{
		$this->idHasher = $idHasher;
	}

	/**
	 * Returns the topic of a specific scope for a given entity.
	 * Ex.: http://koalati.com/scope-hash/entity/1.
	 *
	 * @return string|array<int,string>|null
	 */
	public function getEntityTopic(MercureEntityInterface $entity, string $scope): string | array | null
	{
		$genericTopic = $this->getEntityGenericTopic($entity, $scope);

		if (!$genericTopic) {
			return null;
		}

		if (is_array($genericTopic)) {
			$topics = [];

			foreach ($genericTopic as $topic) {
				$topics[] = str_replace('{id}', $this->idHasher->encode($entity->getId()), $topic);
			}

			return $topics;
		}

		return str_replace('{id}', (string) $this->idHasher->encode($entity->getId()), $genericTopic);
	}

	/**
	 * Returns the generic catch-all topic of a specific scope for a given entity.
	 * Ex.: http://koalati.com/scope-hash/entity/{id}.
	 *
	 * @param MercureEntityInterface|string $entity  The classname or instance of the entity
	 * @param int|null                      $scopeId If `$entity` is passed as a class name, the ID of the scope's entity must be defined via `$scopeId`
	 *
	 * @return string|array<int,string>|null
	 */
	public function getEntityGenericTopic(MercureEntityInterface | string $entity, string $scope, int | string | null $scopeId = null): string | array | null
	{
		$entityTopics = $entity::getMercureTopics();
		$topicTemplate = $entityTopics[$scope];

		if (!str_contains($topicTemplate, '{scope}')) {
			return $topicTemplate;
		}

		if (!$scopeId) {
			$scopes = $entity->getMercureScope($scope);

			if (is_iterable($scopes)) {
				$topics = [];

				foreach ($scopes as $scopeEntity) {
					$topics[] = $this->getEntityGenericTopic($entity, $scope, $this->idHasher->encode($scopeEntity->getId()));
				}

				return $topics;
			}

			$scopeId = $scopes?->getId();

			if (!$scopeId) {
				return null;
			} elseif (is_numeric($scopeId)) {
				$scopeId = $this->idHasher->encode($scopeId);
			}
		}

		return str_replace('{scope}', $scope.'/'.$this->getScopeUid($scope, $scopeId), $topicTemplate);
	}

	private function getScopeUid(string $scope, int | string $scopeId): string
	{
		if (is_numeric($scopeId)) {
			$scopeId = $this->idHasher->encode($scopeId);
		}

		return md5($scope.'-'.$scopeId);
	}
}
