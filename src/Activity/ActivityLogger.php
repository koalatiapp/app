<?php

namespace App\Activity;

class ActivityLogger
{
	/**
	 * @var array<string,EntityActivityLoggerInterface<object>>
	 */
	private array $entityLoggers = [];

	/**
	 * @param iterable<EntityActivityLoggerInterface<object>> $entityLoggers
	 */
	public function __construct(
		iterable $entityLoggers,
	) {
		foreach ($entityLoggers as $entityLogger) {
			$this->entityLoggers[$entityLogger->getEntityClass()] = $entityLogger;
		}
	}

	public function postRemove(object &$data): void
	{
		if (isset($this->entityLoggers[$data::class])) {
			$this->entityLoggers[$data::class]->postRemove($data);
		}
	}

	/** @param array<string,mixed>|null $originalData */
	public function postPersist(object &$data, ?array $originalData): void
	{
		if (isset($this->entityLoggers[$data::class])) {
			$this->entityLoggers[$data::class]->postPersist($data, $originalData);
		}
	}
}
