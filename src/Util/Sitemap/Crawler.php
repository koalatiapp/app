<?php

namespace App\Util\Sitemap;

use App\Util\Sitemap\Exception\CrawlerException;
use App\Util\Sitemap\Exception\CrawlerPageMaximumException;
use App\Util\Sitemap\Exception\CrawlerTimeoutException;
use Exception;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Filter\Prefetch\AllowedHostsFilter;
use VDB\Spider\Filter\Prefetch\RestrictToBaseUriFilter;
use VDB\Spider\Filter\Prefetch\UriFilter;
use VDB\Spider\Filter\Prefetch\UriWithHashFragmentFilter;
use VDB\Spider\QueueManager\InMemoryQueueManager;
use VDB\Spider\Spider;

class Crawler
{
	/**
	 * Maximum crawling duration in seconds.
	 */
	final public const MAX_CRAWL_DURATION = 600;

	/**
	 * Maximum number of unique pages to crawl.
	 */
	final public const MAX_CRAWL_PAGES = 1000;

	/**
	 * @var array<string,Location>
	 */
	private array $pagesFound = [];

	private readonly Spider $spider;

	public function __construct(string $websiteUrl, ?callable $pageFoundCallback = null)
	{
		$this->spider = $this->initializeSpider($websiteUrl, $pageFoundCallback);
	}

	protected function initializeSpider(string $websiteUrl, ?callable $pageFoundCallback = null): Spider
	{
		$startTime = microtime(true);
		$spider = new Spider($websiteUrl);
		/**
		 * @var \VDB\Spider\QueueManager\InMemoryQueueManager
		 */
		$queueManager = $spider->getQueueManager();
		$spider->getDiscovererSet()->set(new XPathExpressionDiscoverer('//a'));
		$spider->getDiscovererSet()->maxDepth = 30;
		$queueManager->maxQueueSize = self::MAX_CRAWL_PAGES;
		$queueManager->setTraversalAlgorithm(InMemoryQueueManager::ALGORITHM_BREADTH_FIRST);

		// Filter out URLs from external domains
		$spider->getDiscovererSet()->addFilter(new AllowedHostsFilter([$websiteUrl], false));
		$spider->getDiscovererSet()->addFilter(new RestrictToBaseUriFilter($websiteUrl));

		// Filter out URLs with anchors
		$spider->getDiscovererSet()->addFilter(new UriWithHashFragmentFilter());

		// Filter out URLs that are in fact email addresses
		$spider->getDiscovererSet()->addFilter(new UriFilter(['~^(https?:)//[^/]+@[^/]+(/.*)?$~']));

		// Delay between consecutive requests
		/**
		 * @var \VDB\Spider\Downloader\Downloader
		 */
		$downloader = $spider->getDownloader();
		$politenessPolicyEventListener = new PolitenessPolicyListener(10);
		$downloader->getDispatcher()->addListener(
			SpiderEvents::SPIDER_CRAWL_PRE_REQUEST,
			$politenessPolicyEventListener->onCrawlPreRequest(...)
		);

		// Check if the maximum crawl duration has been exceeded
		$downloader->getDispatcher()->addListener(
			SpiderEvents::SPIDER_CRAWL_PRE_REQUEST,
			function () use ($startTime) {
				if (count($this->pagesFound) >= Crawler::MAX_CRAWL_PAGES) {
					throw new CrawlerPageMaximumException();
				}

				if (microtime(true) >= $startTime + Crawler::MAX_CRAWL_DURATION) {
					throw new CrawlerTimeoutException();
				}
			}
		);

		$spider->getDispatcher()->addListener(
			SpiderEvents::SPIDER_CRAWL_RESOURCE_PERSISTED,
			function () use ($downloader, $pageFoundCallback) {
				$persistenceHandler = $downloader->getPersistenceHandler();

				/** @var \VDB\Spider\Resource */
				$resource = $persistenceHandler->current();
				$persistenceHandler->next();

				/** @var DomCrawler */
				$crawler = $resource->getCrawler();
				$statusCode = $resource->getResponse()->getStatusCode();

				// Only add pages with an <html> tag to the list - others are likely files or images
				if (!$crawler->filterXpath('//html')->count()) {
					return;
				}

				$url = $this->getStandardUrlFromCrawler($crawler);

				if (!isset($this->pagesFound[$url])) {
					$this->pagesFound[$url] = new Location($url);
				}

				// If this page has already been crawled successfully, skip it
				if ($this->pagesFound[$url]->title) {
					return;
				}

				try {
					$title = $crawler->filterXpath('//title')->text();
				} catch (Exception) {
					$title = null;
				}

				$this->pagesFound[$url]->title = $title;
				$this->pagesFound[$url]->statusCode = $statusCode;

				// Only add pages that don't have a query string.
				if ($pageFoundCallback && !str_contains($url, "?")) {
					call_user_func($pageFoundCallback, [$this->pagesFound[$url]]);
				}
			}
		);

		return $spider;
	}

	/**
	 * Returns the canonical URL if available, then the og:url if available,
	 * or the `$crawler->getUri()` otherwise.
	 */
	private function getStandardUrlFromCrawler(DomCrawler $crawler): string
	{
		// Check for canonical URL
		$canonicalTag = $crawler->filterXPath('//link[@rel="canonical"]');
		if ($canonicalTag->count()) {
			return $canonicalTag->attr('href');
		}

		// Check for og:URL
		$ogurlTag = $crawler->filterXPath('//meta[@property="og:url"]');
		if ($ogurlTag->count()) {
			return $ogurlTag->attr('content');
		}

		return $crawler->getUri();
	}

	/**
	 * Crawls a website's pages and returns its pages URLs and titles.
	 *
	 * @return array<string,Location> Array of pages (locations), indexed by URL
	 */
	public function crawl(): array
	{
		try {
			$this->spider->crawl();
		} catch (\Exception $exception) {
			throw new CrawlerException(previous: $exception);
		}

		return $this->pagesFound;
	}
}
