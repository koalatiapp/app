<?php

namespace App\Util\Screenshot\Driver;

class ApiFlash implements ScreenshotDriverInterface
{
	private string $accessKey;

	public function __construct(string $accessKey)
	{
		$this->accessKey = $accessKey;
	}

	public function screenshot(string $url, int $viewportWidth, int $viewportHeight, bool $fullpage = false, ?int $renderWidth = null, int $renderScale = 1, bool $fresh = false): string
	{
		$params = http_build_query([
			'access_key' => $this->accessKey,
			'url' => $url,
			'fresh' => $fresh,
			'full_page' => $fullpage,
			'width' => $viewportWidth,
			'height' => $viewportHeight,
			'scale_factor' => $renderScale,
			'format' => 'jpeg',
			'no_ads' => true,
			'no_tracking' => true,
			'no_cookie_banners' => true,
			'thumbnail_width' => $renderWidth,
		]);

		$response = file_get_contents('https://api.apiflash.com/v1/urltoimage?'.$params);

		return $response;
	}
}
