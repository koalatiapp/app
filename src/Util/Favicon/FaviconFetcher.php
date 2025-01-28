<?php

namespace App\Util\Favicon;

use App\Util\Favicon\Driver\FaviconDriverInterface;

class FaviconFetcher implements FaviconFetcherInterface
{
	/**
	 * @param array<int,FaviconDriverInterface> $drivers
	 */
	public function __construct(
		private readonly array $drivers
	) {
	}

	public function fetch(string $url): string
	{
		foreach ($this->drivers as $driver) {
			try {
				return $driver->fetch($url);
			} catch (\Throwable) {
				// Oh well, no icon from this driver.
			}
		}

		return base64_decode("iVBORw0KGgoAAAANSUhEUgAAADQAAAA0CAMAAADypuvZAAAAMFBMVEUAAAD/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTr/KTp9OfapAAAAD3RSTlMAESIzRFVmd4iZ7t3Mu6q3AupnAAABcklEQVR4Ae3TBw7cQAiFYRiX6fvf/7aJjdGSNlFU0p8aWHzuyD+S/1HVUFjSlpZmB8o9COx3URvQiq4RW0BbwzJ0jYYjN83VApEdaYN2imgFXmvE9qATOOTKpdICdRgPekGXK3d/LtDeIBsaMMUClAVK5wW+gvIKyYTut9fkzgYcS5Qa3OjAzz8BXSI5HyQdKEm2F1BljWQ+KHU8U76HUjPkys0SSX6QaLnYOOUHo+mX7+v7VlRCdPPehkIx/b/PHWgvf4ZzAMxdnqF2T1cYAb14cvo6WA5DNhVRNHC4ac1PMvHjERVgJkkF6CLZWtk7sD2o6WeoWfNRzzmT9Iv6/lVD91xEW9zNuKq2v74vh0S02114QpsBG6rQdY3YrSw40g41orS8vXDmHtD7yesYI92t+qZXH6pARPnqdt2qHT2t1bMByYe0RxT/AHuaQmx9aCeioNruby20cSgiOWaDXlTkaS9SN4lIu6EY/WJp/rX8zwfIHCTWafSg+gAAAABJRU5ErkJggg==");
	}
}
