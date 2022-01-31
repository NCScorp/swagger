<?php

namespace Nasajon\AppBundle\Service\Financas;

use DateTime;
use Nasajon\MDABundle\Doctrine\Hydrator\EntityHydrator;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Nasajon\MDABundle\Service\Financas\ProjetosService as ParentService;
use GuzzleHttp;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Nasajon\AppBundle\Repository\TenantsRepository;
use Nasajon\MDABundle\Entity\Financas\Projetos;
use Nasajon\MDABundle\Entity\Gp\Tiposprojetos;
use LogicException;

/**
 * Sobreescrito para dar suporte à criação de Projeto diretamente no GP
 */
class ProjetosService extends ParentService
{

  /**
   * 
   * @var \Nasajon\MDABundle\Service\Web\ConfiguracoesService
   */
  protected $webConfiguracoesService;

  /**
   * @var \Nasajon\MDABundle\Service\Web\TiposprojetosService
   */
  protected $tiposProjetosService;

  private $container;
  private $requestStack;

  /**
   * @var Nasajon\AppBundle\Repository\TenantsRepository
   */
  private $tenantsRepository;

  /**
   * Dado o nome da configuração e o tenant, retorna primeira configuração encontrada no sistema CRMWEB.
   * @param string $nomeConfig
   * @param mixed $tenant
   * @return integer
   */
  private function getConfiguracao($nomeConfig, $tenant) {
    $filter = new Filter();
    $filterExpression = $filter->getFilterExpression();
    array_push($filterExpression, new FilterExpression('chave', 'eq', $nomeConfig));
    array_push($filterExpression, new FilterExpression('sistema', 'eq', 'CRMWEB'));
    $filter->setFilterExpression($filterExpression);
    $conf = $this->webConfiguracoesService->findAll($tenant, $filter);
    if (sizeof($conf) > 0) {
      return $conf[0];
    }
    return null;
  }

  /**
   * Sobreescrito para: Web\ConfiguracoesService;
   */
  public function __construct(\Nasajon\MDABundle\Repository\Financas\ProjetosRepository $repository, $webConfiguracoesService, $tiposProjetosService, $container, $requestStack, $tenantsRepository){
    $this->repository = $repository;
    $this->webConfiguracoesService = $webConfiguracoesService;
    $this->tiposProjetosService = $tiposProjetosService;
    $this->container = $container;
    $this->requestStack = $requestStack;
    $this->tenantsRepository = $tenantsRepository;
  }

  /**
   * Verifica se CRM possui integração com GP.
   * @param mixed $tenant
   * @return boolean
   */
  private function possuiIntegracao($tenant) {
    $integracao = $this->getConfiguracao('INTEGRACAO_GP', $tenant);
    if($integracao['valor'] != 1) {
      return false;
    } else {
      return true;
    }
  }

}