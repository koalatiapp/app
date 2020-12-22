<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard")
     */
    public function overview(): Response
    {
        return $this->render('app/dashboard/index.html.twig', [

        ]);
    }

    /**
     * @Route("/projects", name="projects")
     */
    public function projects(): Response
    {
        return $this->render('app/dashboard/index.html.twig', [

        ]);
    }

    /**
     * @Route("/inbox", name="inbox")
     */
    public function inbox(): Response
    {
        return $this->render('app/dashboard/index.html.twig', [

        ]);
    }

    /**
     * @Route("/whats-new", name="koalati_news")
     */
    public function whatsNew(): Response
    {
        return $this->render('app/dashboard/index.html.twig', [

        ]);
    }
}
