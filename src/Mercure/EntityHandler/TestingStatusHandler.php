<?php

namespace App\Mercure\EntityHandler;

use App\Mercure\MercureEntityInterface;
use App\Entity\Project;
use App\Entity\ProjectMember;
use App\Mercure\EntityHandlerInterface;
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
