<?php

namespace App\Api\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\State\TestingStatusProvider;
use App\Util\Testing\TestingStatus as TestingTestingStatus;

/**
 * API-facing data-transfer object that allows API users to request for their
 * website to be tested.
 */
#[ApiResource(operations: [
	new Get(
		openapiContext: ["tags" => ['Testing Status']],
		output: TestingTestingStatus::class,
		provider: TestingStatusProvider::class,
		uriTemplate: "/projects/{projectId}/testing_status",
	),
])]
class TestingStatus
{
}
