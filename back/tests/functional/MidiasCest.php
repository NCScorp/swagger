<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Mídias
 */
class MidiasCest {

  private $url_base = '/api/gednasajon/midias/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::MIDIAS_CREATE, EnumAcao::MIDIAS_PUT, EnumAcao::MIDIAS_INDEX]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('crm.midiasorigem');
  }

  /**
   * Grava temporariamente área de negócio no banco para auxiliar o teste
   * @param FunctionalTester $I
   * @return type
   */
  private function haveInDatabaseMidia(FunctionalTester $I, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf']) {
    $midia = [
        'midiaorigem' => $I->generateUuidV4(),
        'codigo' => 'Midia 1',
        'descricao' => 'Descricao da mídia 1',
        'created_at' => date('Y-m-d'),
        'created_by' => '{"nome":"usuario"}',
        'updated_by' => '{"nome":"usuario"}',
        'updated_at' => date('Y-m-d'),
        'tenant' => $this->tenant_numero,
        'id_grupoempresarial' => $id_grupoempresarial['id_grupoempresarial']
    ];
    $I->haveInDatabase('crm.midiasorigem', $midia);
    unset($midia['created_at']);
    unset($midia['created_by']);
    unset($midia['updated_at']);
    unset($midia['updated_by']);
    return $midia;
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaMidia(FunctionalTester $I) {

    /* execução da funcionalidade */
    $midia = [
        'nome' => 'Midia 1',
        'descricao' => 'Descrição da mídia 1'
    ];
    $midia_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $midia, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);

    /* adaptando */
    $midia['codigo'] = $midia['nome'];
    unset($midia['nome']);

    $midia['midiaorigem'] = $midia_criada['midia']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $I->canSeeInDatabase('crm.midiasorigem', $midia);

    /* remove dado criado no banco*/
    $I->deleteFromDatabase('crm.midiasorigem', ['midiaorigem' => $midia_criada['midia']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaMidia(FunctionalTester $I) {

    /* inicializações */
    $midia = $this->haveInDatabaseMidia($I);
    
    /* execução da funcionalidade */
    $midia['nome'] = 'Novo nome';
    $I->sendRaw('PUT', $this->url_base .$midia['midiaorigem']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $midia, [], [], null);

    $midia['codigo'] = $midia['nome'];
    unset($midia['nome']);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.midiasorigem', $midia);    
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaMidias(FunctionalTester $I) {

    /* inicializações */
    $midia = $I->haveInDatabaseMidia($I);
    $dados['codigo'] = 'midia 2';
    $midia = $I->haveInDatabaseMidia($I, $dados);
    $dados['codigo'] = 'midia 3';
    $midia = $I->haveInDatabaseMidia($I, $dados);
    $countAtual = $I->grabNumRecords('crm.midiasorigem', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $midia, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

}
