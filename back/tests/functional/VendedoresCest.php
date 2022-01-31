<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Vendedores
 */
class VendedoresCest {

  private $url_base = '/api/gednasajon/vendedores/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::VINCULOS_CREATE, EnumAcao::VINCULOS_PUT, EnumAcao::VINCULOS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaVendedores(FunctionalTester $I) {

    $vendedorResponse = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(1, count($vendedorResponse));
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function getVendedor(FunctionalTester $I) {

    $guidVendedor = 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'; //guid do vendedor no dump.sql - ns.pessoas
 
    $vendedorResponse = $I->sendRaw('GET', $this->url_base . $guidVendedor. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
 
    $I->assertEquals($guidVendedor, $vendedorResponse['vendedor_id']);
  }

}
