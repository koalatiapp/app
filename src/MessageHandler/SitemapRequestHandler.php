<?php

namespace App\MessageHandler;

use App\Entity\Page;
use App\Entity\Project;
use App\Message\SitemapRequest;
use App\Message\TestingRequest;
use App\Repository\ProjectRepository;
use App\Subscription\PlanManager;
use App\Util\Sitemap\Builder;
use App\Util\Sitemap\Location;
use App\Util\Url;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class SitemapRequestHandler
{
	public function __construct(
		private readonly ProjectRepository $projectRepository,
		private readonly Builder $sitemapBuilder,
		private readonly Url $urlHelper,
		private readonly EntityManagerInterface $em,
		private readonly MessageBusInterface $bus,
		private readonly HttpClientInterface $httpClient,
		private readonly PlanManager $planManager,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 */
	public function __invoke(SitemapRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		$originalPages = $project->getPages()->toArray();
		$userPlan = $this->planManager->getPlanFromEntity($project->getOwner());
		$pageLimit = $userPlan->getMaxActivePagesPerProject();
		$supportsSsl = $this->websiteSupportsSsl($project);
		// Crawl website and sitemap, creating/updating pages everytime a page is found
		$websiteUrl = $this->urlHelper->standardize($project->getUrl(), $supportsSsl);
		$pagesByUrl = [];
		$foundPageUrls = [];

		foreach ($project->getPages() as $page) {
			$pageUrl = $this->urlHelper->standardize($page->getUrl(), $supportsSsl);
			$pagesByUrl[$pageUrl] = $page;
		}

		$pageIdsSentForTest = [];

		/** @param array<int,Location> $locations */
		$pageFoundCallback = function (array $locations) use (&$pagesByUrl, &$foundPageUrls, &$pageIdsSentForTest, $project, $message, $supportsSsl, $pageLimit) {
			$pagesToTest = [];
			$pendingPersistCount = 0;

			foreach ($locations as $location) {
				$location->url = $this->urlHelper->standardize($location->url, $supportsSsl);
				$foundPageUrls[] = $location->url;

				// Check if an existing page can be updated
				if (isset($pagesByUrl[$location->url])) {
					$page = $pagesByUrl[$location->url];
					$page->setHttpCode($location->statusCode);

					if ($location->title && $page->getTitle() != $location->title) {
						$page->setTitle($location->title);
					}

					$this->em->persist($page);
				}
				// Otherwise, create the new page
				elseif (strlen($location->url) <= 510) {
					$page = new Page($project, $location->url, $location->title);
					$page->setHttpCode($location->statusCode);
					$pagesByUrl[$location->url] = $page;

					$this->em->persist($page);

					if (!$page->respondsWithError()) {
						$pagesToTest[] = $page;
					}
				}

				$pendingPersistCount++;

				// Flusing more frequently prevents the db queries from being too big, which can cause the "MySQL server has gone away" error
				if ($pendingPersistCount == 10) {
					$this->flushOrStopIfProjectIsDeleted();
					$pendingPersistCount = 0;
				}
			}

			$this->flushOrStopIfProjectIsDeleted();

			$pageIds = array_map(fn (Page $page) => $page->getId(), $pagesToTest);

			// Enforce max active pages per projects
			if (count($pageIdsSentForTest) >= $pageLimit) {
				return;
			}

			$pageIds = array_slice($pageIds, 0, max(0, $pageLimit - count($pageIdsSentForTest)));
			$pageIdsSentForTest = [...$pageIdsSentForTest, ...$pageIds];

			// Dispatch a testing request to start the testing on new pages
			if ($pageIds) {
				$this->bus->dispatch(new TestingRequest($message->getProjectId(), null, $pageIds));
			}
		};

		$this->sitemapBuilder->buildFromWebsiteUrl($websiteUrl, $pageFoundCallback, $project->useCanonicalPageUrls());

		$this->fetchMissingTitles($pagesByUrl);
		$this->flushOrStopIfProjectIsDeleted();

		// Check to delete / deactivate pages that aren't reachable anymore
		foreach ($originalPages as $originalPage) {
			if (!in_array($originalPage->getUrl(), $foundPageUrls)) {
				$this->em->remove($originalPage);
			}
		}
		$this->flushOrStopIfProjectIsDeleted();
	}

	private function flushOrStopIfProjectIsDeleted(): void
	{
		try {
			$this->em->flush();
		} catch (\Exception $exception) {
			// Exception types can vary for integrity constraint errors
			// But they only mean one thing in this context: the project no longer exists!
			if (str_contains($exception->getMessage(), "SQLSTATE[23000]")) {
				throw new UnrecoverableMessageHandlingException("The sitemap request stopped because the project doesn't seem to exist anymore.", 0, $exception);
			}

			throw $exception;
		}
	}

	/**
	 * @param array<string,Page> $pagesByUrl
	 */
	private function fetchMissingTitles(array $pagesByUrl): void
	{
		$unsavedTitleCount = 0;

		foreach ($pagesByUrl as $page) {
			if ($page->getTitle()) {
				continue;
			}

			$location = new Location($page->getUrl());
			$location->fetchTitle();

			if ($location->title) {
				$page->setTitle($location->title);
				$this->em->persist($page);
				$unsavedTitleCount++;

				if ($unsavedTitleCount >= 10) {
					$this->flushOrStopIfProjectIsDeleted();
					$unsavedTitleCount = 0;
				}
			}

			usleep(10);
		}
	}

	private function websiteSupportsSsl(Project $project): bool
	{
		try {
			$httpsUrl = $this->urlHelper->standardize($project->getUrl(), true);
			$response = $this->httpClient->request("GET", $httpsUrl);

			if (!in_array(substr((string) $response->getStatusCode(), 0, 1), ["2", "3"])) {
				return false;
			}
		} catch (TransportException $exception) {
			if (str_contains(strtolower($exception->getMessage()), "ssl")) {
				return false;
			}
		}

		return true;
	}
}
