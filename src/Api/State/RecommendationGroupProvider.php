<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProjectRepository;
use App\Security\ProjectVoter;
use App\Util\Testing\RecommendationGroup;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @implements ProviderInterface<RecommendationGroup>
 */
final class RecommendationGroupProvider implements ProviderInterface
{
	public function __construct(
		private ProjectRepository $projectRepository,
		private Security $security,
	) {
	}

	/**
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.context)
	 */
	public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
	{
		if (!in_array($operation::class, [GetCollection::class, Get::class, Patch::class])) {
			throw new MethodNotAllowedHttpException(['GET', 'PATCH']);
		}

		if ($operation instanceof GetCollection) {
			$projectId = $uriVariables['projectId'];
			$project = $this->projectRepository->find($projectId);

			if (!$this->security->isGranted(ProjectVoter::VIEW, $project)) {
				throw new AccessDeniedHttpException("Access Denied.");
			}

			return $project->getActiveRecommendationGroups();
		}

		return RecommendationGroup::loadFromGroupMatchingIdentifier($uriVariables['id'], $this->projectRepository);
	}
}
