<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\LoginBundle\Security\Authorization\Annotation\FuncaoProvisao;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @FuncaoProvisao({"ADMIN", "USUARIO"})
 */
class EstatisticasArtigoController extends Controller {
  
  /**
   * Efetua a consulta dos dados das estatisticas do artigo
   * 
   * @FOS\Get("/artigos/{artigo}/estatisticas")
   * @FuncaoProvisao({"ADMIN", "USUARIO"})
   */
  public function gerarRelatorioAction($artigo) {
    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
    
    $entities = $this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->estatisticasArtigo($tenant, $artigo);
    $entities = array_map(function($item) {
      $item['comentario'] = mb_convert_encoding($item['comentario'], 'UTF-8', mb_detect_encoding($item['comentario'], "UTF-8, ISO-8859-1, ISO-8859-15", true));
      return $item;
    }, $entities);
    
    $response = new JsonResponse();
    $response->setCharset('UTF-8');
    $response->setData($entities);

    return $response;
  }
  
  /**
   * Efetua a consulta dos totalizadores nas estatisticas do artigo
   * 
   * @FOS\Get("/artigos/{artigo}/estatisticas/totalizadores")
   * @FuncaoProvisao({"ADMIN", "USUARIO"})
   */
  public function gerarTotalizadoresAction($artigo) {
    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');
    return new JsonResponse($this->get('nasajon_atendimento_app_bundle.admin_relatorios_repository')->estatisticasArtigoTotalizadores($tenant, $artigo));
  }
  
}