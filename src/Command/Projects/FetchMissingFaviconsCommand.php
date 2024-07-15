<?php

namespace App\Command\Projects;

use App\Message\FaviconRequest;
use App\Repository\ProjectRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(name: 'app:projects:fetch-missing-favicons')]
class FetchMissingFaviconsCommand extends Command
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly ProjectRepository $projectRepository,
		private readonly MessageBusInterface $bus,
	) {
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setDescription('Creates requests to fetch any missing project favicons.');
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$projects = $this->projectRepository->findAll();

		foreach ($projects as $project) {
			$this->bus->dispatch(new FaviconRequest($project->getId()));
		}

		return Command::SUCCESS;
	}
}
