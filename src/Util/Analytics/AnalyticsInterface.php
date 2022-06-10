<?php

namespace App\Util\Analytics;

interface AnalyticsInterface
{
	/**
	 * Submits an event to an analytics platform or service.
	 *
	 * @param string              $name  name of the event to track
	 * @param array<string,mixed> $props additional data for the event
	 */
	public function trackEvent(string $name, array $props = []): void;
}
