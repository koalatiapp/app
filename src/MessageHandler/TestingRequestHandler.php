<?php

namespace App\MessageHandler;

use App\ApiClient\Endpoint\ToolsEndpoint;
use App\Message\TestingRequest;
use App\Repository\ProjectRepository;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TestingRequestHandler implements MessageHandlerInterface
{
	/**
	 * @var ProjectRepository
	 */
	private $projectRepository;

	/**
	 * @var ToolsEndpoint
	 */
	private $toolsEndpoint;

	public function __construct(ProjectRepository $projectRepository, ToolsEndpoint $toolsEndpoint)
	{
		$this->projectRepository = $projectRepository;
		$this->toolsEndpoint = $toolsEndpoint;
	}

	public function __invoke(TestingRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$tools = $project->getEnabledAutomatedTools();
		$pageUrls = $project->getActivePages()->map(fn ($page) => $page->getUrl())->toArray();
		$priority = $project->getPriority();

		// If there are no enable tools or active pages, the handling ends here.
		if (!count($tools) || !count($pageUrls)) {
			return;
		}

		// Submit the processing request to the Tools API
		$this->toolsEndpoint->request($pageUrls, $tools, $priority);
	}
}
