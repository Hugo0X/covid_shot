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
        // $sumPerDate = array_map('intval', $json->{1}->jour);
        // $sumPerDate = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25,26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102,];
        // $sumPerDate = array_map('intval',$sumPerDate);
        $response = [];
// dd($sumPerDate);
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

        // while ($nbrVaccine != 0) {
        //     $firstDose = $json->{$nbrVaccine}->n_dose1;
        //     $secondDose = $json->{$nbrVaccine}->n_dose2;
        //     $sumPerDate = [];
        //     $i = 0;
        //     while ($i != (count($firstDose))) {//
        //         array_push($sumPerDate, $firstDose[$i] + $secondDose[$i]);
        //         $i++;
        //     }
        //     array_push($response, $sumPerDate);
        //     $nbrVaccine--;
        // }
        // array_unshift($response, $json->noms_vaccins, $json->{1}->jour);

        // --------------------

        // while ($nbrVaccine != 0) {
        //     $firstDose = $json->{$nbrVaccine}->n_dose1;
        //     $secondDose = $json->{$nbrVaccine}->n_dose2;
        //     $i = 0;
        //     while ($i != (count($firstDose))) {
        //         $sumPerDate[$i] = [$sumPerDate[$i].'§' . strval($firstDose[$i] + $secondDose[$i])];
        //         if($nbrVaccine == 1) {
        //             array_push($response, explode('§' , $sumPerDate[$i]));
        //         }
        //         $i++;
        //     }        
        //     $nbrVaccine--;
        // }
        // $vaccineName = $json->noms_vaccins;
        // array_unshift($vaccineName, 'Date');
        // array_unshift($response, $vaccineName);
        // dd($response);
        // return $response;

    }
}