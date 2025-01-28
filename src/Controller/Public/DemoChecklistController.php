<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Entity\Project;
use App\Util\Checklist\Generator;
use App\Util\ClientMessageSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

class DemoChecklistController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	#[Route(path: '/free-checklist', name: 'checklist_demo')]
	public function checklistDemo(Generator $checklistGenerator, ClientMessageSerializer $serializer, EntityManagerInterface $entityManager): Response
	{
		// The serializer does not like resources without IDs.
		// To get around this, we'll just create a project & checklist in an
		// SQL transaction, serialize the data, and then rollback the transaction.
		// To avoid doing this too often, we cache this for a 30-day duration.
		$cache = new FilesystemAdapter();
		$serializedItemGroups = $cache->get('demo_checklist', function (ItemInterface $item) use ($checklistGenerator, $serializer, $entityManager) {
			$item->expiresAfter(3600 * 24 * 30);

			$project = (new Project())
				->setName("Temporary checklist project")
				->setUrl("https://sample.koalati.com");
			$checklist = $checklistGenerator->generateChecklist($project);

			$entityManager->beginTransaction();
			$entityManager->persist($project);
			$entityManager->persist($checklist);
			$entityManager->flush();

			$serializedItemGroups = $serializer->serialize($checklist->getItemGroups(), ['checklist.read']);

			$entityManager->rollback();

			return $serializedItemGroups;
		});

		return $this->render('public/checklist_demo.html.twig', [
			"serializedItemGroups" => $serializedItemGroups,
		]);
	}
}
