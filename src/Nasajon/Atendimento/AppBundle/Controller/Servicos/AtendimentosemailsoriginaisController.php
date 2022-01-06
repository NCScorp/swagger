<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Servicos;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\MDABundle\Controller\Servicos\AtendimentosemailsoriginaisController as ParentController;

class AtendimentosemailsoriginaisController extends ParentController {
    
    public function getAction($id, Request $request) {        
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $response = $this->getRepository()->findOriginal($id, $tenant, $request->get('tipo'));
        return new JsonResponse($response);
    }
    
}