<?php
use Codeception\Util\HttpCode;

/**
 * Testa Informes
 * 
 */
class SolicitacoesalteracoesenderecosCest {
    private $url_base = '/gednasajon';
    private $url_base_casouso = 'solicitacoes/alteracoesenderecos';
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
            'meusdados_criacao_sol_alteracao_dados_cadastrais'
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
       * Teste para listar solicitações de alterações de endereços
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
    public function listaSolicitacoesAlteracoesEnderecos(FunctionalTester $I)
    {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $countSolicitacoalteracoesenderecos = $I->grabNumRecords('meurh.solicitacoesalteracoesenderecos', ['tenant' => $this->tenant_numero,  'trabalhador' => $trabalhador['trabalhador']]);
        $I->haveInDatabaseSolicitacoesalteracoesenderecos($I, $trabalhador['trabalhador'], $this->estabelecimento);

        // funcionalidade testada
        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        $solicitacaoalteracaoendereco = $I->sendRaw('GET', $url, [], [], [], null);

        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countSolicitacoalteracoesenderecos+1, $solicitacaoalteracaoendereco);
    }

    /**
       * Teste para retornar a solicitação de alteração de endereço
       * @todo melhorar cenário
       * @param FunctionalTester $I
       */
      public function retornarSolicitacaoAlteracaoEndereco(FunctionalTester $I)
      {
          // cenario
          $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
          $solicitacao = $I->haveInDatabaseSolicitacoesalteracoesenderecos($I, $trabalhador['trabalhador'], $this->estabelecimento);

          // funcionalidade testada
          $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao['solicitacao']}";
          $solicitacaoalteracaoendereco = $I->sendRaw('GET', $url, [], [], [], null);

          // verificação do teste
          $I->canSeeResponseCodeIs(HttpCode::OK);
          $I->assertEquals($I->getDadosBasicosSolicitacao($solicitacao), $I->getDadosBasicosSolicitacao($solicitacaoalteracaoendereco));

          $I->deleteFromDatabase('meurh.solicitacoesalteracoesenderecos', ['solicitacao' => $solicitacao['solicitacao']]);
      }

    /**
       * Teste para criar solicitações de alterações de endereço
       * @param FunctionalTester $I
       */
      public function criaSolicitacaoAlteracoesEnderecos(FunctionalTester $I)
      {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao = [
            'trabalhador' => $trabalhador['trabalhador'],
            'paisresidencia' => [
                'pais' => '1058'
            ],
            'municipioresidencia' => [
                'ibge' => '3304409'
            ],
            'tipologradouro' => [
                'tipologradouro' => 'R'
            ],
            'logradouro' =>  'Avenida Rio branco',
            'bairro' =>  'Centro',
            'numero' =>  '45',
            'cep' =>  '20090003',
            'telefone' =>  '2122222222',
            'email' =>  'usuariosolicitacoesendereco@nasajon.com.br',
            'justificativa' =>  'Mudança de endereço',
            'observacao' =>  'Teste observacao'
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/";
        // funcionalidade testada
        $solicitacao_criada = $I->sendRaw('POST', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $solicitacao['solicitacao'] = $solicitacao_criada['solicitacao']; //colocando chave primária no array original para verificar se todas as informações estão no banco

        // Retirar estes dados devido erro de conversão do canSeeInDatabase
        unset($solicitacao['paisresidencia']);
        unset($solicitacao['tipologradouro']);
        unset($solicitacao['municipioresidencia']);

        $I->canSeeInDatabase('meurh.solicitacoesalteracoesenderecos', $solicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesalteracoesenderecos', ['solicitacao' => $solicitacao_criada['solicitacao']]);
      }

    /**
       * Teste para editar uma solicitação de alteração de endereço
       * @param FunctionalTester $I
       */
      public function editarSolicitacaoAlteracoesEnderecos(FunctionalTester $I)
      {
        // cenario
        $trabalhador = $I->haveInDatabaseTrabalhador($I, $this->tenant_numero, $this->estabelecimento, $this->conta, '932fc1cd-16b3-494e-9701-3dbb8d1e73b7', '001');
        $solicitacao_atual = $I->haveInDatabaseSolicitacoesalteracoesenderecos($I, $trabalhador['trabalhador'], $this->estabelecimento);

        $solicitacao = [
            'trabalhador' => $trabalhador['trabalhador'],
            'paisresidencia' => [
                'pais' => '1058'
            ],
            'municipioresidencia' => [
                'ibge' => '3304409'
            ],
            'tipologradouro' => [
                'tipologradouro' => 'R'
            ],
            'logradouro' =>  'Avenida Rio branco',
            'complemento' => 'Sala 1803',
            'bairro' =>  'Centro',
            'numero' =>  '45',
            'cep' =>  '20090003',
            'dddtel' => '11',
            'telefone' =>  '988888888',
            'dddcel' => '11',
            'celular' =>  '37083708',
            'email' =>  'usuariosolicitacoesendereco@nasajon.com.br',
            'justificativa' =>  'Mudança de endereço',
            'observacao' =>  'Teste observacao'
        ];

        $url = "{$this->url_base}/{$this->estabelecimento}/{$trabalhador['trabalhador']}/{$this->url_base_casouso}/{$solicitacao_atual['solicitacao']}";
        // funcionalidade testada
        $I->sendRaw('PUT', $url, $solicitacao, [], [], null);

        // validação do resultado
        $I->seeResponseCodeIs(HttpCode::OK);

        // Retirar estes dados devido erro de conversão do canSeeInDatabase
        unset($solicitacao['paisresidencia']);
        unset($solicitacao['tipologradouro']);
        unset($solicitacao['municipioresidencia']);

        $I->canSeeInDatabase('meurh.solicitacoesalteracoesenderecos', $solicitacao);

        /* remove dado criado no banco*/
        $I->deleteFromDatabase('meurh.solicitacoesalteracoesenderecos', ['solicitacao' => $solicitacao_atual['solicitacao']]);
      }
}