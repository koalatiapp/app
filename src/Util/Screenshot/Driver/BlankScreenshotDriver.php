<?php

namespace App\Util\Screenshot\Driver;

/**
 * This generates a blank image that can be used to avoid
 * hitting real screenshotting APIs when testing or using
 * a local developement environment that doesn't require
 * real project thumbnails and screenshots.
 */
class BlankScreenshotDriver extends AbstractScreenshotDriver implements ScreenshotDriverInterface
{
	/**
	 * The BlankScreenshotDriver's screenshot always returns a 1x1 gray image.
 	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function screenshot(string $url, int $viewportWidth, int $viewportHeight, ?int $renderWidth = null, int $renderScale = 1): string
	{
		return base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mM88R8AApUByU2MEcEAAAAASUVORK5CYII=");
	}
}
