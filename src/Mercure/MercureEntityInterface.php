<?php

namespace App\Mercure;

interface MercureEntityInterface
{
	public function getId(): int|string|null;

	public function getMercureSerializationGroup(): string;
}
