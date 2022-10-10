<?php

namespace App\Tests\Backend\Unit\Util\Meta\Driver;

use App\Util\Meta\Driver\BasicHttp;
use PHPUnit\Framework\TestCase;

class BasicHttpTest extends TestCase
{
	public function testGetMetasInPerfectConditions()
	{
		$mockBasicHttp = $this->createPartialMock(BasicHttp::class, ['fetchHtml']);
		$mockBasicHttp->method('fetchHtml')->willReturn('
			<html>
				<head>
					<title>Koalati</title>
					<meta name="description" content="A website testing platform for web agencies and developers">
					<meta property="og:image" content="https://sample.koalati.com/media/sample-logo.svg">
					<meta property="og:url" content="https://sample.koalati.com">
				</head>
			</html>
		');
		$metas = $mockBasicHttp->getMetas("https://sample.koalati.com/");

		$this->assertSame("https://sample.koalati.com", $metas->url);
		$this->assertSame("Koalati", $metas->title);
		$this->assertSame("A website testing platform for web agencies and developers", $metas->description);
		$this->assertSame("https://sample.koalati.com/media/sample-logo.svg", $metas->imageUrl);
		$this->assertSame(null, $metas->siteName);
	}

	public function testGetMetasInImperfectScenario()
	{
		$mockBasicHttp = $this->createPartialMock(BasicHttp::class, ['fetchHtml']);
		$mockBasicHttp->method('fetchHtml')->willReturn('
			<html>
				<head>
					<title>Koalati</title>
					<meta name="description" content="A website testing platform for web agencies and developers">
					<link rel="canonical" href="https://sample.koalati.com">
				</head>
			</html>
		');
		$metas = $mockBasicHttp->getMetas("https://sample.koalati.com/");

		$this->assertSame("https://sample.koalati.com", $metas->url);
		$this->assertSame("Koalati", $metas->title);
		$this->assertSame("A website testing platform for web agencies and developers", $metas->description);
		$this->assertSame("https://via.placeholder.com/600x315/DAE1FB/102984.png?text=sample.koalati.com", $metas->imageUrl);
		$this->assertSame(null, $metas->siteName);
	}

	public function testGetMetasInWorstScenario()
	{
		$mockBasicHttp = $this->createPartialMock(BasicHttp::class, ['fetchHtml']);
		$mockBasicHttp->method('fetchHtml')->willReturn('');
		$metas = $mockBasicHttp->getMetas("https://sample.koalati.com/");

		$this->assertSame("https://sample.koalati.com/", $metas->url);
		$this->assertSame(null, $metas->title);
		$this->assertSame(null, $metas->description);
		$this->assertSame("https://via.placeholder.com/600x315/DAE1FB/102984.png?text=sample.koalati.com", $metas->imageUrl);
		$this->assertSame(null, $metas->siteName);
	}

	public function testGetMetasWithRelativeRootUrls()
	{
		$mockBasicHttp = $this->createPartialMock(BasicHttp::class, ['fetchHtml']);
		$mockBasicHttp->method('fetchHtml')->willReturn('
			<html>
				<head>
					<title>Koalati</title>
					<meta name="description" content="A website testing platform for web agencies and developers">
					<meta property="og:image" content="/media/sample-logo.svg">
					<meta property="og:url" content="/">
				</head>
			</html>
		');
		$metas = $mockBasicHttp->getMetas("https://sample.koalati.com/");

		$this->assertSame("https://sample.koalati.com/", $metas->url);
		$this->assertSame("Koalati", $metas->title);
		$this->assertSame("A website testing platform for web agencies and developers", $metas->description);
		$this->assertSame("https://sample.koalati.com/media/sample-logo.svg", $metas->imageUrl);
		$this->assertSame(null, $metas->siteName);
	}

	public function testGetMetasWithRelativeUrls()
	{
		$mockBasicHttp = $this->createPartialMock(BasicHttp::class, ['fetchHtml']);
		$mockBasicHttp->method('fetchHtml')->willReturn('
			<html>
				<head>
					<title>Koalati</title>
					<meta name="description" content="A website testing platform for web agencies and developers">
					<meta property="og:image" content="media/sample-logo.svg">
				</head>
			</html>
		');
		$metas = $mockBasicHttp->getMetas("https://sample.koalati.com/");

		$this->assertSame("https://sample.koalati.com/", $metas->url);
		$this->assertSame("Koalati", $metas->title);
		$this->assertSame("A website testing platform for web agencies and developers", $metas->description);
		$this->assertSame("https://sample.koalati.com/media/sample-logo.svg", $metas->imageUrl);
		$this->assertSame(null, $metas->siteName);
	}
}
