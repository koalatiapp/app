<?php

namespace App\Util\Meta\Driver;

use App\Util\Meta\Metadata;
use App\Util\Meta\MetaDriverInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BasicHttp implements MetaDriverInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly HttpClientInterface $httpClient,
	) {
	}

	public function getMetas(string $url): Metadata
	{
		$crawler = new Crawler($this->fetchHtml($url));

		return new Metadata(
			$this->getUrl($crawler) ?? $url,
			null,
			$this->getTitle($crawler) ?? null,
			$this->getDescription($crawler) ?? null,
			$this->getOgImage($crawler, $url) ?? null,
		);
	}

	protected function fetchHtml(string $url): string
	{
		$response = $this->httpClient->request("GET", $url);

		return $response->getContent(false);
	}

	protected function getUrl(Crawler $crawler): ?string
	{
		$canonicalElement = $crawler->filter("link[rel='canonical']");
		$url = $canonicalElement->count() ? $canonicalElement->attr("href") : null;

		if (!$url) {
			$ogUrlElement = $crawler->filter("meta[property='og:url']");
			$url = $ogUrlElement->count() ? $ogUrlElement->attr("content") : null;
		}

		if ($url && !str_starts_with($url, "http")) {
			$url = null;
		}

		return $url;
	}

	protected function getTitle(Crawler $crawler): ?string
	{
		$ogTitleElement = $crawler->filter("meta[property='og:title']");

		if ($ogTitleElement->count()) {
			return $ogTitleElement->attr("content");
		}

		$titleElement = $crawler->filter("title");

		return $titleElement->count() ? $titleElement->innerText() : null;
	}

	protected function getDescription(Crawler $crawler): ?string
	{
		$ogDescriptionElement = $crawler->filter("meta[property='og:description']");

		if ($ogDescriptionElement->count()) {
			return $ogDescriptionElement->attr("content");
		}

		$descriptionElement = $crawler->filter("meta[name='description']");

		return $descriptionElement->count() ? $descriptionElement->attr('content') : null;
	}

	protected function getOgImage(Crawler $crawler, string $url): ?string
	{
		$ogImageElement = $crawler->filter("meta[property='og:image']");
		$imageUrl = $ogImageElement->count() ? $ogImageElement->attr("content") : null;

		if ($imageUrl && !str_starts_with($imageUrl, "http")) {
			$hostname = parse_url($url, PHP_URL_HOST);
			$scheme = parse_url($url, PHP_URL_SCHEME);
			$prefix = "$scheme://$hostname";

			if (!str_starts_with($imageUrl, "/")) {
				$prefix .= parse_url($url, PHP_URL_PATH);
				$prefix = rtrim($prefix, "/")."/";
			}

			$imageUrl = $prefix.$imageUrl;
		}

		return $imageUrl;
	}
}
