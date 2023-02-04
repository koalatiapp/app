<?php

namespace App\Util;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SelfHosting
{
	private readonly bool $selfHostingMode;
	private readonly bool $inviteOnlyRegistrationMode;

	public function __construct(ParameterBagInterface $parameterBag)
	{
		$this->selfHostingMode = $parameterBag->get('self_hosting_mode');
		$this->inviteOnlyRegistrationMode = $parameterBag->get('invite_only_registration_mode');
	}

	public function isSelfHosted(): bool
	{
		return $this->selfHostingMode;
	}

	public function isInviteOnlyRegistration(): bool
	{
		return $this->isSelfHosted() && $this->inviteOnlyRegistrationMode;
	}
}
