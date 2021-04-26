<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiGeoController extends AbstractController
{
    private $apiLink = 'https://api-adresse.data.gouv.fr/search/?q=';

    public function getGps($postCode)
    {
        $response = file_get_contents( $this->apiLink . $postCode .'&type=municipality&limit=1');
        $response = json_decode($response);

        $r = $response->features[0]->geometry->coordinates;
        return $r;
    }

    public function isPostCodeExist($postCode)
    {
        $url = $this->apiLink . $postCode .'&type=municipality&limit=1';

        if($this->get_http_response_code($url) != "200"){
            return false;
        }

        $response = file_get_contents($url);
        $response = json_decode($response);

        if ($response->features) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isInseeCodeExist($inseeCode)
    {
        $url = $this->apiLink . $inseeCode .'&type=municipality&limit=1';

        if($this->get_http_response_code($url) != "200"){
            dd('code 404');
            return false;
        }

        $response = file_get_contents($url);
        $response = json_decode($response);

        if ($response->features) {
            return true;
        }
        else {
            // dd('empty');
            return false;
        }
    }

    private function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }

    
}
