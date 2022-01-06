<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\Atendimento\AppBundle\Form\Admin\RelatoriosRespostaFiltrosType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nasajon\MDABundle\Request\Filter;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class RelatoriosRespostaController extends FOSRestController {
    
    public function createFilterForm($tenant) {
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, RelatoriosRespostaFiltrosType::class, [], array(
                    'method' => "GET",
                    "camposcustomizados" => $camposcustomizados
                ))
                ->getForm();

        return $form;
    }

    /**
     * @FuncaoProvisao({"ADMIN", "USUARIO"})
     */
    public function gerarRelatorioAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->relatorioRespostas($tenant, $form->getData());

            $entities = array_map(function($item) {
                $item['resposta'] = mb_convert_encoding($item['resposta'], 'UTF-8', mb_detect_encoding($item['resposta'], "UTF-8, ISO-8859-1, ISO-8859-15", true));
                return $item;
            }, $entities);

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
        
        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->relatorioRespostasTotalizadores($tenant, $form->getData());
            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }
}
