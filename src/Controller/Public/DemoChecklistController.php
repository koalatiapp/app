<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Entity\Project;
use App\Repository\Checklist\ChecklistTemplateRepository;
use App\Util\Checklist\TemplateHydrator;
use App\Util\ClientMessageSerializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoChecklistController extends AbstractController
{
	/**
	 * @Route("/free-checklist", name="checklist_demo")
	 */
	public function checklistDemo(ChecklistTemplateRepository $templateRepository, TemplateHydrator $templateHydrator, ClientMessageSerializer $serializer): Response
	{
		$template = current($templateRepository->findAll());
		$checklist = $templateHydrator->hydrate($template, new Project());
		$serializedItemGroups = $serializer->serialize($checklist->getItemGroups());

		return $this->render('public/checklist_demo.html.twig', [
			"serializedItemGroups" => $serializedItemGroups,
		]);
	}
}
