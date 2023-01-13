<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProjectRepository;
use App\Security\ProjectVoter;
use App\ToolsService\Endpoint\StatusEndpoint;
use App\Util\Testing\TestingStatus;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @implements ProviderInterface<TestingStatus>
 */
final class TestingStatusProvider implements ProviderInterface
{
	public function __construct(
		private ProjectRepository $projectRepository,
		private Security $security,
		private StatusEndpoint $statusApi,
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
		$projectId = $uriVariables['projectId'];
		$project = $this->projectRepository->find($projectId);

		if (!$this->security->isGranted(ProjectVoter::VIEW, $project)) {
			throw new AccessDeniedHttpException("Access Denied.");
		}

		$status = $this->statusApi->project($project);

		return new TestingStatus($project, $status);
	}
}
