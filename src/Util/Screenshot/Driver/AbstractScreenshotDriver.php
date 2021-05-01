<?php

namespace App\Util\Screenshot\Driver;

abstract class AbstractScreenshotDriver
{
	/**
	 * Set this parameter to true to allow cached screenshots (if the driver supports it).
	 */
	protected bool $allowCache = false;

	/**
	 * Set this parameter to true to capture the entire page of the target website.
	 */
	protected bool $fullpageMode = false;

	public function allowCache(): static
	{
		$this->allowCache = true;

		return $this;
	}

	public function disallowCache(): static
	{
		$this->allowCache = false;

		return $this;
	}

	public function enableFullpageMode(): static
	{
		$this->fullpageMode = true;

		return $this;
	}

	public function disableFullpageMode(): static
	{
		$this->fullpageMode = false;

		return $this;
	}
}
