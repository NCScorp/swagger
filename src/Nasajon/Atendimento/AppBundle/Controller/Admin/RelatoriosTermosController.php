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
class RelatoriosTermosController extends FOSRestController {
    /**
    *
    * @FOS\Get("/termos/")
    */
    public function termosAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        
            $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->termosAceitos($tenant);
            
            $response = new JsonResponse();
            $response->setData($entities);
            
            return $response;
    }

    /**
     *
     * @FOS\Get("/relatorio_csv/")
     */
    public function exportaCsvAction(Request $request) {
        $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
        $dados = null;

            $dados = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->termosAceitos($tenant);

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
            $response->headers->set('Content-Disposition', 'attachment; filename=relatorio-termos-' . date("d-m-Y H:i:s") . '.csv');
        
        return $response;
    }
}