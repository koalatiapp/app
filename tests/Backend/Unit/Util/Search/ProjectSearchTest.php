<?php

namespace App\Tests\Backend\Unit\Util\Search;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Util\Search\ProjectSearch;
use App\Util\Search\SearchResult;
use Hashids\HashidsInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectSearchTest extends TestCase
{
	private MockObject $mockIdHasher;
	private MockObject $mockTranslator;
	private MockObject $mockRouter;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.route)
	 */
	public function setup(): void
	{
		$this->mockIdHasher = $this->createMock(HashidsInterface::class);
		$this->mockIdHasher->method('encode')->willReturnArgument(0);

		$this->mockTranslator = $this->createMock(TranslatorInterface::class);
		$this->mockTranslator->method('trans')->willReturnArgument('Project');

		$this->mockRouter = $this->createMock(RouterInterface::class);
		$this->mockRouter->method('generate')->willReturnCallback(
			fn ($route, $parameters) => 'https://app.koalati.com/project/'.$parameters['id']
		);
	}

	public function testValidSearch()
	{
		$mockProjects = [];
		$projectsData = [
			1 => 'Emile & Co',
			2 => "Emile's Portfolio",
			3 => 'Koalati',
		];

		foreach ($projectsData as $id => $name) {
			$mockProject = $this->createMock(Project::class);
			$mockProject->method('getId')->willReturn($id);
			$mockProject->method('getName')->willReturn($name);

			$mockProjects[] = $mockProject;
		}

		$mockProjectRepository = $this->createMock(ProjectRepository::class);
		$mockProjectRepository->expects($this->once())
			->method('findBySearchQuery')
			->willReturn($mockProjects);

		$projectSearchEngine = new ProjectSearch($this->mockRouter, $mockProjectRepository, $this->mockTranslator, $this->mockIdHasher);
		$this->assertEquals(
			[
				new SearchResult('https://app.koalati.com/project/1', 'Emile & Co', null),
				new SearchResult('https://app.koalati.com/project/2', "Emile's Portfolio", null),
				new SearchResult('https://app.koalati.com/project/3', 'Koalati', null),
			],
			$projectSearchEngine->search(['emile'], null)->toArray()
		);
	}
}
