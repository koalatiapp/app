<?php

namespace App\Entity;

interface MercureEntityInterface
{
	/**
	 * Returns an array of Mercure topic templates, along with the
	 * name of the method that returns the scope (if applicable).
	 *
	 * The keys of the array must be `SCOPE_` constants defined by
	 * the `App\Mercure\TopicBuilder` class.
	 *
	 * The values of the array are topic URI templates.
	 *
	 * The topic templates can include the following placeholders:
	 * - `{scope}`: replaced by the scope hash, which is based on the result
	 * 				of the entity's `getMercureScope()` method.
	 * - `{id}`: replaced by the ID of the entity for specific topic URIs, or
	 * 			left as is to obtain a generic "catch-all" topic URI for the
	 * 			given scope.
	 *
	 * @return array<string,string>
	 */
	public static function getMercureTopics(): array;

	public function getId(): ?int;

	/**
	 * Returns the entity that represents the provided scope.
	 *
	 * Possible scopes are:
	 * - `TopicBuilder::SCOPE_PUBLIC`
	 * - `TopicBuilder::SCOPE_SPECIFIC`
	 * - `TopicBuilder::SCOPE_PROJECT`
	 * - `TopicBuilder::SCOPE_USER`
	 * - `TopicBuilder::SCOPE_ORGANIZATION`
	 *
	 * @return object|array<int,object>|null
	 */
	public function getMercureScope(string $scope): object | array | null;
}
