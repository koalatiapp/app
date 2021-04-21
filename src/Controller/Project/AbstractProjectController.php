<?php

namespace App\Controller\Project;

use App\Trait\ProjectControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractProjectController extends AbstractController
{
	use ProjectControllerTrait;
}
