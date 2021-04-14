<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Controller\ApiVaccineController;
use App\Controller\ApiGeoController;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\LineChart;

class InfoController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="app_info_home", methods={"GET"})
     */
    public function index(): Response
    {
        if($this->session->get('nextStep'))
            return $this->redirectToRoute($this->session->get('nextStep'));
        
        return $this->render('info/index.html.twig', [  
        ]);
    }

     /**
     * @Route("/information/inscription", name="app_info_registrationInformation", methods={"GET"})
     */
    public function registrationInformation(): Response
    {
        return $this->render('info/registrationInformation.html.twig', [  
        ]);
    }

    /**
     * @Route("/map", name="app_info_map")
     */
    public function map(): Response
    {
        $this->session->remove('nextStep');
        
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_info_home');
        }

        $api = new ApiGeoController;
        $gps = $api->getGps($this->getUser()->getPostCode());

        return $this->render('info/map.html.twig', [ 'gps' => $gps ]);
    }

    /**
     * @Route("/information/vaccins", name="app_info_vaccineInformation", methods={"GET"})
     */
    public function vaccineInformation(): Response
    {
        $api = new ApiVaccineController;

        $firstDose = $api->getNumberOfVaccinatedOneDose();
        $secondDose = $api->getNumberOfVaccinatedTwoDose();
        $vaccine = $api->getNumberOfVaccinatedPerVaccine();
        $vaccineName = $vaccine[0];
        unset($vaccineName[0]);
        
        $line = new LineChart();

        $line->getData()->setArrayToDataTable(
            $vaccine
        );
        
        // main options
        $line->getOptions()
            ->setTitle('Doses de vaccins administrés par jour')
            ->setCurveType('function')
            ->setLineWidth(4)
        ;
        $line->getOptions()->getAnimation()
            ->setDuration(1500)
            ->setEasing('out')
            ->setStartup(true)
        ;
        $line->getOptions()->getLegend()
            ->setPosition('top')
            ->setAlignment('center')
        ;
        $line->getOptions()->getTitleTextStyle()
            ->setFontSize(25)
            ->setColor('#000091')
            ->setBold(false)
        ;

        // Horizontal options
        $line->getOptions()->getHAxis()
            ->setTextPosition('none') // rather simplify
            ->setMinValue(0)
        ;

        // Vertical options
        $line->getOptions()->getVAxis()
            ->setMinValue(0)
            ->setViewWindowMode(true)
            ->getGridlines()->setColor('transparent')
        ;
        $line->getOptions()->getVAxis()->getViewWindow()->setMin(0);
// dd($line);

        return $this->render('info/vaccineInformation.html.twig', [ 
            'vaccineName' => $vaccineName,
            'firstDose' => $firstDose,
            'secondDose' => $secondDose,
            'chart' => $line,
        ]);
    }
}
