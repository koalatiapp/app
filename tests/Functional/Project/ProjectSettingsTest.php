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

	public function testProjectDeletion()
	{
		$crawler = $this->client->request('GET', '/project/'.$this->project->getId().'/settings');

		$form = $crawler->selectButton('project_settings[delete]')->form();
		$crawler = $this->client->submit($form);
		$errorMessage = $crawler->filter('#delete li.error')->first()?->text();

		$this->assertRouteSame('project_settings', ['id' => $this->project->getId()]);
		$this->assertSame('You must check this box to proceed with the deletion of this project.', $errorMessage, 'Prevents project deletion without checking the confirmation box.');

		$form = $crawler->selectButton('project_settings[delete]')->form();
		$form['project_settings[deleteConfirmation]'] = '1';
		$crawler = $this->client->submit($form);
		$successMessageCount = $crawler->filter('#flash-messages .flash-message.success')->count();

		$this->assertRouteSame('dashboard', [], 'Redirects to dashboard after project deletion');
		$this->assertGreaterThanOrEqual(1, $successMessageCount, 'Displays a success notice after project deletion.');

		$this->reloadUser();
		$latestProject = $this->user->getPersonalProjects()->first();

		$this->assertNotEquals($this->project->getId(), $latestProject->getId(), 'Project has been deleted from the database.');
	}
}
