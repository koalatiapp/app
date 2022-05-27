<?php

namespace App\ApiClient\Exception;

final class ServerlessClientNotConfiguredException extends \Exception
{
	private const DEFAULT_MESSAGE = 'The HTTP client for the serverless functions is not configured.';

	public function __construct(?string $message = null, int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message ?: self::DEFAULT_MESSAGE, $code, $previous);
	}
}
