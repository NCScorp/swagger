<?php

namespace Nasajon\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\{
    Method,
    Route
};
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PingController extends Controller
// extends BaseController
{
    /**
     * @Route("/{url}")
     * @Method("OPTIONS")
     */
    public function optionsNoBarAction(Request $request)
    {
        return new JsonResponse(["sucessoOptionsNoBarAction" => true], JsonResponse::HTTP_OK);
    }
}
