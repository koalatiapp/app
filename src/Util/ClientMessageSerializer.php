<?php

namespace App\Util;

use Hashids\HashidsInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ClientMessageSerializer
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.serializer)
	 */
	public function __construct(
		private readonly SerializerInterface $serializer,
		private readonly HashidsInterface $idHasher,
	) {
	}

	/**
	 * Uses Symfony's Serializer component to serialize any data into JSON.
	 * Entities will be turned into regular objects based on their `default` serializer group annotations.
	 *
	 * @param mixed             $data   Data to serialize
	 * @param array<int,string> $groups field groups to include in the serialization (`default` is always included)
	 */
	public function serialize(mixed $data, array $groups = []): mixed
	{
		$idHasher = $this->idHasher;

		return json_decode($this->serializer->serialize($data, 'json', [
			AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn ($object) => $idHasher->encode($object->getId()),
			AbstractNormalizer::GROUPS => ['default', ...$groups],
			AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
		 ]), true, 512, JSON_THROW_ON_ERROR);
	}
}
