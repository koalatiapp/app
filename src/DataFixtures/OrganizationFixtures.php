<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\OrganizationInvitation;
use App\Entity\OrganizationMember;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationFixtures extends Fixture implements DependentFixtureInterface
{
	public function __construct(
		private readonly UserRepository $userRepository
	) {
	}

	public function load(ObjectManager $manager): void
	{
		$organization = (new Organization())
			->setName('Koalati Inc.')
			->setSlug('koalati-inc');
		$manager->persist($organization);

		$users = $this->userRepository->findAll();
		$isFirstUser = true;

		foreach ($users as $user) {
			$role = $isFirstUser ? OrganizationMember::ROLE_OWNER : OrganizationMember::ROLE_MEMBER;
			$membership = new OrganizationMember($organization, $user, $role);
			$organization->addMember($membership);
			$manager->persist($membership);

			if ($isFirstUser) {
				// Create a invitation
				$invitation = new OrganizationInvitation(
					'Émile',
					'emile@koalati.com',
					$organization,
					$user
				);
				$this->forceSetInvitationHash($invitation, 'ad01bca12faaf7113230959d40f8cc12');
				$manager->persist($invitation);
			}

			$isFirstUser = false;
		}

		$manager->flush();
	}

	/**
	 * This method forces the invitation to use the provided hash instead of the
	 * randomly generated one. This is done to keep the hash static, which is
	 * essential in order to make the fixtures' data predictable.
	 */
	private function forceSetInvitationHash(OrganizationInvitation $invitation, string $hash): OrganizationInvitation
	{
		$reflectedHashProperty = new \ReflectionProperty(OrganizationInvitation::class, 'hash');
		$reflectedHashProperty->setAccessible(true);
		$reflectedHashProperty->setValue($invitation, $hash);

		return $invitation;
	}

	public function getDependencies()
	{
		return [
			UserFixtures::class,
		];
	}
}
