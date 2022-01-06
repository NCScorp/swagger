<?php

namespace Nasajon\Atendimento\AppBundle\Controller\Cliente;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOS;
use Nasajon\Atendimento\AppBundle\Entity\HelperGlobals;
use Nasajon\MDABundle\Controller\Atendimento\Cliente\ArtigosController as ArtigosParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nasajon\MDABundle\Entity\Atendimento\Cliente\Artigos;
use Nasajon\Atendimento\AppBundle\Security\Authorization\Voter\GlobalsVoter;
use Nasajon\MDABundle\Request\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ArtigosController extends ArtigosParentController {

/**
     * Lists all Atendimento\Cliente\Artigos entities.
     *
     * @FOS\Get("/artigos/")
     * @ParamConverter("filter", class="Filter", converter="filter_converter")
     */
    public function indexAction(Filter $filter = null, Request $request) { 
      if (!$this->getUser()) {
        $this->denyAccessUnlessGranted(GlobalsVoter::ANONYMOUS, new HelperGlobals());
      }

      return parent::indexAction($filter, $request);
    }

  /**
   * Displays a form to create a new Atendimento\Admin\Artigos entity.
   *
   * @FOS\Get("/artigos/template/show.html", defaults={ "_format" = "html" })
   */
  public function templateShowAction(Request $request) {

    return $this->render('NasajonAtendimentoAppBundle:Cliente/Artigos:show.html.twig');
  }

  /**
   *
   * @FOS\Get("/artigos/template/index.html", defaults={ "_format" = "html" })
   */
  public function templateAction(Request $request) {

    if (!$this->getUser()) {
      $this->denyAccessUnlessGranted(GlobalsVoter::ANONYMOUS, new HelperGlobals());
    }
    
    $em = $this->getDoctrine()->getManager();
    $configuracoes = $em->getRepository('Nasajon\ModelBundle\Entity\Configuracoes')->findByTenant($this->get('nasajon_mda.fixed_attributes')->get('tenant'));
    $conf = [];
    foreach ($configuracoes as $configuracao) {
      $conf[$configuracao->getSistema()][$configuracao->getChave()] = $configuracao->getValor();
    }

    return $this->render('NasajonAtendimentoAppBundle:Cliente/Artigos:index.html.twig', array(
                'mensagem' => $conf['ATENDIMENTO']['ARTIGO_DESCRICAO']
    ));
  }

  /**
   * @FOS\Get("/artigos/dashboard", defaults={ "_format" = "json" })
   *
   */
  public function dashboardAction(Request $request) {
    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');

    $categorias = $this->getRepository()->listDashboard($tenant);

    $response = new \Symfony\Component\HttpFoundation\JsonResponse();
    $response->setData(array(
        'categorias' => $categorias
    ));

    return $response;
  }

  /**
   * @FOS\Put("/artigos/{id}/evitouCriacaoChamado")
   */
  public function incrementarQtdeEvitouCriacaoChamadoAction( $id, Request $request) {
    $tenant = $this->get('nasajon_mda.fixed_attributes')->get('tenant');    
    $entity = new Artigos();
    $entity->setArtigo($id);
    
    $response = $this->getRepository()->incrementarQtdeEvitouCriacaoChamado($tenant, $entity);
    return new JsonResponse($response);
  }
    
}
