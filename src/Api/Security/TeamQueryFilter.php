<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Organization;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class TeamQueryFilter implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
	public function __construct(
		private Security $security
	) {
	}

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
		$membersAlias = $queryNameGenerator->generateJoinAlias("members");
		$queryBuilder->join("$rootAlias.members", $membersAlias);
		$queryBuilder->andWhere("$membersAlias.user = :user");
		$queryBuilder->setParameter(':user', $this->security->getUser());
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param array<mixed> $context
	 * @param array<mixed> $identifiers
	 */
	public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
	{
		if ($resourceClass != Organization::class) {
			return;
		}

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$membersAlias = $queryNameGenerator->generateJoinAlias("members");
		$queryBuilder->join("$rootAlias.members", $membersAlias);
		$queryBuilder->andWhere("$membersAlias.user = :user");
		$queryBuilder->setParameter(':user', $this->security->getUser());
	}
}
