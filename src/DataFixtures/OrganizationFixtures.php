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
	/**
	 * @var UserRepository
	 */
	private $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
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
				$invitation = new OrganizationInvitation('Émile', 'emile@koalati.com', $organization, $user);
				$manager->persist($invitation);
			}

			$isFirstUser = false;
		}

		$manager->flush();
	}

	public function getDependencies()
	{
		return [
			UserFixtures::class,
		];
	}
}
