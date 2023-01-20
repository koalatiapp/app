<?php

namespace App\Api\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: 'kernel.request', method: 'onKernelRequest', priority: PHP_INT_MAX)]
#[AsEventListener(event: 'kernel.response', method: 'onKernelResponse')]
final class RateLimitingKernelListener
{
	private ?LimiterInterface $rateLimiter = null;
	private ?RateLimit $limit = null;

	public function __construct(
		private readonly RateLimiterFactory $anonymousApiLimiter,
		private readonly RateLimiterFactory $authenticatedApiLimiter,
	) {
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		$this->initializeRateLimiter($event->getRequest());

		if (!$this->rateLimiter) {
			return;
		}

		$this->limit = $this->rateLimiter->consume(1);

		if ($this->limit->isAccepted() === false) {
			throw new TooManyRequestsHttpException(retryAfter: $this->limit->getRetryAfter()->getTimestamp(), headers: $this->getRateLimitingHeaders());
		}
	}

	public function onKernelResponse(ResponseEvent $event): void
	{
		if (!$this->limit) {
			return;
		}

		$event->getResponse()->headers->add($this->getRateLimitingHeaders());
	}

	/**
	 * @return array<string,int>
	 */
	private function getRateLimitingHeaders(): array
	{
		if (!$this->limit) {
			return [];
		}

		return [
			'X-RateLimit-Remaining' => $this->limit->getRemainingTokens(),
			'X-RateLimit-Retry-After' => $this->limit->getRetryAfter()->getTimestamp(),
			'X-RateLimit-Limit' => $this->limit->getLimit(),
		];
	}

	/**
	 * Defines and creates the adequate rate limiter based on the user's target
	 * endpoint and request headers.
	 *
	 * The resulting rate limiter is stored in `$this->rateLimiter`.
	 */
	private function initializeRateLimiter(Request $request): void
	{
		$endpointPath = $request->getPathInfo();

		// Rate limiting only applies for the API
		if (!str_starts_with($endpointPath, "/api/")) {
			return;
		}

		// Do not apply rate limiting to API docs
		if (str_starts_with($endpointPath, "/api/docs")) {
			return;
		}

		$authEndpoints = [
			"/api/auth",
			"/api/token/refresh",
		];
		$authorizationHeaders = (array) $request->headers->get("authorization");
		$hasBearerToken = (bool) array_filter(
			array_map(
				fn (string $header) => str_starts_with(strtolower($header), "bearer"),
				$authorizationHeaders
			)
		);
		$rateLimiterFactory = $this->authenticatedApiLimiter;

		// Use the anonymous rate limiter if user is not authenticated or trying to authenticate
		if (in_array($endpointPath, $authEndpoints) || !$hasBearerToken) {
			$rateLimiterFactory = $this->anonymousApiLimiter;
		}

		$this->rateLimiter = $rateLimiterFactory->create($request->getClientIp());
	}
}
