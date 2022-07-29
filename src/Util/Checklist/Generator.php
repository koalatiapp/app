<?php

namespace App\Util\Checklist;

use App\Entity\Checklist\Checklist;
use App\Entity\Checklist\Item;
use App\Entity\Checklist\ItemGroup;
use App\Entity\Project;
use App\Enum\Framework;
use App\Util\Config;

class Generator
{
	public function __construct(
		private Config $config,
	) {
	}

	public function generateChecklist(Project $project): Checklist
	{
		if ($project->getChecklist()) {
			return $project->getChecklist();
		}

		$checklist = (new Checklist())
			->setProject($project);

		$this->addGroupsFromConfig($checklist, "checklist/base");

		if ($project->hasTag(Framework::WEBFLOW)) {
			$this->addWebflowTasks($checklist);
		} elseif ($project->hasTag(Framework::WORDPRESS)) {
			$this->addWordpressTasks($checklist);
		} elseif ($project->hasTag(Framework::SHOPIFY)) {
			$this->addShopifyTasks($checklist);
		}

		return $checklist;
	}

	private function addWebflowTasks(Checklist $checklist): void
	{
		$this->addGroupsFromConfig($checklist, "checklist/webflow");
	}

	private function addWordpressTasks(Checklist $checklist): void
	{
		$this->addGroupsFromConfig($checklist, "checklist/wordpress");
	}

	private function addShopifyTasks(Checklist $checklist): void
	{
		$this->addGroupsFromConfig($checklist, "checklist/shopify");
	}

	private function addGroupsFromConfig(Checklist $checklist, string $configFilename): void
	{
		$templateGroups = $this->config->get($configFilename);

		foreach ($templateGroups as $groupData) {
			$group = (new ItemGroup())->setName($groupData['title']);
			$checklist->addItemGroup($group);

			foreach ($groupData['items'] as $itemTemplate) {
				$item = (new Item())
					->setTitle($itemTemplate['title'])
					->setDescription($itemTemplate['description'] ?? '')
					->setResourceUrls($itemTemplate['resourceUrls'] ?? []);
				$group->addItem($item);
				$checklist->addItem($item);
			}
		}
	}
}
