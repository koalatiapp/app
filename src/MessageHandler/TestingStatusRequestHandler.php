<?php

namespace App\MessageHandler;

use App\ApiClient\Endpoint\StatusEndpoint;
use App\Entity\Project;
use App\Mercure\UpdateDispatcher;
use App\Mercure\UpdateType;
use App\Message\TestingStatusRequest;
use App\Repository\ProjectRepository;
use App\Subscription\PlanManager;
use App\Util\Testing\TestingStatus;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TestingStatusRequestHandler implements MessageHandlerInterface
{
	private ApcuAdapter $cache;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ProjectRepository $projectRepository,
		private PlanManager $planManager,
		private UpdateDispatcher $updateDispatcher,
		private StatusEndpoint $statusApi,
	) {
		$this->cache = new ApcuAdapter('project_status', 30);
	}

	/**
	 * Retrieves a project's status via the Tools API
	 * and sends it to the client(s) via a Mercure update.
	 */
	public function __invoke(TestingStatusRequest $message): void
	{
		$project = $this->projectRepository->find($message->getProjectId());

		if (!$project) {
			return;
		}

		// Check if the user's plan allows them to use automated testing
		$plan = $this->planManager->getPlanFromEntity($project->getOwner());

		if (!$plan->hasTestingAccess()) {
			return;
		}

		// Check if throttling would help
		if ($this->requestShouldBeTrottled($project)) {
			return;
		}

		// Get project status from the tools API
		$statusData = $this->statusApi->project($project);
		$this->cacheProjectStatus($project, $statusData);

		// Build & dispatch the Mercure update
		$status = new TestingStatus($project, $statusData);
		$this->updateDispatcher->dispatch($status, UpdateType::CREATE);
	}

	/**
	 * To avoid querying the API too frequently, not all project status requests are processed.
	 *
	 * If the number of pending requests is above 1 and that a status update request was
	 * already processed in the past 30 seconds, the status update will simply be ignored.
	 *
	 * The latest status is kept in cache and updated optimistically to keep track of
	 * the number of pending requests left without having to call the API every time.
	 */
	private function requestShouldBeTrottled(Project $project): bool
	{
		$previousStatusCache = $this->getCacheItem($project);

		if ($previousStatusCache->isHit()) {
			$previousStatus = $previousStatusCache->get();

			// If the expected ending time has passed (or is about to) already, don't throttle the request.
			if ($previousStatus['expected_end_timestamp'] < time() + 3) {
				return false;
			}

			if ($previousStatus['requestCount'] > 1) {
				// Decreasing the pending request count allows us to keep track of when
				// it becomes important to request status updates from the API
				$previousStatus['requestCount']--;
				$previousStatusCache->set($previousStatus);
				$this->cache->save($previousStatusCache);

				// Allow a request every now and then, just to keep the estimates updated
				if (random_int(0, 100) >= 90) {
					return false;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Caches the project's status server-side.
	 *
	 * This cache is used to avoid making requests to the API for every test
	 * result that is received.
	 *
	 * @param array<string,mixed> $status
	 */
	private function cacheProjectStatus(Project $project, array $status): void
	{
		if (!isset($status['expected_end_timestamp'])) {
			$expectedEndingTimestamp = time() + ((int) $status['timeEstimate'] / 1000);
			$status = array_merge($status, ['expected_end_timestamp' => $expectedEndingTimestamp]);
		}

		$previousStatusCache = $this->getCacheItem($project);
		$previousStatusCache->set($status);

		$this->cache->save($previousStatusCache);
	}

	/**
	 * Retrieves or generates the cache item for a project's status.
	 */
	private function getCacheItem(Project $project): CacheItem
	{
		return $this->cache->getItem((string) $project->getId());
	}
}
