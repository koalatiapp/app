<?php

namespace App\Api\Dto;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Api\State\IgnoreEntryProcessor;
use App\Entity\Testing\Recommendation;
use App\Util\Testing\RecommendationGroup;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * API-facing data-transfer object that allows API users to create ignore
 * entries from a recommendation.
 */
#[ApiResource(
	openapiContext: ["tags" => ['Ignore Recommendations']],
	operations: [
		new Get(controller: NotFoundAction::class, read: false, status: 404, openapi: false),
		new Post(
			uriTemplate: "/ignore_entries",
			denormalizationContext: ["groups" => "ignore_entry.write"],
			processor: IgnoreEntryProcessor::class,
			status: 201,
		),
	]
)]
class IgnoreEntryCreation
{
	#[Groups(['ignore_entry.write'])]
	public string $scope = "project";

	#[Groups(['ignore_entry.write'])]
	public Recommendation|RecommendationGroup $recommendation;
}
