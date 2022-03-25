<?php

namespace App\Tests\Backend\Functional;

/**
 * Tests the features of the privacy proxy.
 */
class ProxyTest extends AbstractAppTestCase
{
	public function setup(): void
	{
		parent::setup();

		$this->client->followRedirects(false);
	}

	/**
	 * @dataProvider validImageUrlProvider
	 */
	public function testValidImages(string $imageUrl)
	{
		$urlEncodedUrl = urlencode($imageUrl);

		$this->loadUser(static::USER_TEST);
		$this->client->request("GET", "/image-proxy?url=$urlEncodedUrl");

		$this->assertResponseIsSuccessful();
		$this->assertResponseStatusCodeSame(200, "Responds with HTTP 200 when valid images are provided.");
	}

	public function testNonExistingImage()
	{
		$urlEncodedUrl = urlencode("http://caddy/test/media/404.jpg");

		$this->loadUser(static::USER_TEST);
		$this->client->request("GET", "/image-proxy?url=$urlEncodedUrl");

		$this->assertResponseStatusCodeSame(404, "Responds with HTTP 404 when a non-existing URL is provided.");
	}

	public function testNonImageUrl()
	{
		$urlEncodedUrl = urlencode("http://caddy/");

		$this->loadUser(static::USER_TEST);
		$this->client->request("GET", "/image-proxy?url=$urlEncodedUrl");

		$this->assertResponseStatusCodeSame(400, "Responds with HTTP 400 when a non-image URL is provided.");
	}

	public function testLoggedOutAccess()
	{
		$urlEncodedUrl = urlencode("http://caddy/test/media/placeholder.jpg");

		$this->client->request("GET", "/image-proxy?url=$urlEncodedUrl");

		$this->assertResponseStatusCodeSame(302, "Responds with HTTP 302 when not logged in");
	}

	public function validImageUrlProvider()
	{
		yield ["http://caddy/test/media/placeholder.jpg"];
		yield ["http://caddy/test/media/placeholder.png"];
		yield ["http://caddy/test/media/placeholder.gif"];
		yield ["http://caddy/test/media/placeholder.webp"];
		yield ["http://caddy/test/media/placeholder.svg"];
	}
}
