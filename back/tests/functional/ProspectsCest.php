<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Prospects
 */
class ProspectsCest {

  private $url_base = '/api/gednasajon/prospects/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';
  private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', []);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaProspect(FunctionalTester $I)
  {
    /* inicializações */

    $prospect = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'codigo' => "1",
      'cnpj' => "123456789",
      'razaosocial' => "Nome prospect",
      'nomefantasia' => "Nome Fantasia Prospect",
    ];

    /* execução da funcionalidade */
    $prospect_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prospect, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $prospect['prospect'] = $prospect_criado['prospect']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $prospectCheckBanco = ['prospect' => $prospect['prospect']];
    $I->canSeeInDatabase('ns.vw_prospects', $prospectCheckBanco);
  }

}
