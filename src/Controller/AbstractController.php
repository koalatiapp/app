<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\HashidsInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as DefaultAbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractController extends DefaultAbstractController
{
	protected TranslatorInterface $translator;
	protected LoggerInterface $logger;
	protected HashidsInterface $idHasher;
	protected EntityManagerInterface $entityManager;

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setEntityManager(EntityManagerInterface $entityManager): void
	{
		$this->entityManager = $entityManager;
	}

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setTranslator(TranslatorInterface $translator): void
	{
		$this->translator = $translator;
	}

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setIdHasher(HashidsInterface $idHasher): void
	{
		$this->idHasher = $idHasher;
	}

	/**
	 * @return User|null
	 */
	protected function getUser(): ?UserInterface
	{
		/** @var User|null */
		$user = parent::getUser();

		return $user;
	}

	/**
	 * @param string                   $message
	 * @param array<string,mixed>|null $messageParams
	 */
	protected function addFlash(string $type, $message, ?array $messageParams = []): void
	{
		parent::addFlash($type, $this->translator->trans($message, $messageParams));
	}
}
