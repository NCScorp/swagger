<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Prioridades
 */
class PrioridadesCest {

  private $url_base = '/api/gednasajon/prioridades/';
  private $tenant = "gednasajon";
  private $tenant_numero = "47";
  private $grupoempresarial = 'FMA';

  public function _before(FunctionalTester $I) {
    $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::PRIORIDADES_INDEX, EnumAcao::PRIORIDADES_GET, EnumAcao::PRIORIDADES_CREATE, EnumAcao::PRIORIDADES_PUT, EnumAcao::PRIORIDADES_DELETE]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function listaPrioridades(FunctionalTester $I) {

    /* Execução da funcionalidade */
    $countAtual = $I->grabNumRecords('ns.prioridades', ['tenant' => $this->tenant_numero]);
    $prioridadesRetornadas = $I->sendRaw('GET', $this->url_base . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertEquals($countAtual, count($prioridadesRetornadas));
  }

  /**
   * @param FunctionalTester $I
   */
  public function retornaPrioridade(FunctionalTester $I) {

    /* inicializações */
    $prioridade = $I->haveInDatabasePrioridades($I, []);

    $retorno = $I->sendRaw('GET', $this->url_base . $prioridade['prioridade'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->assertEquals($prioridade, $retorno);
  }

  /**
   * @param FunctionalTester $I
   */
  public function criaPrioridade(FunctionalTester $I) {

    /* execução da funcionalidade */
    $prioridade = [
        'nome' => 'Prioridade Teste',
        'ordem' => 0,
        'prazoexpiracao' => 120,
        'notificarexpiracaofaltando' => 30,
        'prioridadepadrao' => false,
        'descricao' => 'Descrição Prioridade Teste',
        'cor' => '#1FCA5D'
    ];

    $prioridade_criada = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::CREATED);

    $I->canSeeInDatabase('ns.prioridades', [
      'prioridade' => $prioridade_criada['prioridade'],
      'nome' => $prioridade['nome'],
      'descricao' => $prioridade['descricao'],
      'prazoexpiracao' => $prioridade['prazoexpiracao'],
      'notificarexpiracaofaltando' => $prioridade['notificarexpiracaofaltando'],
      'ordem' => $prioridade['ordem'],
      'prioridadepadrao' => $prioridade['prioridadepadrao'],
      'cor' => $prioridade['cor']
    ]);

    /* remove dado criado no banco*/
    $I->deleteFromDatabase('ns.prioridades', ['prioridade' => $prioridade_criada['prioridade']]);
  }

  /**
   * @param FunctionalTester $I
   */
  public function editaPrioridade(FunctionalTester $I) {

    /* inicializações */
    $prioridade = $I->haveInDatabasePrioridades($I, []);

    /* execução da funcionalidade */
    $prioridade['nome'] = 'Nome editado';
    $prioridade['ordem'] = 77;
    $prioridade['prazoexpiracao'] = 240;
    $prioridade['notificarexpiracaofaltando'] = 60;
    $prioridade['prioridadepadrao'] = true;
    $prioridade['descricao'] = 'Descrição editada';
    $prioridade['cor'] = '#1FCA5D';

    $I->sendRaw('PUT', $this->url_base .$prioridade['prioridade']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->canSeeInDatabase('ns.prioridades', $prioridade);

  }

  /**
   * @param FunctionalTester $I
   */
  public function excluiPrioridade(FunctionalTester $I){

    /* inicializações */
    $prioridade = $I->haveInDatabasePrioridades($I, []);

    $I->sendRaw('DELETE', $this->url_base .$prioridade['prioridade']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $I->cantSeeInDatabase('ns.prioridades', $prioridade);

  }

  /**
   * @param FunctionalTester $I
   */
  public function naoCriaPrioridadePadraoSeJaHouverUma(FunctionalTester $I){

    /* inicializações */

    // Criando prioridade padrão no banco
    $I->haveInDatabasePrioridades($I, ['prioridadepadrao' => true]);

    /* execução da funcionalidade */
    $prioridade = [
      'nome' => 'Prioridade Teste',
      'ordem' => 0,
      'prazoexpiracao' => 120,
      'notificarexpiracaofaltando' => 30,
      'prioridadepadrao' => true,
      'descricao' => 'Descrição Prioridade Teste',
      'cor' => '#CA0813'
    ];

    $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
    
  }

  /**
   * @param FunctionalTester $I
   */
  public function naoEditaPrioridadePadraoSeJaHouverUma(FunctionalTester $I){

    /* inicializações */
    $prioridade = $I->haveInDatabasePrioridades($I, ['cor' => "#1FCA5D"]);

    // Criando prioridade padrão no banco
    $I->haveInDatabasePrioridades($I, ['prioridadepadrao' => true, 'nome' => 'Não deixa editar']);

    /* execução da funcionalidade */
    $prioridade['prioridadepadrao'] = true;

    $I->sendRaw('PUT', $this->url_base .$prioridade['prioridade']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
    
  }

  /**
   * @param FunctionalTester $I
   */
  public function naoExcluiPrioridadeSeHouverPendenciaAssociada(FunctionalTester $I){
    
    /* inicializações */
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $pendencialista = $I->haveInDatabaseAtcpendencialista($I, $atc);
    $prioridade = $I->haveInDatabasePrioridades($I);
    $I->haveInDatabaseAtcpendencia($I, $pendencialista, $atc, $prioridade);

    $I->sendRaw('DELETE', $this->url_base .$prioridade['prioridade']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
    
  }

}
