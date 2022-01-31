<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Atcs Configurações Documentos
 */
class AtcsConfiguracoesDocumentosCest {

  private $url_base = '/api/gednasajon/atcsconfiguracoesdocumentos/';
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
  public function criaAtcConfiguracaoDocumento(FunctionalTester $I){

    /* Inicializações */
    $atcconfiguracaodocumento = [
      'tipogeracaoprestadora' => 1,
      'tipogeracaoseguradora' => 2,
      'emailpadrao' => 'Email padrão teste de criação atcs configurações documentos'
    ];

    /* Execução da funcionalidade */
    $atcconfiguracaodocumento_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $atcconfiguracaodocumento, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.atcsconfiguracoesdocumentos', ['emailpadrao' => $atcconfiguracaodocumento['emailpadrao']]);
    $I->assertEquals($atcconfiguracaodocumento['emailpadrao'], $atcconfiguracaodocumento_criada['emailpadrao']);
    $I->assertEquals($atcconfiguracaodocumento['tipogeracaoprestadora'], $atcconfiguracaodocumento_criada['tipogeracaoprestadora']);
    $I->assertEquals($atcconfiguracaodocumento['tipogeracaoseguradora'], $atcconfiguracaodocumento_criada['tipogeracaoseguradora']);

    /* remove a configuração criada */
    $I->deleteFromDatabase('crm.atcsconfiguracoesdocumentos', ['atcconfiguracaodocumento' => $atcconfiguracaodocumento_criada['atcconfiguracaodocumento']]);

  }
  
  /**
   * @param FunctionalTester $I
   */
  public function editaAtcConfiguracaoDocumento(FunctionalTester $I) {

    /* Inicializações */
    $atcconfiguracaodocumento = $I->haveInDatabaseAtcsConfiguracoesDocumentos($I, $this->grupoempresarial_id);

    /* Execução da funcionalidade */
    $atcconfiguracaodocumento['emailpadrao'] = 'Editando email padrão da configuração do documento';
    $I->sendRaw('PUT', $this->url_base . $atcconfiguracaodocumento['atcconfiguracaodocumento']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $atcconfiguracaodocumento, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
     $I->canSeeInDatabase('crm.atcsconfiguracoesdocumentos', ['atcconfiguracaodocumento' => $atcconfiguracaodocumento['atcconfiguracaodocumento'], 'emailpadrao' => $atcconfiguracaodocumento['emailpadrao']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function getAtcConfiguracaoDocumento(FunctionalTester $I) {

    /* Inicializações */
    $atcconfiguracaodocumento = $I->haveInDatabaseAtcsConfiguracoesDocumentos($I, $this->grupoempresarial_id);

    /* Execução da funcionalidade */
    $atcconfiguracaodocumentoRetornada = $I->sendRaw('GET', $this->url_base . $atcconfiguracaodocumento['atcconfiguracaodocumento']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
     $I->assertEquals($atcconfiguracaodocumento['atcconfiguracaodocumento'], $atcconfiguracaodocumentoRetornada['atcconfiguracaodocumento']);

  }

}
