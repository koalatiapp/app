<?php

namespace App\Mercure;

use App\Entity\User;
use Hashids\HashidsInterface;

class UserTopicBuilder
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.idHasher)
	 */
	public function __construct(
		private HashidsInterface $idHasher
	) {
	}

	public function getTopic(User $user): string
	{
		$userId = $this->idHasher->encode($user->getId());
		return "http://koalati/$userId/";
	}
}
