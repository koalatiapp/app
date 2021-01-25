<?php

namespace App\MessageHandler;

use App\Entity\Page;
use App\Message\SitemapRequest;
use App\Message\TestingRequest;
use App\Repository\PageRepository;
use App\Repository\ProjectRepository;
use App\Util\Sitemap\Builder;
use App\Util\Url;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SitemapRequestHandler implements MessageHandlerInterface
{
	/**
	 * @var PageRepository
	 */
	private $pageRepository;

	/**
	 * @var ProjectRepository
	 */
	private $projectRepository;

	/**
	 * @var Builder
	 */
	private $sitemapBuilder;

	/**
	 * @var Url
	 */
	private $urlHelper;

	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var MessageBusInterface
	 */
	private $bus;

	public function __construct(PageRepository $pageRepository, ProjectRepository $projectRepository, Builder $sitemapBuilder, Url $urlHelper, EntityManagerInterface $em, MessageBusInterface $bus)
	{
		$this->pageRepository = $pageRepository;
		$this->projectRepository = $projectRepository;
		$this->sitemapBuilder = $sitemapBuilder;
		$this->urlHelper = $urlHelper;
		$this->em = $em;
		$this->bus = $bus;
	}

	public function __invoke(SitemapRequest $message): void
	{
		// Crawl website to generate the sitemap
		$websiteUrl = $this->urlHelper->standardize($message->getWebsiteUrl());
		$locations = $this->sitemapBuilder->buildFromWebsiteUrl($websiteUrl)->getLocations();
		$existingPages = $this->pageRepository->findByUrls(array_keys($locations));
		$pagesByUrl = [];
		$allPages = [];

		foreach ($existingPages as $page) {
			$pagesByUrl[$page->getUrl()] = $page;
		}

		// Create/update pages from the sitemap
		foreach ($locations as $location) {
			// Check if an existing page can be updated
			if (isset($pagesByUrl[$location->url])) {
				$page = $pagesByUrl[$location->url];

				if ($location->title && $page->getTitle() != $location->title) {
					$page->setTitle($location->title);
					$this->em->persist($page);
				}

				$allPages[] = $page;
			}
			// Otherwise, create the new page
			else {
				$page = new Page($location->url, $location->title);
				$this->em->persist($page);
				$allPages[] = $page;
			}
		}

		// @TODO: Check to delete / deactivate pages that aren't reachable anymore

		// Ensure all projects for this website have their pages updated
		$matchingProjects = $this->projectRepository->findByUrl($websiteUrl);
		foreach ($matchingProjects as $project) {
			foreach ($allPages as $page) {
				$project->addPage($page);
			}
			$this->em->persist($project);
		}
		$this->em->flush();

		// If a project ID was provided in the message, dispatch a new message to refresh that project's results
		if ($projectId = $message->getProjectId()) {
			$this->bus->dispatch(new TestingRequest($projectId));
		}
	}
}
