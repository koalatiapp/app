<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Subscription\Model\CurrentUsageCycle;
use App\Subscription\UsageManager;

/**
 * @implements ProviderInterface<CurrentUsageCycle>
 */
final class PageTestUsageProvider implements ProviderInterface
{
	public function __construct(
		private readonly UsageManager $usageManager
	) {
	}

	/**
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.context)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.uriVariables)
	 */
	public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
	{
		return $this->usageManager->getCurrentUsageCycleObject();
	}
}
