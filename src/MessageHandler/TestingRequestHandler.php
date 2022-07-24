<?php

namespace App\MessageHandler;

use App\ApiClient\Endpoint\ToolsEndpoint;
use App\Entity\Page;
use App\Entity\Project;
use App\Entity\ProjectActivityRecord;
use App\Message\TestingRequest;
use App\Message\TestingStatusRequest;
use App\Repository\ProjectRepository;
use App\Subscription\PlanManager;
use App\Util\Testing\AvailableToolsFetcher;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TestingRequestHandler implements MessageHandlerInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ProjectRepository $projectRepository,
		private ToolsEndpoint $toolsEndpoint,
		private AvailableToolsFetcher $availableToolsFetcher,
		private EntityManagerInterface $entityManager,
		private PlanManager $planManager,
		private MessageBusInterface $bus,
	) {
	}

	public function __invoke(TestingRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		// Check if the user's plan allow them to use automated testing
		$plan = $this->planManager->getPlanFromEntity($project->getOwner());

		if (!$plan->hasTestingAccess()) {
			return;
		}

		$maxActivePageCount = $plan->getMaxActivePagesPerProject();
		$pages = $this->getPagesToTest($message, $project);
		$pageUrls = $pages->map(fn ($page) => $page->getUrl())->toArray();
		$priority = $project->getPriority();
		$tools = $this->getToolsToUse($message, $project);

		// Remove invalid URLs
		$pageUrls = array_filter($pageUrls, function ($url) {
			return (bool) filter_var($url, FILTER_VALIDATE_URL);
		});

		// If there are no enable tools or active pages, the handling ends here.
		if (!count($tools) || !count($pageUrls)) {
			return;
		}

		// Sort URLs by length, with the shortests ones appearing first.
		// Pages with shorter URLs are often more relevant for testing.
		usort($pageUrls, function (string $urlA, string $urlB) {
			return strlen(urldecode($urlA)) > strlen(urldecode($urlB)) ? 1 : -1;
		});

		// Limit the number of URLs sent for testing to reduce load on server
		if (count($pageUrls) > $maxActivePageCount) {
			$pageUrls = array_slice($pageUrls, 0, $maxActivePageCount);
		}

		try {
			// Submit the processing request to the Tools API
			$this->toolsEndpoint->request(array_values($pageUrls), array_values($tools), $priority);
		} catch (TransportException $exception) {
			throw new RecoverableMessageHandlingException($exception->getMessage(), $exception->getCode(), $exception);
		}

		// Keep a record of these testing requests for account quotas (and analytics)
		$this->recordProjectActivity($project, $tools, $pageUrls);

		// Send an update to the client(s) to indicate that testing is in progress
		$this->bus->dispatch(new TestingStatusRequest($project->getId()));
	}

	/**
	 * @param array<int,string> $tools
	 * @param array<int,string> $pageUrls
	 */
	private function recordProjectActivity(Project $project, array $tools, array $pageUrls): void
	{
		$ownerUser = $project->getOwnerUser() ?: $project->getOwnerOrganization()->getOwner();

		foreach ($tools as $tool) {
			foreach ($pageUrls as $pageUrl) {
				$record = (new ProjectActivityRecord())
					->setProject($project)
					->setUser($ownerUser)
					->setWebsiteUrl($project->getUrl())
					->setPageUrl($pageUrl)
					->setTool($tool);
				$this->entityManager->persist($record);
			}
		}

		$this->entityManager->flush();
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
