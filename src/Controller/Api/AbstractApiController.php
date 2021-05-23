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
	 * The provided data will be automatically serialized to JSON.
	 */
	protected function apiSuccess(mixed $data = null, int $code = 200): JsonResponse
	{
		return new JsonResponse([
			'status' => self::STATUS_OKAY,
			'code' => $code,
			'data' => $this->serializeData($data),
		]);
	}

	/**
	 * Uses Symfony's Serializer component to serialize any data into JSON.
	 * Entities will be turned into regular objects based on their `default` serializer group annotations.
	 */
	protected function serializeData(mixed $data): mixed
	{
		return json_decode($this->serializer->serialize($data, 'json', [
			AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
				return $object->getId();
			},
			AbstractNormalizer::GROUPS => ['default'],
		 ]), true);
	}

	/**
	 * Returns a generic 404 not found error response.
	 */
	protected function notFound(): JsonResponse
	{
		return $this->apiError('This resource does not exist or could not be found.', 404);
	}

	/**
	 * Returns a generic access denied error response.
	 */
	protected function accessDenied(): JsonResponse
	{
		return $this->apiError('You do not have access to this resource.', 403);
	}
}
