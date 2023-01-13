<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Mercure\UpdateDispatcher;
use App\Mercure\UpdateType;
use App\Util\Testing\RecommendationGroup;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\HashidsInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;

class RecommendationGroupProcessor implements ProcessorInterface
{
	public function __construct(
		protected Security $security,
		protected ProcessorInterface $persistProcessor,
		protected ProcessorInterface $removeProcessor,
		protected EntityManagerInterface $entityManager,
		protected HashidsInterface $idHasher,
		protected MessageBusInterface $bus,
		protected UpdateDispatcher $mercureUpdateDispatcher,
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param RecommendationGroup $data
	 * @param array<string,mixed> $uriVariables
	 * @param array<mixed>        $context
	 *
	 * @return RecommendationGroup|null
	 */
	public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?object
	{
		foreach ($data->getRecommendations() as $recommendation) {
			if ($recommendation->getIsCompleted() && !$recommendation->getCompletedBy()) {
				$recommendation->complete($this->getUser());
			}

			$this->entityManager->persist($recommendation);
		}

		$this->entityManager->flush();
		$this->mercureUpdateDispatcher->dispatch($data, $data->getIsCompleted() ? UpdateType::DELETE : UpdateType::UPDATE);

		return $data;
	}

	protected function getUser(): User
	{
		/** @var User */
		$user = $this->security->getUser();

		return $user;
	}
}
