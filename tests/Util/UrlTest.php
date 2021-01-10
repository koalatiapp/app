<?php

namespace App\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UrlTest extends WebTestCase
{
	/**
	 * @var \App\Util\Url;
	 */
	private $urlHelper;

	public function setup()
	{
		self::bootKernel();
		$this->urlHelper = self::$container->get('App\\Util\\Url');
	}

	/**
	 * Checks if a URL exists and returns a valid HTTP code (2xx).
	 */
	public function testExists()
	{
		$this->assertTrue($this->urlHelper->exists('https://google.com'), 'Existing URL exists');
		$this->assertFalse($this->urlHelper->exists(sprintf('http://randomdomain%s.com', rand(10, 10000))), 'Non-existing URL does not exist');
	}

	/**
	 * Checks if a URL is an XML document.
	 */
	public function testIsXML()
	{
		return $this->assertTrue($this->urlHelper->isXML('https://www.google.com/sitemap.xml'), 'XML page/document is XML');

		return $this->assertFalse($this->urlHelper->isXML('https://www.google.com'), 'HTML page/document is not XML');
	}

	/**
	 * Checks if a URL is an HTML document.
	 */
	public function testIsHTML()
	{
		return $this->assertTrue($this->urlHelper->isHTML('https://www.google.com'), 'HTML page/document is HTML');

		return $this->assertFalse($this->urlHelper->isHTML('https://www.google.com/sitemap.xml'), 'Non-HTML page/document is not HTML');
	}

	/**
	 * Standardizes an URL, ensuring the "https(s)://" protocol is defined.
	 *
	 * @param bool $forceHttps when $forceHttps is set to true, the URL will be changed to use HTTPS
	 */
	public function testStandardize()
	{
		$this->assertSame('http://domain.com', $this->urlHelper->standardize('domain.com'), 'Standardize root domain');
		$this->assertSame('http://sub.domain.com', $this->urlHelper->standardize('sub.domain.com'), 'Standardize subdomain');
		$this->assertSame('https://domain.com', $this->urlHelper->standardize('domain.com', true), 'Standardize root domain (HTTPS forced)');
		$this->assertSame('https://sub.domain.com', $this->urlHelper->standardize('sub.domain.com', true), 'Standardize subdomain (HTTPS forced)');
		$this->assertSame('https://domain.com', $this->urlHelper->standardize('https://domain.com'), 'Standardize already standard domain (HTTPS)');
		$this->assertSame('http://domain.com', $this->urlHelper->standardize('http://domain.com'), 'Standardize already standard domain (HTTP)');
		$this->assertSame('//domain.com', $this->urlHelper->standardize('//domain.com'), 'Standardize already standard domain (//)');
		$this->assertSame('//domain.com', $this->urlHelper->standardize('//domain.com', true), 'Standardize already standard domain (//) (HTTPS forced)');
		$this->assertSame('https://domain.com', $this->urlHelper->standardize('http://domain.com', true), 'Standardize already standard domain (//) (HTTP)');
		$this->assertSame('https://domain.com', $this->urlHelper->standardize('https://domain.com', true), 'Standardize already standard domain (//) (HTTPS forced, already HTTPS)');
	}

	/**
	 * Removes the query string, anchor and trailing slash from an URL.
	 */
	public function testRootUrl()
	{
		$this->assertSame('http://domain.com/my-page', $this->urlHelper->rootUrl('domain.com/my-page?foo=bar#anchor'), 'Root domain without protocol');
		$this->assertSame('http://domain.com/my-page', $this->urlHelper->rootUrl('http://domain.com/my-page?foo=bar#anchor'), 'Root domain with protocol (HTTP)');
		$this->assertSame('https://domain.com/my-page', $this->urlHelper->rootUrl('https://domain.com/my-page?foo=bar#anchor'), 'Root domain with protocol (HTTPS)');
		$this->assertSame('http://sub.domain.com/my-page', $this->urlHelper->rootUrl('sub.domain.com/my-page?foo=bar#anchor'), 'Subdomain domain without protocol');
		$this->assertSame('https://sub.domain.com/my-page', $this->urlHelper->rootUrl('https://sub.domain.com/my-page?foo=bar#anchor'), 'Subdomain with protocol (HTTP)');
		$this->assertSame('https://sub.domain.com/my-page', $this->urlHelper->rootUrl('https://sub.domain.com/my-page?foo=bar#anchor'), 'Subdomain with protocol (HTTPS)');
		$this->assertSame('https://domain.com', $this->urlHelper->rootUrl('https://domain.com/'), 'Domain with trailing slash');
		$this->assertSame('https://domain.com/my-page', $this->urlHelper->rootUrl('https://domain.com/my-page?foo=bar'), 'Domain with query string');
	}

	/**
	 * Extract the domain name from an URL.
	 */
	public function testDomain()
	{
		$this->assertSame('domain.com', $this->urlHelper->domain('domain.com/my-page?foo=bar#anchor'), 'Root domain without protocol');
		$this->assertSame('domain.com', $this->urlHelper->domain('http://domain.com/my-page?foo=bar#anchor'), 'Root domain with protocol (HTTP)');
		$this->assertSame('domain.com', $this->urlHelper->domain('https://domain.com/my-page?foo=bar#anchor'), 'Root domain with protocol (HTTPS)');
		$this->assertSame('sub.domain.com', $this->urlHelper->domain('sub.domain.com/my-page?foo=bar#anchor'), 'Subdomain domain without protocol');
		$this->assertSame('sub.domain.com', $this->urlHelper->domain('https://sub.domain.com/my-page?foo=bar#anchor'), 'Subdomain with protocol (HTTP)');
		$this->assertSame('sub.domain.com', $this->urlHelper->domain('https://sub.domain.com/my-page?foo=bar#anchor'), 'Subdomain with protocol (HTTPS)');
		$this->assertSame('domain.com', $this->urlHelper->domain('https://domain.com/'), 'Domain with trailing slash');
		$this->assertSame('domain.com', $this->urlHelper->domain('https://domain.com/my-page?foo=bar'), 'Domain with query string');
	}

	/**
	 * Suggests the standard sitemap URL for the provided website URL.
	 */
	public function testGuessSitemap()
	{
		$this->assertSame('http://domain.com/sitemap.xml', $this->urlHelper->guessSitemap('domain.com'), 'Root domain without protocol');
		$this->assertSame('http://domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('domain.com/dir?foo=bar#anchor'), 'Root domain without protocol, with directory & query');
		$this->assertSame('http://domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('http://domain.com/dir?foo=bar#anchor'), 'Root domain with protocol (HTTP)');
		$this->assertSame('https://domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('https://domain.com/dir?foo=bar#anchor'), 'Root domain with protocol (HTTPS)');
		$this->assertSame('http://sub.domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('sub.domain.com/dir?foo=bar#anchor'), 'Subdomain domain without protocol');
		$this->assertSame('https://sub.domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('https://sub.domain.com/dir?foo=bar#anchor'), 'Subdomain with protocol (HTTP)');
		$this->assertSame('https://sub.domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('https://sub.domain.com/dir?foo=bar#anchor'), 'Subdomain with protocol (HTTPS)');
		$this->assertSame('https://domain.com/sitemap.xml', $this->urlHelper->guessSitemap('https://domain.com/'), 'Domain with trailing slash');
		$this->assertSame('https://domain.com/dir/sitemap.xml', $this->urlHelper->guessSitemap('https://domain.com/dir?foo=bar'), 'Domain with query string');
	}

	/**
	 * Returns the message for an HTTP code.
	 */
	public function testHttpCodeMessage()
	{
		$this->assertSame('OK', $this->urlHelper->httpCodeMessage(200));
		$this->assertSame('Moved Permanently', $this->urlHelper->httpCodeMessage(301));
		$this->assertSame('Bad Request', $this->urlHelper->httpCodeMessage(400));
		$this->assertSame('I\'m a teapot', $this->urlHelper->httpCodeMessage(418));
		$this->assertSame('Internal Server Error', $this->urlHelper->httpCodeMessage(500));
	}
}
