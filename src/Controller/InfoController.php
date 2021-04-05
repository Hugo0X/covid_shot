<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InfoController extends AbstractController
{
    /**
     * @Route("/", name="app_info_home")
     */
    public function index(): Response
    {
        return $this->render('info/index.html.twig', [  
        ]);
    }
}
