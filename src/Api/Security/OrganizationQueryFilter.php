<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class OrganizationQueryFilter extends AbstractQueryFilter
{
	/**
	 * {@inheritDoc}
	 *
	 * @param array<mixed> $context
	 */
	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
	{
		if ($resourceClass != Organization::class) {
			return;
		}

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$queryBuilder->join(OrganizationMember::class, "current_member", Join::WITH, "current_member.organization = $rootAlias AND current_member.user = :user");
		$queryBuilder->setParameter('user', $this->getUser());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param array<mixed> $context
	 * @param array<mixed> $identifiers
	 */
	public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
	{
		// Nothing here: security logic is handled by the voter for single items.
	}
}
