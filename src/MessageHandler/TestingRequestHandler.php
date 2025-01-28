<?php

namespace App\MessageHandler;

use App\Entity\Page;
use App\Entity\Project;
use App\Entity\ProjectActivityRecord;
use App\Message\TestingRequest;
use App\Message\TestingStatusRequest;
use App\Repository\ProjectRepository;
use App\Subscription\PlanManager;
use App\Subscription\UsageManager;
use App\ToolsService\Endpoint\ToolsEndpoint;
use App\Util\Testing\AvailableToolsFetcher;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class TestingRequestHandler
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly ProjectRepository $projectRepository,
		private readonly ToolsEndpoint $toolsEndpoint,
		private readonly AvailableToolsFetcher $availableToolsFetcher,
		private readonly EntityManagerInterface $entityManager,
		private readonly PlanManager $planManager,
		private readonly MessageBusInterface $bus,
		private UsageManager $usageManager,
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
		$pageUrls = $pages->map(fn (?Page $page = null) => $page->getUrl())->toArray();
		$priority = $project->getPriority();
		$tools = $this->getToolsToUse($message, $project);

		// Remove invalid URLs
		$pageUrls = array_filter($pageUrls, fn ($url) => (bool) filter_var($url, FILTER_VALIDATE_URL));

		// If there are no enable tools or active pages, the handling ends here.
		if (!count($tools) || !count($pageUrls)) {
			return;
		}

		// Sort URLs by length, with the shortests ones appearing first.
		// Pages with shorter URLs are often more relevant for testing.
		usort($pageUrls, fn (string $urlA, string $urlB) => strlen(urldecode($urlA)) <=> strlen(urldecode($urlB)));

		// Limit the number of URLs sent for testing to reduce load on server
		if (count($pageUrls) > $maxActivePageCount) {
			$pageUrls = array_slice($pageUrls, 0, $maxActivePageCount);
		}

		// Make sure not to cross the amount of page tests allowed by the user's spending limits
		$usageManager = $this->usageManager->withUser($project->getTopLevelOwner());
		$numberOfPageTestsAllowed = $usageManager->getNumberOfPageTestsAllowed();

		if ($numberOfPageTestsAllowed <= 0) {
			// Send an update to the client(s) to indicate that testing is NOT in progress
			$this->bus->dispatch(new TestingStatusRequest($project->getId()));

			return;
		}

		if (count($pageUrls) > $numberOfPageTestsAllowed) {
			$pageUrls = array_slice($pageUrls, 0, $numberOfPageTestsAllowed);
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
			return $pages->filter(fn (?Page $page = null) => in_array($page->getId(), $message->getPageIds()));
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
