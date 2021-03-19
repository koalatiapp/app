<?php

namespace App\MessageHandler;

use App\Message\ScreenshotRequest;
use App\Repository\ProjectRepository;
use App\Storage\ProjectStorage;
use App\Util\Screenshot\ScreenshotGeneratorInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ScreenshotRequestHandler implements MessageHandlerInterface
{
	/**
	 * @var ProjectRepository
	 */
	private $projectRepository;

	/**
	 * @var ScreenshotGeneratorInterface
	 */
	private $screenshotGenerator;

	/**
	 * @var ProjectStorage
	 */
	private $projectStorage;

	public function __construct(ProjectRepository $projectRepository, ScreenshotGeneratorInterface $screenshotGenerator, ProjectStorage $projectStorage)
	{
		$this->projectRepository = $projectRepository;
		$this->screenshotGenerator = $screenshotGenerator;
		$this->projectStorage = $projectStorage;
	}

	public function __invoke(ScreenshotRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$screenshot = $this->screenshotGenerator
			->setRenderWidth(400)
			->renderDesktop($project->getUrl());
		$this->projectStorage->uploadThumbnail($project, $screenshot);
	}
}
