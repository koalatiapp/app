<?php

namespace App\Util\Screenshot;

use App\Util\Screenshot\Driver\ScreenshotDriverInterface;
use App\Util\Url;

class ScreenshotGenerator implements ScreenshotGeneratorInterface
{
	/**
	 * Screenshot generation driver.
	 */
	private ScreenshotDriverInterface $driver;

	private Url $urlHelper;

	/**
	 * Whether screenshots should be generated in fullpage mode or not.
	 */
	private bool $fullpageMode = false;

	/**
	 * The width of the images to generate, in pixel.
	 * If `null`, the viewport's size is used.
	 */
	private ?int $renderWidth = null;

	/**
	 * The viewport/resolution scale.
	 * Default value is `1`.
	 */
	private int $renderScale = 1;

	public function __construct(ScreenshotDriverInterface $driver, Url $urlHelper)
	{
		$this->driver = $driver;
		$this->urlHelper = $urlHelper;
	}

	public function setFullpage(bool $fullpage): self
	{
		$this->fullpageMode = $fullpage;

		return $this;
	}

	public function setRenderWidth(?int $width = null): self
	{
		$this->renderWidth = $width;

		return $this;
	}

	public function setRenderScale(int $scale): self
	{
		$this->renderScale = $scale;

		return $this;
	}

	public function renderMobile(string $url, bool $fresh = false): string
	{
		return $this->renderCustom($url, 375, 667, $fresh);
	}

	public function renderDesktop(string $url, bool $fresh = false): string
	{
		return $this->renderCustom($url, 1920, 1080, $fresh);
	}

	public function renderCustom(string $url, int $viewportWidth, int $viewportHeight, bool $fresh = false): string
	{
		$standardizedUrl = $this->urlHelper->standardize($url);

		return $this->driver->screenshot(
			url: $standardizedUrl,
			viewportWidth: $viewportWidth,
			viewportHeight: $viewportHeight,
			renderWidth: $this->renderWidth,
			renderScale: $this->renderScale,
			fullpage: $this->fullpageMode,
			fresh: $fresh,
		);
	}
}
