<?php

namespace App\Util\Screenshot\Driver;

interface ScreenshotDriverInterface
{
	/**
	 * Generates a screenshot for a given URL based on the options that are provided.
	 *
	 * @param string   $url            URL of the website or page to screenshot
	 * @param int      $viewportWidth  width of the viewport, in pixels
	 * @param int      $viewportHeight height of the viewport, in pixels
	 * @param bool     $fullpage       set this parameter to true to capture the entire page of the target website
	 * @param int|null $renderWidth    The width, in pixels, of the image to generate. The aspect ratio will be preserved. In case of a `null` value, the original width should be used.
	 * @param int      $renderScale    The scale factor to use when capturing the screenshot. A scale factor of 2 will produce a high definition screenshot suited to be displayed on retina devices.
	 * @param bool     $fresh          whether to request a fresh screenshot or to allow a cached version if available
	 *
	 * @return string File contents of the resulting image
	 */
	public function screenshot(string $url, int $viewportWidth, int $viewportHeight, bool $fullpage = false, ?int $renderWidth = null, int $renderScale = 1, bool $fresh = false): string;
}
