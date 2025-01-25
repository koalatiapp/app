<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Routing\EncryptedIriConverter;
use App\Entity\Testing\IgnoreEntry;
use App\Security\OrganizationVoter;
use App\Security\ProjectVoter;
use App\Trait\SecurityAwareTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class IgnoreEntryQueryFilter implements QueryCollectionExtensionInterface
{
	use SecurityAwareTrait;

	private EncryptedIriConverter $iriConverter;

	#[Required]
	public function setIriConverter(EncryptedIriConverter $iriConverter): void
	{
		$this->iriConverter = $iriConverter;
	}

	/**
	 * @param array<mixed> $context
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
	{
		if ($resourceClass != IgnoreEntry::class) {
			return;
		}

		// If we're querying the ignore entries of a project, check if the user has access to that project
		if ($projectId = $context['uri_variables']['projectId'] ?? null) {
			$project = $this->iriConverter->getResourceFromIri("/api/projects/$projectId");

			if (!$this->security->isGranted(ProjectVoter::VIEW, $project)) {
				throw new AccessDeniedHttpException();
			}
		}

		// If we're querying the ignore entries of an organization, check if the user has access to that organization
		if ($organizationId = $context['uri_variables']['organizationId'] ?? null) {
			$organization = $this->iriConverter->getResourceFromIri("/api/organizations/$organizationId");

			if (!$this->security->isGranted(OrganizationVoter::VIEW, $organization)) {
				throw new AccessDeniedHttpException();
			}
		}

		// If we're querying the ignore entries of the current user, enforce exactly that.
		if ($context['operation_name'] == '_api_/users/current/ignore_entries_get_collection') {
			$rootAlias = $queryBuilder->getRootAliases()[0];
			$criteria = new Criteria();
			$criteria->where(Criteria::expr()->eq("$rootAlias.targetUser", $this->getUser()));
			$queryBuilder->addCriteria($criteria);
		}
	}
}
