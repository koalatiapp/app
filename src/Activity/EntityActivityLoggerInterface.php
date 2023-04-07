<?php

namespace App\Activity;

/**
 * Base interface for all entity-based activity logging classes.
 *
 * @template T of object
 */
interface EntityActivityLoggerInterface
{
	public static function getEntityClass(): string;

	/**
	 * Hook after a resource has been removed in the database.
	 *
	 * @param T $data
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.data)
	 */
	public function postRemove(object &$data): void;

	/**
	 * Hook after a resource has been persisted in the database.
	 *
	 * @param T                   $data
	 * @param array<string,mixed> $originalData
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.data)
	 */
	public function postPersist(object &$data, ?array $originalData): void;
}
