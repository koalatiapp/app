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
		private readonly Config $config,
	) {
	}

	public function generateChecklist(?Project $project): Checklist
	{
		if ($project?->getChecklist()) {
			return $project->getChecklist();
		}

		$checklist = (new Checklist());
		$this->addGroupsFromConfig($checklist, "checklist/base");

		if ($project) {
			$checklist->setProject($project);

			if ($project->hasTag(Framework::WORDPRESS)) {
				$this->addWordpressTasks($checklist);
			} elseif ($project->hasTag(Framework::SHOPIFY)) {
				$this->addShopifyTasks($checklist);
			} elseif ($project->hasTag(Framework::WEBFLOW)) {
				$this->addWebflowTasks($checklist);
			}
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
		/**
		 * @var array<int,array<string,ItemGroup>>
		 */
		static $existingGroups = [];
		/**
		 * @var array<int,array<string,array<string,Item>>>
		 */
		static $existingItems = [];

		$templateGroups = $this->config->get($configFilename);

		foreach ($templateGroups as $groupData) {
			$group = $existingGroups[$checklist->getId()][$groupData["title"]] ?? null;

			if (!$group) {
				$group = (new ItemGroup())->setName($groupData['title']);
				$checklist->addItemGroup($group);

				$existingGroups[$checklist->getId()][$group->getName()] = $group;
			}

			// Create or update items
			foreach ($groupData['items'] as $itemTemplate) {
				$item = $existingItems[$checklist->getId()][$group->getName()][$itemTemplate["title"]] ?? null;

				if (!$item) {
					// Create new item
					$item = (new Item())
						->setTitle($itemTemplate['title'])
						->setDescription($itemTemplate['description'] ?? '')
						->setResourceUrls($itemTemplate['resourceUrls'] ?? []);
					$group->addItem($item);
					$checklist->addItem($item);

					$existingItems[$checklist->getId()][$group->getName()][$item->getTitle()] = $item;

					continue;
				}

				// Update existing item
				if ($itemTemplate['description'] ?? null) {
					$item->setDescription($item->getDescription()."\n\n".$itemTemplate["description"]);
				}

				if ($itemTemplate['resourceUrls'] ?? null) {
					$item->setResourceUrls(array_merge(
						$itemTemplate['resourceUrls'],
						$item->getResourceUrls(),
					));
				}
			}
		}
	}
}
