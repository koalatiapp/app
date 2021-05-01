<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractApiController extends AbstractController
{
	public const STATUS_ERROR = 'error';
	public const STATUS_OKAY = 'ok';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.serializer)
	 */
	public function __construct(
		public SerializerInterface $serializer
	) {
	}

	/**
	 * Generates a JsonResponse for an API request that has encountered an error.
	 */
	protected function apiError(string $message, int $code = 500): JsonResponse
	{
		return new JsonResponse([
			'status' => self::STATUS_ERROR,
			'code' => $code,
			'message' => $message,
		]);
	}

	/**
	 * Generates a JsonResponse for a successful API request.
	 */
	protected function apiSuccess(mixed $data, int $code = 200): JsonResponse
	{
		return new JsonResponse([
			'status' => self::STATUS_OKAY,
			'code' => $code,
			'data' => $data,
		]);
	}

	/**
	 * Uses Symfony's Serializer component to turn an entity into a regular object.
	 *
	 * @return array<mixed,mixed>
	 */
	protected function simplifyEntity(mixed $entity): array
	{
		return json_decode($this->serializer->serialize($entity, 'json', [
			AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
				return $object->getId();
			},
			AbstractNormalizer::GROUPS => ['default'],
		 ]), true);
	}

	/**
	 * Returns a generic access denied error response.
	 */
	protected function accessDenied(): JsonResponse
	{
		return $this->apiError('You do not have access to this resource.', 403);
	}
}
