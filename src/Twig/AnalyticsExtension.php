<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AnalyticsExtension extends AbstractExtension
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private RouterInterface $router,
		private RequestStack $requestStack
	) {
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('getAnalyticsUrl', [$this, 'getAnalyticsUrl']),
		];
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
			return $this->requestStack->getMasterRequest()->getUri();
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
