<?php

namespace AppBundle\EventListener;

use AppBundle\Repository\Ns\TenantsRepository;
use AppBundle\Service\Persona\TrabalhadoresService;
use Doctrine\ORM\NoResultException;
use Nasajon\MDABundle\Entity\Persona\Trabalhadores;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Nasajon\MDABundle\Request\Filter;
use Nasajon\MDABundle\Request\FilterExpression;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Service\PermissoesService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Service\Web\ConfiguracoesService;
use Exception;
use DateTime;
use DateTimeZone;

class MDAFixedAttributesListener {

  /**
   *
   * @var TokenStorageInterface 
   */
  private $tokenStorage;
  private $fixedAttributes;
  private $tenantRepository;
  private $trabalhadoresService;
  private $permissoesService;
  private $configuracoesService;


  public function __construct(TokenStorageInterface $tokenStorage,ParameterBag $fixedAttributes, TenantsRepository $tenantRepository,
    TrabalhadoresService $trabalhadoresService, PermissoesService $permissoesService, ConfiguracoesService $configuracoesService) {
    $this->tokenStorage = $tokenStorage;
    $this->fixedAttributes = $fixedAttributes;
    $this->tenantRepository = $tenantRepository;
    $this->trabalhadoresService = $trabalhadoresService;
    $this->permissoesService = $permissoesService;
    $this->configuracoesService = $configuracoesService;
  }
  public function onKernelController(FilterControllerEvent $event) {
    if (!is_array($event->getController())) {
      return;
    }

    $controller = $event->getController()[0];

    if ($controller instanceof \Symfony\Bundle\WebProfilerBundle\Controller\ExceptionController || $controller instanceof \FOS\RestBundle\Controller\ExceptionController || $controller instanceof \Symfony\Bundle\TwigBundle\Controller\ExceptionController) {
      return;
    }
    if ($this->tokenStorage->getToken() && ($this->tokenStorage->getToken()->getUser() instanceof \Nasajon\LoginBundle\Security\User\ContaUser)) {
      $logged_user = [
          "nome" => $this->tokenStorage->getToken()->getUser()->getNome(),
          "email" => $this->tokenStorage->getToken()->getUser()->getUsername()
      ];
      $this->fixedAttributes->set('logged_user', $logged_user);
    }

    $request = $event->getRequest();
    $codigo = $request->get('tenant');//pegar tbm da url no caso do index
    $estabelecimento = $request->get('estabelecimento');
    $trabalhador = $request->get('trabalhador');
    $this->fixedAttributes->set('trabalhadorrescindido', false);

    if (!empty($codigo)) {
      
      $tenant = $this->getTenant($codigo);
      $this->fixedAttributes->set('tenant', $tenant['tenant']);
      $this->fixedAttributes->set('tenant_codigo', $tenant['codigo']);
      $this->fixedAttributes->set('estabelecimento', $estabelecimento);
      /* TODO - Remover o acoes_permissoes do fixed Attributes após a refatoração das permissões (Como é feito no Meurh)*/
      $tenantsProfile = $this->tokenStorage->getToken()->getUser()->getTenants();
      $possuiAcessoTenantRequisicao = !empty($tenantsProfile) && !empty($tenantsProfile[$codigo]) ? true : false;
      if($possuiAcessoTenantRequisicao){
        $funcoes = $this->tokenStorage->getToken()->getUser()->getFuncoesByEstabelecimento($codigo, $estabelecimento);
        $acoes_permitidas = $this->permissoesService->getPermissoesByFuncoes($funcoes);
      } else {
        $acoes_permitidas = [];
      }
      $configuracoes = $this->configuracoesService->getConfiguracoesFormatadas($tenant['tenant']);
      $this->fixedAttributes->set('gestores_todos_niveis', ($configuracoes['NOTIFICACAO_GESTORES_TODOS_NIVEIS'] ? 'true' : 'false'));
      $this->fixedAttributes->set('acoes_permitidas', $acoes_permitidas);
      /* TODO - Remover o acoes_permissoes do fixed Attributes após a refatoração das permissões (Como é feito no Meurh)*/
      if($estabelecimento) {
        $this->validateId($estabelecimento);

        if($trabalhador && strcmp($trabalhador, "undefined") !== 0) {
          $trabalhador = $this->getTrabalhador($tenant['tenant'],$estabelecimento ,$logged_user['email'], $trabalhador);
          $this->fixedAttributes->set('trabalhador', $trabalhador['trabalhador']);  
          if(!empty($trabalhador['datarescisao'])) {
            $configuracoes = $this->configuracoesService->getConfiguracoesFormatadas($tenant['tenant']);
            $fusohorario = $configuracoes['TIMEZONE'];
            $hoje =  new DateTime(null, new DateTimeZone($fusohorario));
            $hoje->setTime(0, 0, 0); 
            $datarescisao = new DateTime($trabalhador['datarescisao']);
            $this->fixedAttributes->set('trabalhadorrescindido', $hoje > $datarescisao);
          }
        }
      }
    }
  }

  /**
   * Valida se é uuid no estabelecimento recebido
   */
  private function validateId($id)
  {
      $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
      if (strlen($id) < 36 || !preg_match($UUIDv4, $id)) {
          throw new BadRequestHttpException('Estabelecimento em formato inválido');
      }
  }
  /**
   * Pega o tenant baseado em seu código
   * @param string codigo ex: gednasajon
   * @return array com dados do tenant 
   */
  private function getTenant($codigo){
    try {
      return $this->tenantRepository->findOneByCodigo($codigo);
    } catch (NoResultException $e) {
      throw new NotFoundHttpException(sprintf("Tenant  '%s' não encontrado.", $codigo));
    }
  }
  /**
   * Baseado no email e no estabelecimento carrega o trabalhador
   * @param bigint tenant ex 47
   * @param uuid estabelecimento 
   * @param string identificacaonasajon ex: email@nasajon.com.
   * @return uuid trabalhador
   */
  private function getTrabalhador($tenant, $estabelecimento, $identidadenasajon,$trabalhador){
    $trabalhador = $this->trabalhadoresService->getTrabalhador($tenant, $estabelecimento, $identidadenasajon, $trabalhador);
    if(empty($trabalhador)){
      throw new NotFoundHttpException(sprintf("Trabalhador não encontrado."));
    }
    return $trabalhador;
  }

}
