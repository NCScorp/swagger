<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa NegociosContatos
 */
class NegociosContatosCest {

  private $url_base = '/api/gednasajon/';
  private $url_negocioscontatos = '/negocioscontatos/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::NEGOCIOS_INDEX, EnumAcao::NEGOCIOS_GET, EnumAcao::NEGOCIOS_CREATE, EnumAcao::NEGOCIOS_PUT, EnumAcao::NEGOCIOS_DELETE, EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO, EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('crm.negociostelefones');
    $I->deleteAllFromDatabase('crm.negocioscontatos');
    $I->deleteAllFromDatabase('crm.historicoatcs');
    $I->deleteAllFromDatabase('crm.atcs');
    $I->deleteAllFromDatabase('crm.historicosnegocios');
    $I->deleteAllFromDatabase('crm.negociospropostasvendedores');
    $I->deleteAllFromDatabase('crm.negocios');
    $I->deleteAllFromDatabase('crm.midiasorigem');
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaNegocioContato(FunctionalTester $I)
  {
    /* inicializações */
    $negocio = $I->haveInDatabaseNegocio($I);

    $negocioContato = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'documento' => ['documento' => $negocio['documento']],
      "nome" => 'Nome do contato',
      "sobrenome" => 'Sobrenome do contato',
      "cargo" => "Sócio/Proprietário/CEO",
      "email" => 'email@do.contato',
      "ddi" => '55',
      "ddd" => '21',
      "telefone" => '987654321',
      "ramal" => '',
    ];

    /* execução da funcionalidade */
    $negocioContato_criado = $I->sendRaw('POST', $this->url_base . $negocio['documento'] . $this->url_negocioscontatos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $negocioContato['id'] = $negocioContato_criado['id']; //colocando chave primária no array original para verificar se todas as informações estão no banco
    $negocioContatoCheckBanco = ['id' => $negocioContato['id']];
    $I->canSeeInDatabase('crm.negocioscontatos', $negocioContatoCheckBanco);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaNegocioContato(FunctionalTester $I)
  {
    /* inicializações */
    $negocioContato = $I->haveInDatabaseNegocioContato($I);

    $negocioContato['sobrenome'] .= ' da Silva';
    /* execução da funcionalidade */ 
    $response = $I->sendRaw('PUT', $this->url_base . $negocioContato['negocio']['documento'] . $this->url_negocioscontatos . $negocioContato['id'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioContatoCheckBanco = [
      'id' => $negocioContato['id'],
      'sobrenome' => $negocioContato['sobrenome'],
    ];
    $I->canSeeInDatabase('crm.negocioscontatos', $negocioContatoCheckBanco);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaTelefoneDoContatoDoNegocio(FunctionalTester $I){

    /* inicializações */
    $negocioContato = $I->haveInDatabaseNegocioContato($I);
    
    $negocioContato['ddd'] = '11';
    $negocioContato['telefone'] = '12123434';
    $negocioContato['ramal'] = '47';

    /* Execução da Funcionalidade */
    $response = $I->sendRaw('PUT', $this->url_base . $negocioContato['negocio']['documento'] . $this->url_negocioscontatos . $negocioContato['id'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $telefoneCheckBanco = [
      'negociocontato' => $negocioContato['id'],
      'ddd' => $negocioContato['ddd'],
      'telefone' => $negocioContato['telefone'],
      'ramal' => $negocioContato['ramal']
    ];

    $I->canSeeInDatabase('crm.negociostelefones', $telefoneCheckBanco);

  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiTelefoneDoContatoDoNegocio(FunctionalTester $I){
    
    /* inicializações */
    $negocioContato = $I->haveInDatabaseNegocioContato($I);

    //Guardando o telefone que será apagado para verificar no banco
    $telefoneCheckBanco = [
      'id' => $negocioContato['id'],
      'ddi' => $negocioContato['ddi'],
      'ddd' => $negocioContato['ddd'],
      'telefone' => $negocioContato['telefone'],
      'ramal' => $negocioContato['ramal']
    ];
    
    //Apagando o telefone
    $negocioContato['ddi'] = '';
    $negocioContato['ddd'] = '';
    $negocioContato['telefone'] = '';
    $negocioContato['ramal'] = '';

    /* Execução da Funcionalidade */
    $response = $I->sendRaw('PUT', $this->url_base . $negocioContato['negocio']['documento'] . $this->url_negocioscontatos . $negocioContato['id'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);

    /* Validação do Resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    //Verificando se o telefone apagado não está mais no banco
    $I->cantSeeInDatabase('crm.negocioscontatos', $telefoneCheckBanco);

  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiNegocioContato(FunctionalTester $I)
  {
    /* inicializações */
    $negocioContato = $I->haveInDatabaseNegocioContato($I);

    /* execução da funcionalidade */
    $response = $I->sendRaw('DELETE', $this->url_base . $negocioContato['negocio']['documento'] . $this->url_negocioscontatos . $negocioContato['id'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    $negocioContatoCheckBanco = [
      'id' => $negocioContato['id'],
    ];
    $I->cantSeeInDatabase('crm.negocioscontatos', $negocioContatoCheckBanco);
  }

}
