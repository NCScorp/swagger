<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Campanhas de Origem
 */
class PromocoesleadsCest {

  private $url_base = '/api/gednasajon/promocoesleads/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::PROMOCOESLEADS_INDEX, EnumAcao::PROMOCOESLEADS_GET, EnumAcao::PROMOCOESLEADS_CREATE, EnumAcao::PROMOCOESLEADS_PUT, EnumAcao::PROMOCOESLEADS_DELETE]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaCampanhasDeOrigem(FunctionalTester $I) {

    /* inicializações */
    $listaPromocoes = [];
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);
    $listaPromocoes[] = $promocaoLead;
    $promocaoLead = $I->haveInDatabasePromocaoLead($I, [
        'codigo' => 'cod 2'
    ]);
    $listaPromocoes[] = $promocaoLead;
    $promocaoLead = $I->haveInDatabasePromocaoLead($I, [
        'codigo' => 'cod 3'
    ]);
    $listaPromocoes[] = $promocaoLead;

    $promocoesLeads = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(count($listaPromocoes), count($promocoesLeads));
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function exibeCampanhaDeOrigem(FunctionalTester $I) {

     /* inicializações */
     $listaPromocoes = [];
     $promocaoLead = $I->haveInDatabasePromocaoLead($I);
     $listaPromocoes[] = $promocaoLead;
     $promocaoLead = $I->haveInDatabasePromocaoLead($I, [
        'codigo' => 'cod 2'
    ]);
     $listaPromocoes[] = $promocaoLead;
     $promocaoLead = $I->haveInDatabasePromocaoLead($I, [
        'codigo' => 'cod 3'
    ]);
     $listaPromocoes[] = $promocaoLead;
 
     $promocaoLeadResponse = $I->sendRaw('GET', $this->url_base . $promocaoLead['promocaolead']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
 
     $I->assertEquals($promocaoLead['promocaolead'], $promocaoLeadResponse['promocaolead']);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaCampanhaDeOrigem(FunctionalTester $I){

    /* Inicializações */
    $promocao = [
      "codigo" => 'PromoCodigo',
      "nome" => 'PromoNome',
      "bloqueado" => false
    ];
    
    /* Execução da funcionalidade */
    $promocao_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $promocao, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.promocoesleads', ['promocaolead' => $promocao_criada['promocaolead'], 'codigo' => $promocao_criada['codigo'],
                         'nome' => $promocao_criada['nome'], 'bloqueado' => $promocao_criada['bloqueado']]);

    /* remove dado criado no banco*/
    $I->deleteFromDatabase('crm.promocoesleads', ['promocaolead' => $promocao_criada['promocaolead']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaCampanhaDeOrigem(FunctionalTester $I){

    /* Inicializações */
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);

    /* Execução da funcionalidade */
    $promocaoLead['codigo'] = 'CodEditado';
    $promocaoLead['nome'] = 'NomeEditado';
    $promocaoLead['bloqueado'] = true;

    $I->sendRaw('PUT', $this->url_base . $promocaoLead['promocaolead']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $promocaoLead, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.promocoesleads', ['promocaolead' => $promocaoLead['promocaolead'], 'codigo' => $promocaoLead['codigo'],
                         'nome' => $promocaoLead['nome'], 'bloqueado' => $promocaoLead['bloqueado']]); 

  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiCampanhaDeOrigem(FunctionalTester $I){

    /* Inicializações */
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);

    /* Execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $promocaoLead['promocaolead']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $promocaoLead, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.promocoesleads', ['promocaolead' => $promocaoLead['promocaolead']]);
  }

}
