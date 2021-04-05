<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\ApiGeoController;

/**
 * @Route("/user")
*/
class UserController extends AbstractController
{
    /**
     * @Route("/", name="app_user")
     */
    public function index(): Response
    {  
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/map", name="app_user_map")
     */
    public function map(): Response
    {
        $api = new ApiGeoController;
        $gps = $api->getGps($this->getUser()->getPostCode());

        return $this->render('user/map.html.twig', [ 'gps' => $gps ]);
    }
}
