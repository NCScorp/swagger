<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Unidades
 */
class UnidadesCest
{
  private $url_base = '/api/gednasajon/unidades/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = 'b4c12f6c-e637-48e3-a858-cf5a04e12603';
  private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 

  /**
   *
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::UNIDADES_INDEX, EnumAcao::UNIDADES_CREATE, EnumAcao::UNIDADES_PUT]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('estoque.unidades');
  }


  /**
   * @param FunctionalTester $I
   */
  public function listaUnidades(FunctionalTester $I)
  {
    /* inicializações */ 
    $unidade = $I->haveInDatabaseUnidade($I);
    $unidade = $I->haveInDatabaseUnidade($I);
    $unidade = $I->haveInDatabaseUnidade($I);
    $unidade = $I->haveInDatabaseUnidade($I);
    $countAtual = $I->grabNumRecords('estoque.unidades', ['tenant' => $this->tenant_numero]);
    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $unidade, [], [], null);
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaUnidade(FunctionalTester $I)
  {
      /* inicializações */
      $unidade = [
        'codigo' => '123',
        'descricao' => 'teste',
        'decimais' => 2,
        'created_at' => date("Y-m-d H:i:s"),
        'created_by' => '{"nome":"usuario"}',
        'tenant' => $this->tenant_numero,
    ];
    /* execução da funcionalidade */
    $unidade_criada = $I->sendRaw('POST', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $unidade, [], [], null);
     // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('estoque.unidades', ['unidade' => $unidade_criada['unidade']]); 
    }

     /**
   * @param FunctionalTester $I
   */
  public function editaUnidade(FunctionalTester $I)
  {
      /* inicializações */
      $unidade = $I->haveInDatabaseUnidade($I);
      $unidade['descricao'] = 'editado';
    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url_base . $unidade['unidade'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $unidade, [], [], null);
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('estoque.unidades', ['unidade' => $unidade['unidade']]); 
    }
  
}