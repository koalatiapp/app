<?php

namespace App\Api\Model;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\State\TeamState;
use App\Entity\Organization;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @implements EntityFacadeInterface<Organization>
 */
#[ApiResource(
	provider: TeamState::class,
	processor: TeamState::class,
	operations: [
		new Get(),
		new GetCollection(),
		new Post(),
		new Patch(),
		new Delete(),
	],
)]
class Team implements EntityFacadeInterface
{
	#[ApiProperty(identifier: true)]
	#[Groups(["read"])]
	public int|string $id;

	#[Groups(["read", "write"])]
	public string $name;

	#[Groups(["read"])]
	public string $slug;

	/**
	 * @param Organization $entity
	 */
	public static function fromEntity(object $entity): self
	{
		$resource = new self();
		$resource->id = $entity->getId();
		$resource->name = $entity->getName();
		$resource->slug = $entity->getSlug();

		return $resource;
	}
}
