<?php

namespace App\Util;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ClientMessageSerializer
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.serializer)
	 */
	public function __construct(
		private SerializerInterface $serializer,
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
		return json_decode($this->serializer->serialize($data, 'json', [
			AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
				return $object->getId();
			},
			AbstractNormalizer::GROUPS => array_merge(['default'], $groups),
			AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
		 ]), true);
	}
}