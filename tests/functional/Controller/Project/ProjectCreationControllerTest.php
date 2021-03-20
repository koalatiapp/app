<?php

namespace App\Tests\Functional\Controller\Project;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectCreationControllerTest extends WebTestCase
{
	public function setup()
	{
		$this->client = static::createClient();
		$this->client->followRedirects();

		// Log user in
		$userRepository = static::$container->get(UserRepository::class);
		$testUser = $userRepository->findOneByEmail('name@email.com');
		$this->client->loginUser($testUser);
	}

	public function testSuccessfulCreation()
	{
		$crawler = $this->client->request('GET', '/project/create');

		$form = $crawler->selectButton('new_project[save]')->form();
		$form['new_project[name]'] = 'My Test Project';
		$form['new_project[url]'] = 'https://docs.koalati.com';
		$crawler = $this->client->submit($form);

		$this->assertResponseIsSuccessful();
		$this->assertRouteSame('project_dashboard', [], 'Successfully redirects to the project dashboard after the creation');
	}

	public function testUnreachableUrlCreation()
	{
		$crawler = $this->client->request('GET', '/project/create');

		$form = $crawler->selectButton('new_project[save]')->form();
		$form['new_project[name]'] = 'My Test Project';
		$form['new_project[url]'] = 'https://doesnotexist.koalati.com';
		$crawler = $this->client->submit($form);

		$errorMessage = $crawler->filter("form .errors .error")->first()?->text();

		$this->assertRouteSame('project_creation', []);
		$this->assertSame("This URL is invalid or unreachable.", $errorMessage, "Displayed an error message for invalid URL.");
	}
}
