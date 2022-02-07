<?php

use Codeception\Util\HttpCode;

/**
 * Testes de aprovação, execução, reprovação e aprovaçãoExecução.
 */
class SolicitacoesDocumentosCest {

  private $tenant = "gednasajon";
  private $tenant_numero = 47;
  private $conta = "rodrigodirk@nasajon.com.br";
  private $estabelecimento = '39836516-7240-4fe5-847b-d5ee0f57252d'; //origem: dump.sql
  private $id_empresa = '431bc005-9894-4c86-9dcd-7d1da9e2d006'; //origem: dump.sql
  private $id_nivelcargo = 'cb86ab25-76af-4d4a-8577-e2fbfe5aeb81'; //origem: dump.sql
  private $url_base_grupoempresarial = 'gednasajon';
  private $url_base_casouso = 'solicitacoes/salarios';

  /**
   * gednasajon/nasajon/solicitacoesdocumentos/
   */
  private function getUrlBase(){
    $url = "/{$this->tenant}";
    return $url;
  }

  
    /**
     * Executado antes de cada método da classe
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs($this->conta, [], $this->estabelecimento);
        $I->deleteTrabalhador($I, $this->conta, $this->tenant_numero);
    }

    /**
     * Executado depois de cada método da classe
     */
    public function  _after(FunctionalTester $I){
        $I->deleteTrabalhador($I, $this->conta, $this->tenant_numero);
    }

  private function nenhumaPermissao() {
    return [];
  }

  /**
   * Teste para aprovar solicitações de salários sob demanda
   * @param FunctionalTester $I
   */
  public function listarSolicitacoesDeDocumentoDeUmaSolicitacao(FunctionalTester $I)
  {
    // cenario
    $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
    $solicitacao = $I->haveInDatabaseSolicitacao($I, $this->tenant_numero, $this->estabelecimento, $trabalhador['trabalhador']);
    $solicitacaoDocumento = $I->haveInDatabaseSolicitacaoDocumento($I, $this->tenant_numero, $solicitacao["solicitacao"]);

    // funcionalidade testada
    $url = $this->getUrlBase()."/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao['solicitacao']}/documentos";
    $solicitacoesdocumentosretornadas = $I->sendRaw('GET', $url, [], [], [], null);

    // validação do resultado
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount(1, $solicitacoesdocumentosretornadas);
  }
}