<?php

namespace App\Trait;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

trait SecurityAwareTrait
{
	protected Security $security;

	#[Required]
	public function setSecurity(Security $security): void
	{
		$this->security = $security;
	}

	protected function getUser(): User
	{
		/** @var User $user */
		$user = $this->security->getUser();

		return $user;
	}
}
