<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\CallableProvider;
use ApiPlatform\State\ProviderInterface;
use Hashids\HashidsInterface;

/**
 * Decorates the built-in callable provider to decrypt IDs in the URI variables
 * so the built-in callable provider can do its job.
 *
 * @template T of object
 *
 * @implements ProviderInterface<T>
 */
class EncryptedIdsCallableProvider implements ProviderInterface
{
	public function __construct(
		private CallableProvider $callableProvider,
		private HashidsInterface $idHasher,
	) {
	}

	/**
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 */
	public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
	{
		/*
		 * Encrypted IDs in the $uriVariables have already been casted to
		 * integers, transforming them all into zeros.
		 *
		 * To fix that, we update the URI variables by decrypting the original
		 * (not-yet-casted-to-integer) values from the `$context`, decrypt them,
		 * and update them in the $uriVariables.
		 *
		 * Then, we give it all back to the API Platform's built-in provider
		 * handler so it can do its job as usual.
		 */
		foreach (array_keys($uriVariables) as $key) {
			$originalValue = $context["uri_variables"][$key] ?? null;

			// Force users to use encrypted IDs by breaking numerical IDs
			if (is_numeric($originalValue)) {
				$uriVariables[$key] = 0;
			} elseif (is_string($originalValue)) {
				$uriVariables[$key] = $this->idHasher->decode($originalValue)[0] ?? $originalValue;
			}
		}

		return $this->callableProvider->provide($operation, $uriVariables, $context);
	}
}
