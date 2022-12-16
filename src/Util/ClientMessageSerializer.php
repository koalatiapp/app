<?php

namespace App\Util;

use App\Serializer\JsonNormalizer;
use Hashids\HashidsInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ClientMessageSerializer
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.serializer)
	 */
	public function __construct(
		private readonly JsonNormalizer $normalizer,
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
		if (!is_iterable($data)) {
			$context = [
				AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn ($object) => $this->idHasher->encode($object->getId()),
				AbstractNormalizer::GROUPS => ['default', ...$groups],
				AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
				'resource_class' => $data::class,
			];

			return $this->normalizer->normalize($data, 'json', $context);
		}

		$normalized = [];

		foreach ($data as $key => $value) {
			$normalized[$key] = $this->serialize($value, $groups);
		}

		return $normalized;
	}
}
