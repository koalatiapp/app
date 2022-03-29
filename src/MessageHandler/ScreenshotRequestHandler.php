<?php

namespace App\MessageHandler;

use App\Message\ScreenshotRequest;
use App\Repository\ProjectRepository;
use App\Storage\ProjectStorage;
use App\Util\Screenshot\ScreenshotGeneratorInterface;
use App\Util\Url;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ScreenshotRequestHandler implements MessageHandlerInterface
{
	public function __construct(
		private ProjectRepository $projectRepository,
		private ScreenshotGeneratorInterface $screenshotGenerator,
		private ProjectStorage $projectStorage,
		private Url $urlHelper,
	) {
	}

	public function __invoke(ScreenshotRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$thumbnailUrl = $this->projectStorage->thumbnailUrl($project);

		if ($this->urlHelper->exists($thumbnailUrl)) {
			// We already have a screenshot for this URL!
			// @TODO: Add some kind of timer on this to allow refreshing screenshots after a while (or on demand)
			return;
		}

		$screenshot = $this->screenshotGenerator
			->setRenderWidth(400)
			->renderDesktop($project->getUrl());
		$this->projectStorage->uploadThumbnail($project, $screenshot);
	}
}
