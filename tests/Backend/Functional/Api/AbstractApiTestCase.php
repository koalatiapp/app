<?php

namespace App\Tests\Backend\Functional\Api;

use App\Tests\Backend\Functional\AbstractAppTestCase;

/**
 * Abstract class providing useful methods for testing the API (ex.: authentication).
 */
abstract class AbstractApiTestCase extends AbstractAppTestCase
{
	/**
	 * Makes an authentication call to the API for the requested user
	 * and provides the bearer and refresh tokens.
	 *
	 * @param string $userKey (see class constants)
	 */
	protected function authenticate(string $userKey): ApiResponse
	{
		static $cache = [];

		if (!isset($cache[$userKey])) {
			$credentials = $this->getUserCredentials($userKey);
			$response = $this->apiRequest("/api/auth", "POST", $credentials);
			$cache[$userKey] = $response;
		}

		return $cache[$userKey];
	}

	/**
	 * Makes a request to the API and returns the response.
	 * To authenticate automatically for the request, provide a `$userKey`.
	 *
	 * @param string $url Ex.: `/api/organizations`
	 * @param string $method `GET`, `POST`, `PATCH`, `DELETE`, etc.
	 * @param null|string $user Which user to authenticate as for the API call (see `USER_` class constants)
	 */
	protected function apiRequest(string $url, string $method = "GET", ?array $payload = null, ?string $user = null): ApiResponse
	{
		$ch = curl_init("http://caddy/" . ltrim($url, "/"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$headers = [
			"Accept: application/ld+json",
			"Content-type: application/json",
		];

		// Authenticate user automatically if a user key is provided
		if ($user) {
			$authenticationResponse = $this->authenticate($user);
			$headers[] = "Authorization: Bearer {$authenticationResponse->getContent()['token']}";
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		switch (strtoupper($method)) {
			case "POST":
				curl_setopt($ch, CURLOPT_POST, 1);
				break;

			case "GET":
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
				break;

			default:
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
				break;
		}

		if ($payload) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
		}

		$responseContent = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (curl_error($ch)) {
			throw new \Exception(curl_error($ch));
		}

		curl_close($ch);

		return new ApiResponse($httpCode, $responseContent);
	}
}

class ApiResponse
{
	public function __construct(
		private int $statusCode,
		private ?string $rawContent,
	) {
	}

	/**
	 * @return array<mixed,mixed>
	 */
	public function getContent(): array
	{
		return $this->rawContent ? json_decode($this->rawContent, true) : [];
	}

	public function getRawContent(): ?string
	{
		return $this->rawContent;
	}

    public function getStatusCode(): int
	{
		return $this->statusCode;
	}

    public function getReasonPhrase(): string
	{
		return match($this->statusCode) {
			100 => "Continue",
			101 => "Switching Protocols",
			102 => "Processing",
			103 => "Early Hints",
			200 => "OK",
			201 => "Created",
			202 => "Accepted",
			203 => "Non-Authoritative Information",
			204 => "No Content",
			205 => "Reset Content",
			206 => "Partial Content",
			207 => "Multi-Status",
			208 => "Already Reported",
			209 => "225	Unassigned	",
			226 => "IM Used",
			300 => "Multiple Choices",
			301 => "Moved Permanently",
			302 => "Found",
			303 => "See Other",
			304 => "Not Modified",
			305 => "Use Proxy",
			307 => "Temporary Redirect",
			308 => "Permanent Redirect",
			400 => "Bad Request",
			401 => "Unauthorized",
			402 => "Payment Required",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			406 => "Not Acceptable",
			407 => "Proxy Authentication Required",
			408 => "Request Timeout",
			409 => "Conflict",
			410 => "Gone",
			411 => "Length Required",
			412 => "Precondition Failed",
			413 => "Content Too Large",
			414 => "URI Too Long",
			415 => "Unsupported Media Type",
			416 => "Range Not Satisfiable",
			417 => "Expectation Failed",
			421 => "Misdirected Request",
			422 => "Unprocessable Content",
			423 => "Locked",
			424 => "Failed Dependency",
			425 => "Too Early",
			426 => "Upgrade Required",
			427 => "Unassigned	",
			428 => "Precondition Required",
			429 => "Too Many Requests",
			430 => "Unassigned	",
			431 => "Request Header Fields Too Large",
			451 => "Unavailable For Legal Reasons",
			500 => "Internal Server Error",
			501 => "Not Implemented",
			502 => "Bad Gateway",
			503 => "Service Unavailable",
			504 => "Gateway Timeout",
			505 => "HTTP Version Not Supported",
			506 => "Variant Also Negotiates",
			507 => "Insufficient Storage",
			508 => "Loop Detected",
			509 => "Unassigned",
			510 => "Not Extended (OBSOLETED)",
			511 => "Network Authentication Required",
			default => "",
		};
	}
}
