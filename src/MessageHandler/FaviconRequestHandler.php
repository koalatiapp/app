<?php

namespace App\MessageHandler;

use App\Message\FaviconRequest;
use App\Repository\ProjectRepository;
use App\Storage\ProjectStorage;
use App\Util\Favicon\FaviconFetcherInterface;
use App\Util\Url;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FaviconRequestHandler
{
	public function __construct(
		private readonly ProjectRepository $projectRepository,
		private readonly FaviconFetcherInterface $faviconFetcher,
		private readonly ProjectStorage $projectStorage,
		private readonly Url $urlHelper,
	) {
	}

	public function __invoke(FaviconRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$faviconUrl = $this->projectStorage->faviconUrl($project);

		if ($this->urlHelper->exists($faviconUrl) && $this->urlHelper->isImage($faviconUrl)) {
			// We already have a favicon for this URL!
			// @TODO: Add some kind of timer on this to allow refreshing favicons after a while (or on demand)
			return;
		}

		$favicon = $this->faviconFetcher->fetch($project->getUrl());
		$this->projectStorage->uploadFavicon($project, $favicon);
	}
}
