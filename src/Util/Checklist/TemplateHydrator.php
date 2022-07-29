<?php

namespace App\Util\Checklist;

use App\Entity\Checklist\Checklist;
use App\Entity\Checklist\ChecklistTemplate;
use App\Entity\Checklist\Item;
use App\Entity\Checklist\ItemGroup;
use App\Entity\Project;

class TemplateHydrator
{
	public function hydrate(ChecklistTemplate $template, Project $project): Checklist
	{
		$checklist = (new Checklist())
			->setProject($project)
			->setTemplate($template);
		$project->setChecklist($checklist);

		foreach ($template->getChecklistContent() as $groupTemplate) {
			$group = (new ItemGroup())->setName($groupTemplate['title']);
			$checklist->addItemGroup($group);

			foreach ($groupTemplate['items'] as $itemTemplate) {
				$item = (new Item())
					->setTitle($itemTemplate['title'])
					->setDescription($itemTemplate['description'] ?? '')
					->setResourceUrls($itemTemplate['resourceUrls'] ?? []);
				$group->addItem($item);
				$checklist->addItem($item);
			}
		}

		return $checklist;
	}
}
