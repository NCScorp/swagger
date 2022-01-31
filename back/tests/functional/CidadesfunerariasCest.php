<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Cidadesfunerarias
 */
class CidadesfunerariasCest {

  private $url_base = '/api/gednasajon/cidadesinformacoesfunerarias/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';
  private $id_grupoempresarial = '95cd450c-30c5-4172-af2b-cdece39073bf';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::CIDADESINFORMACOESFUNERARIAS_INDEX, EnumAcao::CIDADESINFORMACOESFUNERARIAS_GET, EnumAcao::CIDADESINFORMACOESFUNERARIAS_CREATE, EnumAcao::CIDADESINFORMACOESFUNERARIAS_PUT]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaCidadeSemFornecedores(FunctionalTester $I) {

    /* Inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado); //Se município já existir, não deixa criar

    $municipio['codigo'] = $municipio['ibge']; //Objeto enviado tem o campo com nome de código
    $municipio['uf'] = ['uf' => $estado['uf'], 'nome' => $estado['nome']]; //UF dentro de município é um objeto
    unset($municipio['federal'], $municipio['ibge']); //Não serão usados
    $fornecedores = [];

    /* execução da funcionalidade */
    $cidade = [
      'fornecedores' => $fornecedores,
      'pais' => $pais,
      'estado' => $estado,
      'municipio' => $municipio,
      'possuisvo' => true,
      'possuicrematorio' => true,
      'possuicemiteriomunicipal' => true,
      'possuicapelamunicipal' => true,
      'trabalhacomfloresnaturais' => true,
      'possuiiml' => true,
      'perfilfunerario' => 1
    ];
    $cidade_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('ns.cidadesinformacoesfunerarias', ['cidadeinformacaofuneraria' => $cidade_criada['cidadeinformacaofuneraria']]);

    /* remove dado criado no banco */
    $I->deleteFromDatabase('ns.cidadesinformacoesfunerarias', ['cidadeinformacaofuneraria' => $cidade_criada['cidadeinformacaofuneraria']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaCidadeComFornecedor(FunctionalTester $I) {

    /* Inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado); //Se município já existir, não deixa criar

    $municipio['codigo'] = $municipio['ibge']; //Objeto enviado tem o campo com nome de código
    $municipio['uf'] = ['uf' => $estado['uf'], 'nome' => $estado['nome']]; //UF dentro de município é um objeto
    unset($municipio['federal'], $municipio['ibge']); //Não serão usados

    $fornecedor = $I->haveInDatabaseFornecedor($I);

    //Colocando o objeto fornecedor no formato esperado na requisição
    $fornecedor['razaosocial'] = $fornecedor['nome'];
    $fornecedor['codigofornecedores'] = $fornecedor['pessoa'];
    $fornecedor['status'] = 0;
    $fornecedor['diasparavencimento'] = null;
    $fornecedor['anotacao'] = null;
    $fornecedor['checked'] = true;

    unset($fornecedor['nome'], $fornecedor['pessoa']); //Não serão usados

    /* execução da funcionalidade */
    $cidade = [
      'fornecedores' => [
        [
          "fornecedor" => $fornecedor,
          "ordem" => 1
        ]
      ],
      'pais' => $pais,
      'estado' => $estado,
      'municipio' => $municipio,
      'possuisvo' => true,
      'possuicrematorio' => true,
      'possuicemiteriomunicipal' => true,
      'possuicapelamunicipal' => true,
      'trabalhacomfloresnaturais' => true,
      'possuiiml' => true,
      'perfilfunerario' => 1
    ];
    $cidade_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);
    $I->canSeeInDatabase('ns.cidadesinformacoesfunerarias', ['cidadeinformacaofuneraria' => $cidade_criada['cidadeinformacaofuneraria']]);

    /* remove dado criado no banco */
    $I->deleteFromDatabase('ns.cidadesinfofunerariasfornecedores', ['id_cidadefuneraria' => $cidade_criada['cidadeinformacaofuneraria']]);
    $I->deleteFromDatabase('ns.cidadesinformacoesfunerarias', ['cidadeinformacaofuneraria' => $cidade_criada['cidadeinformacaofuneraria']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaCidade(FunctionalTester $I) {

    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado); //Se município já existir, não deixa criar
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->id_grupoempresarial);

    /* execução da funcionalidade */
    $cidade['possuisvo'] = false;
    $cidade['possuiiml'] = false;
    $cidade['possuicrematorio'] = false;
    $I->sendRaw('PUT', $this->url_base . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('ns.cidadesinformacoesfunerarias', ['cidadeinformacaofuneraria' => $cidade['cidadeinformacaofuneraria'], 'possuisvo' => $cidade['possuisvo'],
                                                             'possuiiml' => $cidade['possuiiml'], 'possuicrematorio' => $cidade['possuicrematorio']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listarCidades(FunctionalTester $I) {

    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio1 = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $municipio2 = $I->haveInDatabaseMunicipioUnico($I, $estado, '1111111', '00');

    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio1, $this->id_grupoempresarial);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio2, $this->id_grupoempresarial);
    $countAtual = $I->grabNumRecords('ns.cidadesinformacoesfunerarias', ['tenant' => $this->tenant_numero]);

    /* execução da funcionalidade */
    $lista = $I->sendRaw('GET', $this->url_base .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertCount($countAtual, $lista);
  }

  /**
   * @param FunctionalTester $I
   */
  public function exibirCidade(FunctionalTester $I) {

    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->id_grupoempresarial);

    /* execução da funcionalidade */
    $cidade_retornada = $I->sendRaw('GET', $this->url_base . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertEquals($cidade['cidadeinformacaofuneraria'], $cidade_retornada['cidadeinformacaofuneraria']);

  }

  /**
   * @param FunctionalTester $I
   */
  public function adicionarPrestadorNaCidade(FunctionalTester $I) {

    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->id_grupoempresarial);
    $fornecedor = $I->haveInDatabaseFornecedor($I);

    //Colocando o objeto fornecedor no formato esperado na requisição
    $fornecedor['razaosocial'] = $fornecedor['nome'];
    $fornecedor['codigofornecedores'] = $fornecedor['pessoa'];
    $fornecedor['status'] = 0;
    $fornecedor['diasparavencimento'] = null;
    $fornecedor['anotacao'] = null;
    $fornecedor['checked'] = true;
    unset($fornecedor['nome'], $fornecedor['pessoa']); //Não serão usados

    $cidade['fornecedores'][0]['fornecedor'] = $fornecedor;
    $cidade['fornecedores'][0]['ordem'] = 1;
    $cidade['fornecedores'][0]['cidadeinformacaofuneraria'] = $cidade['cidadeinformacaofuneraria'];

    //Verificando se na tabela de fornecedores das cidades funerárias, não há referencia para a cidade editada, o que significa que ela não tem fornecedor
    $I->cantSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);

    /* Execução da funcionalidade */
    $I->sendRaw('PUT', $this->url_base . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* Validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    //Verificando se agora há referencia para a cidade editada, o que significa que agora ela tem fornecedor
    $I->canSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);

    //Apagando dados do banco
    $I->deleteFromDatabase('ns.cidadesinfofunerariasfornecedores', ['id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);
    
  }

  /**
   * Associa um prestador a cidade e depois troca o prestador por outro
   * @param FunctionalTester $I
   */
  public function editarPrestadorNaCidade(FunctionalTester $I){
 
    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->id_grupoempresarial);

    $fornecedor1 = $I->haveInDatabaseFornecedorCidadeInformacaoFuneraria($I);
    $fornecedor2 = $I->haveInDatabaseFornecedorCidadeInformacaoFuneraria($I, "Prest2", "Prestador2", "Prestador 2", "64.826.781/0001-50", "2");
    
    // Associando o fornecedor1 a cidade
    $I->haveInDatabaseCidadesInfoFunerariasFornecedores($I, $cidade, $fornecedor1, $this->id_grupoempresarial);

    //Verificando se na tabela de fornecedores das cidades funerárias, há referencia do fornecedor1
    $I->canSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['id_fornecedor' => $fornecedor1['fornecedor']]);

    //Colocando o objeto fornecedor2 no formato esperado na requisição
    $fornecedor2['razaosocial'] = $fornecedor2['nome'];
    $fornecedor2['codigofornecedores'] = $fornecedor2['pessoa'];
    $fornecedor2['status'] = 0;
    $fornecedor2['diasparavencimento'] = null;
    $fornecedor2['anotacao'] = null;
    $fornecedor2['checked'] = true;
    unset($fornecedor2['nome'], $fornecedor2['pessoa']); //Não serão usados

    /* Execução da funcionalidade */
    // Mudando do fornecedor1 para o fornecedor2
    $cidade['fornecedores'][0]['fornecedor'] = $fornecedor2;
    $cidade['fornecedores'][0]['ordem'] = 1;
    $cidade['fornecedores'][0]['cidadeinformacaofuneraria'] = $cidade['cidadeinformacaofuneraria'];
    $I->sendRaw('PUT', $this->url_base . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* Validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    // Verificando se o fornecedor1 não está na tabela e o fornecedor2 está
    $I->cantSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['id_fornecedor' => $fornecedor1['fornecedor']]);
    $I->canSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['id_fornecedor' => $fornecedor2['fornecedor']]);

    // Apagando dado do banco
    $I->deleteFromDatabase('ns.cidadesinfofunerariasfornecedores', ['id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);

  }
  
  /**
   * @param FunctionalTester $I
   */
  public function excluirPrestadorDaCidade(FunctionalTester $I){
    
    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->id_grupoempresarial);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $cidade_fornecedor = $I->haveInDatabaseCidadesInfoFunerariasFornecedores($I, $cidade, $fornecedor, $this->id_grupoempresarial);

    // Verificando se na tabela que associa cidade ao fornecedor, tem a cidade e o fornecedor adicionados acima
    $I->canSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['cidadeinfofunerariafornecedor' => $cidade_fornecedor['cidadeinfofunerariafornecedor'],
                                                                  'id_fornecedor' => $fornecedor['fornecedor'], 'id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);

    /* Execução da funcionalidade */
    $cidade['fornecedores'] = [];
    $I->sendRaw('PUT', $this->url_base . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* Validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('ns.cidadesinfofunerariasfornecedores', ['cidadeinfofunerariafornecedor' => $cidade_fornecedor['cidadeinfofunerariafornecedor'],
                                                                   'id_fornecedor' => $fornecedor['fornecedor'], 'id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);

  }

  /**
   * @param FunctionalTester $I
   */
  public function listarPrestadoresDaCidade(FunctionalTester $I){

    /* inicializações */
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->id_grupoempresarial);

    $fornecedor1 = $I->haveInDatabaseFornecedorCidadeInformacaoFuneraria($I);
    $fornecedor2 = $I->haveInDatabaseFornecedorCidadeInformacaoFuneraria($I, "Prest2", "Prestador2", "Prestador 2", "64.826.781/0001-50", "2");
    
    // Associando os fornecedores a cidade
    $I->haveInDatabaseCidadesInfoFunerariasFornecedores($I, $cidade, $fornecedor1, $this->id_grupoempresarial);
    $I->haveInDatabaseCidadesInfoFunerariasFornecedores($I, $cidade, $fornecedor2, $this->id_grupoempresarial);

    /* Execução da funcionalidade */
    $cidade_retornada = $I->sendRaw('GET', $this->url_base . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* Validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);

    // Quantos fornecedores estão associados a essa cidade, espera-se 2
    $countAtual = $I->grabNumRecords('ns.cidadesinfofunerariasfornecedores', ['id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria']]);
    $I->assertCount($countAtual, $cidade_retornada['fornecedores']);

  }

}
