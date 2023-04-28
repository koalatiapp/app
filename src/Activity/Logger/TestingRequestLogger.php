<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Api\Dto\TestingRequest;

/**
 * @extends AbstractEntityActivityLogger<TestingRequest>
 */
class TestingRequestLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return TestingRequest::class;
	}

	public function postPersist(object &$testingRequest, ?array $originalData): void
	{
		$this->log(
			type: "testing_request",
			organization: $testingRequest->project->getOwnerOrganization(),
			project: $testingRequest->project,
			target: $testingRequest->project,
		);
	}

	public function postRemove(object &$testingRequest): void
	{
	}
}
