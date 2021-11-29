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
	protected KernelBrowser $client;

	protected User $user;

	public function setup(): void
	{
		$this->client = static::createClient();
		$this->client->followRedirects();

		// Log user in
		$this->loadUser();
		$this->client->loginUser($this->user);
	}

	protected function loadUser()
	{
		$this->reloadUser();
	}

	protected function reloadUser()
	{
		$userRepository = static::getContainer()->get(UserRepository::class);
		$this->user = $userRepository->findOneByEmail('name@email.com');
	}
}
