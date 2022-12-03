<?php

namespace App\Util;

use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The Url service provides URL generation, validation and utility methods.
 */
class Url
{
	public function __construct(private readonly ?Config $config = null, private readonly ?HttpClientInterface $client = null)
	{
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
		} catch (TransportException) {
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
		} catch (TransportException) {
			return false;
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
		} catch (TransportException) {
			$type = '';
		}

		return str_contains($type, 'text/html');
	}

	/**
	 * Standardizes an URL, ensuring the "https(s)://" protocol is defined.
	 *
	 * @param bool $forceHttps when $forceHttps is set to true, the URL will be changed to use HTTPS
	 */
	public static function standardize(string $url, bool $forceHttps): string
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
	public static function rootUrl(string $url): string
	{
		$url = static::standardize($url, false);

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
	public static function domain(string $url): string
	{
		$url = static::standardize($url, false);

		return parse_url($url, PHP_URL_HOST);
	}

	/**
	 * Extract the path/slug from an URL.
	 */
	public static function path(string $url): string
	{
		$url = static::standardize($url, false);

		return parse_url($url, PHP_URL_PATH) ?? '';
	}

	/**
	 * Suggests the standard sitemap URL for the provided website URL.
	 */
	public static function guessSitemap(string $url): string
	{
		return static::rootUrl($url).'/sitemap.xml';
	}

	/**
	 * Returns the message for an HTTP code.
	 */
	public function httpCodeMessage(int $code): string
	{
		return $this->config->get('http_codes', (string) $code) ?: 'Error';
	}
}
