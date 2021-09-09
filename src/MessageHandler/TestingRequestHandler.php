<?php

namespace App\MessageHandler;

use App\ApiClient\Endpoint\ToolsEndpoint;
use App\Entity\Page;
use App\Entity\Project;
use App\Message\TestingRequest;
use App\Repository\ProjectRepository;
use App\Util\Testing\AvailableToolsFetcher;
use Doctrine\Common\Collections\Collection;
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

		$pages = $this->getPagesToTest($message, $project);
		$pageUrls = $pages->map(fn ($page) => $page->getUrl())->toArray();
		$priority = $project->getPriority();
		$tools = $this->getToolsToUse($message, $project);

		// If there are no enable tools or active pages, the handling ends here.
		if (!count($tools) || !count($pageUrls)) {
			return;
		}

		// Submit the processing request to the Tools API
		$this->toolsEndpoint->request($pageUrls, $tools, $priority);
	}

	/**
	 * @return Collection<int,Page>
	 */
	private function getPagesToTest(TestingRequest $message, Project $project): Collection
	{
		$pages = $project->getActivePages();

		// If the testing request specifies pages to test, filter pages using this specification
		if ($message->getPageIds()) {
			return $pages->filter(function ($page) use ($message) {
				return in_array($page->getId(), $message->getPageIds());
			});
		}

		return $pages;
	}

	/**
	 * @return array<int,string>
	 */
	private function getToolsToUse(TestingRequest $message, Project $project): array
	{
		$tools = $this->availableToolsFetcher->getTools();

		foreach ($project->getDisabledTools() as $disabledTool) {
			unset($tools[$disabledTool]);
		}

		$tools = array_keys($tools);

		// If the testing request specifies tools, filter tools using this specification
		if ($message->getTools()) {
			return array_intersect($tools, $message->getTools());
		}

		return $tools;
	}
}
