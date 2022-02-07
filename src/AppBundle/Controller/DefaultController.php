<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    
    // public function indexAction(Request $request)
    // {   
    //     return $this->render('@NasajonMDABundle/Resources/js/index.html', []);                
    // }

    public function optionsAction(Request $request)
    {
        return new JsonResponse(["sucessoOptionsNoBarAction" => true], JsonResponse::HTTP_OK);
    }
}
