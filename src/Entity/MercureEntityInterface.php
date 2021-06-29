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
	 * The values of the array are arrays themselves, with:
	 * - 1st element: the topic template as the first element of the array
	 * - 2nd element (optional): the name of the method returning the scope
	 *
	 * The topic templates can include the following placeholders:
	 * - `{scope}`: replaced by the scope hash, which is based on the result
	 * 				of the method in the 2nd element
	 * - `{id}`: replaced by the ID of the entity for specific topic URIs, or
	 * 			left as is to obtain a generic "catch-all" topic URI for the
	 * 			given scope.
	 *
	 * @return array<string,array<int,string>>
	 */
	public static function getMercureTopics(): array;

	public function getId(): ?int;
}
