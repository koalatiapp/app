<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Routing\EncryptedIriConverter;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Security\OrganizationVoter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class OrganizationMemberQueryFilter extends AbstractQueryFilter
{
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
		if ($resourceClass != OrganizationMember::class) {
			return;
		}

		// If we're querying the members of an organization, check if the user has access to that organization
		if ($organizationId = $context['uri_variables']['organizationId'] ?? null) {
			$organization = $this->iriConverter->getResourceFromIri("/api/organizations/$organizationId");

			if (!$this->security->isGranted(OrganizationVoter::VIEW, $organization)) {
				throw new AccessDeniedHttpException();
			}
		}

		$allowedOrganizationIds = $this->getUser()->getOrganizationLinks()->map(fn (?OrganizationMember $membership = null) => $membership->getOrganization()->getId())->toArray();

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$queryBuilder->join(Organization::class, "parent_organization", Join::WITH, "$rootAlias.organization = parent_organization AND parent_organization.id IN (:allowedOrganizationIds)");
		$queryBuilder->setParameter("allowedOrganizationIds", $allowedOrganizationIds);
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
