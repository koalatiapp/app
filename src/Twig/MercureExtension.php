<?php

namespace App\Twig;

use App\Entity\User;
use App\Mercure\UserTopicBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MercureExtension extends AbstractExtension
{
	public function __construct(
		private readonly UserTopicBuilder $userTopicBuilder,
	) {
	}

	public function getFunctions(): array
	{
		return [
			new TwigFunction('mercure_topic', $this->getMercureTopic(...)),
			new TwigFunction('mercure_config', $this->getMercureConfig(...)),
		];
	}

	public function getMercureTopic(User $user): string
	{
		return $this->userTopicBuilder->getTopic($user);
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getMercureConfig(User $user): array
	{
		return [
			"subscribe" => $this->getMercureTopic($user),
			"additionalClaims" => [
				"exp" => new \DateTimeImmutable("+1 day"),
			],
		];
	}
}
