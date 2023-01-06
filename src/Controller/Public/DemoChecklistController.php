<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Util\Checklist\Generator;
use App\Util\ClientMessageSerializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoChecklistController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	#[Route(path: '/free-checklist', name: 'checklist_demo')]
	public function checklistDemo(Generator $checklistGenerator, ClientMessageSerializer $serializer): Response
	{
		// @FIXME: This does not work with the new serializer, since the items/groups don't have IDs
		// $checklist = $checklistGenerator->generateChecklist(null);
		// $serializedItemGroups = $serializer->serialize($checklist->getItemGroups());

		return $this->render('public/checklist_demo.html.twig', [
				"serializedItemGroups" => [], // $serializedItemGroups,
			]);
	}
}
