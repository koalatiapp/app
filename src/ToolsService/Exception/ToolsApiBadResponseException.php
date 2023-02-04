<?php

namespace App\ToolsService\Exception;

final class ToolsApiBadResponseException extends \Exception
{
	private const DEFAULT_MESSAGE = 'A request sent to the Tools Service API has failed with the following error: ';

	public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct(self::DEFAULT_MESSAGE.$message, $code, $previous);
	}
}
