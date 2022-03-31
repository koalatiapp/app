<?php

namespace App\Controller\Trait;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait PreventDirectAccessTrait
{
	/**
	 * Prevents users from accessing API endpoints directly in their browser.
	 *
	 * This prevents users from clicking on API links from mischievous
	 * people who might that might
	 *
	 * @required
	 * */
	public function preventDirectAccess(RequestStack $requestStack): void
	{
		if (!$requestStack->getCurrentRequest()->isXmlHttpRequest()) {
			throw new BadRequestHttpException("Direct access to internal API endpoints is not allowed.", null, 400);
		}
	}
}
