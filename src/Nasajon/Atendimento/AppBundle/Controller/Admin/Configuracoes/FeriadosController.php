<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin\Configuracoes;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\FeriadosController as ParentController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @FuncaoProvisao("ADMIN")
 */
class FeriadosController extends ParentController {
  
  /**
    * 
    * @FOS\Get("/feriados/template/index.html", defaults={ "_format" = "html" })     
    */
  public function templateAction(Request $request) {
    return $this->render('NasajonAtendimentoAppBundle:Admin/Feriados:index.html.twig');
  }
  
  /**
    * 
    * @FOS\Get("/feriados/template/form.html", defaults={ "_format" = "html" })     
    */
  public function templateFormAction(Request $request) {
    return $this->render('NasajonAtendimentoAppBundle:Admin/Feriados:form.html.twig');
  }
}