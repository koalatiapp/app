<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as DefaultAbstractController;

abstract class AbstractController extends DefaultAbstractController
{
	/**
	 * Get a user from the Security Token Storage.
	 *
	 * @return User|object|null
	 *
	 * @throws \LogicException If SecurityBundle is not available
	 *
	 * @see TokenInterface::getUser()
	 */
	protected function getUser()
	{
		return parent::getUser();
	}
}
