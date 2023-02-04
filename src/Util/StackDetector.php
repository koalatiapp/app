<?php

namespace App\Util;

use App\Enum\Framework;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class StackDetector
{
	public function __construct(
		private readonly HttpClientInterface $httpClient,
		private readonly LoggerInterface $logger,
	) {
	}

	public function detectFramework(string $url): ?string
	{
		try {
			$crawler = $this->getCrawler($url);
			$documentNode = $crawler->filter("html");
			$headNode = $crawler->filter("head");
			$generatorNode = $headNode->filter("meta[name='generator']");
			$generator = $generatorNode->count() ? strtolower($generatorNode->attr("content")) : null;

			// Webflow
			if ($documentNode->attr("data-wf-site")) {
				return Framework::WEBFLOW;
			}

			// Wordpress
			if (str_contains($generator, "wordpress") ||
				// A single wp-* resource could be from an external source; 2 or more is most likely a Wordpress site though.
				$headNode->children("[href*='wp-content'], [src*='wp-content'], [href*='wp-includes'], [src*='wp-includes']")->count() > 1) {
				return Framework::WORDPRESS;
			}

			// Shopify
			if ($headNode->children("[href*='cdn.shopify.com'], [src*='cdn.shopify.com']")->count() > 1) {
				return Framework::SHOPIFY;
			}
		} catch (\Exception $exception) {
			$this->logger->error($exception);
		}

		return null;
	}

	private function getCrawler(string $url): ?Crawler
	{
		static $cachedCrawlers = [];

		$response = $this->fetch($url);
		$contentType = implode("\n", $response?->getHeaders()["content-type"] ?? []);

		if (!str_contains($contentType, "html")) {
			return null;
		}

		$html = $response?->getContent();
		$cachedCrawlers[$url] = $html ? new Crawler($html) : null;

		return $cachedCrawlers[$url];
	}

	private function fetch(string $url): ?ResponseInterface
	{
		static $cachedResponses = [];

		if (isset($cachedResponses[$url])) {
			return $cachedResponses[$url];
		}

		try {
			$response = $this->httpClient->request("GET", $url);
			$response->getHeaders();
			$response->getContent();
			$cachedResponses[$url] = $response;
		} catch (Exception) {
			$cachedResponses[$url] = null;
		}

		return $cachedResponses[$url];
	}
}
