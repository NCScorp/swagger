<?php

use Codeception\Util\HttpCode;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Malotes
 */
class MalotesCest
{

  private $url_base = '/api/gednasajon/malotes/';
  private $tenant = 'gednasajon';
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  /**
   *
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::MALOTES_INDEX, EnumAcao::MALOTES_CREATE, EnumAcao::MALOTES_PUT, EnumAcao::MALOTES_ENVIAR, EnumAcao::MALOTES_CANCELARENVIO, EnumAcao::MALOTES_APROVAR]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
  }

   /**
   * @param FunctionalTester $I
   */
  public function criaMalote(FunctionalTester $I)
  {
    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
      $malote = [
      'dtenvio' => '2019-09-12',
      'codigo' => 'meow',
      'tenant' => $this->tenant_numero,
      'requisitantecliente'=> $cliente
      ];

    /* execução da funcionalidade */
    $malote_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $malote, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.malotes', ['malote' => $malote_criado['malote']]); //verificando se está no banco

    /* Apagando dado do banco */
    $I->deleteFromDatabase('crm.malotes', ['malote' => $malote_criado['malote']]);

  }

   /**
   * @param FunctionalTester $I
   */
  public function editaMalote(FunctionalTester $I)
  {
    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMalote($I, $cliente['cliente']);

    //Objeto que será enviado na requisição, o cliente precisa ser um objeto
    $malote['requisitantecliente'] = $cliente;

    /* execução da funcionalidade */
    $malote['codigo'] = 'Código Editado';
    $I->sendRaw('PUT', $this->url_base . $malote['malote'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $malote, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote']]);

  }

   /**
   * @param FunctionalTester $I
   */
  public function enviaMalote(FunctionalTester $I)
  {
    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMalote($I, $cliente['cliente']);

    //Objeto enviado na requisição
    $malote_enviado = [
      'enviodata' => '2020-12-25',
      'enviomodal' => 2,
      'malote' => $malote['malote']
    ];

    /* execução da funcionalidade */
    $I->sendRaw('POST',  $this->url_base . $malote_enviado['malote'] . '/maloteEnviar?' . 'grupoempresarial='.$this->grupoempresarial, $malote_enviado, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote'], "status" => 1]); //Verificando se o status mudou para 1 (enviado)

  }

  /**
   * @param FunctionalTester $I
   */
  public function editaEnvioDoMalotePorEmail(FunctionalTester $I){

    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'],2);

    /* execução da funcionalidade */
    $dados_envio = [
      'enviodata' => '2030-03-06', //Editando data de envio
      'enviomodal' => $malote['enviomodal'],
      'malote' => $malote['malote']
    ];

    $I->sendRaw('POST',  $this->url_base . $dados_envio['malote'] . '/maloteEditarEnvio?' . 'grupoempresarial='.$this->grupoempresarial, $dados_envio, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote'], "enviodata" => $dados_envio['enviodata'], "dtenvio" => $dados_envio['enviodata']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaEnvioEmMaosDoMalote(FunctionalTester $I){

    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 0);

    /* execução da funcionalidade */
    $dados_envio = [
      'enviomodal' => $malote['enviomodal'],
      'enviorecebidoporcargo' => 'Cargo receptor editado',
      'enviorecebidopornome' => 'Nome receptor editado',
      'enviorecebimentodata' => '2030-03-06',
      'malote' => $malote['malote']
    ];

    $I->sendRaw('POST',  $this->url_base . $dados_envio['malote'] . '/maloteEditarEnvio?' . 'grupoempresarial='.$this->grupoempresarial, $dados_envio, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote'], "enviorecebimentodata" => $dados_envio['enviorecebimentodata'], "dtenvio" => $dados_envio['enviorecebimentodata'],
                                         "enviorecebidopornome" => $dados_envio['enviorecebidopornome'], "enviorecebidoporcargo" => $dados_envio['enviorecebidoporcargo']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaEnvioPorCorreioDoMalote(FunctionalTester $I){

    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 1);

    /* execução da funcionalidade */
    $dados_envio = [
      'enviocodigorastreio' => 'XYZ987RST',
      'enviodata' => '2030-03-06',
      'enviomodal' => $malote['enviomodal'],
      'malote' => $malote['malote']
    ];

    $I->sendRaw('POST',  $this->url_base . $dados_envio['malote'] . '/maloteEditarEnvio?' . 'grupoempresarial='.$this->grupoempresarial, $dados_envio, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote'], "enviocodigorastreio" => $dados_envio['enviocodigorastreio'], "dtenvio" => $dados_envio['enviodata'],
                                         "enviodata" => $dados_envio['enviodata']]);
  }
  
   /**
   * @param FunctionalTester $I
   */
  public function cancelaEnvioMalote(FunctionalTester $I)
  {
    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 2);

    //Objeto enviado na requisição
    $malote_enviado = [
      'malote' => $malote['malote'],
      'dtenvio' => $malote['dtenvio'],
      'dtresposta' => null,
      'codigo' => $malote['codigo'],
      'created_at' => $malote['created_at'],
      'created_by' => $malote['created_by'],
      'updated_at' => $malote['updated_at'],
      'updated_by' => $malote['updated_by'],
      "requisitantenome" => null,
      "requisitantecargo" => null,
      "requisitanteobservacoes" => null,
      "status" => $malote['status'],
      "tenant" => $this->tenant_numero,
      "enviomodal" => $malote['enviomodal'],
      "enviocodigorastreio" => null,
      "enviodata" => $malote['enviodata'],
      "enviorecebimentodata" => null,
      "enviorecebidopornome" => null,
      "enviorecebidoporcargo" => null,
      "requisitantecliente" => $cliente,
      "requisitantefornecedor" => null,
      "requisitantemidia" => null,
      "statusLabel" => "Enviado",
      "documentos" => []
    ];

    /* execução da funcionalidade */
    $I->sendRaw('POST',  $this->url_base . $malote_enviado['malote'] . '/malotecancelaenvio?' . 'grupoempresarial='.$this->grupoempresarial, $malote_enviado, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote'], "status" => 0]); //Verificando se o status mudou para 0 (novo)

  }

   
   /**
   * @param FunctionalTester $I
   */
  public function salvaRespostaRequisitanteMalote(FunctionalTester $I)
  {
    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $midia = $I->haveInDatabaseMidia($I);
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 2);

    //Objeto enviado na requisição
    $malote_enviado = [
      'malote' => $malote['malote'],
      "requisitantenome" => "Nome Requisitante",
      "requisitantecargo" => "Cargo Requisitante",
      "statusaprova" => 2, //aceito
      'requisitantemidia' => [
          "midia" => $midia['midiaorigem'],
          "nome" => $midia['codigo'],
          "full_count" => 1
      ],
      'requisitanteobservacoes' => "Requisitante Observações",
    ];

    /* execução da funcionalidade */
    $I->sendRaw('POST',  $this->url_base . $malote_enviado['malote'] . '/maloteAprova?' . 'grupoempresarial='.$this->grupoempresarial, $malote_enviado, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.malotes', ["malote" => $malote['malote'], "status" => 2]); //Verificando se o status mudou para 2 (aceito)

  }

   /**
   * @param FunctionalTester $I
   */
  public function listaMalotes(FunctionalTester $I)
  {
    /* inicializações */
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $malote = $I->haveInDatabaseMalote($I, $cliente['cliente']);
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 2, "Malote 1");
    $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 0, "Malote 2");
    $countAtual = $I->grabNumRecords('crm.malotes', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $malote, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);

  }

}