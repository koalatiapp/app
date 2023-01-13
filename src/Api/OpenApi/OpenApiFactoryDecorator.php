<?php

namespace App\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

final class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
	public function __construct(
		private OpenApiFactoryInterface $decorated
	) {
	}

	/**
	 * @param array<mixed> $context
	 */
	public function __invoke(array $context = []): OpenApi
	{
		$openApi = ($this->decorated)($context);

		$this->addTokenSchema($openApi);
		$this->addRefreshTokenSchema($openApi);
		$this->addCredentialsSchema($openApi);

		$securitySchemas = $openApi->getComponents()->getSecuritySchemes() ?? [];
		$securitySchemas['JWT'] = new \ArrayObject([
			'type' => 'http',
			'scheme' => 'bearer',
			'bearerFormat' => 'JWT',
		]);

		$this->addAuthOperation($openApi);
		$this->addTokenRefreshOperation($openApi);

		$this->updateIdentifierTypesForHashIds($openApi);

		return $openApi;
	}

	private function addTokenSchema(OpenApi $openApi): void
	{
		$schemas = $openApi->getComponents()->getSchemas();
		$schemas['Token'] = new \ArrayObject([
			'type' => 'object',
			'properties' => [
				'token' => [
					'type' => 'string',
					'readOnly' => true,
				],
				'refresh_token' => [
					'type' => 'string',
					'readOnly' => true,
				],
			],
		]);
	}

	private function addRefreshTokenSchema(OpenApi $openApi): void
	{
		$schemas = $openApi->getComponents()->getSchemas();
		$schemas['RefreshToken'] = new \ArrayObject([
			'type' => 'object',
			'properties' => [
				'refresh_token' => [
					'type' => 'string',
				],
			],
		]);
	}

	private function addCredentialsSchema(OpenApi $openApi): void
	{
		$schemas = $openApi->getComponents()->getSchemas();
		$schemas['Credentials'] = new \ArrayObject([
			'type' => 'object',
			'properties' => [
				'email' => [
					'type' => 'string',
					'example' => 'name@email.com',
				],
				'password' => [
					'type' => 'string',
					'example' => '123456',
				],
			],
		]);
	}

	private function addAuthOperation(OpenApi $openApi): void
	{
		$pathItem = new Model\PathItem(
			ref: 'JWT Token',
			post: new Model\Operation(
				operationId: 'postCredentialsItem',
				tags: ['API Authentication (JWT)'],
				responses: [
					'200' => [
						'description' => 'Get JWT token',
						'content' => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Token',
								],
							],
						],
					],
				],
				summary: 'Get JWT token to authenticate.',
				requestBody: new Model\RequestBody(
					description: 'Generate new JWT Token',
					content: new \ArrayObject([
						'application/json' => [
							'schema' => [
								'$ref' => '#/components/schemas/Credentials',
							],
						],
					]),
				),
				security: [],
			),
		);
		$openApi->getPaths()->addPath('/api/auth', $pathItem);
	}

	private function addTokenRefreshOperation(OpenApi $openApi): void
	{
		$pathItem = new Model\PathItem(
			ref: 'JWT Token',
			post: new Model\Operation(
				operationId: 'postRefreshTokenItem',
				tags: ['API Authentication (JWT)'],
				responses: [
					'200' => [
						'description' => 'Refresh JWT token',
						'content' => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Token',
								],
							],
						],
					],
				],
				summary: 'Refresh the JWT token used to authenticate.',
				requestBody: new Model\RequestBody(
					description: 'Generates a new JWT Token from the provided Refresh Token.',
					content: new \ArrayObject([
						'application/json' => [
							'schema' => [
								'$ref' => '#/components/schemas/RefreshToken',
							],
						],
					]),
				),
				security: [],
			),
		);
		$openApi->getPaths()->addPath('/api/token/refresh', $pathItem);
	}

	/**
	 * Updates schemas and request body formats to make sure all IDs
	 * and entity references are documented as IRIs instead of numerical IDs.
	 */
	private function updateIdentifierTypesForHashIds(OpenApi $openApi): void
	{
		foreach ($openApi->getComponents()->getSchemas() as $component) {
			foreach ($component["properties"] as $property => $definition) {
				if ($property == "id" || str_ends_with($property, "_id")) {
					$definition["type"] = "string";
				}
			}
		}
	}
}
