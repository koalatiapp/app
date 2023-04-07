<?php

namespace App\Activity;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\ActivityLog;
use App\Entity\Organization;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @template T of object
 * @implements EntityActivityLoggerInterface<T>
 */
abstract class AbstractEntityActivityLogger implements EntityActivityLoggerInterface
{
	public function __construct(
		private EntityManagerInterface $entityManager,
		private IriConverterInterface $iriConverter,
		private Security $security,
	) {
	}

	/**
	 * Creates an activity log for the current user.
	 *
	 * @param null|array<string,mixed> $data
	 */
	protected function log(string $type, ?Organization $organization = null, ?Project $project = null, ?object $target = null, ?array $data = null): void
	{
		/** @var User */
		$user = $this->security->getUser();

		$data['user'] = $user->getFullName();
		$data['project'] = $project?->getName();
		$data['organization'] = $organization?->getName();

		$log = (new ActivityLog())
			->setUser($user)
			->setType($type)
			->setData($data)
			->setOrganization($organization)
			->setProject($project)
			->makePublic();

		if ($target) {
			try {
				$log->setTarget($this->iriConverter->getIriFromResource($target));
			} catch (InvalidArgumentException) {
				// This is not a valid target. Just leave it null.
			}
		}

		$this->entityManager->persist($log);
		$this->entityManager->flush();
	}
}
