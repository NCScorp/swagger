<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Advertencias
 */
class AdvertenciasCest
{
  private $url_base = '/api/gednasajon/advertencias/';
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
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::ADVERTENCIAS_ARQUIVAR, EnumAcao::ADVERTENCIAS_EXCLUIR]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('ns.advertencias');
  }


  /**
   * @param FunctionalTester $I
   */
  public function arquivaAdvertencia(FunctionalTester $I)
  {
    /* inicializações */
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $advertencia = $I->haveInDatabaseAdvertencia($I, $fornecedor);
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url_base . $advertencia['advertencia'] . '/arquivar'.'?tenant=' . $this->tenant.'&grupoempresarial='.$this->grupoempresarial, $advertencia, [], [], null);
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $advertencia['status'] = 1; //Em caso de arquivamento, status muda para 1.
    $I->canSeeInDatabase('ns.advertencias', ['advertencia' => $advertencia['advertencia'], "status" => $advertencia['status']]); 
  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiAdvertencia(FunctionalTester $I)
  {
    /* inicializações */
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $advertencia = $I->haveInDatabaseAdvertencia($I, $fornecedor);
    $advertencia['motivoremocao'] = 'teste';
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url_base . $advertencia['advertencia'] . '/excluir'.'?tenant=' . $this->tenant.'&grupoempresarial='.$this->grupoempresarial, $advertencia, [], [], null);
     // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $advertencia['status'] = 2; //Em caso de exclusão, status muda para 2.
    $I->canSeeInDatabase('ns.advertencias', ['advertencia' => $advertencia['advertencia'], "status" => $advertencia['status']]); 
    }
  
}
