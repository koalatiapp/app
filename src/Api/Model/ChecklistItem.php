<?php

namespace App\Api\Model;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\State\ChecklistItemState;
use App\Entity\Checklist\Item;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @implements EntityFacadeInterface<Item>
 */
#[ApiResource(
	provider: ChecklistItemState::class,
	processor: ChecklistItemState::class,
	operations: [
		new Get(),
		new GetCollection(),
		new Post(),
		new Patch(),
		new Delete(),
	],
)]
class ChecklistItem implements EntityFacadeInterface
{
	#[ApiProperty(identifier: true)]
	#[Groups(["read"])]
	public int|string $id;

	/**
	 * Task to complete.
	 */
	#[Groups(["read"])]
	public string $title;

	/**
	 * Markdown-formatted description of the task to complete.
	 */
	#[Groups(["read"])]
	public string $description;

	/**
	 * URLs of useful resources to help users complete this task.
	 *
	 * @var array<string>
	 */
	#[Groups(["read"])]
	public array $resources;

	#[Groups(["read", "write"])]
	public bool $isCompleted;

	/**
	 * @param Item $entity
	 */
	public static function fromEntity(object $entity): self
	{
		$resource = new self();
		$resource->id = $entity->getId();
		$resource->title = $entity->getTitle();
		$resource->description = $entity->getDescription();
		$resource->resources = $entity->getResourceUrls();
		$resource->isCompleted = $entity->getIsCompleted();

		return $resource;
	}
}
