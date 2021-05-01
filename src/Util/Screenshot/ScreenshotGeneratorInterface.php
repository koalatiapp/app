<?php

namespace App\Util\Screenshot;

use App\Util\Screenshot\Driver\ScreenshotDriverInterface;
use App\Util\Url;

interface ScreenshotGeneratorInterface
{
	public function __construct(ScreenshotDriverInterface $driver, Url $urlHelper);

	public function allowCache(): self;

	public function disallowCache(): self;

	public function enableFullpageMode(): self;

	public function disableFullpageMode(): self;

	/**
	 * Defines the width of the image that is returned.
	 * If the width is smaller than that of the viewport, the
	 * image will be scaled down.
	 * If `null` is provided, the original size will be kept.
	 */
	public function setRenderWidth(?int $width = null): self;

	/**
	 * Defines the scale factor to use for the screenshot generation.
	 * A value of `2` will generate images suitable for retina displays.
	 * The default value is `1`.
	 */
	public function setRenderScale(int $scale): self;

	/**
	 * Takes a screenshot using a mobile resolution and
	 * returns the resulting image content.
	 */
	public function renderMobile(string $url): string;

	/**
	 * Takes a screenshot using a desktop resolution and
	 * returns the resulting image content.
	 */
	public function renderDesktop(string $url): string;

	/**
	 * Takes a screenshot using the provided resolution and
	 * returns the resulting image content.
	 */
	public function renderCustom(string $url, int $viewportWidth, int $viewportHeight): string;
}
