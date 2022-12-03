<?php

namespace App\Message;

class TestingResultRequest
{
	/**
	 * @param array<string,mixed> $payload
	 */
	public function __construct(
		private readonly array $payload,
	) {
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}
}
