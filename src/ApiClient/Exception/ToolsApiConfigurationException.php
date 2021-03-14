<?php

namespace App\ApiClient\Exception;

final class ToolsApiConfigurationException extends \Exception
{
	private const DEFAULT_MESSAGE = 'The "TOOLS_API_URL" and "TOOLS_API_BEARER_TOKEN" environment variables must be defined to use the tools API client.';

	public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message ?: self::DEFAULT_MESSAGE, $code, $previous);
	}
}
