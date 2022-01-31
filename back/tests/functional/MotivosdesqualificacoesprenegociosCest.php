<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Motivosdesqualificacoesprenegocios
 */
class MotivosdesqualificacoesprenegociosCest {

  private $url_base = '/api/gednasajon/motivosdesqualificacoesprenegocios/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::VINCULOS_CREATE, EnumAcao::VINCULOS_PUT, EnumAcao::VINCULOS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaMotivosDesqualificacoes(FunctionalTester $I) {

    $motivo = $I->haveInDatabaseMotivoDesqualificacao($I);
    $motivo = $I->haveInDatabaseMotivoDesqualificacao($I);

    $motivosResponse = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(2, count($motivosResponse));
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function getMotivoDesqualificacao(FunctionalTester $I) {

    $motivo = $I->haveInDatabaseMotivoDesqualificacao($I);

    $motivoResponse = $I->sendRaw('GET', $this->url_base . $motivo['motivodesqualificacaoprenegocio']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
 
    $I->assertEquals($motivo['motivodesqualificacaoprenegocio'], $motivoResponse['motivodesqualificacaoprenegocio']);
  }

}
