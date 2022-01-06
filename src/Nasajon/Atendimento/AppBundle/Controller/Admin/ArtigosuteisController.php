<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Controller\Atendimento\Admin\ArtigosuteisController as ParentController;

class ArtigosuteisController extends ParentController {
    
    public function getInfoArtigoUtilAction($artigo) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $response = $this->getRepository()->getInfoArtigoUtil($artigo, $tenant);
        return new JsonResponse($response);
    }
    
}