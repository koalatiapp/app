<?php

namespace App\Api\Routing;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Exception\ItemNotFoundException;
use ApiPlatform\Exception\RuntimeException;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\Routing\IriConverter;
use Hashids\HashidsInterface;

/**
 * This service decorates the API Platform's IRI converter
 * to make it use encrypted IDs (aka: hashids) instead of
 * numerical IDs.
 */
class EncryptedIriConverter implements IriConverterInterface
{
	public function __construct(
		private IriConverter $iriConverter,
		private HashidsInterface $idHasher,
	) {
	}

	/**
	 * Retrieves an item from its IRI.
	 *
	 * @param array<mixed> $context
	 *
	 * @throws InvalidArgumentException
	 * @throws ItemNotFoundException
	 */
	public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
	{
		$iri = $this->decryptIdsInIri($iri);

		return $this->iriConverter->getResourceFromIri($iri, $context, $operation);
	}

	/**
	 * Gets the IRI associated with the given item.
	 *
	 * @param object|class-string $resource
	 * @param array<mixed>        $context
	 *
	 * @throws InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function getIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
	{
		$iri = $this->iriConverter->getIriFromResource($resource, $referenceType, $operation, $context);

		return $this->encryptIdsInIri($iri);
	}

	/**
	 * @param string $iri IRI with numerical IDs that must be encrypted
	 *
	 * @return string The updated IRI with encrypted IDs
	 */
	private function encryptIdsInIri(string $iri): string
	{
		return implode(
			"/",
			array_map(
				fn (string $urlPart) => is_numeric($urlPart) ? $this->idHasher->encode($urlPart) : $urlPart,
				explode("/", $iri)
			)
		);
	}

	/**
	 * @param string $iri IRI with encrypted IDs that must be decrypted
	 *
	 * @return string The updated IRI with decrypted IDs
	 */
	private function decryptIdsInIri(string $iri): string
	{
		return implode(
			"/",
			array_map(
				fn (string $urlPart) => is_numeric($urlPart) ? 0 : $this->idHasher->decode($urlPart)[0] ?? $urlPart,
				explode("/", $iri)
			)
		);
	}
}
