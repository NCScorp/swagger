<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Situacoesprenegocios
 */
class SituacoesprenegociosCest {

  private $url_base = '/api/gednasajon/situacoesprenegocios/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::SITUACOESPRENEGOCIOS_INDEX, EnumAcao::SITUACOESPRENEGOCIOS_GET, EnumAcao::SITUACOESPRENEGOCIOS_CREATE,
                                                    EnumAcao::SITUACOESPRENEGOCIOS_PUT, EnumAcao::SITUACOESPRENEGOCIOS_DELETE]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaSituacoesPreNegocios(FunctionalTester $I) {

    /* inicializações */
    $listaSituacoesprenegocios = [];
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I);
    $listaSituacoesprenegocios[] = $situacoesprenegocios;
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 2');
    $listaSituacoesprenegocios[] = $situacoesprenegocios;
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 3');
    $listaSituacoesprenegocios[] = $situacoesprenegocios;

    $promocoesLeads = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(count($listaSituacoesprenegocios), count($promocoesLeads));
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaSituacaoPreNegocio(FunctionalTester $I){

    /* Execução da Funcionalidade */
    $situacao = [
      'codigo' => 'ST123',
      'nome' => 'Nome ST123',
      'cor' => 1
    ];
    $situacao_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $situacao, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.situacoesprenegocios', ['situacaoprenegocio' => $situacao_criada['situacaoprenegocio'], 'codigo' => $situacao_criada['codigo'],
                         'nome' => $situacao_criada['nome'], 'cor' => $situacao_criada['cor']]);

    /* remove dado criado no banco*/
    $I->deleteFromDatabase('crm.situacoesprenegocios', ['situacaoprenegocio' => $situacao_criada['situacaoprenegocio']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaSituacaoPreNegocio(FunctionalTester $I) {

    /* Inicializações */
    $situacaoprenegocio = $I->haveInDatabaseSituacoesprenegocios($I);

    /* Execução da Funcionalidade */
    $situacaoprenegocio['codigo'] = 'Edit Cod';
    $situacaoprenegocio['cor'] = 10;

    $I->sendRaw('PUT', $this->url_base . $situacaoprenegocio['situacaoprenegocio']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $situacaoprenegocio, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.situacoesprenegocios', ['situacaoprenegocio' => $situacaoprenegocio['situacaoprenegocio'], 'codigo' => $situacaoprenegocio['codigo'],
                         'cor' => $situacaoprenegocio['cor']]);  
                        
  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiSituacaoPreNegocio(FunctionalTester $I){

    /* Inicializações */
    $situacaoprenegocio = $I->haveInDatabaseSituacoesprenegocios($I);

    /* Execução da Funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $situacaoprenegocio['situacaoprenegocio'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $situacaoprenegocio, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.situacoesprenegocios', ['situacaoprenegocio' => $situacaoprenegocio['situacaoprenegocio']]);
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function getSituacaoPreNegocio(FunctionalTester $I) {

     /* inicializações */
     $listaSituacoesprenegocios = [];
     $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I);
     $listaSituacoesprenegocios[] = $situacoesprenegocios;
     $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 2');
     $listaSituacoesprenegocios[] = $situacoesprenegocios;
     $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 3');
     $listaSituacoesprenegocios[] = $situacoesprenegocios;
 
     $situacoesprenegociosResponse = $I->sendRaw('GET', $this->url_base . $situacoesprenegocios['situacaoprenegocio']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
 
     $I->assertEquals($situacoesprenegocios['situacaoprenegocio'], $situacoesprenegociosResponse['situacaoprenegocio']);
  }

}
