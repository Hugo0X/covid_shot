<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiVaccineController extends AbstractController
{// 'https://raw.githubusercontent.com/rozierguillaume/vaccintracker/main/data/output/vacsi-fra.json';
    
    private $apiLink = 'https://raw.githubusercontent.com/rozierguillaume/vaccintracker/main/data/output/vacsi-v-fra.json'; 
    
    private function getJsonResponse()
    {
        $response = file_get_contents( $this->apiLink);
        return json_decode($response);
    }

    private function getNumberOfVaccinated($needDose)
    {
        $nbrVaccine = count($this->getJsonResponse()->types_vaccins);
        $response = 0;

        while ($nbrVaccine != 0) {
            $response += end($this->getJsonResponse()->{$nbrVaccine}->$needDose);
            $nbrVaccine--;
        }

        return $response;
    }

    public function getNumberOfVaccinatedOneDose()
    {
        $response = $this->getNumberOfVaccinated('n_cum_dose1');

        return $response;
    }

    public function getNumberOfVaccinatedTwoDose()
    {
        $response = $this->getNumberOfVaccinated('n_cum_dose2');
        
        return $response;
    }

    public function getNumberOfVaccinatedPerVaccine()
    {
        $json = $this->getJsonResponse();

        $nbrVaccine = count($json->types_vaccins);
        $sumPerDate = $json->{1}->jour;
        $response = [];
        
        while ($nbrVaccine != 0) {
            $firstDose = $json->{$nbrVaccine}->n_dose1;
            $secondDose = $json->{$nbrVaccine}->n_dose2;
            $i = 0;
            while ($i != (count($firstDose))) {
                if($nbrVaccine == count($json->types_vaccins))
                    $sumPerDate[$i] = [$sumPerDate[$i], $firstDose[$i] + $secondDose[$i]];
                else
                    array_push($sumPerDate[$i], $firstDose[$i] + $secondDose[$i]);
                
                if($nbrVaccine == 1) 
                    array_push($response, $sumPerDate[$i]);
                
                $i++;
            }   
            $nbrVaccine--;
        }
        $vaccineName = array_reverse($json->noms_vaccins);
        array_unshift($vaccineName, 'Date');
        array_unshift($response, $vaccineName);
        // dd($response);
        return $response;
    }
}