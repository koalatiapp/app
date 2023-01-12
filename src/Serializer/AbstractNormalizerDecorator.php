<?php

namespace App\Serializer;

use App\Serializer\EntityExtension\EntityExtensionInterface;
use Hashids\HashidsInterface;
use Symfony\Component\DependencyInjection\Attribute\MapDecorated;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractNormalizerDecorator implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface, NormalizerAwareInterface
{
	/** @var NormalizerInterface&DenormalizerInterface&SerializerAwareInterface */
	private NormalizerInterface $decorated;

	public function __construct(
		private HashidsInterface $idHasher,
		/**
		 * @var iterable<EntityExtensionInterface>
		 */
		#[TaggedIterator('app.serializer.entity_extension')] private iterable $entityExtensions,
		#[MapDecorated] NormalizerInterface $inner,
	) {
		if (!$inner instanceof DenormalizerInterface) {
			throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
		}

		if (!$inner instanceof SerializerAwareInterface) {
			throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', SerializerAwareInterface::class));
		}

		$this->decorated = $inner;
	}

	public function supportsNormalization($data, $format = null): bool
	{
		return $this->decorated->supportsNormalization($data, $format);
	}

	/**
	 * @param array<mixed> $context
	 *
	 * @return array<mixed,mixed>|string|int|float|bool|\ArrayObject<int|string,mixed>|null
	 */
	public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
	{
		$data = $this->decorated->normalize($object, $format, $context);

		foreach ($this->entityExtensions as $entityExtension) {
			if ($entityExtension->supports($object)) {
				$data = $entityExtension->extendNormalization($object, $data);
			}
		}

		return $this->hashIdsInData($data);
	}

	public function supportsDenormalization($data, $type, $format = null): bool
	{
		return $this->decorated->supportsDenormalization($data, $type, $format);
	}

	public function denormalize($data, string $type, string $format = null, array $context = []): mixed
	{
		return $this->decorated->denormalize($data, $type, $format, $context);
	}

	public function setSerializer(SerializerInterface $serializer): void
	{
		if ($this->decorated instanceof SerializerAwareInterface) {
			$this->decorated->setSerializer($serializer);
		}
	}

	public function setNormalizer(NormalizerInterface $normalizer): void
	{
		if ($this->decorated instanceof NormalizerAwareInterface) {
			$this->decorated->setNormalizer($normalizer);
		}
	}

	/**
	 * @param array<mixed,mixed>|string|int|float|bool|\ArrayObject<int|string,mixed>|null $data
	 *
	 * @return array<mixed,mixed>|string|int|float|bool|null
	 */
	private function hashIdsInData($data): array|string|int|float|bool|null
	{
		if (!is_array($data) && !($data instanceof \Traversable)) {
			return $data;
		}

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
}
