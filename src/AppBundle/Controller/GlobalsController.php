<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Service\ConfiguracoesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use function GuzzleHttp\json_decode;

class GlobalsController extends Controller {
    
    /**
     * @FOS\Get("/globals",  defaults={ "_format" = "json" })
     */
    public function globalsAction(Request $request) {
        $response = new \Symfony\Component\HttpFoundation\JsonResponse(array(
           "tenant"           => "gednasajon",
           'grupoempresarial' => "ns.gruposempresariais"
        //    'acoespermitidas' => getAcoesPermitidas('gednasajon', 304)
        ));
        return $response;
    }

    /**
     * 
     * @return \Nasajon\LoginBundle\Entity\Provisao
     */
    public function getProvisao() {
        return $this->get('nasajon_loginbundle_provisao');
    }

}
