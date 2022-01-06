<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\Atendimento\AppBundle\Form\Admin\RelatoriosBaseConhecimentoFiltrosType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Request\Filter;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class RelatoriosBaseConhecimentoController extends FOSRestController {
    
    public function createFilterForm() {
        $form = $this->get('form.factory')->createNamedBuilder(NULL, RelatoriosBaseConhecimentoFiltrosType::class, [], ['method' => "GET"])->getForm();
        return $form;
    }

    /**
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function gerarRelatorioAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $form = $this->createFilterForm();
        $form->handleRequest($request);
        
        // Pega as condições que vieram da requisição
        $condicoes = $request->query->get('condicoes');

        // Verifica se há uma requisição para data_publicacao com o operador is_null onde o valor seja false.
        if(!empty($condicoes)) {
          $hasNotPublished = array_filter($condicoes, function($condicao) {
            if(isset($condicao['valor'])){
            return $condicao['campo'] == 'data_publicacao' && $condicao['operador'] == 'is_null' && $condicao['valor'] == 'false';
            }
          });
        }

        if ((!$form->isSubmitted() || ($form->isSubmitted() && $form->isValid())) || $hasNotPublished) {
          $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->relatorioBaseConhecimento($tenant, $form->getData());
          $response = new JsonResponse();
          $response->setCharset('UTF-8');
          $response->setData($entities);
          return $response;
        } else {
          return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function gerarTotalizadoresAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $form = $this->createFilterForm();
        $form->handleRequest($request);

         // Pega as condições que vieram da requisição
         $condicoes = $request->query->get('condicoes');

         // Verifica se há uma requisição para data_publicacao com o operador is_null onde o valor seja false.
         if(!empty($condicoes)) {
           $hasNotPublished = array_filter($condicoes, function($condicao) {
            if(isset($condicao['valor'])){
             return $condicao['campo'] == 'data_publicacao' && $condicao['operador'] == 'is_null' && $condicao['valor'] == 'false';
            }
           });
         }
        
        if (!$form->isSubmitted() || ($form->isSubmitted() && $form->isValid()) || $hasNotPublished) {
          return new JsonResponse($this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->relatorioBaseConhecimentoTotalizadores($tenant, $form->getData()));
        } else {
          return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

}
