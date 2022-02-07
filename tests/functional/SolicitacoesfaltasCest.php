<?php
use Codeception\Util\HttpCode;

/**
 * Testa Faltas
 * 
 */
class SolicitacoesfaltasCest {
    private $url_base = '/gednasajon';
    private $url_base_casouso = 'solicitacoes/faltas';
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
            'meusdados_criacao_sol_falta'
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
       * Teste para listar solicitações de faltas
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
    public function listaSolicitacoesFaltas(FunctionalTester $I)
    {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $countSolicitacoesfaltas = $I->grabNumRecords('meurh.solicitacoesfaltas', ['tenant' => $this->tenant_numero, 'trabalhador' => $trabalhador['trabalhador']]);
        $I->haveInDatabaseSolicitacoesFaltas($I, $trabalhador['trabalhador'], $this->estabelecimento);

        // funcionalidade testada
        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        $solicitacaofalta = $I->sendRaw('GET', $url, [], [], [], null);

        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countSolicitacoesfaltas+1, $solicitacaofalta);
    }

    /**
       * Teste para retornar a solicitação de alteração de falta
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
      public function retornarSolicitacaoFalta(FunctionalTester $I)
      {
          // cenario
          $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
          $solicitacao = $I->haveInDatabaseSolicitacoesFaltas($I, $trabalhador['trabalhador'], $this->estabelecimento);

          // funcionalidade testada
          $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao['solicitacao']}";
          $solicitacaofalta = $I->sendRaw('GET', $url, [], [], [], null);

          // verificação do teste
          $I->canSeeResponseCodeIs(HttpCode::OK);
          $I->assertEquals($I->getDadosBasicosSolicitacao($solicitacao), $I->getDadosBasicosSolicitacao($solicitacaofalta));

          $I->deleteFromDatabase('meurh.solicitacoesfaltas', ['solicitacao' => $solicitacao['solicitacao']]);
      }

    /**
       * Teste para criar solicitações de alterações de falta
       * @param FunctionalTester $I
       */
      public function criaSolicitacaoFalta(FunctionalTester $I)
      {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao = [
            'datas' => array('2020-07-01','2020-07-02'),
            'justificada' => true,
            'tipojustificativa' => 1
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        // funcionalidade testada
        $solicitacao_criada = $I->sendRaw('POST', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $solicitacao['solicitacao'] = $solicitacao_criada['solicitacao']; //colocando chave primária no array original para verificar se todas as informações estão no banco

        $solicitacao['datas'] = $I->array_to_pgarray($solicitacao['datas']);
        $I->canSeeInDatabase('meurh.solicitacoesfaltas', $solicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesfaltas', ['solicitacao' => $solicitacao_criada['solicitacao']]);
      }

    /**
       * Teste para editar uma solicitação de alteração de falta
       * @param FunctionalTester $I
       */
      public function editarSolicitacaoFaltas(FunctionalTester $I)
      {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao_atual = $I->haveInDatabaseSolicitacoesfaltas($I, $trabalhador['trabalhador'], $this->estabelecimento);

        $solicitacao = [
            'datas' => ['2020-07-01','2020-07-02'],
            'justificada' => true,
            'tipojustificativa' => 2
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao_atual['solicitacao']}";
        // funcionalidade testada
        $I->sendRaw('PUT', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::OK);

        $solicitacao['datas'] = $I->array_to_pgarray($solicitacao['datas']);
        $I->canSeeInDatabase('meurh.solicitacoesfaltas', $solicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesfaltas', ['solicitacao' => $solicitacao_atual['solicitacao']]);
      }
}