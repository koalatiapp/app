<?php

namespace App\Util\Screenshot\Driver;

class ApiFlash extends AbstractScreenshotDriver implements ScreenshotDriverInterface
{
	public function __construct(private readonly string $accessKey)
	{
	}

	public function screenshot(string $url, int $viewportWidth, int $viewportHeight, ?int $renderWidth = null, int $renderScale = 1): string
	{
		$params = http_build_query([
			'access_key' => $this->accessKey,
			'url' => $url,
			'fresh' => !$this->allowCache,
			'full_page' => $this->fullpageMode,
			'width' => $viewportWidth,
			'height' => $viewportHeight,
			'scale_factor' => $renderScale,
			'format' => 'jpeg',
			'no_ads' => true,
			'no_tracking' => true,
			'no_cookie_banners' => true,
			'thumbnail_width' => $renderWidth,
		]);

		return file_get_contents('https://api.apiflash.com/v1/urltoimage?'.$params);
	}
}
