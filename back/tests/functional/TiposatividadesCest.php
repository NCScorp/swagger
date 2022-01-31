<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Tipos Atividades
 */
class TiposatividadesCest
{
  private $url_base = '/api/gednasajon/tiposatividades/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = 'b4c12f6c-e637-48e3-a858-cf5a04e12603';
  private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 

  /**
   *
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::TIPOSATIVIDADES_INDEX, EnumAcao::TIPOSATIVIDADES_CREATE, EnumAcao::TIPOSATIVIDADES_PUT]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('ns.pessoastiposatividades');
    $I->deleteAllFromDatabase('ns.tiposatividades');
  }


  /**
   * @param FunctionalTester $I
   */
  public function listaTiposAtividades(FunctionalTester $I)
  {
    /* inicializações */
    $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
    $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
    $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
    $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
    $countAtual = $I->grabNumRecords('ns.tiposatividades', ['tenant' => $this->tenant_numero]);
    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoatividade, [], [], null);
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaTipoAtividade(FunctionalTester $I)
  {
      /* inicializações */
    $tipoatividade = [
        'nome' => 'TestagemAvançada',
        'descricao' => 'teste',
        'created_at' => date("Y-m-d H:i:s"),
        'created_by' => '{"nome":"usuario"}',
        'tenant' => $this->tenant_numero,
        'tipo' => 0,
        'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
    ];
    /* execução da funcionalidade */
    $tipoatividade_criado = $I->sendRaw('POST', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoatividade, [], [], null);
     // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('ns.tiposatividades', ['tipoatividade' => $tipoatividade_criado['tipoatividade']]); 
    }

     /**
   * @param FunctionalTester $I
   */
  public function editaTipoAtividade(FunctionalTester $I)
  {
      /* inicializações */
      $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
      $tipoatividade['descricao'] = "editado";
    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url_base . $tipoatividade['tipoatividade'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoatividade, [], [], null);
     // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('ns.tiposatividades', ['tipoatividade' => $tipoatividade['tipoatividade'], 'descricao' => $tipoatividade['descricao']]); 
    }
  
}