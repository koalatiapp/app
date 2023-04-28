<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Routing\EncryptedIriConverter;
use App\Entity\ActivityLog;
use App\Security\OrganizationVoter;
use App\Security\ProjectVoter;
use App\Trait\SecurityAwareTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class ActivityLogQueryFilter implements QueryCollectionExtensionInterface
{
	use SecurityAwareTrait;

	private EncryptedIriConverter $iriConverter;

	#[Required]
	public function setIriConverter(EncryptedIriConverter $iriConverter): void
	{
		$this->iriConverter = $iriConverter;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param array<mixed> $context
	 */
	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
	{
		if ($resourceClass != ActivityLog::class) {
			return;
		}

		$organizationIris = (array) ($context['filters']['organization'] ?? []);
		$projectIris = (array) ($context['filters']['project'] ?? []);

		foreach ($organizationIris as $organizationIri) {
			$organization = $this->iriConverter->getResourceFromIri($organizationIri);

			if (!$this->security->isGranted(OrganizationVoter::VIEW, $organization)) {
				throw new AccessDeniedHttpException();
			}
		}

		foreach ($projectIris as $projectIri) {
			$project = $this->iriConverter->getResourceFromIri($projectIri);

			if (!$this->security->isGranted(ProjectVoter::VIEW, $project)) {
				throw new AccessDeniedHttpException();
			}
		}

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$criteria = new Criteria();
		$criteria->where(Criteria::expr()->eq("$rootAlias.isInternal", false));

		// If no scope is specified, only load the activity logs for the authenticated user.
		if (!$organizationIris && !$projectIris) {
			$criteria->andWhere(Criteria::expr()->eq("$rootAlias.user", $this->getUser()));
		}

		$queryBuilder->addCriteria($criteria);
	}
}
