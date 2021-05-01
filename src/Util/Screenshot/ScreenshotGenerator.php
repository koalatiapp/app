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

	public function allowCache(): self
	{
		$this->driver->allowCache();

		return $this;
	}

	public function disallowCache(): self
	{
		$this->driver->disallowCache();

		return $this;
	}

	public function enableFullpageMode(): self
	{
		$this->driver->enableFullpageMode();

		return $this;
	}

	public function disableFullpageMode(): self
	{
		$this->driver->disableFullpageMode();

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

	public function renderMobile(string $url): string
	{
		return $this->renderCustom($url, 375, 667);
	}

	public function renderDesktop(string $url): string
	{
		return $this->renderCustom($url, 1920, 1080);
	}

	public function renderCustom(string $url, int $viewportWidth, int $viewportHeight): string
	{
		return $this->driver->screenshot(
			url: $this->urlHelper->standardize($url),
			viewportWidth: $viewportWidth,
			viewportHeight: $viewportHeight,
			renderWidth: $this->renderWidth,
			renderScale: $this->renderScale,
		);
	}
}
