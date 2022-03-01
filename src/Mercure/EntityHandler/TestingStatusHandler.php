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
	 * @param Project $project
	 */
	public function getAffectedUsers(MercureEntityInterface $project): array
	{
		static $cache = [];

		if (isset($cache[$project->getId()])) {
			return $cache[$project->getId()];
		}

		$teamMembers = $project->getTeamMembers();
		$ownerUser = $project->getOwnerUser();
		$ownerOrganization = $project->getOwnerOrganization();
		$users = [
			...$teamMembers->map(fn (ProjectMember $member) => $member->getUser()),
		];

		if ($ownerUser) {
			$users[] = $ownerUser;
		}

		if ($ownerOrganization) {
			$orgUsers = (new OrganizationHandler())->getAffectedUsers($ownerOrganization);
			array_push($users, ...$orgUsers);
		}

		$users = array_unique($users);
		$cache[$project->getId()] = $users;

		return $users;
	}
}
