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

		$form = $this->createForm(ProjectSettingsType::class, $project);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$websiteUrl = $urlHelper->standardize($project->getUrl());

			if ($originalProject->getUrl() != $project->getUrl()) {
				// Check if the provided website URL exists
				if (!$urlHelper->exists($websiteUrl)) {
					$form->get('url')->addError(new FormError('This URL is invalid or unreachable.'));
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

		return $this->render('app/project/settings.html.twig', [
			'project' => $project,
			'form' => $form->createView(),
		]);
	}
}
