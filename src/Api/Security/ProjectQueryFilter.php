<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Project;
use App\Trait\SecurityAwareTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;

class ProjectQueryFilter implements QueryCollectionExtensionInterface
{
	use SecurityAwareTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param array<mixed> $context
	 */
	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
	{
		if ($resourceClass != Project::class) {
			return;
		}

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$criteria = new Criteria();
		$criteria->where(Criteria::expr()->eq("$rootAlias.ownerUser", $this->getUser()));
		$criteria->orWhere(Criteria::expr()->in("$rootAlias.ownerOrganization", $this->getUser()->getOrganizations()->toArray()));
		$queryBuilder->addCriteria($criteria);
	}
}
