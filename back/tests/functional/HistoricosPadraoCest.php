<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Históricos Padrão
 */
class HistoricosPadraoCest
{

  private $url_base = '/api/gednasajon/historicospadrao/';
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
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::HISTORICOSPADRAO_CREATE, EnumAcao::HISTORICOSPADRAO_INDEX, EnumAcao::HISTORICOSPADRAO_PUT, EnumAcao::HISTORICOSPADRAO_GET, EnumAcao::HISTORICOSPADRAO_DELETE]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('crm.historicospadrao');
  }


  /**
   * @param FunctionalTester $I
   */
  public function criaHistoricoPadrao(FunctionalTester $I)
  {

    /* inicializações */
    $historicopadrao = [
        'codigo' => 'HP001',
        'tipo' => 100, //100-Geral, 101-Acompanhamento, 102-Pendencias
        'descricao' => 'Descrição Histórico Padrão',
        'texto' => 'Texto Histórico Padrão',
        'tenant' => '47',
        'id_grupoempresarial' => '95cd450c-30c5-4172-af2b-cdece39073bf'
    ];

    /* execução da funcionalidade */
    $historicopadrao_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.historicospadrao', $historicopadrao);
    $I->assertEquals($historicopadrao['codigo'], $historicopadrao_criado['codigo']);
    $I->assertEquals($historicopadrao['tipo'], $historicopadrao_criado['tipo']);
    $I->assertEquals($historicopadrao['texto'], $historicopadrao_criado['texto']);

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaHistoricoPadrao(FunctionalTester $I)
  {

    /* inicializações */
    $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I);
    $historicopadrao['descricao'] = 'Descrição Editada';

    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url_base . $historicopadrao['historicopadrao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.historicospadrao', $historicopadrao);

  }

  /**
   * @param FunctionalTester $I
   */
  public function deletaHistoricoPadrao(FunctionalTester $I)
  {

    /* inicializações */
    $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I);

    /* execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $historicopadrao['historicopadrao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.historicospadrao', $historicopadrao);

  }

  /**
   * @param FunctionalTester $I
   */
  public function listaHistoricosPadrao(FunctionalTester $I)
  {

    /* inicializações */
    $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I);
    $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I, 'Código 456');
    $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I, 'Código 789');
    $countAtual = $I->grabNumRecords('crm.historicospadrao', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
    
  }

}