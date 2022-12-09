<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Form\Project\NewProjectType;
use App\Message\FaviconRequest;
use App\Message\ScreenshotRequest;
use App\Message\SitemapRequest;
use App\Util\StackDetector;
use App\Util\Url;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjectCreationController extends AbstractProjectController
{
	#[Route(path: '/project/create', name: 'project_creation')]
	public function projectCreation(Url $urlHelper, Request $request, StackDetector $stackDetector, MessageBusInterface $bus): Response
	{
		$project = new Project();
		$project->setOwnerUser($this->getUser());
		$availableOrganizationsById = [];

		$formOptions = [
				'available_owners' => NewProjectType::getDefaultAvailableOwners(),
			];

		foreach ($this->getUser()->getOrganizationLinks() as $organizationLink) {
			$organization = $organizationLink->getOrganization();
			$formOptions['available_owners'][$organization->getId()] = $organization->getId();
			$availableOrganizationsById[$organization->getId()] = $organization;
		}

		$form = $this->createForm(NewProjectType::class, $project, $formOptions);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$websiteUrl = $urlHelper->standardize($project->getUrl(), false);
			$formIsValid = true;

			// Check if the provided website URL exists
			if (!$urlHelper->exists($websiteUrl)) {
				$form->get('url')->addError(new FormError('This URL is invalid or unreachable.'));
				$formIsValid = false;
			}

			if ($formIsValid) {
				$project->setUrl($websiteUrl);

				// Set ownership
				$ownerValue = $form->get('owner')->getData();
				if (is_numeric($ownerValue) && isset($availableOrganizationsById[$ownerValue])) {
					$project->setOwnerUser(null);
					$project->setOwnerOrganization($availableOrganizationsById[$ownerValue]);
				}

				// Detect website framework / CMS
				$framework = $stackDetector->detectFramework($websiteUrl);
				if ($framework) {
					$project->addTag($framework);
				}

				$this->entityManager->persist($project);
				$this->entityManager->flush();

				$bus->dispatch(new ScreenshotRequest($project->getId()));
				$bus->dispatch(new FaviconRequest($project->getId()));
				$bus->dispatch(new SitemapRequest($project->getId()));
				$this->addFlash('success', 'project_creation.flash.created_successfully', ['%name%' => $project->getName()]);

				return $this->redirectToRoute('project_dashboard', ['id' => $this->idHasher->encode($project->getId())]);
			}
		}

		return $this->render('app/project/creation.html.twig', [
				'form' => $form->createView(),
			]);
	}
}
