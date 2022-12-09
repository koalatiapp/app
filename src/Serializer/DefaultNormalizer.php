<?php

namespace App\Serializer;

use App\Entity\Project;
use Hashids\HashidsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Hashes IDs to obfuscate the numerical values.
 */
class DefaultNormalizer implements ContextAwareNormalizerInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly ContainerInterface $container,
		private readonly Serializer $simpleSerializer,
		private readonly HashidsInterface $idHasher
	) {
	}

	/**
	 * @return array<mixed,mixed>|string|int|float|bool|\ArrayObject<int|string,mixed>|null
	 */
	public function normalize(mixed $data, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
	{
		if ($data === null || is_scalar($data)) {
			return $data;
		}

		if (is_iterable($data)) {
			if (($context[AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS] ?? false) === true && $data instanceof \Countable && $data->count() === 0) {
				return $data;
			}

			$normalized = [];
			foreach ($data as $key => $value) {
				$normalized[$key] = $this->container->get(SerializerInterface::class)->normalize($value, $format, $context);
			}

			return $this->hashIdsInData($normalized);
		}

		$data = $this->simpleSerializer->normalize($data, $format, $context);

		return $this->hashIdsInData($data);
	}

	/**
	 * @param array<mixed,mixed>|string|int|float|bool|\ArrayObject<int|string,mixed>|null $data
	 *
	 * @return array<mixed,mixed>|string|int|float|bool|null
	 */
	private function hashIdsInData($data)
	{
		if (is_iterable($data)) {
			$normalized = [];

			foreach ($data as $key => $value) {
				$normalized[$key] = $value;

				if (preg_match('/.*[iI]d$/', $key) && is_numeric($value)) {
					$normalized[$key] = $this->idHasher->encode($value);
				} elseif (is_iterable($value)) {
					$normalized[$key] = $this->hashIdsInData($value);
				}
			}

			return $normalized;
		}

		return $data;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function supportsNormalization($data, string $format = null, array $context = []): bool
	{
		return !($data instanceof Project);
	}
}
