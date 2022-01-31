<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa FollowupsNegocios
 */
class FollowupsNegociosCest {

  private $url_base = '/api/gednasajon/followupsnegocios/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::FOLLOWUPSNEGOCIOS_INDEX, EnumAcao::FOLLOWUPSNEGOCIOS_CREATE]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('ns.followups');
    $I->deleteAllFromDatabase('crm.historicoatcs');
    $I->deleteAllFromDatabase('crm.atcs');
    $I->deleteAllFromDatabase('crm.historicosnegocios');
    $I->deleteAllFromDatabase('crm.negociospropostasvendedores');
    $I->deleteAllFromDatabase('crm.negocioscontatos');
    $I->deleteAllFromDatabase('crm.negociostelefones');
    $I->deleteAllFromDatabase('crm.negocios');
    $I->deleteAllFromDatabase('crm.midiasorigem');
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaFollowUpNegocio(FunctionalTester $I)
  {
    /* inicializações */
    $negocio = $I->haveInDatabaseNegocio($I);

    $followUpNegocio = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'proposta' => ['documento' => $negocio['documento']],
      "historico" => 'Historico registrado',
      "participante" => null,//cliente
      "receptor" => '1',
      "meiocomunicacao" => '2',
      "figuracontato" => '3',
    ];

    /* execução da funcionalidade */
    $followUpNegocio_criado = $I->sendRaw('POST', "/api/gednasajon/$negocio[documento]/followupsnegocios/" . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $followUpNegocio, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $followUpNegocio['followup'] = $followUpNegocio_criado['followup']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $followUpNegocioCheckBanco = ['followup' => $followUpNegocio['followup']];
    $I->canSeeInDatabase('ns.followups', $followUpNegocioCheckBanco);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaFollowupsNegocio(FunctionalTester $I){

    /* Inicializações */
    $negocio = $I->haveInDatabaseNegocio($I);
    $follow1 = $I->haveInDatabaseFollowupNegocio($I, $negocio['documento'], 'Histórico 1');
    $follow2 = $I->haveInDatabaseFollowupNegocio($I, $negocio['documento']);
    $countAtual = $I->grabNumRecords('ns.followups', ['proposta' => $negocio['documento'], 'tenant' => $this->tenant_numero]);

    /* Execução da funcionalidade */
    $lista = $I->sendRaw('GET', "/api/gednasajon/$negocio[documento]/followupsnegocios/" . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* Validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

}
