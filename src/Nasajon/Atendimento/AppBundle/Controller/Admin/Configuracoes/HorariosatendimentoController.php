<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin\Configuracoes;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Nasajon\MDABundle\Controller\Atendimento\HorariosatendimentoController as ParentController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @FuncaoProvisao("ADMIN")
 */
class HorariosatendimentoController extends ParentController {

  /**
   * 
   * @FOS\Get("/horariosatendimento/template/form.html", defaults={ "_format" = "html" })     
   */
  public function templateFormAction(Request $request) {
    return $this->render('NasajonAtendimentoAppBundle:Admin/Horariosatendimento:form.html.twig');
  }

  /**
   * Obter o horário de atendimento cadastrado para todas as equipes
   *
   * @FOS\Get("/horariosatendimento", defaults={ "_format" = "json" })
   */
  public function obterHorarioGlobalAction() {
    return new JsonResponse($this->getRepository()->obterHorarioAtendimentoGlobal($this->get('nasajon_mda.fixed_attributes')->get('tenant')));
  }

  /**
   * Este método está sendo sobrescrito para tratar a forma que as horas estão chegando pela requisição.
   *
   * @FOS\Put("/horariosatendimento/{id}", defaults={ "_format" = "json" })
   */
  public function putAction(Request $request, $id) {

    $all = $request->request->all();

    foreach($all as $key => $value) {
      // Caso seja uma data formatada com Timezone, no formato 2020-10-05T08:20:00.000Z, por exemplo,
      // pega somente a hora para enviar para o banco.
      if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{3}Z/', $value)) {
        $request->request->set($key, substr($value, 11, 8));
      }
    }

    return parent::putAction($request, $id);
  }

}