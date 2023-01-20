<?php

namespace App\Api\Dto;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Api\State\TestingRequestProcessor;
use App\Entity\Page;
use App\Entity\Project;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * API-facing data-transfer object that allows API users to request for their
 * website to be tested.
 */
#[ApiResource(
	openapiContext: ["tags" => ['Testing Request']],
	operations: [
		new Get(controller: NotFoundAction::class, read: false, status: 404, openapi: false),
		new Post(messenger: true, output: false, status: 202, processor: TestingRequestProcessor::class),
	]
)]
class TestingRequest
{
	#[Groups(['write'])]
	public ?Project $project = null;

	/**
	 * Tool(s) to run.
	 *
	 * @var array<string>|null
	 */
	#[Groups(['write'])]
	public ?array $tools = null;

	/**
	 * ID(s) of the pages on which to run the tools.
	 *
	 * @var array<Page>|null
	 */
	#[Groups(['write'])]
	public ?array $pages = null;
}
