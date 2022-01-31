<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Familias
 */
class FamiliasCest
{

  private $url_base = '/api/gednasajon/familias/';
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
    $I->amSamlLoggedInAs('usuario@nasajon.com.br');
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaFamilias(FunctionalTester $I)
  {
    /* inicializações */
    $familias[] = $I->haveInDatabaseFamilia($I);
    $familias[] = $I->haveInDatabaseFamilia($I, ['codigo' => '456', 'descricao' => 'familia 2', 'valor' => '200.00', 'familiacoringa' => true]);
    $countAtual = $I->grabNumRecords('estoque.familias', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertGreaterOrEquals(count($countAtual), count($lista));
  }

  /**
   * @param FunctionalTester $I
   */
  public function retornaFamilia(FunctionalTester $I)
  {
    /* inicializações */
    $familia = $I->haveInDatabaseFamilia($I);

    /* execução da funcionalidade */
    $familiaRetornada = $I->sendRaw('GET', $this->url_base . $familia['familia'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertEquals($familia['codigo'], $familiaRetornada['codigo']);
    $I->assertEquals($familia['descricao'], $familiaRetornada['descricao']);
    $I->assertEquals($familia['valor'], $familiaRetornada['valor']);
    $I->assertEquals($familia['familiacoringa'], $familiaRetornada['familiacoringa']);

  }

}