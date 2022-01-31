<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Tipos de Acionamento
 */
class TiposacionamentosCest
{

  private $url_base = '/api/gednasajon/tiposacionamentos/';
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
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::TIPOSACIONAMENTOS_CREATE, EnumAcao::TIPOSACIONAMENTOS_INDEX, EnumAcao::TIPOSACIONAMENTOS_PUT, EnumAcao::TIPOSACIONAMENTOS_GET, EnumAcao::TIPOSACIONAMENTOS_DELETE]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  // public function _after(FunctionalTester $I)
  // {
  //   $I->deleteAllFromDatabase('crm.tiposacionamentos');
  // }


  /**
   * @param FunctionalTester $I
   */
  public function criaTipoDeAcionamento(FunctionalTester $I)
  {

    /* inicializações */
    $tipoacionamento = [
        'nome' => 'Tipo de Acionamento 1',
        'descricao' => 'Descrição do Tipo de Acionamento 1',
        'tenant' => '47',
        'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
    ];

    /* execução da funcionalidade */
    $tipoacionamento_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.tiposacionamentos', $tipoacionamento);
    $I->assertEquals($tipoacionamento['nome'], $tipoacionamento_criado['nome']);
    $I->assertEquals($tipoacionamento['descricao'], $tipoacionamento_criado['descricao']);

    /* remove tipo de acionamento criado */
    $I->deleteFromDatabase('crm.tiposacionamentos', ['tiposacionamento' => $tipoacionamento_criado['tiposacionamento']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaTipoDeAcionamento(FunctionalTester $I)
  {

    /* inicializações */
    $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I);
    $tipoacionamento['descricao'] = 'Descrição Editada';

    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url_base . $tipoacionamento['tiposacionamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.tiposacionamentos', $tipoacionamento);

  }

  /**
   * @param FunctionalTester $I
   */
  public function deletaTipoDeAcionamento(FunctionalTester $I)
  {

    /* inicializações */
    $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I);

    /* execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $tipoacionamento['tiposacionamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.tiposacionamentos', $tipoacionamento);

  }

  /**
   * @param FunctionalTester $I
   */
  public function listaTiposDeAcionamento(FunctionalTester $I)
  {

    /* inicializações */
    $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I);
    $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I, 'Nome 456');
    $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I, 'Nome 789');
    $countAtual = $I->grabNumRecords('crm.tiposacionamentos', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
    
  }

}