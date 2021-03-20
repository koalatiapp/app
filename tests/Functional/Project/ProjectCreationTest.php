<?php

namespace App\Tests\Functional\Project;

use App\Tests\Functional\AbstractAppTestCase;

class ProjectCreationTest extends AbstractAppTestCase
{
	public function testSuccessfulCreation()
	{
		$crawler = $this->client->request('GET', '/project/create');

		$form = $crawler->selectButton('new_project[save]')->form();
		$form['new_project[name]'] = 'My Test Project';
		$form['new_project[url]'] = 'https://docs.koalati.com';
		$crawler = $this->client->submit($form);
		$this->reloadUser();
		$project = $this->user->getPersonalProjects()->first();

		$this->assertResponseIsSuccessful();
		$this->assertRouteSame('project_dashboard', [], 'Successfully redirects to the project dashboard after the creation');
		$this->assertSame('My Test Project', $project->getName(), 'Project was created in the database.');
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
