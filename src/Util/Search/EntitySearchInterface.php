<?php

namespace App\Util\Search;

use Doctrine\Common\Collections\Collection;

interface EntitySearchInterface
{
	/**
	 * @param array<string> $queryParts
	 *
	 * @return Collection<int,SearchResult>
	 */
	public function search(array $queryParts): Collection;
}
