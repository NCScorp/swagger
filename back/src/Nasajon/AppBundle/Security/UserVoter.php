<?php

namespace Nasajon\AppBundle\Security;

use Doctrine\Common\Annotations\Annotation\Enum;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Nasajon\AppBundle\Enum\EnumAcao;

class UserVoter extends Voter
{

  //Adicionar todos os nomes definidos no EnumAcao.php
  const ACOESCONTROLADAS = [
    EnumAcao::ATCS_INDEX,
    EnumAcao::ATCS_CREATE,
    EnumAcao::ATCS_PUT,
    EnumAcao::ATCS_GET,
    EnumAcao::FORNECEDORESENVOLVIDOS_CREATE,
    EnumAcao::FORNECEDORESENVOLVIDOS_DELETE,
    EnumAcao::FORNECEDORES_INDEX,
    EnumAcao::FORNECEDORES_CREATE,
    EnumAcao::FORNECEDORES_PUT,
    EnumAcao::FORNECEDORES_SUSPENDER,
    EnumAcao::FORNECEDORES_ADVERTIR,
    EnumAcao::FORNECEDORES_REATIVAR,
    EnumAcao::FORNECEDORES_GET,
    EnumAcao::FORNECEDORESSUSPENSOS_INDEX,
    EnumAcao::CLIENTES_INDEX,
    EnumAcao::CLIENTES_CREATE,
    EnumAcao::CLIENTES_PUT,
    EnumAcao::COMPOSICOES_INDEX,
    EnumAcao::COMPOSICOES_CREATE,
    EnumAcao::COMPOSICOES_PUT,
    EnumAcao::DOCUMENTOS_INDEX,
    EnumAcao::DOCUMENTOS_CREATE,
    EnumAcao::DOCUMENTOS_PUT,
    EnumAcao::VINCULOS_INDEX,
    EnumAcao::VINCULOS_CREATE,
    EnumAcao::VINCULOS_PUT,
    EnumAcao::MIDIAS_INDEX,
    EnumAcao::MIDIAS_CREATE,
    EnumAcao::MIDIAS_PUT,
    EnumAcao::TIPOSACIONAMENTOS_INDEX,
    EnumAcao::TIPOSACIONAMENTOS_GET,
    EnumAcao::TIPOSACIONAMENTOS_CREATE,
    EnumAcao::TIPOSACIONAMENTOS_PUT,
    EnumAcao::TIPOSACIONAMENTOS_DELETE,
    EnumAcao::ATCSAREAS_INDEX,
    EnumAcao::ATCSAREAS_CREATE,
    EnumAcao::ATCSAREAS_PUT,
    EnumAcao::ORCAMENTOS_CREATE,
    EnumAcao::ORCAMENTOS_APROVAR,
    EnumAcao::ORCAMENTOS_ENVIAR,
    EnumAcao::ORCAMENTOS_REABRIR,
    EnumAcao::UNIDADES_INDEX,
    EnumAcao::UNIDADES_CREATE,
    EnumAcao::UNIDADES_PUT,
    EnumAcao::TIPOSATIVIDADES_INDEX,
    EnumAcao::TIPOSATIVIDADES_CREATE,
    EnumAcao::TIPOSATIVIDADES_PUT,
    EnumAcao::ADVERTENCIAS_ARQUIVAR, 
    EnumAcao::ADVERTENCIAS_EXCLUIR,
    EnumAcao::PROPOSTASCAPITULOS_CREATE,
    EnumAcao::PROPOSTASITENS_CREATE,
    EnumAcao::PROPOSTASITENS_GET,
    EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR,
    EnumAcao::PROPOSTASITENSFUNCOES_CREATE,
    EnumAcao::PROPOSTASITENSFAMILIAS_CREATE,
    EnumAcao::FUNCOES_INDEX,
    EnumAcao::FUNCOES_CREATE,
    EnumAcao::FUNCOES_PUT,
    EnumAcao::FUNCOES_GET,
    EnumAcao::FUNCOES_DELETE,
    EnumAcao::HISTORICOSPADRAO_INDEX,
    EnumAcao::HISTORICOSPADRAO_CREATE,
    EnumAcao::HISTORICOSPADRAO_PUT,
    EnumAcao::HISTORICOSPADRAO_GET,
    EnumAcao::HISTORICOSPADRAO_DELETE,
    EnumAcao::MALOTES_INDEX,
    EnumAcao::MALOTES_CREATE,
    EnumAcao::MALOTES_PUT,
    EnumAcao::MALOTES_ENVIAR,
    EnumAcao::MALOTES_CANCELARENVIO,
    EnumAcao::MALOTES_APROVAR,
    EnumAcao::PENDENCIAS_INDEX,
    EnumAcao::PENDENCIAS_CREATE,
    EnumAcao::PENDENCIAS_PUT,
    EnumAcao::PENDENCIAS_DELETE,
    EnumAcao::PENDENCIAS_MARCAR,
    EnumAcao::NEGOCIOS_INDEX,
    EnumAcao::NEGOCIOS_GET,
    EnumAcao::NEGOCIOS_CREATE,
    EnumAcao::NEGOCIOS_PUT,
    EnumAcao::NEGOCIOS_DELETE,
    EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO,
    EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO,
    EnumAcao::FOLLOWUPSNEGOCIOS_INDEX,
    EnumAcao::FOLLOWUPSNEGOCIOS_CREATE,
    EnumAcao::SEGMENTOSATUACAO_INDEX,
    EnumAcao::SEGMENTOSATUACAO_GET,
    EnumAcao::SEGMENTOSATUACAO_CREATE,
    EnumAcao::SEGMENTOSATUACAO_PUT,
    EnumAcao::SEGMENTOSATUACAO_DELETE,
    EnumAcao:: SITUACOESPRENEGOCIOS_INDEX,
    EnumAcao:: SITUACOESPRENEGOCIOS_GET,
    EnumAcao:: SITUACOESPRENEGOCIOS_CREATE,
    EnumAcao:: SITUACOESPRENEGOCIOS_PUT,
    EnumAcao:: SITUACOESPRENEGOCIOS_DELETE,
    EnumAcao:: PROMOCOESLEADS_INDEX,
    EnumAcao:: PROMOCOESLEADS_GET,
    EnumAcao:: PROMOCOESLEADS_CREATE,
    EnumAcao:: PROMOCOESLEADS_PUT,
    EnumAcao:: PROMOCOESLEADS_DELETE,
    EnumAcao:: LISTADAVEZVENDEDORES_INDEX,
    EnumAcao:: LISTADAVEZVENDEDORES_GET,
    EnumAcao:: LISTADAVEZVENDEDORES_CREATE,
    EnumAcao:: LISTADAVEZVENDEDORES_PUT,
    EnumAcao:: LISTADAVEZVENDEDORES_DELETE,
    EnumAcao::LISTADAVEZCONFIGURACOES,
    EnumAcao::RELATORIOS_PAINEL_MARKETING,
    EnumAcao::CIDADESINFORMACOESFUNERARIAS_INDEX,
    EnumAcao::CIDADESINFORMACOESFUNERARIAS_GET,
    EnumAcao::CIDADESINFORMACOESFUNERARIAS_CREATE,
    EnumAcao::CIDADESINFORMACOESFUNERARIAS_PUT,
    EnumAcao::CIDADESINFORMACOESFUNERARIAS_PRESTADORES,
    EnumAcao::PRIORIDADES_INDEX,
    EnumAcao::PRIORIDADES_GET,
    EnumAcao::PRIORIDADES_CREATE,
    EnumAcao::PRIORIDADES_PUT,
    EnumAcao::PRIORIDADES_DELETE,
    EnumAcao::CFGTAXASADM_GERENCIAR,
    EnumAcao::CONTRATOSTAXASADM_GERENCIAR,
    EnumAcao::ATCSCONTASPAGAR_GERENCIAR
  ];

  private $fixedAttributes;
  private $diretorio_sistema_id;
  private $token;

  public function __construct($diretorio_sistema_id, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $token,  ParameterBag $fixedAttributes)
  {
    $this->diretorio_sistema_id = $diretorio_sistema_id;
    $this->fixedAttributes = $fixedAttributes;
    $this->token = $token;
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

    $user = $this->token->getToken()->getUser();
    $acoes_permitidas = $user->getAcoesPermitidas(
      $this->fixedAttributes->get('tenant_codigo'),
      $this->diretorio_sistema_id
    );

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

    $acoes_permitidas = $user->getAcoesPermitidas(
      $this->fixedAttributes->get('tenant_codigo'),
      $this->diretorio_sistema_id
    );

    if (!is_array($acoes_permitidas)) {
      return false;
    }

    if (!in_array($attribute, $acoes_permitidas)) {
      return false;
    }
    return true;
  }
}