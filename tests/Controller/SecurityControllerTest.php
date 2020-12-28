<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
	public function testUnauthorizedRedirection()
	{
		$client = static::createClient();
		$client->request('GET', '/');
		$this->assertResponseRedirects('/login', 302, 'Redirection to login for non-logged user');
	}

	public function testAlreadyLoggedRedirection()
	{
		$client = static::createClient();
		$userRepository = static::$container->get(UserRepository::class);
		$testUser = $userRepository->findOneByEmail('name@email.com');
		$client->loginUser($testUser);

		$client->request('GET', '/login');
		$this->assertResponseRedirects('/', 302, 'Redirection to dashboard when accessing login page while already logged in');
	}

	public function testLogin()
	{
		$client = static::createClient();
		$client->followRedirects();
		$crawler = $client->request('GET', '/login');

		$form = $crawler->selectButton('submit')->form();
		$form['email'] = 'name@email.com';
		$form['password'] = '123456';
		$crawler = $client->submit($form);

		$this->assertResponseIsSuccessful('Login is successful');
		$this->assertRouteSame('dashboard', [], 'Successful login redirects to the dashboard');
	}

	public function testLogout()
	{
		$client = static::createClient();
		$client->followRedirects();
		$userRepository = static::$container->get(UserRepository::class);
		$testUser = $userRepository->findOneByEmail('name@email.com');
		$client->loginUser($testUser);

		$client->request('GET', '/');
		$this->assertResponseIsSuccessful();

		$client->request('GET', '/logout');
		$this->assertRouteSame('login', [], 'Logout successfully redirects to login page.');
	}
}
