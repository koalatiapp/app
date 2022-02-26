<?php

namespace App\Controller;

use App\Entity\User;
use Hashids\HashidsInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as DefaultAbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractController extends DefaultAbstractController
{
	protected TranslatorInterface $translator;
	protected LoggerInterface $logger;
	protected HashidsInterface $idHasher;

	/**
	 * @required
	 */
	public function setTranslator(TranslatorInterface $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * @required
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * @required
	 */
	public function setIdHasher(HashidsInterface $idHasher): void
	{
		$this->idHasher = $idHasher;
	}

	/**
	 * Get a user from the Security Token Storage.
	 *
	 * @return User|null
	 *
	 * @throws \LogicException If SecurityBundle is not available
	 *
	 * @see TokenInterface::getUser()
	 */
	protected function getUser()
	{
		/** @var User|null */
		$user = parent::getUser();

		return $user;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param string                   $message
	 * @param array<string,mixed>|null $messageParams
	 */
	protected function addFlash(string $type, $message, ?array $messageParams = []): void
	{
		parent::addFlash($type, $this->translator->trans($message, $messageParams));
	}
}
