<?php

namespace App\Util;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The Url service provides URL generation, validation and utility methods.
 */
class Url
{
	/**
	 * @var \App\Util\Config;
	 */
	private $config;

	/**
	 * @var \Symfony\Contracts\HttpClient\HttpClientInterface;
	 */
	private $client;

	public function __construct(Config $config, HttpClientInterface $client)
	{
		$this->config = $config;
		$this->client = $client;
	}

	/**
	 * Checks if a URL exists and returns a valid HTTP code (2xx).
	 */
	public function exists(string $url): bool
	{
		try {
			$response = $this->client->request('GET', $url);
			$code = (string) $response->getStatusCode();
			$response->cancel();
		} catch (\Symfony\Component\HttpClient\Exception\TransportException $e) {
			$code = '404';
		}

		return $code[0] == 2;
	}

	/**
	 * Checks if a URL is an XML document.
	 */
	public function isXml(string $url): bool
	{
		try {
			$response = $this->client->request('GET', $url);
			$type = $response->getHeaders()['content-type'][0] ?? '';
			$response->cancel();
		} catch (\Symfony\Component\HttpClient\Exception\TransportException $e) {
			$type = null;
		}

		return (bool) preg_match('~^.+\/(.+[+-])?xml([+-].+)?.*$~', $type);
	}

	/**
	 * Checks if a URL is an HTML document.
	 */
	public function isHtml(string $url): bool
	{
		try {
			$response = $this->client->request('GET', $url);
			$type = $response->getHeaders()['content-type'][0] ?? '';
			$response->cancel();
		} catch (\Symfony\Component\HttpClient\Exception\TransportException $e) {
			$type = '';
		}

		return str_contains($type, 'text/html');
	}

	/**
	 * Standardizes an URL, ensuring the "https(s)://" protocol is defined.
	 *
	 * @param bool $forceHttps when $forceHttps is set to true, the URL will be changed to use HTTPS
	 */
	public function standardize(string $url, bool $forceHttps = false): string
	{
		$url = strtolower($url);

		if (!preg_match('#(https?:)?//.+#', $url)) {
			$url = 'http://'.$url;
		}

		if ($forceHttps && preg_match('~^http://~', $url)) {
			$url = preg_replace('~(?:^https?:)//(.+)~', 'https://$1', $url);
		}

		return $url;
	}

	/**
	 * Removes the query string, anchor and trailing slash from an URL.
	 */
	public function rootUrl(string $url): string
	{
		$url = $this->standardize($url);

		// Trim URL queries and anchor hash
		$url = explode('?', $url)[0];
		$url = explode('#', $url)[0];

		// Remove trailing slash
		if (substr($url, -1) == '/') {
			$url = substr($url, 0, strlen($url) - 1);
		}

		return $url;
	}

	/**
	 * Extract the domain name from an URL.
	 */
	public function domain(string $url): string
	{
		$url = $this->standardize($url);
		$urlData = parse_url($url);

		return $urlData['host'];
	}

	/**
	 * Suggests the standard sitemap URL for the provided website URL.
	 */
	public function guessSitemap(string $url): string
	{
		return $this->rootUrl($url).'/sitemap.xml';
	}

	/**
	 * Returns the message for an HTTP code.
	 */
	public function httpCodeMessage(int $code): string
	{
		return $this->config->get('http_codes', (string) $code) ?: 'Error';
	}
}
