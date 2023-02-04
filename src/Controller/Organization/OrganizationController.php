<?php

namespace App\Controller\Organization;

use App\Controller\AbstractController;
use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Form\Organization\DeletionOrganizationType;
use App\Form\Organization\LeaveOrganizationType;
use App\Form\Organization\NewOrganizationType;
use App\Form\Organization\OrganizationType;
use App\Repository\OrganizationRepository;
use App\Security\OrganizationVoter;
use App\Subscription\UsageManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(path: '/team', name: 'organization_')]
class OrganizationController extends AbstractController
{
	use SuggestUpgradeControllerTrait;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		public OrganizationRepository $organizationRepository,
		public SluggerInterface $slugger,
		private UsageManager $usageManager,
	) {
	}

	private function getDefaultOrganization(): ?Organization
	{
		$organizationLink = $this->getUser()->getOrganizationLinks()->first() ?: null;

		return $organizationLink?->getOrganization();
	}

	#[Route(path: '/create', name: 'create')]
	public function create(Request $request): Response
	{
		if (!$this->isGranted(OrganizationVoter::CREATE)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.create_team');
		}

		$organization = new Organization();
		$form = $this->createForm(NewOrganizationType::class, $organization);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$membership = new OrganizationMember($organization, $this->getUser(), [OrganizationMember::ROLE_OWNER]);
				$this->entityManager->persist($organization);
				$this->entityManager->persist($membership);
				$this->entityManager->flush();

				$this->addFlash('success', 'organization.flash.created_successfully', ['%name%' => $organization->getName()]);

				return $this->redirectToRoute('organization_dashboard', ['id' => $this->idHasher->encode($organization->getId())]);
			}
		}

		if ($this->getUser()->getOrganizationLinks()->isEmpty()) {
			return $this->render('app/organization/create_first.html.twig', ['form' => $form->createView()]);
		}

		return $this->render('app/organization/create.html.twig', ['form' => $form->createView()]);
	}

	#[Route(path: '/{id}', name: 'dashboard', defaults: ['id' => null])]
	public function dashboard(int $id = null): Response
	{
		if ($this->getUser()->getOrganizationLinks()->isEmpty()) {
			return $this->redirectToRoute('organization_create');
		}

		$organization = $id ? $this->organizationRepository->find($id) : $this->getDefaultOrganization();

		return $this->render('app/organization/dashboard.html.twig', [
				'organization' => $organization,
				'usageManager' => $this->usageManager->withUser($organization->getOwner()),
			]);
	}

	#[Route(path: '/{id}/leave', name: 'leave')]
	public function leave(int $id, Request $request): Response
	{
		$organization = $this->organizationRepository->find($id);
		$this->denyAccessUnlessGranted(OrganizationVoter::VIEW, $organization);

		$form = $this->createForm(LeaveOrganizationType::class, $organization);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$membership = $organization->getMemberFromUser($this->getUser());

			if ($membership->getHighestRole() == OrganizationMember::ROLE_ADMIN) {
				$hasOtherAdmin = false;

				foreach ($organization->getMembers() as $otherMember) {
					if ($otherMember != $membership && $otherMember->getHighestRole() == OrganizationMember::ROLE_ADMIN) {
						$hasOtherAdmin = true;
						break;
					}
				}

				if (!$hasOtherAdmin) {
					$this->addFlash('danger', 'organization.flash.member_leave_no_admins');

					return $this->redirectToRoute('organization_leave', ['id' => $this->idHasher->encode($id)]);
				}
			}

			$this->entityManager->remove($membership);
			$this->entityManager->flush();

			$this->addFlash('success', 'organization.flash.member_left_successfully', ['%organization%' => $organization->getName()]);

			return $this->redirectToRoute('dashboard');
		}

		return $this->render('app/organization/leave.html.twig', [
				'organization' => $organization,
				'form' => $form->createView(),
			]);
	}

	#[Route(path: '/{id}/settings', name: 'settings')]
	public function settings(int $id, Request $request): Response
	{
		$organization = $this->organizationRepository->find($id);
		$this->denyAccessUnlessGranted(OrganizationVoter::EDIT, $organization);

		$deletionForm = $this->processDeletionForm($organization, $request);

		if (!$deletionForm) {
			return $this->redirectToRoute('dashboard');
		}

		$updateForm = $this->processUpdateForm($organization, $request);

		return $this->render('app/organization/settings.html.twig', [
				'organization' => $organization,
				'form' => $updateForm->createView(),
				'deletionForm' => $deletionForm->createView(),
			]);
	}

	private function processDeletionForm(Organization $organization, Request $request): ?FormInterface
	{
		$deletionForm = $this->createForm(DeletionOrganizationType::class, $organization);
		$deletionForm->handleRequest($request);

		if ($deletionForm->isSubmitted() && $deletionForm->isValid()) {
			foreach ($organization->getMembers() as $member) {
				$this->entityManager->remove($member);
			}

			foreach ($organization->getProjects() as $project) {
				$this->entityManager->remove($project);
			}

			$this->entityManager->remove($organization);
			$this->entityManager->flush();

			$this->addFlash('success', 'organization.flash.deleted_successfully', ['%name%' => $organization->getName()]);

			return null;
		}

		return $deletionForm;
	}

	private function processUpdateForm(Organization $organization, Request $request): FormInterface
	{
		$form = $this->createForm(OrganizationType::class, $organization);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				$this->entityManager->persist($organization);
				$this->entityManager->flush();

				$this->addFlash('success', 'organization.flash.updated_successfully', ['%name%' => $organization->getName()]);
			}
		}

		return $form;
	}
}
