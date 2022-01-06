<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use FOS\RestBundle\Controller\Annotations as FOS;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TermosController extends Controller {

  /**
   *
   * @return \Nasajon\MDABundle\Repository\Atendimento\Cliente\TermosRepository;
   */
  public function getRepository() {
    return $this->get('nasajon_atendimento_app_bundle.termos_repository');
  }

  /**
   *
   * @FOS\Get("/termos/texto", defaults={ "_format" = "json" })     
   */
  public function textoAction(Request $request) {
    try {
      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
      $configuracoesService = $this->get('modelbundle.service.configuracoes');
      $configuracoes = $configuracoesService->get($tenant, 'ATENDIMENTO');

      $result = $configuracoes['TERMO_TEXTO'];

      return new JsonResponse($result);
    } catch (\Exception $e) {
      throw $this->createNotFoundException();
    }
  }

  /**
   *
   * @FOS\Post("/termos/aceitar", defaults={ "_format" = "json" })     
   */
  public function aceitarAction(Request $requet) {
    try {
      $logged_user = $this->get('nasajon_mda.fixed_attributes')->get('logged_user');
      $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

      $result = $this->getRepository()->termoAceitar($tenant, $logged_user);

      return new JsonResponse($result);
    } catch (\Exception $e) {
      throw $this->createNotFoundException();
    }
  }

}
