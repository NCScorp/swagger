<?php

namespace Nasajon\AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * 
 * @FuncaoProvisao({"USUARIO", "ADMIN"})
 */
class IndexController extends Controller {

  /**
   * @FOS\Get("/globals",  defaults={ "_format" = "json" })
   */
  public function globalsAction(Request $request) {
    $response = new \Symfony\Component\HttpFoundation\JsonResponse(array(
      "tenant"           => null,
      'grupoempresarial' => null
    ));
    $response->setCallback('nsj.globals.setGlobals');
    return $response;
  }

  /**
   *
   * @FOS\Get("/{html5mode}")
   */
  public function indexAction(Request $request) {
    return $this->render('@NasajonMDABundle/Resources/js/index.html', []);
  }

}

