<?php

namespace App\Tests\Functional\Project;

use App\Entity\Project;
use App\Tests\Functional\AbstractAppTestCase;

class ProjectSettingsTest extends AbstractAppTestCase
{
	protected Project $project;

	public function setup()
	{
		parent::setup();

		// Create project
		$em = static::$container->get('doctrine')->getManager();
		$project = new Project();
		$project->setOwnerUser($this->user)
			->setName('Test Project')
			->setUrl('http://localhost.com');
		$em->persist($project);
		$em->flush();
		$this->project = $project;
	}

	/**
	 * @Depends ProjectCreationTest::testSuccessfulCreation
	 */
	public function testProjectNameChange()
	{
		$crawler = $this->client->request('GET', '/project/'.$this->project->getId().'/settings');

		$form = $crawler->selectButton('project_settings[save]')->form();
		$form['project_settings[name]'] = 'Updated project name';
		$crawler = $this->client->submit($form);

		$this->reloadUser();
		$updatedProject = $this->user->getPersonalProjects()->first();
		$successMessageCount = $crawler->filter('#flash-messages .flash-message.success')->count();

		$this->assertRouteSame('project_settings', ['id' => $this->project->getId()]);
		$this->assertSame('Updated project name', $updatedProject->getName(), 'Project name has been updated in the database.');
		$this->assertGreaterThanOrEqual(1, $successMessageCount, 'Form submission results in a success message.');
	}

	/*
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
		*/
}
