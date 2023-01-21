<?php

namespace App\Api\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
	private OpenApi $openApi;

	public function __construct(
		private OpenApiFactoryInterface $decorated,
		private Packages $packages,
		private TranslatorInterface $translator,
	) {
	}

	/**
	 * @param array<mixed> $context
	 */
	public function __invoke(array $context = []): OpenApi
	{
		$this->openApi = ($this->decorated)($context);

		$this->addLogo();
		$this->addTokenSchema();
		$this->addRefreshTokenSchema();
		$this->addCredentialsSchema();
		$this->addAuthOperation();
		$this->addTokenRefreshOperation();
		$this->updateIdentifierTypesForHashIds();
		$this->addBasicDocumentation();

		return $this->openApi;
	}

	private function addLogo(): void
	{
		$info = $this->openApi->getInfo();
		$logoObject = [
			"url" => $this->packages->getUrl("media/brand/koalati-logo.svg"),
			"altText" => "Koalati",
			"href" => "https://www.koalati.com",
		];

		$this->openApi = $this->openApi->withInfo(
			$info->withExtensionProperty("x-logo", $logoObject)
		);
	}

	private function addBasicDocumentation(): void
	{
		$customDocumentationTags = ["introduction", "url", "formats", "rate_limiting", "pagination", "http_methods", "http_codes"];

		foreach ($customDocumentationTags as &$tag) {
			$tag = [
				"name" => $this->translator->trans("api.docs.$tag.title"),
				"description" => $this->translator->trans("api.docs.$tag.content"),
				"x-traitTag" => true,
			];
		}

		$tags = $this->openApi->getTags();
		array_unshift($tags, ...$customDocumentationTags);

		$this->openApi = $this->openApi->withTags($tags);
	}

	private function addTokenSchema(): void
	{
		$schemas = $this->openApi->getComponents()->getSchemas();
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

	private function addRefreshTokenSchema(): void
	{
		$schemas = $this->openApi->getComponents()->getSchemas();
		$schemas['RefreshToken'] = new \ArrayObject([
			'type' => 'object',
			'properties' => [
				'refresh_token' => [
					'type' => 'string',
				],
			],
		]);
	}

	private function addCredentialsSchema(): void
	{
		$schemas = $this->openApi->getComponents()->getSchemas();
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

	private function addAuthOperation(): void
	{
		$securitySchemas = $this->openApi->getComponents()->getSecuritySchemes() ?? [];
		$securitySchemas['JWT'] = new \ArrayObject([
			'type' => 'http',
			'scheme' => 'bearer',
			'bearerFormat' => 'JWT',
		]);

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
		$this->openApi->getPaths()->addPath('/api/auth', $pathItem);
	}

	private function addTokenRefreshOperation(): void
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
		$this->openApi->getPaths()->addPath('/api/token/refresh', $pathItem);
	}

	/**
	 * Updates schemas and request body formats to make sure all IDs
	 * and entity references are documented as IRIs instead of numerical IDs.
	 */
	private function updateIdentifierTypesForHashIds(): void
	{
		foreach ($this->openApi->getComponents()->getSchemas() as $component) {
			foreach ($component["properties"] as $property => $definition) {
				if ($property == "id" || str_ends_with($property, "_id")) {
					$definition["type"] = "string";
				}
			}
		}
	}
}
