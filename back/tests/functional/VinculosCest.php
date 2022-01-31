<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Vinculos
 */
class VinculosCest {

  private $url_base = '/api/gednasajon/vinculos/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::VINCULOS_CREATE, EnumAcao::VINCULOS_PUT, EnumAcao::VINCULOS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaVinculo(FunctionalTester $I) {

    /* execução da funcionalidade */
    $vinculo = [
      'nome' => 'Vinculo 1',
      'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
    ];
    $vinculo_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $vinculo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $vinculo['vinculo'] = $vinculo_criado['vinculo']; // colocando chave primária no array original para verificar se todas as informações estão no banco
    $I->canSeeInDatabase('crm.vinculos', $vinculo);

    /* remove dado criado no banco */
    $I->deleteFromDatabase('crm.vinculos', ['vinculo' => $vinculo_criado['vinculo']]);
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function editaVinculo(FunctionalTester $I) {

    /* inicializações */
    $vinculo = $I->haveInDatabaseVinculo($I);

    /* execução da funcionalidade */
    $vinculo['nome'] = 'Nome editado';
    $I->sendRaw('PUT', $this->url_base . $vinculo['vinculo'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $vinculo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.vinculos', $vinculo);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaVinculos(FunctionalTester $I) {

    /* inicializações */
    $vinculo = $I->haveInDatabaseVinculo($I);
    $vinculo = $I->haveInDatabaseVinculo($I);;
    $vinculo = $I->haveInDatabaseVinculo($I);
    $countAtual = $I->grabNumRecords('crm.vinculos', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $vinculo, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

}
