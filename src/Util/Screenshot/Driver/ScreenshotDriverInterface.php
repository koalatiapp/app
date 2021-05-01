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
	 * @param int|null $renderWidth    The width, in pixels, of the image to generate. The aspect ratio will be preserved. In case of a `null` value, the original width should be used.
	 * @param int      $renderScale    The scale factor to use when capturing the screenshot. A scale factor of 2 will produce a high definition screenshot suited to be displayed on retina devices.
	 *
	 * @return string File contents of the resulting image
	 */
	public function screenshot(string $url, int $viewportWidth, int $viewportHeight, ?int $renderWidth = null, int $renderScale = 1): string;

	public function allowCache(): static;

	public function disallowCache(): static;

	public function enableFullpageMode(): static;

	public function disableFullpageMode(): static;
}
