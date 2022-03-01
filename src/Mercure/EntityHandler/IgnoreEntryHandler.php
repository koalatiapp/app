<?php

namespace App\Mercure\EntityHandler;

use App\Entity\Testing\IgnoreEntry;
use App\Mercure\MercureEntityInterface;
use App\Mercure\EntityHandlerInterface;

class IgnoreEntryHandler implements EntityHandlerInterface
{
	public function getSupportedEntity(): string
	{
		return IgnoreEntry::class;
	}

	public function getType(): string
	{
		return "IgnoreEntry";
	}

	/**
	 * @param IgnoreEntry $ignoreEntry
	 */
	public function getAffectedUsers(MercureEntityInterface $ignoreEntry): array
	{
		$project = $ignoreEntry->getTargetProject();
		$organization = $ignoreEntry->getTargetOrganization();
		$page = $ignoreEntry->getTargetPage();
		$user = $ignoreEntry->getTargetUser();

		if ($page) {
			$project = $page->getProject();
		}

		if ($project) {
			return (new ProjectHandler())->getAffectedUsers($project);
		}

		if ($organization) {
			return (new OrganizationHandler())->getAffectedUsers($organization);
		}

		return [$user];
	}
}
