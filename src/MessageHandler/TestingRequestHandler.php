<?php

namespace App\MessageHandler;

use App\Message\TestingRequest;
use App\Repository\ProjectRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TestingRequestHandler implements MessageHandlerInterface
{
	/**
	 * @var ProjectRepository
	 */
	private $projectRepository;

	public function __construct(ProjectRepository $projectRepository)
	{
		$this->projectRepository = $projectRepository;
	}

	public function __invoke(TestingRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		// @TODO: Implement the call to the tools service API here
	}
}
