<?php

use Codeception\Util\HttpCode;
use Codeception\Util\JsonType;

/**
 * Teste de assinaturas de solicitações de férias
 */
class SolicitacoesferiasCest {

  private $url_base = '/gednasajon';
  private $url_base_casouso = 'solicitacoes/ferias';
  private $tenant_numero = "47";
  private $conta = "rodrigodirk@nasajon.com.br";
  private $estabelecimento = '39836516-7240-4fe5-847b-d5ee0f57252d';

  /**
   * Executado antes de cada método da classe
   * @param FunctionalTester $I
   */
  public function _before(FunctionalTester $I)
  {
      $permissoes = [
          'meusdados_criacao_sol_ferias'
      ];

      $I->amSamlLoggedInAs($this->conta, $permissoes, $this->estabelecimento);
      $I->deleteTrabalhador($I, $this->conta, $this->tenant_numero);
  }

  /**
   * Executado depois de cada método da classe
   */
  public function  _after(FunctionalTester $I){
      $I->deleteTrabalhador($I, $this->conta, $this->tenant_numero);
  }

  /**
     * Teste para listar solicitações de ferias
     * @todo melhorar cenário
     * @param FunctionalTester $I
     */
  public function listaSolicitacoesFerias(FunctionalTester $I)
  {
      // cenario
      $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
      $countSolicitacoesferias = $I->grabNumRecords('meurh.solicitacoesferias', ['tenant' => $this->tenant_numero, 'trabalhador' => $trabalhador['trabalhador']]);
      $I->haveInDatabaseSolicitacoesFerias($I, $trabalhador['trabalhador'], $this->estabelecimento);

      // funcionalidade testada
      $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
      $solicitacaoferias = $I->sendRaw('GET', $url, [], [], [], null);

      // verificação do teste
      $I->canSeeResponseCodeIs(HttpCode::OK);
      $I->assertCount($countSolicitacoesferias+1, $solicitacaoferias);
  }

  /**
     * Teste para retornar a solicitação de ferias
     * @todo melhorar cenário
     * @param FunctionalTester $I
     */
    public function retornarSolicitacaoFerias(FunctionalTester $I)
    {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao = $I->haveInDatabaseSolicitacoesFerias($I, $trabalhador['trabalhador'], $this->estabelecimento);

        // funcionalidade testada
        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao['solicitacao']}";
        $solicitacaoferias = $I->sendRaw('GET', $url, [], [], [], null);

        unset($solicitacaoferias['wkf_data']);
        unset($solicitacaoferias['wkf_estado']);
        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($I->getDadosBasicosSolicitacao($solicitacao), $I->getDadosBasicosSolicitacao($solicitacaoferias));

        $I->deleteFromDatabase('meurh.solicitacoesferias', ['solicitacao' => $solicitacao['solicitacao']]);
    }

  /**
     * Teste para criar solicitações de ferias
     * @param FunctionalTester $I
     */
    public function criaSolicitacaoFerias(FunctionalTester $I)
    {
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao = [
            'trabalhador' => $trabalhador['trabalhador'],
            'estabelecimento' => $this->estabelecimento,
            'tiposolicitacao' => 7,
            'situacao' => -1,
            'codigo' => 13,
            'justificativa' => null,
            'observacao' => null,
            'origem' => 1,
            'tenant' => 47,
            'datainiciogozo' => '2022-02-10',
            'datafimgozo' => '2022-02-25',
            'datainicioperiodoaquisitivo' => '2017-01-01',
            'datafimperiodoaquisitivo' => '2017-12-31',
            'temabonopecuniario' => null,
            'diasvendidos' => 0,
            'diasferiascoletivas' => $this->getDireitoEstagiario('2017-01-01'),
            'adto13nasferias' => null
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        // funcionalidade testada
        $solicitacao_criada = $I->sendRaw('POST', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $solicitacao['solicitacao'] = $solicitacao_criada['solicitacao']; //colocando chave primária no array original para verificar se todas as informações estão no banco
        $solicitacao['dataaviso'] = $solicitacao_criada['dataaviso']; 
        $solicitacao['origem'] = $solicitacao_criada['origem']; 
        $solicitacao['codigo'] = $solicitacao_criada['codigo']; 
        unset($solicitacao['wkf_data']);
        unset($solicitacao['wkf_estado']);
        unset($solicitacao['created_by']);
        unset($solicitacao['created_at']);
        unset($solicitacao['updated_at']);
        unset($solicitacao['updated_by']);
        unset($solicitacao['lastupdate']);

        $I->canSeeInDatabase('meurh.solicitacoesferias', $solicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesferias', ['solicitacao' => $solicitacao_criada['solicitacao']]);
    }

    /**
     * Teste para editar uma solicitação de ferias
     * @param FunctionalTester $I
     */
    public function editarSolicitacaoFerias(FunctionalTester $I)
    {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao_atual = $I->haveInDatabaseSolicitacoesFerias($I, $trabalhador['trabalhador'], $this->estabelecimento);

        $solicitacao = [
            'trabalhador' => $trabalhador['trabalhador'],
            'estabelecimento' => $this->estabelecimento,
            'tiposolicitacao' => 7,
            'situacao' => -1,
            'codigo' => 13,
            'justificativa' => null,
            'observacao' => null,
            'origem' => 1,
            'tenant' => 47,
            'datainiciogozo' => '2022-02-10',
            'datafimgozo' => '2022-02-25',
            'datainicioperiodoaquisitivo' => '2017-01-01',
            'datafimperiodoaquisitivo' => '2017-12-31',
            'temabonopecuniario' => null,
            'diasvendidos' => 0,
            'diasferiascoletivas' => $this->getDireitoEstagiario('2017-01-01'),
            'adto13nasferias' => null
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao_atual['solicitacao']}";
        // funcionalidade testada
        $I->sendRaw('PUT', $url, $solicitacao, [], [], null);
        $solicitacao['solicitacao'] = $solicitacao_atual['solicitacao'];
        $solicitacao['origem'] = $solicitacao_atual['origem']; 
        $solicitacao['codigo'] = $solicitacao_atual['codigo']; 
        unset($solicitacao['wkf_data']);
        unset($solicitacao['wkf_estado']);
        unset($solicitacao['created_by']);
        unset($solicitacao['created_at']);
        unset($solicitacao['updated_at']);
        unset($solicitacao['updated_by']);
        unset($solicitacao['lastupdate']);
        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->canSeeInDatabase('meurh.solicitacoesferias', $solicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesferias', ['solicitacao' => $solicitacao_atual['solicitacao']]);
    }

    private function getDireitoEstagiario(string $inicioPeriodoAquisitivo)
    {
        $mesesTrabalhados = 0;

        $dataAtual = new \DateTime();
        $dataInicioPeriodo = new \DateTime($inicioPeriodoAquisitivo);
        
        if (intval($dataInicioPeriodo->format('Y')) > intval($dataAtual->format('Y'))) {
            return 0;
        }

        $anosTrabalhados = ($dataAtual->diff($dataInicioPeriodo))->y;
        $mesesTrabalhados = ($dataAtual->diff($dataInicioPeriodo))->m;
        $mesesTrabalhados += $anosTrabalhados * 12;

        $diasDireito = ceil($mesesTrabalhados * 2.5);

        return $diasDireito > 30 ? 30 : $diasDireito;
    }
}