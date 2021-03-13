<?php

namespace App\Tests\Util;

use App\Util\Config;
use App\Util\Url;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @covers \Url::
 */
class UrlTest extends WebTestCase
{
	private Url $urlHelper;
	private Config $configHelper;
	private string $stubsDir;

	public function setup()
	{
		self::bootKernel();
		$this->urlHelper = self::$container->get('App\\Util\\Url');
		$this->configHelper = self::$container->get('App\\Util\\Config');
		$this->stubsDir = self::$container->getParameter('test_stub_dir');
	}

	/**
	 * Checks if a URL exists and returns a valid HTTP code (2xx).
	 *
	 * @covers \Url::exists
	 */
	public function testExists()
	{
		$mockHttpClient = new MockHttpClient([
			new MockResponse(),
			new MockResponse('', ['http_code' => 404]),
		]);
		$urlHelper = new Url($this->configHelper, $mockHttpClient);
		$this->assertTrue($urlHelper->exists('https://domain.com'), 'Existing URL exists');
		$this->assertFalse($urlHelper->exists('https://bad-domain.com'), 'Non-existing URL does not exist');
	}

	/**
	 * Checks if a URL is an XML document.
	 *
	 * @covers \Url::isXml
	 */
	public function testIsXml()
	{
		$mockHttpClient = new MockHttpClient([
			new MockResponse(file_get_contents($this->stubsDir.'/sitemap.xml'), ['response_headers' => ['Content-Type' => 'application/xml']]),
			new MockResponse(file_get_contents($this->stubsDir.'/webpage.html'), ['response_headers' => ['Content-Type' => 'text/html']]),
		]);
		$urlHelper = new Url($this->configHelper, $mockHttpClient);
		$this->assertTrue($urlHelper->isXML('https://www.domain.com/sitemap.xml'), 'XML page/document is XML');
		$this->assertFalse($urlHelper->isXML('https://www.domain.com'), 'HTML page/document is not XML');
	}

	/**
	 * Checks if a URL is an HTML document.
	 *
	 * @covers \Url::isHtml
	 */
	public function testIsHtml()
	{
		$mockHttpClient = new MockHttpClient([
			new MockResponse(file_get_contents($this->stubsDir.'/webpage.html'), ['response_headers' => ['Content-Type' => 'text/html']]),
			new MockResponse(file_get_contents($this->stubsDir.'/image.png'), ['response_headers' => ['Content-Type' => 'image/png']]),
		]);
		$urlHelper = new Url($this->configHelper, $mockHttpClient);
		$this->assertTrue($urlHelper->isHTML('https://www.domain.com'), 'HTML page/document is HTML');
		$this->assertFalse($urlHelper->isHTML('https://www.domain.com/sitemap.xml'), 'Non-HTML page/document is not HTML');
	}

	/**
	 * Standardizes an URL, ensuring the "https(s)://" protocol is defined.
	 *
	 * @param bool $forceHttps when $forceHttps is set to true, the URL will be changed to use HTTPS
	 * @covers \Url::standardize
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
	 *
	 * @covers \Url::rootUrl
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
	 *
	 * @covers \Url::domain
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
	 *
	 * @covers \Url::guessSitemap
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
	 *
	 * @covers \Url::httpCodeMessage
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
