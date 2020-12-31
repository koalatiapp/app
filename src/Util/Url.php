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
		$response = $this->client->request('GET', $url);
		$code = (string) $response->getStatusCode();
		$response->cancel();

		return $code[0] == 2;
	}

	/**
	 * Checks if a URL is an XML document.
	 */
	public function isXML(string $url): bool
	{
		$response = $this->client->request('GET', $url);
		$type = $response->getHeaders()['content-type'][0] ?? '';
		$response->cancel();

		return (bool) preg_match('~^.+\/(.+[+-])?xml([+-].+)?$~', $type);
	}

	/**
	 * Checks if a URL is an HTML document.
	 */
	public function isHTML(string $url): bool
	{
		$response = $this->client->request('GET', $url);
		$type = $response->getHeaders()['content-type'][0] ?? '';
		$response->cancel();

		return $type == 'text/html';
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
			$url = preg_replace('~((?:^https?:)//).+~', '$1', $url);
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
	 * Generates an absolute URL.
	 */
	public function absolute(string $url, string $domain, ?string $currentUrl = null): string
	{
		if ($currentUrl) {
			$parsedCurrentUrl = parse_url($currentUrl);

			if (isset($parsedCurrentUrl['scheme']) && isset($parsedCurrentUrl['host'])) {
				if (strpos($url, '/') === 0) {
					return $parsedCurrentUrl['scheme'].'://'.$parsedCurrentUrl['host'].$url;
				} elseif ((strpos($url, '../') === 0 || strpos($url, '?') === 0 || strpos($url, '#') === 0) && isset($parsedCurrentUrl['path'])) {
					return $parsedCurrentUrl['scheme'].'://'.$parsedCurrentUrl['host'].$parsedCurrentUrl['path'].$url;
				} elseif (strpos($url, '../') === 0 || strpos($url, '?') === 0 || strpos($url, '#') === 0) {
					return $parsedCurrentUrl['scheme'].'://'.$parsedCurrentUrl['host'].$url;
				} elseif (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, 'mailto:') === 0 || strpos($url, 'tel:') === 0 || strpos($url, 'fax:') === 0 || strpos($url, 'javascript:') === 0) {
					return $url;
				} elseif (isset($parsedCurrentUrl['path']) && strlen($parsedCurrentUrl['path']) && substr($parsedCurrentUrl['path'], -1) == '/') {
					return $parsedCurrentUrl['scheme'].'://'.$parsedCurrentUrl['host'].$parsedCurrentUrl['path'].$url;
				} elseif (isset($parsedCurrentUrl['path'])) {
					return $parsedCurrentUrl['scheme'].'://'.$parsedCurrentUrl['host'].substr($parsedCurrentUrl['path'], 0, strrpos($parsedCurrentUrl['path'], '/')).'/'.$url;
				} else {
					return $parsedCurrentUrl['scheme'].'://'.$parsedCurrentUrl['host'].'/'.$url;
				}
			}
		}

		$parsed_domain = parse_url($domain);

		if (strpos($url, '//') === 0) {
			$absolute = 'http://'.substr($url, 2);
		} elseif (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
			$absolute = $url;
		} elseif (strpos($url, 'www.') === 0) {
			$absolute = 'http://'.$url;
		} elseif (strpos($url, '/') === 0 && isset($parsed_domain['scheme']) && isset($parsed_domain['host'])) {
			$absolute = $parsed_domain['scheme'].'://'.$parsed_domain['host'].$url;
		} elseif (strrpos($domain, '/') != strlen($domain) - 1) {
			$absolute = $domain.'/'.$url;
		} else {
			$absolute = $domain.$url;
		}

		return $absolute;
	}

	/**
	 * Checks if an URL is from a provided domain name.
	 */
	public function isFromDomain(string $url, string $domain): bool
	{
		return strpos($url, $domain) !== false;
	}

	/**
	 * Extract the domain name from an URL.
	 */
	public function domain(string $url): string
	{
		return substr($url, 0, (strpos($url, '/', 8 <= strlen($url) ? 8 : strlen($url)) ?: strlen($url)));
	}

	/**
	 * Extracts the root domain name from an URL.
	 */
	public function rootDomain(string $url): ?string
	{
		$url_data = parse_url($url);

		if (!$url_data || !isset($url_data['host'])) {
			return null;
		}

		return str_replace('www.', '', $url_data['host']);
	}

	/**
	 * Suggests the standard sitemap URL for the provided website URL.
	 */
	public function guessSitemap(string $url): string
	{
		return $this->rootUrl($url).'/sitemap.xml';
	}

	/**
	 * Extracts the page URL from an URL.
	 */
	public function slug(string $url): string
	{
		if (substr($url, 0, 1) == '/') {
			return $url;
		}

		if (preg_match('#^((https?:)?//).+#', $url)) {
			$url = preg_replace('#^((https?:)?//)#', '', $url);
		}

		if (strpos($url, '/') !== false) {
			return substr($url, strpos($url, '/'));
		}

		return $url;
	}

	/**
	 * Returns the message for an HTTP code.
	 */
	public function httpCodeMessage(int $code): string
	{
		return $this->config->get('http_codes', (string) $code) ?: 'Error';
	}
}
