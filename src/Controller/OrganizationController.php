<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Form\Organization\DeletionOrganizationType;
use App\Form\Organization\NewOrganizationType;
use App\Form\Organization\OrganizationType;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/team", name="organization_")
 */
class OrganizationController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		public OrganizationRepository $organizationRepository,
		public SluggerInterface $slugger,
		public TranslatorInterface $translator
	) {
	}

	private function getDefaultOrganization(): ?Organization
	{
		return $this->getUser()->getOrganizationLinks()->first()?->getOrganization();
	}

	/**
	 * @Route("/create", name="create")
	 */
	public function create(Request $request): Response
	{
		$organization = new Organization();
		$form = $this->createForm(NewOrganizationType::class, $organization);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$slug = $this->slugger->slug($organization->getName());
			$organization->setSlug($slug);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$membership = new OrganizationMember($organization, $this->getUser(), [OrganizationMember::ROLE_ADMIN]);
				$em->persist($organization);
				$em->persist($membership);
				$em->flush();

				$this->addFlash('success', $this->translator->trans('organization.flash.created_successfully', ['%name%' => $organization->getName()]));

				return $this->redirectToRoute('organization_dashboard', ['id' => $organization->getId()]);
			}
		}

		if ($this->getUser()->getOrganizationLinks()->isEmpty()) {
			return $this->render('app/organization/create_first.html.twig', ['form' => $form->createView()]);
		}

		return $this->render('app/organization/create.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * @Route("/{id}", name="dashboard", requirements={"id"="\d*"})
	 */
	public function dashboard(int $id = null, OrganizationRepository $organizationRepository): Response
	{
		if ($this->getUser()->getOrganizationLinks()->isEmpty()) {
			return $this->redirectToRoute('organization_create');
		}

		return $this->render('app/organization/dashboard.html.twig', [
			'organization' => $id ? $organizationRepository->find($id) : $this->getDefaultOrganization(),
		]);
	}

	/**
	 * @Route("/{id}/settings", name="settings")
	 */
	public function settings(int $id, Request $request): Response
	{
		$organization = $this->organizationRepository->find($id);
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
			$em = $this->getDoctrine()->getManager();

			foreach ($organization->getMembers() as $member) {
				$em->remove($member);
			}

			foreach ($organization->getProjects() as $project) {
				$em->remove($project);
			}

			$em->remove($organization);
			$em->flush();

			if ($deletionForm->isValid()) {
				$this->addFlash('success', $this->translator->trans('organization.flash.deleted_successfully', ['%name%' => $organization->getName()]));

				return null;
			}
		}

		return $deletionForm;
	}

	private function processUpdateForm(Organization $organization, Request $request): FormInterface
	{
		$form = $this->createForm(OrganizationType::class, $organization);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			$slug = $this->slugger->slug(trim($organization->getName()));
			$organization->setSlug($slug);

			if ($form->isValid()) {
				$em = $this->getDoctrine()->getManager();
				$em->persist($organization);
				$em->flush();

				$this->addFlash('success', $this->translator->trans('organization.flash.updated_successfully', ['%name%' => $organization->getName()]));
			}
		}

		return $form;
	}
}
