<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Segmentosatuacao
 */
class SegmentosatuacaoCest {

  private $url_base = '/api/gednasajon/segmentosatuacao/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::SEGMENTOSATUACAO_INDEX, EnumAcao::SEGMENTOSATUACAO_GET, EnumAcao::SEGMENTOSATUACAO_CREATE, EnumAcao::SEGMENTOSATUACAO_PUT, EnumAcao::SEGMENTOSATUACAO_DELETE]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('crm.negocioscontatos');
    $I->deleteAllFromDatabase('crm.negociostelefones');
    $I->deleteAllFromDatabase('crm.negociospropostasvendedores');
    $I->deleteAllFromDatabase('crm.negocios');
    $I->deleteAllFromDatabase('crm.segmentosatuacao');
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaSegmentosAtuacao(FunctionalTester $I) {

    /* inicializações */
    $listaSegmentosAtuacao = [];
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 2'
    ]);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 3'
    ]);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;

    $promocoesLeads = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $I->assertGreaterOrEquals(count($listaSegmentosAtuacao), count($promocoesLeads));
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaSegmentoDeAtuacao(FunctionalTester $I){

    /* Execução da Funcionalidade */
    $segmento = [
      'codigo' => 'SA123',
      'descricao' => 'Descrição SA123'
    ];
    $segmento_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.segmentosatuacao', ['segmentoatuacao' => $segmento_criado['segmentoatuacao'], 'codigo' => $segmento_criado['codigo']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaSegmentoDeAtuacao(FunctionalTester $I){

    /* Inicializações */
    $segmento = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'SA456'
    ]);

    /* Execução da Funcionalidade */
    $segmento['descricao'] = 'Descrição SA456';
    $I->sendRaw('PUT', $this->url_base . $segmento['segmentoatuacao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.segmentosatuacao', ['segmentoatuacao' => $segmento['segmentoatuacao'], 'descricao' => $segmento['descricao']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiSegmentoDeAtuacao(FunctionalTester $I){

    /* Inicializações */
    $segmento = $I->haveInDatabaseSegmentoAtuacao($I);

    /* Execução da Funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $segmento['segmentoatuacao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.segmentosatuacao', ['segmentoatuacao' => $segmento['segmentoatuacao']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function naoExcluiSegmentoDeAtuacaoRelacionadoANegocio(FunctionalTester $I){

    /* Inicializações */
    $segmento = $I->haveInDatabaseSegmentoAtuacao($I);
    $I->haveInDatabaseNegocioComSegmentoDeAtuacao($I, $segmento);

    /* Execução da Funcionalidade */
    $I->sendRaw('DELETE', $this->url_base . $segmento['segmentoatuacao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
    $I->canSeeInDatabase('crm.segmentosatuacao', ['segmentoatuacao' => $segmento['segmentoatuacao']]);
  }

  /**
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function getSegmentoAtuacao(FunctionalTester $I) {

     /* inicializações */
     $listaSegmentosAtuacao = [];
     $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);
     $listaSegmentosAtuacao[] = $segmentoAtuacao;
     $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 2'
    ]);
     $listaSegmentosAtuacao[] = $segmentoAtuacao;
     $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 3'
    ]);
     $listaSegmentosAtuacao[] = $segmentoAtuacao;
 
     $segmentoatuacaoResponse = $I->sendRaw('GET', $this->url_base . $segmentoAtuacao['segmentoatuacao']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);
 
     /* validação do resultado */
     $I->canSeeResponseCodeIs(HttpCode::OK);
 
     $I->assertEquals($segmentoAtuacao['segmentoatuacao'], $segmentoatuacaoResponse['segmentoatuacao']);
  }

}
