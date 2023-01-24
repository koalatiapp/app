<?php

namespace App\Util\Search;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;

interface SearchInterface
{
	/**
	 * Runs the search query on Projects.
	 *
	 * @param array<string> $queryParts
	 * @param User|null     $user       if a user is specified, the search will be limited to
	 *                                  ressources that the user has access to
	 *
	 * @return Collection<int,SearchResult>
	 */
	public function search(array $queryParts, ?User $user = null): Collection;
}
