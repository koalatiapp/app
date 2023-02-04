<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\Dto\TestingRequest;
use App\Entity\Page;
use App\Message\TestingRequest as TestingRequestMessage;
use App\Security\ProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class TestingRequestProcessor implements ProcessorInterface
{
	public function __construct(
		protected Security $security,
		protected MessageBusInterface $bus,
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param TestingRequest      $data
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 *
	 * @return TestingRequestMessage
	 */
	public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?object
	{
		if (!$data->project) {
			throw new BadRequestHttpException("Payload is missing 'project'.");
		}

		if (!$this->security->isGranted(ProjectVoter::PARTICIPATE, $data->project)) {
			throw new AccessDeniedHttpException("Access Denied.");
		}

		$pageIds = !$data->pages ? null : array_map(fn (Page $page) => $page->getId(), $data->pages);
		$message = new TestingRequestMessage($data->project->getId(), $data->tools, $pageIds);

		$this->bus->dispatch($message);

		return $message;
	}
}
