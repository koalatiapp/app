<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

abstract class AbstractQueryFilter implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
	public function __construct(
		protected Security $security
	) {
	}

	protected function getUser(): User
	{
		/** @var User $user */
		$user = $this->security->getUser();

		return $user;
	}
}
