<?php

namespace App\Util\Sitemap;

use App\Exception\CrawlingException;
use Exception;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Filter\Prefetch\AllowedHostsFilter;
use VDB\Spider\Filter\Prefetch\UriFilter;
use VDB\Spider\Filter\Prefetch\UriWithHashFragmentFilter;
use VDB\Spider\Spider;

class Crawler
{
	protected Spider $spider;

	public function __construct(string $websiteUrl)
	{
		$this->spider = $this->initializeSpider($websiteUrl);
	}

	protected function initializeSpider(string $websiteUrl): Spider
	{
		$spider = new Spider($websiteUrl);
		/**
		 * @var \VDB\Spider\QueueManager\InMemoryQueueManager
		 */
		$queueManager = $spider->getQueueManager();
		$spider->getDiscovererSet()->set(new XPathExpressionDiscoverer('//a'));
		$spider->getDiscovererSet()->maxDepth = 30;
		$queueManager->maxQueueSize = 1000;

		// Filter out URLs from external domains
		$spider->getDiscovererSet()->addFilter(new AllowedHostsFilter([$websiteUrl], false));

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
			[$politenessPolicyEventListener, 'onCrawlPreRequest']
		);

		return $spider;
	}

	/**
	 * @return array<string,string|null> Array of page titles, indexed by URL
	 */
	public function crawlPages(): array
	{
		$pages = [];
		$this->spider->crawl();

		foreach ($this->spider->getDownloader()->getPersistenceHandler() as $resource) {
			/** @var DomCrawler */
			$crawler = $resource->getCrawler();

			// Only add pages with an <html> tag to the list - others are likely files or images
			if (!$crawler->filterXpath('//html')->count()) {
				continue;
			}

			$url = $this->getStandardUrlFromCrawler($crawler);

			// If this page has already been crawled successfully, skip it
			if (($pages[$url] ?? null) !== null) {
				continue;
			}

			try {
				$title = $crawler->filterXpath('//title')->text();
			} catch (Exception $exception) {
				$title = null;
			}

			$pages[$url] = $title;
		}

		return $pages;
	}

	/**
	 * Returns the canonical URL if available, or the `$crawler->getUri()` otherwise.
	 */
	private function getStandardUrlFromCrawler(DomCrawler $crawler): string
	{
		// Check for canonical URL
		$canonicalTag = $crawler->filterXPath('//link[@rel="canonical"]');
		if ($canonicalTag->count()) {
			$canonicalUrl = $canonicalTag->attr('href');

			return $canonicalUrl;
		}

		return $crawler->getUri();
	}

	/**
	 * Discards repeated URLs (ex.: both HTTP and HTTPS are present, etc.).
	 *
	 * @param array<string,string|null> $pages
	 *
	 * @return array<string,string|null> Array of page titles, indexed by URL
	 */
	public function filterDuplicatePages(array $pages): array
	{
		// Discard repeated URLs (ex.: both HTTP and HTTPS are present, etc.)
		foreach (array_keys($pages) as $url) {
			$httpUrl = str_replace('https://', 'http://', $url);
			if ($httpUrl != $url && isset($pages[$httpUrl])) {
				unset($pages[$httpUrl]);
			}
		}

		return $pages;
	}

	/**
	 * Crawls a website's pages and returns an array of its pages URLs and titles.
	 *
	 * @return array<string,string|null> Array of page titles, indexed by URL
	 */
	public function crawl(): array
	{
		try {
			$pages = $this->crawlPages();
			$pages = $this->filterDuplicatePages($pages);
		} catch (\Exception $exception) {
			throw new CrawlingException(previous: $exception);
		}

		return $pages;
	}
}
