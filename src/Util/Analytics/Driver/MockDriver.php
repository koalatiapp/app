<?php

namespace App\Util\Analytics\Driver;

use App\Util\Analytics\AnalyticsInterface;

class MockDriver implements AnalyticsInterface
{
	public function trackEvent(string $name, array $props = []): void
	{
		// Nothing to do: this is only used to prevent tracking on dev/test environments
	}
}
