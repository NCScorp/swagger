<?php

use Codeception\Util\HttpCode;

/**
 * Testa lista de pendências, pendências em área de negócio e em negócio
 * 
 * [ok] Cria lista de pendencia no negocio
 * [ok] Edita lista de pendencia no negocio
 * [ok] Exclui lista de pendencia no negocio (com pendencia[ñ permite] e sem pendencia)
 * [ok] Cria pendencia na lista de pendencia do negocio
 * [ok] Edita pendencia na lista de pendencia do negocio
 * [ok] Exclui pendencia na lista de pendencia do negocio (com pendencia[ñ permite] e sem pendencia)
 * Cria lista de pendencia na area de negocio
 * Edita lista de pendencia na area de negocio
 * Exclui lista de pendencia na area de negocio (com pendencia[ñ permite] e sem pendencia)
 * Cria pendencia na lista de pendencia na area de negocio
 * Edita pendencia na lista de pendencia na area de negocio
 * Exclui pendencia na lista de pendencia na area de negocio (com pendencia[ñ permite] e sem pendencia)
 * Ao salvar negócio que possui lista de pendencias na area de negócio, criar as pendencias no negocio automaticamente [como fazer para recuperar as pendencias?]
 * [ok] Marcar/desmarcar pendencia como realizada (verificando apenas a flag)
 */
class PrestacaominicipiosCest
{

  private $url_base = '/api/gednasajon';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = "FMA";

  /**
   *
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
    $I->amSamlLoggedInAs('rodrigodirk@nasajon.com.br');
  }

  /**
   * Teste para verificar se a lista de prestação de municipio está obedecendo o filtro por cliente
   * @param FunctionalTester $I
   */
  public function naoListaMunicipiosDePrestacaoDeServicoSePessoamunicipioNaoEstiverLigadoAPessoa(FunctionalTester $I)
  {
    // cenario
    $cliente = $I->haveInDatabaseCliente($I);
    $clienteid = $cliente['cliente'];

    // funcionalidade testada
    $url = "{$this->url_base}/{$clienteid}/pessoasmunicipios/?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}";
    $municipios = $I->sendRaw('GET', $url, [], [], [], null);
    
    // verificação do teste
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount(0, $municipios);
  }

  /**
   * Teste para verificar se a lista de prestação de municipio está obedecendo o filtro por cliente
   * @param FunctionalTester $I
   */
  public function listaMunicipiosDePrestacaoDeServicoSePessoamunicipioEstiverLigadoAPessoa(FunctionalTester $I)
  {
    // cenario
    $cliente = $I->haveInDatabaseCliente($I);
    $municipio = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);

    $clienteid = $cliente['cliente'];
    // funcionalidade testada
    $url = "{$this->url_base}/{$clienteid}/pessoasmunicipios/?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}";
    $municipios = $I->sendRaw('GET', $url, [], [], [], null);

    // verificação do teste
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount(1, $municipios);
    $I->assertEquals($municipio['pessoamunicipio'], $municipios[0]['pessoamunicipio']);
  }


}