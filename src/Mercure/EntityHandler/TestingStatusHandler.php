<?php

namespace App\Mercure\EntityHandler;

use App\Mercure\EntityHandlerInterface;
use App\Mercure\MercureEntityInterface;
use App\Util\Testing\TestingStatus;

class TestingStatusHandler implements EntityHandlerInterface
{
	public function getSupportedEntity(): string
	{
		return TestingStatus::class;
	}

	public function getType(): string
	{
		return "TestingStatus";
	}

	/**
	 * @param TestingStatus $testingStatus
	 */
	public function getAffectedUsers(MercureEntityInterface $testingStatus): array
	{
		$project = $testingStatus->getProject();

		return (new ProjectHandler())->getAffectedUsers($project);
	}
}
