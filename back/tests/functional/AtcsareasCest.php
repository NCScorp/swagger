<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Área de Negócios
 */
class AtcsareasCest {

  private $url_base = '/api/gednasajon/atcsareas/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::ATCSAREAS_CREATE, EnumAcao::ATCSAREAS_PUT, EnumAcao::ATCSAREAS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaAreaDeAtc(FunctionalTester $I) {

    $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
    $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);

    /* execução da funcionalidade */
    $area = [
        'nome' => 'Area 1',
        'descricao' => 'Descrição da área 1',
        'estabelecimento' => $estabelecimento,
        'possuiseguradora' => true
    ];
    $area_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $area, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.atcsareas', [
                          'negocioarea' => $area_criada['negocioarea'], 
                          'descricao' => $area['descricao'], 
                          'estabelecimento' => $estabelecimento['estabelecimento'],
                          'possuiseguradora' => $area['possuiseguradora']
    ]);

    /* remove dado criado no banco*/
    $I->deleteFromDatabase('crm.atcsareas', ['negocioarea' => $area_criada['negocioarea']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaAreaDeAtc(FunctionalTester $I) {

    /* inicializações */
    $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
    $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);
    $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I, $estabelecimento);
    $areaDeAtc['estabelecimento'] = $estabelecimento;
    
    /* execução da funcionalidade */
    $areaDeAtc['nome'] = 'Novo nome';
    $areaDeAtc['possuiseguradora'] = false;
    $I->sendRaw('PUT', $this->url_base .$areaDeAtc['negocioarea']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $areaDeAtc, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.atcsareas', [
        'negocioarea' => $areaDeAtc['negocioarea'], 
        'nome' => $areaDeAtc['nome'], 
        'possuiseguradora' => $areaDeAtc['possuiseguradora']
    ]);    
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaAreasdeAtcs(FunctionalTester $I) {

    /* inicializações */
    $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);
    $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);
    $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);
    $countAtual = $I->grabNumRecords('crm.atcsareas', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $areaDeAtc, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }
}
