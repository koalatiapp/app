<?php

namespace App\Controller\Project;

use App\Form\Project\ProjectSettingsType;
use App\Util\Url;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectSettingsController extends AbstractProjectController
{
	/**
	 * @Route("/project/{id}/settings", name="project_settings")
	 */
	public function projectSettings(Request $request, Url $urlHelper, TranslatorInterface $translator, int $id): Response
	{
		$project = $this->getProject($id);
		$originalProject = clone $project;

		/**
		 * @var \Symfony\Component\Form\Form $form
		 */
		$form = $this->createForm(ProjectSettingsType::class, $project);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			// Handle deletion first without regular form validation
			if ($form->getClickedButton()->getName() == 'delete' && $this->isCsrfTokenValid($form->getName(), $request->request->get($form->getName())['_token'] ?? null)) {
				if ($form->get('deleteConfirmation')->getData() === true) {
					$em = $this->getDoctrine()->getManager();
					$em->remove($project);
					$em->flush();

					$this->addFlash('success', $translator->trans('project_settings.flash.deleted_successfully', ['%name%' => $project->getName()]));

					return $this->redirectToRoute('dashboard');
				} else {
					$form->get('deleteConfirmation')->addError(new FormError($translator->trans('project_settings.delete.error_confirmation')));
				}
			}
			// Handle the good old form settings form regularly
			elseif ($form->isValid()) {
				$websiteUrl = $urlHelper->standardize($project->getUrl());

				if ($originalProject->getUrl() != $project->getUrl()) {
					// Check if the provided website URL exists
					if (!$urlHelper->exists($websiteUrl)) {
						$form->get('url')->addError(new FormError($translator->trans('project_settings.form.field.url.error_unreachable')));
					}
				}

				if ($form->isValid()) {
					$project->setUrl($websiteUrl);
					$em = $this->getDoctrine()->getManager();
					$em->persist($project);
					$em->flush();

					$this->addFlash('success', $translator->trans('project_settings.flash.updated_successfully', ['%name%' => $project->getName()]));
				}
			}
		}

		return $this->render('app/project/settings.html.twig', [
			'project' => $project,
			'form' => $form->createView(),
		]);
	}
}
