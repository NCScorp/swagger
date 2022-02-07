<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use AppBundle\Service\Ns\ConfiguracoesService as ConfiguracoesService;
use AppBundle\Service\PermissoesService as PermissoesService;
use AppBundle\Enum\Ns\configuracoesGeraisEnum as ConfiguracoesGeraisEnum;


class ConfiguracoesVoter extends Voter
{

  const ACOESCONTROLADAS = [
    configuracoesGeraisEnum::SALARIO_SOB_DEMANDA
  ];

  private $fixedAttributes;
  private $diretorio_sistema_id;
  private $token;
  private $configuracoesService;

  public function __construct($diretorio_sistema_id, ConfiguracoesService $configuracoesService, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $token,  ParameterBag $fixedAttributes)
  {
    $this->diretorio_sistema_id = $diretorio_sistema_id;
    $this->fixedAttributes = $fixedAttributes;
    $this->token = $token;
    $this->configuracoesService = $configuracoesService;
  }

  /**
   * @param type $attribute
   * @param type $subject
   * @return bool
   */
  protected function supports($attribute, $subject): bool
  {

    //esse voter só atua se o tenant estiver completamente carregado
    if (empty($this->fixedAttributes->get('tenant'))) {
      return false;
    }

    // apenas algumas actions possuem acesso limitado
    if (!in_array($attribute, self::ACOESCONTROLADAS)) {
      return false;
    }

    return true;
  }

  /**
   * @todo tratar para casos como criar grupo_empresarial (quando cria, dá permissão para o usuário? Ou verifico se ele é super admin e sempre mostro td ?)
   * @param type $attribute codigo da permissao
   * @param TokenInterface $token
   * @return bool
   */
  protected function voteOnAttribute($attribute, $subject, \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token): bool
  {
    $user = $token->getUser();
    $tenant = $this->fixedAttributes->get('tenant');
    
    $moduloshabilitados = array_keys(array_filter($this->configuracoesService->estabelecimentoModulosHabilitados($tenant, $this->fixedAttributes->get('estabelecimento'))));

    if (!is_array($moduloshabilitados)) {
      return false;
    }

    if (!in_array($attribute, $moduloshabilitados)) {
      return false;
    }
    return true;
  }
}
