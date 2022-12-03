<?php

namespace App\Util\Analytics\Driver;

use App\Util\Analytics\AnalyticsInterface;
use App\Util\Analytics\UrlRedactor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Plausible implements AnalyticsInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly UrlRedactor $urlRedactor,
		private readonly HttpClientInterface $httpClient,
		private readonly RequestStack $requestStack,
		private readonly LoggerInterface $logger,
	) {
	}

	public function trackEvent(string $name, array $props = []): void
	{
		$request = $this->requestStack->getMainRequest();

		try {
			$this->httpClient->request(
				"POST",
				"https://plausible.io/api/event",
				[
					"headers" => [
						"Content-Type" => "application/json",
						"User-Agent" => $request->headers->get("user-agent"),
						"X-Forwarded-For" => $request->getClientIp(),
					],
					"body" => json_encode([
						"name" => $name,
						"url" => $this->urlRedactor->getAnalyticsUrl(),
						"domain" => "app.koalati.com",
						"props" => $props,
					], JSON_THROW_ON_ERROR),
				]
			);
		} catch (\Exception $exception) {
			$this->logger->error($exception->getMessage(), $exception->getTrace());
		}
	}
}
