<?php

namespace App\Util\Screenshot;

interface ScreenshotInterface
{
	/**
	 * @param string|null $driver The driver class to use for the screenshot generation. If `null`, a driver will automatically be selected.
	 */
	public function __construct(?string $driver = null);

	/**
	 * Toggles the fullpage screenshot mode.
	 */
	public function setFullpage(bool $fullpage): self;

	/**
	 * Defines the width of the image that is returned.
	 * If the width is smaller than that of the viewport, the
	 * image will be scaled down.
	 * If `null` is provided, the original size will be kept.
	 */
	public function setRenderWidth(?int $width = null): self;

	/**
	 * Takes a screenshot using a mobile resolution and
	 * returns the resulting image content.
	 *
	 * @param bool $fresh whether to force a refresh or allow cached images
	 */
	public function mobile(bool $fresh = false): string;

	/**
	 * Takes a screenshot using a custom resolution and
	 * returns the resulting image content.
	 *
	 * @param bool $fresh whether to force a refresh or allow cached images
	 */
	public function desktop(bool $fresh = false): string;

	/**
	 * Takes a screenshot using the provided resolution and
	 * returns the resulting image content.
	 */
	public function custom(int $viewportWidth, int $viewportHeight, bool $fresh = false): string;
}
