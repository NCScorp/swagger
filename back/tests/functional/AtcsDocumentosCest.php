<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Atcs Documentos
 */
class AtcsDocumentosCest {

  private $url_base = '/api/gednasajon/atcsdocumentos/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('rodrigodirk@nasajon.com.br');
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaAtcsDocumentos(FunctionalTester $I) {

    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $cliente = $I->haveInDatabaseCliente($I);
    $tipoDocumento = $I->haveInDatabaseDocumento($I);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $atcDocumento = $I->haveInDatabaseAtcDocumento($I, $atc, $tipoDocumento, $this->grupoempresarial_id);
    $atcDocumento = $I->haveInDatabaseAtcDocumento($I, $atc, $tipoDocumento, $this->grupoempresarial_id, "arquivo2");
    
    $countAtual = $I->grabNumRecords('crm.atcsdocumentos', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $atcDocumento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);

  }

}
