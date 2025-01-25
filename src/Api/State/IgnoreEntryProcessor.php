<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\Dto\IgnoreEntryCreation;
use App\Entity\Testing\IgnoreEntry;
use App\Entity\User;
use App\Mercure\UpdateDispatcher;
use App\Mercure\UpdateType;
use App\Security\ProjectVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @implements ProcessorInterface<IgnoreEntryCreation, IgnoreEntry>
 */
class IgnoreEntryProcessor implements ProcessorInterface
{
	public function __construct(
		private readonly Security $security,
		private readonly UpdateDispatcher $mercureUpdateDispatcher,
		private readonly EntityManagerInterface $entityManager,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?object
	{
		$allowedScopes = [
			'user',
			'organization',
			'project',
			'page',
		];

		if (!$data->scope) {
			$data->scope = "project";
		}

		if (!in_array($data->scope, $allowedScopes)) {
			throw new BadRequestHttpException("Invalid scope. Must be one of the following: ".implode(', ', $allowedScopes));
		}

		if (!isset($data->recommendation)) {
			throw new BadRequestHttpException("Payload is missing 'recommendation'.");
		}

		if (!$this->security->isGranted(ProjectVoter::PARTICIPATE, $data->recommendation->getProject())) {
			throw new AccessDeniedHttpException("Access Denied.");
		}

		$scopeTarget = match ($data->scope) {
			"project" => $data->recommendation->getProject(),
			"page" => $data->recommendation->getRelatedPage(),
			"organization" => $data->recommendation->getProject()->getOwnerOrganization(),
			"user" => $data->recommendation->getProject()->getOwnerUser(),
			default => throw new BadRequestHttpException("Invalid scope. Must be one of the following: ".implode(', ', $allowedScopes)),
		};

		/** @var User */
		$user = $this->security->getUser();
		$ignoreEntry = new IgnoreEntry(
			$data->recommendation->getTool(),
			$data->recommendation->getParentResult()->getUniqueName(),
			$data->recommendation->getUniqueName(),
			$data->recommendation->getTitle(),
			$scopeTarget,
			$user,
		);

		$this->entityManager->persist($ignoreEntry);
		$this->entityManager->flush();

		$this->mercureUpdateDispatcher->dispatch($ignoreEntry, UpdateType::CREATE);

		return $ignoreEntry;
	}
}
