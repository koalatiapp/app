<?php

namespace App\ApiClient\Exception;

final class ToolsRequestFailedException extends \Exception
{
	private const DEFAULT_MESSAGE = 'The processing request sent to the Tools Service API has received a bad response.
									It is likely that the desired requests have not been added to the queue.';

	public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message ?: self::DEFAULT_MESSAGE, $code, $previous);
	}
}
