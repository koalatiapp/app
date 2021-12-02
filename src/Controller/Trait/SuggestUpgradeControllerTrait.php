<?php

namespace App\Controller\Trait;

use Symfony\Component\HttpFoundation\Response;

trait SuggestUpgradeControllerTrait
{
	protected function suggestPlanUpgrade(string $message): Response
	{
		$this->addFlash('info', $message);

		return $this->redirectToRoute('manage_subscription');
	}
}
