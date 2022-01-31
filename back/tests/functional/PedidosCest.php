<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Atcs
 */
class PedidosCest
{

  private $url = '/api/gednasajon/';
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
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::PROPOSTASITENS_CREATE, EnumAcao::PROPOSTASCAPITULOS_CREATE, EnumAcao::PROPOSTASITENSFUNCOES_CREATE, EnumAcao::PROPOSTASITENSFAMILIAS_CREATE, EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR]);
  }

  /**
   *
   * @param FunctionalTester $I
   */
  public function _after(FunctionalTester $I)
  {
    $I->deleteAllFromDatabase('crm.atcsresponsaveisfinanceiros');
    $I->deleteAllFromDatabase('crm.historicoatcs');
    $I->deleteAllFromDatabase('crm.atcsdadosseguradoras');
    $I->deleteAllFromDatabase('crm.propostasitensfamilias');
    $I->deleteAllFromDatabase('crm.propostasitensfuncoes');
    $I->deleteAllFromDatabase('crm.orcamentos');
    $I->deleteAllFromDatabase('gp.tarefas');
    $I->deleteAllFromDatabase('crm.propostasitens');
    $I->deleteAllFromDatabase('crm.propostascapitulos');
    $I->deleteAllFromDatabase('crm.propostas');
    $I->deleteAllFromDatabase('crm.atcs');
    $I->deleteAllFromDatabase('financas.itenscontratos');
    $I->deleteAllFromDatabase('financas.contratos');
  }

  /**
   * Testa a criação de propostaCapitulo
   * @param FunctionalTester $I
   */
  public function criaPropostaCapitulo(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $capitulo = [
      'nome' => 'Pedido2',
      'proposta'=> $proposta['proposta'],
      'pai' => null,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'updated_by' => '{"nome":"usuario"}',
      'updated_at' => date('Y-m-d'),
      'tenant' => $this->tenant_numero
  ];
    /* execução da funcionalidade */
    $propostaCapitulo_criado = $I->sendRaw('POST', $this->url . $proposta['proposta'] . '/propostascapitulos/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $capitulo, [], [], null);
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostascapitulos', ['propostacapitulo' => $propostaCapitulo_criado['propostacapitulo']]);
  }


  /**
   * Testa a edição de Proposta Capitulo
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function editaPropostaCapitulo(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $proposta['propostacapitulo']['nome'] = 'Editado';
    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url . $proposta['proposta'] . '/propostascapitulos/' . $proposta['propostacapitulo']['propostacapitulo'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $proposta['propostacapitulo'], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostascapitulos', ['nome' => $proposta['propostacapitulo']['nome']]);
  }

  /**
   * @param FunctionalTester $I
   * @todo testa exclusão de Proposta Capitulos
   */
  public function excluiPropostaCapitulo(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $proposta['propostacapitulo']['nome'] = 'Editado';
    /* execução da funcionalidade */
    $atc['nome'] = 'Nome editado';
    $I->sendRaw('DELETE', $this->url . $proposta['proposta'] . '/propostascapitulos/' . $proposta['propostacapitulo']['propostacapitulo'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $proposta['propostacapitulo'], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.propostascapitulos', ['propostacapitulo' => $proposta['propostacapitulo']['propostacapitulo']]);
  }

  /**
   * testa Criação de PropostaItem
   * @param FunctionalTester $I
   */
  public function criaPropostaItem(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $composicao = $I->haveInDatabaseComposicao($I);
    $propostaitens = [
        'propostaitem' => $I->generateUuidV4(),
        'proposta' => $proposta,
        'propostacapitulo' => $proposta['propostacapitulo'],
        'composicao' =>  $composicao,
        'fornecedor' => $fornecedor['fornecedor'],
        'nome' => 'Velório',
        'descricao' => 'Item vendido',
        'codigo' => '001',
        'valor' => 1,
        'itemdefaturamentovalor' => 1,
        'quantidade' => 1,
        'negocio' => $atc['negocio'],
        'created_at' => date('Y-m-d'),
        'created_by' => '{"nome":"usuario"}',
        'updated_by' => '{"nome":"usuario"}',
        'updated_at' => date('Y-m-d'),
        'tenant' => $this->tenant_numero,
        'previsaodatahorainicio' => date('Y-m-d H:i:s'),
        'previsaodatahorafim' => date('Y-m-d H:i:s'),
        'id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf',
        'nomeservicoalterado' => false
    ];

    /* execução da funcionalidade */
    $propostaitens_criado = $I->sendRaw('POST', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitens, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostasitens', ['propostaitem' => $propostaitens_criado['propostaitem']]);
    $I->canSeeInDatabase('crm.propostasitens', ['nome' => $composicao['nome']]);
    
  }

  /**
   * testa Criação de PropostaItem que permite informar a descrição
   * @param FunctionalTester $I
   */
  public function criaPropostaItemComNomeAlterado(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $composicao = $I->haveInDatabaseComposicao($I);
    $propostaitens = [
        'propostaitem' => $I->generateUuidV4(),
        'proposta' => $proposta,
        'propostacapitulo' => $proposta['propostacapitulo'],
        'composicao' =>  $composicao,
        'fornecedor' => $fornecedor['fornecedor'],
        'nome' => 'Velório',
        'descricao' => 'Item vendido',
        'codigo' => '001',
        'valor' => 1,
        'itemdefaturamentovalor' => 1,
        'quantidade' => 1,
        'negocio' => $atc['negocio'],
        'created_at' => date('Y-m-d'),
        'created_by' => '{"nome":"usuario"}',
        'updated_by' => '{"nome":"usuario"}',
        'updated_at' => date('Y-m-d'),
        'tenant' => $this->tenant_numero,
        'previsaodatahorainicio' => date('Y-m-d H:i:s'),
        'previsaodatahorafim' => date('Y-m-d H:i:s'),
        'id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf',
        'nomeservicoalterado' => true
    ];

    /* execução da funcionalidade */
    $propostaitens_criado = $I->sendRaw('POST', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitens, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostasitens', ['propostaitem' => $propostaitens_criado['propostaitem']]);
    $I->canSeeInDatabase('crm.propostasitens', ['nome' => $propostaitens['nome']]);
    
  }

  /**
   * Testa edição de propostaItem
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function editaPropostaItem(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    
    /* execução da funcionalidade */
    $propostaitem['previsaodatainicio'] = "2030-04-13";
    $propostaitem['previsaodatafim'] = "2030-04-14";
    $propostaitem['previsaohorainicio'] = "1970-01-01T13:13:00.000Z";
    $propostaitem['previsaohorafim'] = "1970-01-01T14:14:00.000Z";

    $propostaitem['previsaodatahorainicio'] = $propostaitem['previsaodatainicio'] . " " . substr($propostaitem['previsaohorainicio'], 11, 8);
    $propostaitem['previsaodatahorafim'] = $propostaitem['previsaodatafim'] . " " . substr($propostaitem['previsaohorafim'], 11, 8);

    $I->sendRaw('PUT', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/'. $propostaitem['propostaitem'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitens', ['previsaodatahorainicio' => $propostaitem['previsaodatahorainicio'], "previsaodatahorafim" => $propostaitem['previsaodatahorafim']]);

  }

  /**
   * Testa edição de propostaItem que permite informar a descrição
   * @param FunctionalTester $I
   */
  public function editaPropostaItemComNomeAlterado(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor, null, null, null, true);
    
    /* execução da funcionalidade */
    $propostaitem['nome'] = 'Proposta Item Nome Alterado';

    $propostaitem['previsaodatainicio'] = "2030-04-13";
    $propostaitem['previsaodatafim'] = "2030-04-14";
    $propostaitem['previsaohorainicio'] = "1970-01-01T13:13:00.000Z";
    $propostaitem['previsaohorafim'] = "1970-01-01T14:14:00.000Z";

    $propostaitem['previsaodatahorainicio'] = $propostaitem['previsaodatainicio'] . " " . substr($propostaitem['previsaohorainicio'], 11, 8);
    $propostaitem['previsaodatahorafim'] = $propostaitem['previsaodatafim'] . " " . substr($propostaitem['previsaohorafim'], 11, 8);

    $I->sendRaw('PUT', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/'. $propostaitem['propostaitem'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitens', [
                          "nome" => $propostaitem['nome'],
                          'previsaodatahorainicio' => $propostaitem['previsaodatahorainicio'], 
                          "previsaodatahorafim" => $propostaitem['previsaodatahorafim']
    ]);

  }
// // CRIAR TAREFA PARA CORREÇÃO DO LOOKUP DE ENDEREÇO

  /**
   * Testa exclusão de propostaItem
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function excluiPropostaItem(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);

    /* execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem']. '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.propostasitens', ['propostaitem' => $propostaitem['propostaitem']]);
  }

  /**
  * Testa criação de propostaItemFunção
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function criaPropostaItemFuncao(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaitemfuncao = [
      'propostaitem' => $propostaitem,
      'funcao' => $funcao,
      'quantidade' => 5,
      'valor' => 10,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'tenant' => $this->tenant_numero,
      'composicaofuncao' => $composicaofuncao,
      'composicao' => $composicao,
      'id_grupoempresarial' => $this->grupoempresarial_id,
      'nome' => $funcao['descricao'],
      'nomefuncaoalterado' => false
    ];
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url . $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemfuncao, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostasitensfuncoes', ['composicaofuncao' => $composicaofuncao['composicaofuncao']]);
  }

  /**
   * Testa criação de propostaItemFunção que permite informar a descrição 
   * @param FunctionalTester $I
   */
  public function criaPropostaItemFuncaoComNomeAlterado(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaitemfuncao = [
      'propostaitem' => $propostaitem,
      'funcao' => $funcao,
      'quantidade' => 10,
      'valor' => 20,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'tenant' => $this->tenant_numero,
      'composicaofuncao' => $composicaofuncao,
      'composicao' => $composicao,
      'id_grupoempresarial' => $this->grupoempresarial_id,
      'nome' => 'Proposta Item Função Nome Manual',
      'nomefuncaoalterado' => true
    ];
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url . $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemfuncao, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostasitensfuncoes', [
      'composicaofuncao' => $composicaofuncao['composicaofuncao'],
      'nome' => $propostaitemfuncao['nome'],
      'nomefuncaoalterado' => $propostaitemfuncao['nomefuncaoalterado']
    ]);
  }

  /**
  * Testa edição de propostaItem Função
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function editaPropostaItemFuncao(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $propostaItemFuncao['valor'] = "10";
    $propostaItemFuncao['quantidade'] = 1;
    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url . $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . $propostaItemFuncao['propostaitemfuncao'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFuncao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitensfuncoes', ['valor' => $propostaItemFuncao['valor']]);
  }

  /**
   * Testa edição de propostaItem Função que permite informar a descrição 
   * @param FunctionalTester $I
  */
  public function editaPropostaItemFuncaoComNomeAlterado(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao, ['nomefuncaoalterado' => true]);
    
    $propostaItemFuncao['nome'] = 'Nome Manual PropItemFunc Editado';
    $propostaItemFuncao['quantidade'] = 3;

    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url . $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . $propostaItemFuncao['propostaitemfuncao'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFuncao, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitensfuncoes', ['nome' => $propostaItemFuncao['nome']]);
  }

  /**
  * Testa exclusão de propostaItemFunção
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function excluiPropostaItemFuncao(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);

    /* execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url . $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . $propostaItemFuncao['propostaitemfuncao'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFuncao, [], [], null);
    
    /* validação do resultado */
    $I->cantSeeInDatabase('crm.propostasitensfuncoes', ['propostaitemfuncao' => $propostaItemFuncao['propostaitemfuncao']]);
    $I->canSeeResponseCodeIs(HttpCode::OK);
  }

  /**
  * Testa criação de propostaItemFamilia
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function criaPropostaItemFamilia(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaitemfamilia = [
      'propostaitem' => $propostaitem,
      'familia' => $familia,
      'quantidade' => 5,
      'valor' => 10,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'tenant' => $this->tenant_numero,
      'composicaofamilia' => $composicaofamilia,
      'composicao' => $composicao,
      'id_grupoempresarial' => $this->grupoempresarial_id,
      'nome' => $familia['descricao'],
      'nomefamiliaalterado' => false
    ];

    /* execução da funcionalidade */
    $propostaitemfamilia_criada = $I->sendRaw('POST', $this->url . $propostaitem['propostaitem'] . '/propostasitensfamilias/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemfamilia, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostasitensfamilias', ['composicaofamilia' => $composicaofamilia['composicaofamilia']]);
  }

  /**
   * Testa criação de propostaItemFamilia que permite informar a descrição
   * @param FunctionalTester $I
   */
  public function criaPropostaItemFamiliaComNomeAlterado(FunctionalTester $I)
  {
    /* inicializações */
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaitemfamilia = [
      'propostaitem' => $propostaitem,
      'familia' => $familia,
      'quantidade' => 5,
      'valor' => 10,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'tenant' => $this->tenant_numero,
      'composicaofamilia' => $composicaofamilia,
      'composicao' => $composicao,
      'id_grupoempresarial' => $this->grupoempresarial_id,
      'nome' => 'Proposta Item Família Nome Manual',
      'nomefamiliaalterado' => true
    ];

    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url . $propostaitem['propostaitem'] . '/propostasitensfamilias/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemfamilia, [], [], null);
    
    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('crm.propostasitensfamilias', [
      'composicaofamilia' => $composicaofamilia['composicaofamilia'],
      'nome' => $propostaitemfamilia['nome'],
      'nomefamiliaalterado' => $propostaitemfamilia['nomefamiliaalterado']
    ]);
  }

  /**
  * Testa edição de propostaItemFamilia
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function editaPropostaItemFamilia(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaItemFamilia =  $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    $propostaItemFamilia['valor'] = "7";
    
    /* execução da funcionalidade */
    $I->sendRaw('PUT', $this->url . $propostaitem['propostaitem'] . '/propostasitensfamilias/' . $propostaItemFamilia['propostaitemfamilia'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFamilia, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitensfamilias', ['valor' => $propostaItemFamilia['valor']]);
  }

  /**
   * Testa edição de propostaItemFamilia que permite informar a descrição
   * @param FunctionalTester $I
   */
  public function editaPropostaItemFamiliaComNomeAlterado(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaItemFamilia =  $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia, ['nome' => 'PropItemFam', 'nomefamiliaalterado' => true]);
    
    /* execução da funcionalidade */
    $propostaItemFamilia['nome'] = 'Nome PropItemFam Editado';
    $propostaItemFamilia['quantidade'] = 1;
    $propostaItemFamilia['valor'] = 7;
    $I->sendRaw('PUT', $this->url . $propostaitem['propostaitem'] . '/propostasitensfamilias/' . $propostaItemFamilia['propostaitemfamilia'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFamilia, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitensfamilias', ['valor' => $propostaItemFamilia['valor']]);
  }

  /**
   * Testa exclusão de propostaItemFamilia
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function excluirPropostaItemFamilia(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaItemFamilia =  $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    
    /* execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url . $propostaitem['propostaitem'] . '/propostasitensfamilias/' . $propostaItemFamilia['propostaitemfamilia'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFamilia, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.propostasitensfamilias', ['propostaitemfamilia' => $propostaItemFamilia['propostaitemfamilia']]);
  }

  /**
   * Testa vinculação de fornecedor a propostaItem
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function propostaItensVincularFornecedor(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    /* execução da funcionalidade */
    $atc['nome'] = 'Nome editado';
    $propostaitem['fornecedor'] = $fornecedor;
    $I->sendRaw('POST', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasItensVincularFornecedor' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitens', ['fornecedor' => $fornecedor['fornecedor']]);
  }

  /**
   * Testa vinculação de fornecedor a propostaItem
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function propostaItensVincularFornecedorNaoCriaTarefa(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtcComProjeto($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I, true, $this->estabelecimento);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    
    /* execução da funcionalidade */
    $atc['nome'] = 'Nome editado';
    $propostaitem['fornecedor'] = $fornecedor;
    $dados = $I->sendRaw('POST', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasItensVincularFornecedor' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('gp.tarefas', ['propostaitem' => $propostaitem['propostaitem']]);
    $I->canSeeInDatabase('crm.propostasitens', ['propostaitem' => $propostaitem['propostaitem'], 'tarefa' => null]);

    /* removendo relacionamento para que seja possível apagar as tabelas. */
    // $I->updateInDatabase('gp.tarefas', ['propostaitem' => null], ['tarefa' => $dados['tarefa']['tarefa'] ]);
    $I->updateInDatabase('crm.propostasitens', ['tarefa' => null], ['propostaitem' => $propostaitem['propostaitem']]);
  }
  

  /**
   * Testa edição de propostaItem
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function editaTarefaPropostaItem(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    // $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $atc = $I->haveInDatabaseAtcComProjeto($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    // $fornecedor = $I->haveInDatabaseFornecedor($I);
    $fornecedor = $I->haveInDatabaseFornecedor($I, true, $this->estabelecimento);
    
    $projetoescopo = $I->haveInDatabaseProjetoEscopo($I, [
      'projeto' => $atc['projeto']['projeto'],
      'descricao' => 'uma desc'
    ]);
    $tarefa = $I->haveInDatabaseTarefa($I, $projetoescopo);

    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor, null, null, $tarefa);
    $I->updateInDatabase('gp.tarefas', ['propostaitem' => $propostaitem['propostaitem']], ['tarefa' => $tarefa['tarefa'] ]);
    $I->updateInDatabase('crm.propostasitens', ['tarefa' => $tarefa['tarefa']], ['propostaitem' => $propostaitem['propostaitem']]);

    /* execução da funcionalidade */
    $propostaitem['previsaodatainicio'] = "2030-04-13";
    $propostaitem['previsaodatafim'] = "2030-04-14";
    $propostaitem['previsaohorainicio'] = "1970-01-01T13:13:00.000Z";
    $propostaitem['previsaohorafim'] = "1970-01-01T14:14:00.000Z";

    $propostaitem['previsaodatahorainicio'] = $propostaitem['previsaodatainicio'] . " " . substr($propostaitem['previsaohorainicio'], 11, 8);
    $propostaitem['previsaodatahorafim'] = $propostaitem['previsaodatafim'] . " " . substr($propostaitem['previsaohorafim'], 11, 8);

    $I->sendRaw('PUT', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/'. $propostaitem['propostaitem'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $tarefaVerificar = [
      'tarefa' => $tarefa['tarefa'],
      'previsaoinicio' => $propostaitem['previsaodatahorainicio'],
      'previsaotermino' => $propostaitem['previsaodatahorafim']
    ];
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitens', ['previsaodatahorainicio' => $propostaitem['previsaodatahorainicio'], "previsaodatahorafim" => $propostaitem['previsaodatahorafim']]);
    $I->canSeeInDatabase('gp.tarefas', $tarefaVerificar);

    $I->updateInDatabase('crm.propostasitens', ['tarefa' => null], ['propostaitem' => $propostaitem['propostaitem']]);
    $I->updateInDatabase('gp.tarefas', ['propostaitem' => null], ['tarefa' => $tarefa['tarefa'] ]);
  }

  /**
   * Testa Desvinculação de fornecedor em propostaitem
   * @param FunctionalTester $I
   * @todo testar demais campos
   */
  public function propostaItensDesvincularFornecedor(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    /* execução da funcionalidade */
    $atc['nome'] = 'Nome editado';
    $propostaitem['fornecedor'] = $fornecedor;
    $I->sendRaw('POST', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasItensDesvincularFornecedor' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('crm.propostasitens', ['fornecedor' => $fornecedor['fornecedor']]);
  }

  /**
   * Testa a função fornecedorEscolheCliente
  * @param FunctionalTester $I
  * @todo testar demais campos
  */
  public function propostaItensFornecedorEscolheCliente(FunctionalTester $I)
  {
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $propostaitemescolhacliente = [
      'propostaitem' => $propostaitem['propostaitem'],
      'escolhacliente' => true,
      'tenant' => $this->tenant_numero
    ];
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasitensfornecedorescolhacliente' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemescolhacliente, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('crm.propostasitens', ['escolhacliente' => $propostaitemescolhacliente['escolhacliente']]);
  }

}
