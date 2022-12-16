<?php

namespace App\Tests\Backend\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * AbstractAppTestCase is the base class for functional tests that
 * take place within the Koalati application.
 * This class provides helper methods, and handles client generation
 * and user authentication by default in the `setup()` method.
 */
abstract class AbstractAppTestCase extends WebTestCase
{
	protected const USER_TEST = 'user.test';
	protected const USER_NO_PLAN = 'user.plan.none';
	protected const USER_SOLO_PLAN = 'user.plan.solo';
	protected const USER_SMALL_TEAM_PLAN = 'user.plan.team';
	protected const USER_BUSINESS_PLAN = 'user.plan.business';
	protected const USER_UNVERIFIED_EMAIL = 'user.unverified';

	protected KernelBrowser $client;
	protected ?User $user = null;

	public function setup(): void
	{
		$this->client = static::createClient();
		$this->client->followRedirects();
	}

	protected function loadUser(string $key)
	{
		$credentials = $this->getUserCredentials($key);
		$userRepository = static::getContainer()->get(UserRepository::class);
		$user = $userRepository->findOneByEmail($credentials['email']);

		$this->user = $user;
		$this->client->loginUser($user);
	}

	/**
	 * @param string $key User key (see lass constants)
	 *
	 * @return array{email:string,password:string}
	 */
	protected function getUserCredentials(string $key): array
	{
		$userEmail = match ($key) {
			self::USER_TEST => 'name@email.com',
			self::USER_NO_PLAN => 'no@plan.com',
			self::USER_SOLO_PLAN => 'solo@plan.com',
			self::USER_SMALL_TEAM_PLAN => 'smallteam@plan.com',
			self::USER_BUSINESS_PLAN => 'business@plan.com',
			self::USER_UNVERIFIED_EMAIL => 'unverified@email.com',
		};

		if (!$userEmail) {
			throw new \Exception('Invalid user key: no user is defined in AbstractAppTestCase for key '.$key);
		}

		return [
			"email" => $userEmail,
			"password" => "123456",
		];
	}
}
