<?php

namespace App\MessageHandler;

use App\ApiClient\Endpoint\ToolsEndpoint;
use App\Message\TestingRequest;
use App\Repository\ProjectRepository;
use App\Util\Testing\AvailableToolsFetcher;
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

	/**
	 * @var AvailableToolsFetcher
	 */
	private $availableToolsFetcher;

	public function __construct(ProjectRepository $projectRepository, ToolsEndpoint $toolsEndpoint, AvailableToolsFetcher $availableToolsFetcher)
	{
		$this->projectRepository = $projectRepository;
		$this->toolsEndpoint = $toolsEndpoint;
		$this->availableToolsFetcher = $availableToolsFetcher;
	}

	public function __invoke(TestingRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$pageUrls = $project->getActivePages()->map(fn ($page) => $page->getUrl())->toArray();
		$priority = $project->getPriority();
		$tools = $this->availableToolsFetcher->getTools();

		foreach ($project->getDisabledTools() as $disabledTool) {
			unset($tools[$disabledTool]);
		}

		// If there are no enable tools or active pages, the handling ends here.
		if (!count($tools) || !count($pageUrls)) {
			return;
		}

		// Submit the processing request to the Tools API
		$this->toolsEndpoint->request($pageUrls, array_keys($tools), $priority);
	}
}
