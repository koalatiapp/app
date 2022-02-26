<?php

namespace App\Subscription;

use App\Entity\Organization;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectActivityRecordRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuotaManager
{
	private SessionInterface $session;
	private FlashBagInterface $flashBag;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ProjectActivityRecordRepository $projectActivityRepository,
		private PlanManager $planManager,
		private TranslatorInterface $translator,
		RequestStack $requestStack
	) {
		/** @var Session */
		$session = $requestStack->getSession();

		$this->session = $session;
		$this->flashBag = $this->session->getFlashBag();
	}

	public function isUserProjectQuotaExceeded(User $user): bool
	{
		$activeProjectCount = $this->projectActivityRepository->getActiveProjectCount($user);
		$plan = $this->planManager->getPlanFromEntity($user);

		return $activeProjectCount >= $plan->getMaxActiveProjects();
	}

	public function isOrganizationProjectQuotaExceeded(Organization $organization): bool
	{
		$organizationOwner = $organization->getOwner();

		return $this->isUserProjectQuotaExceeded($organizationOwner);
	}

	public function isProjectOwnerProjectQuotaExceeded(Project $project): bool
	{
		$projectOwner = $project->getOwner();

		if ($projectOwner instanceof Organization) {
			return $this->isOrganizationProjectQuotaExceeded($projectOwner);
		}

		return $this->isUserProjectQuotaExceeded($projectOwner);
	}

	public function notifyIfQuotaExceeded(Project $project): void
	{
		$sessionKey = 'quota_exceeded_last_count';
		$lastActiveProjectCountNotified = $this->session->get($sessionKey);
		$ownerUser = $project->getOwnerUser() ?: $project->getOwnerOrganization()->getOwner();
		$activeProjectCount = $this->projectActivityRepository->getActiveProjectCount($ownerUser);

		if ($this->isProjectOwnerProjectQuotaExceeded($project)) {
			if ($lastActiveProjectCountNotified == $activeProjectCount) {
				return;
			}

			$this->flashBag->add('warning', $this->translator->trans('subscription.active_project_quota_already_reached'));
			$this->session->set($sessionKey, $activeProjectCount);
		}
	}
}
