<?php

namespace App\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;

final class JwtDecorator implements OpenApiFactoryInterface
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

		$schemas = $openApi->getComponents()->getSecuritySchemes() ?? [];
		$schemas['JWT'] = new \ArrayObject([
			'type' => 'http',
			'scheme' => 'bearer',
			'bearerFormat' => 'JWT',
		]);

		$this->addAuthOperation($openApi);
		$this->addTokenRefreshOperation($openApi);

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
				tags: ['Token'],
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
				tags: ['Token'],
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
}
