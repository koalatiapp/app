<?php

namespace App\Api\Security;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Api\Routing\EncryptedIriConverter;
use App\Entity\Comment;
use App\Security\ProjectVoter;
use App\Trait\SecurityAwareTrait;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class CommentQueryFilter implements QueryCollectionExtensionInterface
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
		if ($resourceClass != Comment::class) {
			return;
		}

		// If we're querying the pages of a project, check if the user has access to that project
		if ($projectId = $context['uri_variables']['projectId'] ?? null) {
			$project = $this->iriConverter->getResourceFromIri("/api/projects/$projectId");

			if (!$this->security->isGranted(ProjectVoter::VIEW, $project)) {
				throw new AccessDeniedHttpException();
			}
		}

		$rootAlias = $queryBuilder->getRootAliases()[0];
		$criteria = new Criteria();
		$criteria->where(Criteria::expr()->in("$rootAlias.project", $this->getUser()->getAllProjects()->toArray()));
		$criteria->andWhere(Criteria::expr()->isNull("$rootAlias.thread"));
		$queryBuilder->addCriteria($criteria);
	}
}
