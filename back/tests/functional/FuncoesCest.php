<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Funções
 */
class FuncoesCest
{

  private $url_base = '/api/gednasajon/funcoes/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';
  private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 

  /**
   *
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::FUNCOES_CREATE, EnumAcao::FUNCOES_INDEX, EnumAcao::FUNCOES_PUT, EnumAcao::FUNCOES_GET, EnumAcao::FUNCOES_DELETE]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('gp.funcoescustos');
    $I->deleteAllFromDatabase('gp.funcoesusuarios');
    $I->deleteAllFromDatabase('gp.funcoes');
  }


  /**
   * @param FunctionalTester $I
   */
  public function criaFuncao(FunctionalTester $I)
  {
    /* inicializações */
    $funcao = [
        'codigo' => '1234',
        'descricao' => 'funcao de teste',
        'tenant' => '47',
        'funcaocoringa' => true
    ];

    /* execução da funcionalidade */
    $funcao_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('gp.funcoes', $funcao);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaFuncao(FunctionalTester $I)
  {
    /* inicializações */
    $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I);
    $funcao['descricao'] = 'editada';
    $funcao['funcaocoringa'] = true;

    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url_base . $funcao['funcao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('gp.funcoes', $funcao);
  }

  /**
   * @param FunctionalTester $I
   */
  public function deletaFuncao(FunctionalTester $I)
  {
    /* inicializações */
    $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I);

    /* execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $funcao['funcao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('gp.funcoes', $funcao);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaFuncoes(FunctionalTester $I)
  {
    /* inicializações */
    $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I);
    $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I, '789');
    $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I, '456');
    $countAtual = $I->grabNumRecords('gp.funcoes', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

}