<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\User;

/**
 * @extends AbstractEntityActivityLogger<User>
 */
class UserLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return User::class;
	}

	public function postPersist(object &$user, ?array $originalData): void
	{
	}

	public function postRemove(object &$user): void
	{
	}

	public function updateProfile(object &$user): void
	{
		$this->log(
			type: "user_profile_update",
			target: $user,
		);
	}

	public function updatePassword(object &$user): void
	{
		$this->log(
			type: "user_password_change",
			target: $user,
		);
	}

	public function updateEmail(object &$user): void
	{
		$this->log(
			type: "user_email_change",
			target: $user,
		);
	}

	public function updateSubscription(object &$user, string $previousPlan, string $newPlan): void
	{
		$this->log(
			type: "user_subscription_change",
			target: $user,
			data: [
				'previousPlan' => $previousPlan,
				'newPlan' => $newPlan,
			]
		);
	}

	public function cancelSubscription(object &$user): void
	{
		$this->log(
			type: "user_subscription_cancel",
			target: $user,
		);
	}

	/**
	 * @param array<string,mixed> $data
	 */
	public function updateApiUsageSettings(object &$user, array $data): void
	{
		$this->log(
			type: "user_api_usage_settings_change",
			target: $user,
			data: $data
		);
	}
}
