<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
	public function setup()
	{
		$this->client = static::createClient();
	}

	public function testUnauthorizedRedirection()
	{
		$this->client->request('GET', '/');
		$this->assertResponseRedirects('/login', 302, 'Redirection to login for non-logged user');
	}

	public function testAlreadyLoggedRedirection()
	{
		$userRepository = static::$container->get(UserRepository::class);
		$testUser = $userRepository->findOneByEmail('name@email.com');
		$this->client->loginUser($testUser);

		$this->client->request('GET', '/login');
		$this->assertResponseRedirects('/', 302, 'Redirection to dashboard when accessing login page while already logged in');
	}

	public function testLogin()
	{
		$this->client->followRedirects();
		$crawler = $this->client->request('GET', '/login');

		$form = $crawler->selectButton('submit')->form();
		$form['email'] = 'name@email.com';
		$form['password'] = '123456';
		$crawler = $this->client->submit($form);

		$this->assertResponseIsSuccessful('Login is successful');
		$this->assertRouteSame('dashboard', [], 'Successful login redirects to the dashboard');
	}

	public function testLogout()
	{
		$this->client->followRedirects();
		$userRepository = static::$container->get(UserRepository::class);
		$testUser = $userRepository->findOneByEmail('name@email.com');
		$this->client->loginUser($testUser);

		$this->client->request('GET', '/');
		$this->assertResponseIsSuccessful();

		$this->client->request('GET', '/logout');
		$this->assertRouteSame('login', [], 'Logout successfully redirects to login page.');
	}
}
