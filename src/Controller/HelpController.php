<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelpController extends AbstractController
{
	/**
	 * @Route("/help", name="help")
	 */
	public function help(): Response
	{
		return $this->render('app/help.html.twig');
	}
}
