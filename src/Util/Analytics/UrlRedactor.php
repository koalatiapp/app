<?php

namespace App\Util\Analytics;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class UrlRedactor
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private RouterInterface $router,
		private RequestStack $requestStack
	) {
	}

	/**
	 * Return the current page's URL with redacted IDs.
	 */
	public function getAnalyticsUrl(): string
	{
		$currentRequest = $this->requestStack->getCurrentRequest();
		$route = $currentRequest->get('_route');
		$params = $currentRequest->get('_route_params') ?: [];
		$redactedParams = [];

		if (!$route) {
			return $this->requestStack->getMainRequest()->getUri();
		}

		foreach ($params as $key => $value) {
			if ($key == 'id' || preg_match('~.+_?[iI]d$~', $key)) {
				$value = '_'.strtoupper($key).'_';
			}

			$redactedParams[$key] = $value;
		}

		return $this->router->generate($route, $redactedParams, RouterInterface::ABSOLUTE_URL);
	}
}
