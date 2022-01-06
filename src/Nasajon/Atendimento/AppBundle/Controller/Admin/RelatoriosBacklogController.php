<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\Atendimento\AppBundle\Form\Admin\RelatoriosBacklogFiltrosType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class RelatoriosBacklogController extends FOSRestController {

    public function createFilterForm($tenant) {
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, RelatoriosBacklogFiltrosType::class, [], array(
                    'method' => "GET",
                    "camposcustomizados" => $camposcustomizados
                ))
                ->getForm();

        return $form;
    }

    /**
     *
     * @FOS\Get("/atribuicao/")
     */
    public function atribuicaoAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');


        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_backlog_repository')->porAtribuicao($tenant, $form->getData());

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     * @FOS\Get("/atribuicao/totalizadores")
     */
    public function atribuicaoTotalizadoresAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_backlog_repository')->porAtribuicaoTotalizadores($tenant, $form->getData());

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }


    /**
     *
     * @FOS\Get("/relatorio_csv/")
     */
    public function exportaCsvAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $dados = null;

        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $dados = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_backlog_repository')->porAtribuicao($tenant, $form->getData());

            $response = new StreamedResponse(function() use($dados) {
                if (ob_get_contents()) {
                    ob_clean();
                }

                if (isset($dados['0'])) {
                    $fp = fopen('php://output', 'w');
                    fputcsv($fp, array_keys($dados['0']));
                    foreach ($dados AS $values) {
                        fputcsv($fp, $values);
                    }
                    fclose($fp);
                }

                if (ob_get_contents()) {
                    ob_flush();
                }
            });

            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
            $response->headers->set('Content-Disposition', 'attachment; filename=relatorio-backlogs-' . date("d-m-Y H:i:s") . '.csv');
        }

        return $response;
    }

}
