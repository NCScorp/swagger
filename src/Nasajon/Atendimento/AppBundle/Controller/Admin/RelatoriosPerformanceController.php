<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Controller\FOSRestController;
use Nasajon\Atendimento\AppBundle\Form\Admin\RelatoriosFiltrosType;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class RelatoriosPerformanceController extends FOSRestController {


    public function createFilterForm($tenant) {
        $camposcustomizados = $this->get('nasajon_mda.servicos_atendimentoscamposcustomizados_repository')->findAll($tenant);
        $form = $this->get('form.factory')
                ->createNamedBuilder(NULL, RelatoriosFiltrosType::class, [], array(
                    'method' => "GET",
                    "camposcustomizados" => $camposcustomizados
                ))
                ->getForm();

        return $form;
    }

    /**
     *
     * @FOS\Get("/usuario/")
     */
    public function usuarioAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceUsuario($tenant, $form->getData());

            $response = new JsonResponse();
            $response->setData($entities);

            return $response;
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     *
     * @FOS\Get("/cliente/")
     */
    public function clienteAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $form = $this->createFilterForm($tenant);

        $form->handleRequest($request);
        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceCliente($tenant, $form->getData());

            $response = new JsonResponse();

            $response->setData($entities);

            return $response;
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     *
     * @FOS\Get("/campocustomizado/")
     */
    public function campocustomizadoAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $form = $this->createFilterForm($tenant);

        $form->handleRequest($request);
        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceCampocustomizado($tenant, $form->getData());

            $response = new JsonResponse();

            $response->setData($entities);

            return $response;
        } else {
            return $this->handleView($this->view($form, JsonResponse::HTTP_BAD_REQUEST));
        }
    }

    /**
     *
     * @FOS\Get("/fila/")
     */
    public function filaAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');


        $form = $this->createFilterForm($tenant);
        $form->handleRequest($request);
        if ($form->isValid()) {

            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceFila($tenant, $form->getData());

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
            switch ($request->get('relatorio')) {
                case "usuarios": 
                    $dados = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceUsuario($tenant, $form->getData());
                    break;
                case "filas": 
                    $dados = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceFila($tenant, $form->getData());
                    break;
                case "camposcustomizados": 
                    $dados = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceCampocustomizado($tenant, $form->getData());
                    break;
                case "clientes": 
                    $dados = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->performanceCliente($tenant, $form->getData());
                    break;
            }
            
            $dados = array_map(function ($dado) {
                unset($dado['chamados']);
                return $dado;
            }, $dados);

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
            $response->headers->set('Content-Disposition', 'attachment; filename=relatorio-performance-'.$request->get('relatorio').'-'. date("d-m-Y H:i:s") . '.csv');

            return $response;
        } else {
            return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}