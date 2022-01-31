<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Negociosoperacoes
 */
class NegociosoperacoesCest {

  private $url_base = '/api/gednasajon/negociosoperacoes/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::VINCULOS_CREATE, EnumAcao::VINCULOS_PUT, EnumAcao::VINCULOS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaNegociosoperacoes(FunctionalTester $I) {

    /* inicializações */
    $listaNegociosoperacoes = [];
    $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I);
    $listaNegociosoperacoes[] = $negocioOperacao;
    $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I, [
        'codigo' => 'cod 2'
    ]);
    $listaNegociosoperacoes[] = $negocioOperacao;
    $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I, [
        'codigo' => 'cod 3'
    ]);
    $listaNegociosoperacoes[] = $negocioOperacao;

    $promocoesLeads = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(count($listaNegociosoperacoes), count($promocoesLeads));
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function getNegocioOperacao(FunctionalTester $I) {

     /* inicializações */
     $listaNegociosoperacoes = [];
     $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I);
     $listaNegociosoperacoes[] = $negocioOperacao;
     $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I, [
        'codigo' => 'cod 2'
    ]);
     $listaNegociosoperacoes[] = $negocioOperacao;
     $negocioOperacao = $I->haveInDatabaseNegocioOperacao($I, [
        'codigo' => 'cod 3'
    ]);
     $listaNegociosoperacoes[] = $negocioOperacao;
 
     $negocioOperacaoResponse = $I->sendRaw('GET', $this->url_base . $negocioOperacao['proposta_operacao']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
 
     $I->assertEquals($negocioOperacao['proposta_operacao'], $negocioOperacaoResponse['proposta_operacao']);
  }

}
