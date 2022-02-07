<?php
use Codeception\Util\HttpCode;

/**
 * Testa Informes
 * 
 */
class SolicitacoesalteracoesVTsCest {
    private $url_base = '/gednasajon';
    private $url_base_casouso = 'solicitacoes/alteracoes-vts';
    private $tenant_numero = "47";
    private $conta = "rodrigodirk@nasajon.com.br";
    private $estabelecimento = '39836516-7240-4fe5-847b-d5ee0f57252d';
    private $tarifaconcessionaria = '9745fc74-900f-42c9-9eef-b56bb186f252';
    private $tipoalterecaoVT = 4;

    /**
     * Executado antes de cada método da classe
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $permissoes = [
            'meusdados_criacao_sol_alteracao_vt'
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
       * Teste para listar solicitações de alterações de VTs
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
    public function listaSolicitacoesAlteracoesVts(FunctionalTester $I)
    {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $countSolicitacoes = $I->grabNumRecords('meurh.solicitacoes', ['tiposolicitacao' => $this->tipoalterecaoVT, 'tenant' => $this->tenant_numero, 'trabalhador' => $trabalhador['trabalhador']]);
        $I->haveInDatabaseSolicitacoesAlteracaoVTs($I, $trabalhador['trabalhador'], $this->estabelecimento);

        // funcionalidade testada
        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        $solicitacao = $I->sendRaw('GET', $url, [], [], [], null);

        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countSolicitacoes+1, $solicitacao);
    }

    /**
       * Teste para retornar a solicitação de alteração de VT
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
      public function retornarSolicitacaoAlteracaoVt(FunctionalTester $I)
      {
          // cenario
          $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
          $solicitacao = $I->haveInDatabaseSolicitacoesAlteracaoVTs($I, $trabalhador['trabalhador'], $this->estabelecimento);

          // funcionalidade testada
          $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao['solicitacao']}";
          $solicitacaoVt = $I->sendRaw('GET', $url, [], [], [], null);

          // verificação do teste
          $I->canSeeResponseCodeIs(HttpCode::OK);
          $I->assertEquals($I->getDadosBasicosSolicitacao($solicitacao), $I->getDadosBasicosSolicitacao($solicitacaoVt));

          //Validar a quantidade de tarifas da solicitação
          $I->assertCount(2, $solicitacaoVt['solicitacoesalteracoesvtstarifas']);

          $I->deleteFromDatabase('meurh.solicitacoesalteracoesvtstarifas', ['solicitacao' => $solicitacao['solicitacao']]);
      }

    /**
       * Teste para criar solicitações de alterações de VT
       * @param FunctionalTester $I
       */
      public function criaSolicitacaoAlteracaoVt(FunctionalTester $I)
      {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao = [
            "motivo" => "Novo endereço",
            "solicitacoesalteracoesvtstarifas"=> [
                [
                    "tarifaconcessionariavt"=> [
                        "tarifaconcessionariavt"=> $this->tarifaconcessionaria
                    ],
                    "quantidade" => "2"
                ]
            ]
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        // funcionalidade testada
        $solicitacao_criada = $I->sendRaw('POST', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::CREATED);

        //Testar a solicitação criada.
        $solicitacaoVt = [
            "justificativa" => "Novo endereço"
        ];
        $solicitacaoVt['solicitacao'] = $solicitacao_criada['solicitacao']; //colocando chave primária no array original para verificar se todas as informações estão no banco
        $I->canSeeInDatabase('meurh.solicitacoes', $solicitacaoVt);

        //Ajustando o objeto para comparar com o banco de dados
        $tarifasSolicitacao['solicitacao'] = $solicitacao_criada['solicitacao'];
        $tarifasSolicitacao['tarifaconcessionariavt'] = $this->tarifaconcessionaria;
        $tarifasSolicitacao['quantidade'] = 2;
        $I->canSeeInDatabase('meurh.solicitacoesalteracoesvtstarifas', $tarifasSolicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesalteracoesvtstarifas', ['solicitacao' => $solicitacao_criada['solicitacao']]);
      }

    /**
       * Teste para editar uma solicitação de alteração de VT
       * @param FunctionalTester $I
       */
      public function editarSolicitacaoAlteracoesVts(FunctionalTester $I)
      {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao_atual = $I->haveInDatabaseSolicitacoesAlteracaoVTs($I, $trabalhador['trabalhador'], $this->estabelecimento);

        $solicitacao = [
            "motivo" => "Novo endereço",
            "solicitacoesalteracoesvtstarifas"=> [
                [
                    "tarifaconcessionariavt"=> [
                        "tarifaconcessionariavt"=> $this->tarifaconcessionaria
                    ],
                    "quantidade" => "4"
                ]
            ]
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao_atual['solicitacao']}";
        // funcionalidade testada
        $I->sendRaw('PUT', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::OK);

        //Ajustando o objeto para comparar com o banco de dados
        $tarifasSolicitacao['solicitacao'] = $solicitacao_atual['solicitacao'];
        $tarifasSolicitacao['tarifaconcessionariavt'] = $this->tarifaconcessionaria;
        $tarifasSolicitacao['quantidade'] = 4;
        $I->canSeeInDatabase('meurh.solicitacoesalteracoesvtstarifas', $tarifasSolicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesalteracoesvtstarifas', ['solicitacao' => $solicitacao_atual['solicitacao']]);
      }
}