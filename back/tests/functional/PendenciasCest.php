<?php

use Codeception\Util\HttpCode;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa lista de pendências, pendências em área de Atc e em Atc
 * 
 * [ok] Cria lista de pendencia no atc
 * [ok] Edita lista de pendencia no atc
 * [ok] Exclui lista de pendencia no atc (com pendencia[ñ permite] e sem pendencia)
 * [ok] Cria pendencia na lista de pendencia do atc
 * [ok] Edita pendencia na lista de pendencia do atc
 * [ok] Exclui pendencia na lista de pendencia do atc (com pendencia[ñ permite] e sem pendencia)
 * Cria lista de pendencia na area de atc
 * Edita lista de pendencia na area de atc
 * Exclui lista de pendencia na area de atc (com pendencia[ñ permite] e sem pendencia)
 * Cria pendencia na lista de pendencia na area de atc
 * Edita pendencia na lista de pendencia na area de atc
 * Exclui pendencia na lista de pendencia na area de atc (com pendencia[ñ permite] e sem pendencia)
 * Ao salvar Atc que possui lista de pendencias na area de Atc, criar as pendencias no atc automaticamente [como fazer para recuperar as pendencias?]
 * [ok] Marcar/desmarcar pendencia como realizada (verificando apenas a flag)
 */
class PendenciasCest
{

  private $url_base = '/api/gednasajon';
  private $url_complemento_atcpendencialistas = 'atcspendenciaslistas';
  private $url_complemento_atcpendencia = 'atcspendencias';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  /**
   *
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::PENDENCIAS_INDEX, EnumAcao::PENDENCIAS_CREATE, EnumAcao::PENDENCIAS_PUT, EnumAcao::PENDENCIAS_MARCAR]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    
    $I->deleteAllFromDatabase('crm.atcspendencias');
    $I->deleteAllFromDatabase('crm.atcspendenciaslistas');
  }
  
  private function getAtcpendenciasListaDados($lista){
    return [
      'negociopendencialista' => $lista['negociopendencialista'],
      'nome' => $lista['nome'],
      'tenant' => $lista['tenant'],
    ];
  }
  private function getAtcpendenciaDados($pendencia){
    $dados = [
      'negociopendencia' => isset($pendencia['negociopendencia']) ? $pendencia['negociopendencia'] : null,
      'texto' => $pendencia['texto'],
      'tenant' => $pendencia['tenant'],
      'negociopendencialista' => $pendencia['negociopendencialista']['negociopendencialista'],
    ];
    if(!isset($pendencia['negociopendencia'])) {
      unset($dados['negociopendencia']);
    }
    return $dados;
  }

  /* ----- Testa no Atc ------ */  

  /**
   * @param FunctionalTester $I
   */
  public function criaListaDePendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = [
      'nome' => 'Dados do cliente',
      'tenant' => $this->tenant_numero
    ];

    /* execução da funcionalidade */
    $pendencialista_criado = $I->sendRaw('POST', "{$this->url_base}/{$this->url_complemento_atcpendencialistas}/?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $pendencialista, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $pendencialista['negociopendencialista'] = $pendencialista_criado['negociopendencialista']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $pendenciasDados = $this->getAtcpendenciasListaDados($pendencialista);
    $I->canSeeInDatabase('crm.atcspendenciaslistas', $pendenciasDados);

    /* remove dado criado no banco*/
    $I->deleteFromDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista_criado['negociopendencialista']]);
    $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc['negocio']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaListaDePendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialista($I, $atc);
    $pendencialista['nome'] = 'Dados do responsável';

    /* execução da funcionalidade */
    $pendencialista_editado = $I->sendRaw('PUT', "{$this->url_base}/{$this->url_complemento_atcpendencialistas}/{$pendencialista['negociopendencialista']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $pendencialista, [], [], null);
   
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $pendenciasDados = $this->getAtcpendenciasListaDados($pendencialista);
    $I->canSeeInDatabase('crm.atcspendenciaslistas', $pendenciasDados);

    /* remove dados criados no banco*/
    $I->deleteFromDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
    $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('crm.atcsareas', ['negocioarea' => $atc['area']['negocioarea']]);
  }

  /**
   * @param FunctionalTester $I
   */
//   public function excluiListaDePendenciaVaziaNoNegocio(FunctionalTester $I)
//   {
//     /* inicializações */
//     $origem = $I->haveInDatabaseMidia($I);
//     $area = $I->haveInDatabaseAreaDeNegocio($I);
//     $estado = $I->haveInDataBaseEstado($I);
//     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
//     $pais = $I->haveInDatabasePais($I);
//     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
//     $negocio = $I->haveInDatabaseNegocio($I, $area, $origem, $cliente);
//     $pendencialista = $I->haveInDatabaseNegociopendencialista($I, $negocio);

//     /* execução da funcionalidade */
//     $I->sendRaw('DELETE', "{$this->url_base}/{$this->url_complemento_negociopendencialistas}/{$pendencialista['negociopendencialista']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);
    
//     /* validação do resultado */
//     $I->canSeeResponseCodeIs(HttpCode::OK);
//     $I->cantSeeInDatabase('crm.negociospendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);

//     /* remove dados criados no banco*/
//     $I->deleteFromDatabase('crm.negocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negociosareas', ['negocioarea' => $negocio['area']['negocioarea']]);
//   }

  /**
   * @param FunctionalTester $I
   * @todo melhorar resposta de falha do controller
   */
  public function naoExcluiListaDePendenciaComPendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialistaComPendencia($I, $atc);

    /* execução da funcionalidade */
    $I->sendRaw('DELETE', "{$this->url_base}/{$this->url_complemento_atcpendencialistas}/{$pendencialista['negociopendencialista']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR); /*Péssima resposta */
    $I->canSeeInDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaPendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialista($I, $atc);
    $prioridade = $I->haveInDatabasePrioridade($I);

    $prazoConfiguradoEmMinutos = 60;
    $date = new DateTime('now', new DateTimeZone("America/Bahia"));
    $date->add(new DateInterval('PT'.$prazoConfiguradoEmMinutos.'M'));
    
    $pendencia = [
        'texto' => 'Pendencia 1',
        'negociopendencialista' => ['negociopendencialista' => $pendencialista['negociopendencialista']],
        'negocio' => $atc,
        'prioridade' => ['prioridade' => $prioridade['prioridade']],
        'impeditivo' => 2, //Impeditivo Forcenedor
        'tenant' => $this->tenant_numero,
        'temponotificaexpiracao' => 10,
        'dataprazopendencia' => $date->format('Y-m-d H:i:s')
      ];

    /* execução da funcionalidade */
    $pendencia_criado = $I->sendRaw('POST', "{$this->url_base}/{$this->url_complemento_atcpendencia}/?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $pendencia, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $pendencia['negociopendencia'] = $pendencia_criado['negociopendencia']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $pendenciaDados = $this->getAtcpendenciaDados($pendencia);
    $pendenciaDados['prazo'] = $prazoConfiguradoEmMinutos;
    $I->canSeeInDatabase('crm.atcspendencias', $pendenciaDados);

    /* remove dados criados no banco*/
    $I->deleteFromDatabase('crm.atcspendencias', [
      'negociopendencia' => $pendencia_criado['negociopendencia']
    ]);
    $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
    $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('ns.prioridades', ['prioridade' => $prioridade['prioridade']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function naoCriaPendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialista($I, $atc);
    $prioridade = $I->haveInDatabasePrioridade($I);

    $prazoConfiguradoEmMinutos = 60;
    $date = new DateTime();
    $date->add(new DateInterval('PT'.$prazoConfiguradoEmMinutos.'M'));
    
    $pendencia = [
        'texto' => 'Pendencia 1',
        'negociopendencialista' => ['negociopendencialista' => $pendencialista['negociopendencialista']],
        'negocio' => $atc,
        'prioridade' => ['prioridade' => $prioridade['prioridade']],
        'impeditivo' => 2, //Impeditivo Forcenedor
        'tenant' => $this->tenant_numero,
        'temponotificaexpiracao' => 10,
      ];

    /* execução da funcionalidade */
    $pendencia_criado = $I->sendRaw('POST', "{$this->url_base}/{$this->url_complemento_atcpendencia}/?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $pendencia, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    $pendenciaDados = $this->getAtcpendenciaDados($pendencia);
    $pendenciaDados['dataprazopendencia'] = $date->format('Y-m-d H:i:s');
    $pendenciaDados['prazo'] = $prazoConfiguradoEmMinutos;
    $I->cantSeeInDatabase('crm.atcspendencias', $pendenciaDados);

    /* remove dados criados no banco*/
    $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
    $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('ns.prioridades', ['prioridade' => $prioridade['prioridade']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaPendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialista($I, $atc);
    $prioridade = $I->haveInDatabasePrioridade($I);

    $prioridade = $I->haveInDatabasePrioridade($I);
    $pendencia = $I->haveInDatabaseAtcpendencia($I, $pendencialista, $atc, $prioridade);
    $pendencia['nome'] = 'Pendencia editada';

    $prazoConfiguradoEmMinutos = 60;
    $date = new DateTime('now', new DateTimeZone("America/Bahia"));
    $date->add(new DateInterval('PT'.$prazoConfiguradoEmMinutos.'M'));
    
    $pendencia['negociopendencialista'] = ['negociopendencialista' => $pendencia['negociopendencialista']];
    $pendencia['prioridade'] = ['prioridade' => $pendencia['prioridade']];
    $pendencia['dataprazopendencia'] = $date->format('Y-m-d H:i:s');

    /* execução da funcionalidade */
    $pendencia_editado = $I->sendRaw('PUT', "{$this->url_base}/{$this->url_complemento_atcpendencia}/{$pendencia['negociopendencia']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $pendencia, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.atcspendencias', [
      'negociopendencia' => $pendencia['negociopendencia'],
      'prazo' => $prazoConfiguradoEmMinutos
    ]);

    /* remove dados criados no banco*/
    $I->deleteFromDatabase('crm.atcspendencias', [
      'negociopendencia' => $pendencia['negociopendencia'],
    ]);
    $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
    $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('ns.prioridades', ['prioridade' => $prioridade['prioridade']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function naoEditaPendenciaNoAtc(FunctionalTester $I)
  {
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialista($I, $atc);
    $prioridade = $I->haveInDatabasePrioridade($I);

    $prioridade = $I->haveInDatabasePrioridade($I);
    $pendencia = $I->haveInDatabaseAtcpendencia($I, $pendencialista, $atc, $prioridade);
    $pendencia['nome'] = 'Pendencia editada';

    $prazoConfiguradoEmMinutos = 60;
    $date = new DateTime();
    $date->add(new DateInterval('PT'.$prazoConfiguradoEmMinutos.'M'));
    
    $pendencia['negociopendencialista'] = ['negociopendencialista' => $pendencia['negociopendencialista']];
    $pendencia['prioridade'] = ['prioridade' => $pendencia['prioridade']];

    /* execução da funcionalidade */
    $pendencia_editado = $I->sendRaw('PUT', "{$this->url_base}/{$this->url_complemento_atcpendencia}/{$pendencia['negociopendencia']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $pendencia, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    $I->cantSeeInDatabase('crm.atcspendencias', [
      'negociopendencia' => $pendencia['negociopendencia'],
      'dataprazopendencia' => $date->format('Y-m-d H:i:s'),
      'prazo' => $prazoConfiguradoEmMinutos
    ]);

    /* remove dados criados no banco*/
    $I->deleteFromDatabase('crm.atcspendencias', [
      'negociopendencia' => $pendencia['negociopendencia'],
    ]);
    $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('crm.atcspendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
    $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc['negocio']]);
    $I->deleteFromDatabase('ns.prioridades', ['prioridade' => $prioridade['prioridade']]);
  }

  /**
   * @param FunctionalTester $I
   */
//   public function excluiPendenciaNoNegocio(FunctionalTester $I)
//   {
//     /* inicializações */
//     $origem = $I->haveInDatabaseMidia($I);
//     $area = $I->haveInDatabaseAreaDeNegocio($I);
//     $estado = $I->haveInDataBaseEstado($I);
//     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
//     $pais = $I->haveInDatabasePais($I);
//     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
//     $negocio = $I->haveInDatabaseNegocio($I, $area, $origem, $cliente);
//     $pendencialista = $I->haveInDatabaseNegociopendencialista($I, $negocio);
//     $prioridade = $I->haveInDatabasePrioridade($I);
//     $pendencia = $I->haveInDatabaseNegociopendencia($I, $pendencialista, $negocio, $prioridade);

//     /* execução da funcionalidade */
//     $I->sendRaw('DELETE', "{$this->url_base}/{$this->url_complemento_negociopendencia}/{$pendencia['negociopendencia']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);
    
//     /* validação do resultado */
//     $I->canSeeResponseCodeIs(HttpCode::OK);
//     $I->cantSeeInDatabase('crm.negociospendencias', ['negociopendencia' => $pendencia['negociopendencia']]);

//     /* remove dados criados no banco*/
//     $I->deleteFromDatabase('crm.negociospendencias', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
//     $I->deleteFromDatabase('crm.negociospendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
//     $I->deleteFromDatabase('crm.historiconegocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negociosareas', ['negocioarea' => $negocio['area']['negocioarea']]);
//   }

  /**
   * @param FunctionalTester $I
   * @todo incluir verificação para realizado_por e realizado_em
   */
//   public function marcaPendenciaComoRealizada(FunctionalTester $I)
//   {
//     /* inicializações */
//     $origem = $I->haveInDatabaseMidia($I);
//     $area = $I->haveInDatabaseAreaDeNegocio($I);
//     $estado = $I->haveInDataBaseEstado($I);
//     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
//     $pais = $I->haveInDatabasePais($I);
//     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
//     $negocio = $I->haveInDatabaseNegocio($I, $area, $origem, $cliente);
//     $pendencialista = $I->haveInDatabaseNegociopendencialista($I, $negocio);
//     $prioridade = $I->haveInDatabasePrioridade($I);
//     $pendencia = $I->haveInDatabaseNegociopendencia($I, $pendencialista, $negocio, $prioridade);

//     /* execução da funcionalidade */
//     $I->sendRaw('POST', "{$this->url_base}/{$pendencialista['negociopendencialista']}/{$this->url_complemento_negociopendencia}/{$pendencia['negociopendencia']}/marcar?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);
    
//     /* validação do resultado */
//     $I->canSeeResponseCodeIs(HttpCode::OK); 
//     $pendenciaDados = $this->getNegociopendenciaDados($pendencia);
//     $pendenciaDados['realizada'] = true;
//     $I->canSeeInDatabase('crm.negociospendencias', $pendenciaDados);

//     /* remove dados criados no banco*/
//     $I->deleteFromDatabase('crm.negociospendencias', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
//     $I->deleteFromDatabase('crm.negociospendenciaslistas', ['negociopendencialista' => $pendencialista_criado['negociopendencialista']]);
//     $I->deleteFromDatabase('crm.historiconegocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negociosareas', ['negocioarea' => $negocio['area']['negocioarea']]);

//   }

  /**
   * @param FunctionalTester $I
   * @todo incluir verificação para realizado_por e realizado_em
   */
//   public function desmarcaPendenciaComoRealizada(FunctionalTester $I)
//   {
//     /* inicializações */
//     $origem = $I->haveInDatabaseMidia($I);
//     $area = $I->haveInDatabaseAreaDeNegocio($I);
//     $estado = $I->haveInDataBaseEstado($I);
//     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
//     $pais = $I->haveInDatabasePais($I);
//     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
//     $negocio = $I->haveInDatabaseNegocio($I, $area, $origem, $cliente);
    
//     $pendencialista = $I->haveInDatabaseNegociopendencialista($I, $negocio);
//     $prioridade = $I->haveInDatabasePrioridade($I);
//     $pendencia = $I->haveInDatabaseNegociopendencia($I, $pendencialista, $negocio, $prioridade, true);

//     /* execução da funcionalidade */
//     $I->sendRaw('POST', "{$this->url_base}/{$this->url_complemento_negociopendencia}/{$pendencia['negociopendencia']}/desmarcar?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);

//     /* validação do resultado */
//     $I->canSeeResponseCodeIs(HttpCode::OK); 
//     $pendenciaDados = $this->getNegociopendenciaDados($pendencia);
//     $pendenciaDados['realizada'] = false;
//     $I->canSeeInDatabase('crm.negociospendencias', $pendenciaDados);

//     /* remove dados criados no banco*/
//     $I->deleteFromDatabase('crm.negociospendencias', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
//     $I->deleteFromDatabase('crm.negociospendenciaslistas', ['negociopendencialista' => $pendencialista['negociopendencialista']]);
//     $I->deleteFromDatabase('crm.historiconegocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negocios', ['negocio' => $negocio['negocio']]);
//     $I->deleteFromDatabase('crm.negociosareas', ['negocioarea' => $negocio['area']['negocioarea']]);

//   }
}