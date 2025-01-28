<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Trait\SecurityAwareTrait;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class OrganizationQueryFilter implements QueryCollectionExtensionInterface
{
	use SecurityAwareTrait;

	/**
	 * @param array<mixed> $context
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
	{
		if ($resourceClass != Organization::class) {
			return;
		}

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$queryBuilder->join(OrganizationMember::class, "current_member", Join::WITH, "current_member.organization = $rootAlias AND current_member.user = :user");
		$queryBuilder->setParameter('user', $this->getUser());
	}
}
