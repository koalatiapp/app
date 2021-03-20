<?php

namespace App\MessageHandler;

use App\Message\FaviconRequest;
use App\Repository\ProjectRepository;
use App\Storage\ProjectStorage;
use App\Util\Favicon\FaviconFetcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FaviconRequestHandler implements MessageHandlerInterface
{
	/**
	 * @var ProjectRepository
	 */
	private $projectRepository;

	/**
	 * @var FaviconFetcherInterface
	 */
	private $faviconFetcher;

	/**
	 * @var ProjectStorage
	 */
	private $projectStorage;

	public function __construct(ProjectRepository $projectRepository, FaviconFetcherInterface $faviconFetcher, ProjectStorage $projectStorage)
	{
		$this->projectRepository = $projectRepository;
		$this->faviconFetcher = $faviconFetcher;
		$this->projectStorage = $projectStorage;
	}

	public function __invoke(FaviconRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$favicon = $this->faviconFetcher->fetch($project->getUrl());
		$this->projectStorage->uploadFavicon($project, $favicon);
	}
}
