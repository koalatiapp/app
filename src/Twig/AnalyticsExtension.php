<?php

namespace App\Twig;

use App\Util\Analytics\UrlRedactor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnalyticsExtension extends AbstractExtension
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private UrlRedactor $urlRedactor,
	) {
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('getAnalyticsUrl', [$this->urlRedactor, 'getAnalyticsUrl']),
		];
	}
}
