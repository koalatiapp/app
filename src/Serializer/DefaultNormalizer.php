<?php

namespace App\Serializer;

use App\Entity\Project;
use Hashids\HashidsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Hashes IDs to obfuscate the numerical values.
 */
class DefaultNormalizer implements ContextAwareNormalizerInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ContainerInterface $container,
		private Serializer $simpleSerializer,
		private ProjectNormalizer $projectNormalizer,
		private HashidsInterface $idHasher
	) {
	}

	/**
	 * @return array<mixed,mixed>|string|int|float|bool|\ArrayObject<mixed,mixed>|null
	 */
	public function normalize(mixed $data, string $format = null, array $context = [])
	{
		if ($data === null || is_scalar($data)) {
			return $data;
		}

		if (is_array($data) || $data instanceof \Traversable) {
			if (($context[AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS] ?? false) === true && $data instanceof \Countable && $data->count() === 0) {
				return $data;
			}

			$normalized = [];
			foreach ($data as $key => $value) {
				$normalized[$key] = $this->container->get("Symfony\Component\Serializer\SerializerInterface")->normalize($value, $format, $context);
			}

			$normalized = $this->hashIdsInData($normalized);

			return $normalized;
		}

		$data = $this->simpleSerializer->normalize($data, $format, $context);

		return $this->hashIdsInData($data);
	}

	/**
	 * @param array<mixed,mixed>|string|int|float|bool|\ArrayObject<mixed,mixed>|null $data
	 *
	 * @return array<mixed,mixed>|string|int|float|bool|null
	 */
	private function hashIdsInData($data)
	{
		if (is_array($data) || $data instanceof \Traversable) {
			$normalized = [];

			foreach ($data as $key => $value) {
				$normalized[$key] = $value;

				if (preg_match('/.*[iI]d$/', $key) && is_numeric($value)) {
					$normalized[$key] = $this->idHasher->encode($value);
				} elseif (is_array($value) || $value instanceof \Traversable) {
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
	public function supportsNormalization($data, string $format = null, array $context = [])
	{
		return !($data instanceof Project);
	}
}
