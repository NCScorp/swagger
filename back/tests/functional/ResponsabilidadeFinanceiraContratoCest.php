<?php

use Codeception\Util\HttpCode;

/**
 * Testa fornecedores vinculados
 * 
 * [ok] criar Responsabilidade Financeira Proposta Item
 * [ok] criar Responsabilidade Financeira Proposta Item Funcao
 * [ok] criar Responsabilidade Financeira Proposta Item Familia
 * [ok] alterar Responsabilidade Financeira
 * [ok] excluir Responsabilidade Financeira
 * [ok] get Responsabilidade Financeira
 * [ok] get All Responsabilidade Financeira
 * [ok] salvar Lote Responsabilidade Financeira Atc
 * [ok] atualizar Lote Responsabilidade Financeira Atc
 * 
 */
class ResponsabilidadeFinanceiraContratoCest
{
    private $url_base = '/api/gednasajon';
    private $url_complemento_responsabilidadesfinanceiras = 'responsabilidadesfinanceiras';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $id_grupoempresarial = "95cd450c-30c5-4172-af2b-cdece39073bf";
    private $grupoempresarial = 'FMA';

    /**
     * Retorna o código do método de acionamento do fornecedor de acordo com o nome passado
     */
    private function getAcionamentoMetodo($pMetodo) {
        $arrAcionamentoMetodos = [
            'sistema' => 1,
            'email' => 2,
            'telefone' => 3
        ];

        return $arrAcionamentoMetodos[$pMetodo];
    }

    /**
    *
    * @param FunctionalTester $I
    */
    public function _before(FunctionalTester $I){
        $I->amSamlLoggedInAs('rodrigodirk@nasajon.com.br');
        $this->id_grupoempresarial = $I->grabColumnFromDatabase('ns.gruposempresariais', 'grupoempresarial', [
            'codigo' => $this->grupoempresarial,
            'tenant' => $this->tenant_numero
        ])[0];
    }

    /**
    *
    * @param FunctionalTester $I
    */
    public function _after(FunctionalTester $I){}

    /**
     * Cria registro referente a responsabilidade financeira com o serviço de propostasitens
     * @param FunctionalTester $I
     * @return void
     */
    public function criarResponsabilidadeFinanceiraOrcamento(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);

        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);

        $dados = [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento['orcamento'],
            'valorservico' => 10,
            'tipodivisao' => 1,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'responsavelfinanceiro' => $cliente['cliente'],
                    'valorpagar' => 10
                ]
            ]
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/?grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);
     
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $I->assertEquals($dados['negocio'], $retorno['negocio']);
        $I->assertEquals($dados['orcamento'], $retorno['orcamento']);
        $I->assertEquals($this->tenant_numero, $retorno['tenant']);

        //Verifico no banco se meu registro está lá
        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $retorno['responsabilidadefinanceira'],
            'negocio' => $retorno['negocio'],
            'orcamento' => $retorno['orcamento'],
            // 'propostaitem' => $retorno['propostaitem'],
            'valorservico' => 10,
            'tenant' => $retorno['tenant']
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'responsabilidadefinanceira' => $retorno['responsabilidadefinanceira'],
            'id_grupoempresarial' => $retorno['id_grupoempresarial'],
            'responsavelfinanceiro' => $cliente['cliente'],
            'valorpagar' => 10,
            'tenant' => $retorno['tenant']
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.responsabilidadesfinanceirasvalores', [
            'responsabilidadefinanceira' => $retorno['responsabilidadefinanceira'],
            'id_grupoempresarial' => $retorno['id_grupoempresarial'],
            'tenant' => $retorno['tenant']
        ]);

        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $retorno['responsabilidadefinanceira'],
            'tenant' => $retorno['tenant']
        ]);
    }

    /**
     * Atualiza registro referente a responsabilidade financeira, alterando o cliente
     * @param FunctionalTester $I
     * @return void
     */
    public function alterarResponsabilidadeFinanceira(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);

        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);


        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento);

        $cliente2 = $I->haveInDatabaseCliente($I, $municipio, $pais);

        $dados = [
            'responsabilidadefinanceira' => $responsabilidadefinanceira['responsabilidadefinanceira'],
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento['orcamento'],
            'tipodivisao' => 1,
            'valorservico' => 15,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente2['cliente']
                ]
            ]
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('PUT', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/{$responsabilidadefinanceira['responsabilidadefinanceira']}?grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        //Verifico no banco se meu registro alterado está lá com o cliente atualizado
        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $responsabilidadefinanceira['responsabilidadefinanceira'],
            'valorservico' => $dados['valorservico'],
            'tipodivisao' => $dados['tipodivisao'],
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'responsavelfinanceiro' => $cliente2['cliente'],
            'valorpagar' => 15,
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $this->id_grupoempresarial
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.responsabilidadesfinanceirasvalores', [
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Exclui registro referente a responsabilidade financeira
     * @param FunctionalTester $I
     * @return void
     */
    public function excluirResponsabilidadeFinanceira(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);

        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);

        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('DELETE', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/{$responsabilidadefinanceira['responsabilidadefinanceira']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        //Verifico no banco se meu registro foi apagado
        $I->cantSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $responsabilidadefinanceira['responsabilidadefinanceira'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Busca registro referente a responsabilidade financeira
     * @param FunctionalTester $I
     * @return void
     */
    public function getResponsabilidadeFinanceira(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);

        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);

        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/{$responsabilidadefinanceira['responsabilidadefinanceira']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($responsabilidadefinanceira['negocio'], $retorno['negocio']);
        $I->assertEquals($responsabilidadefinanceira['orcamento'], $retorno['orcamento']);
        $I->assertEquals($responsabilidadefinanceira['tenant'], $retorno['tenant']);
    }

    /**
     * Busca lista de registros referente a responsabilidade financeira
     * @param FunctionalTester $I
     * @return void
     */
    public function getAllResponsabilidadeFinanceira(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);

        //Crio mais de um responsável financeiro
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $cliente2 = $I->haveInDatabaseCliente($I, $municipio, $pais);

        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $composicao = $I->haveInDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $funcao = $I->haveInDatabaseFuncao($I);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
        
        //Crio mais de um serviço
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem2 = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem3 = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem3, $familia, $composicao, $composicaofamilia);
        $propostaitemfuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem3, $funcao, $composicao, $composicaofuncao);

        $orcamento1 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento2 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento3 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento4 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento1);
        $I->haveInDatabase('crm.orcamentos', $orcamento2);
        $I->haveInDatabase('crm.orcamentos', $orcamento3);
        $I->haveInDatabase('crm.orcamentos', $orcamento4);

        //Separo as responsabilidades de cada um
        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente2['cliente']
                ]
            ]
        ], $atc, $orcamento1);
        $responsabilidadefinanceira2 = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento2);
        $responsabilidadefinanceira3 = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento3);
        $responsabilidadefinanceira4 = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento4);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(true, is_array($retorno));
        $I->assertEquals(4, count($retorno));
    }

    /**
     * Salva os dados da responsabilidade financeira em lote pela primeira vez
     * @param FunctionalTester $I
     * @return void
     */
    public function salvarLoteResponsabilidadeFinanceiraAtc(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);

        //Crio mais de um responsável financeiro
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $cliente2 = $I->haveInDatabaseCliente($I, $municipio, $pais);

        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $composicao = $I->haveInDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $funcao = $I->haveInDatabaseFuncao($I);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
        
        //Crio mais de um serviço
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem2 = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem3 = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem3, $familia, $composicao, $composicaofamilia);
        $propostaitemfuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem2, $funcao, $composicao, $composicaofuncao);

        $dados_requisicao = [
            'negocio' => $atc['negocio'],
            'responsabilidadesfinanceiras' => []
        ];

        $orcamento1 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento2 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento3 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento4 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento1);
        $I->haveInDatabase('crm.orcamentos', $orcamento2);
        $I->haveInDatabase('crm.orcamentos', $orcamento3);
        $I->haveInDatabase('crm.orcamentos', $orcamento4);

        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento1['orcamento'],
            'tipodivisao' => 1,
            'valorservico' => 5,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 5,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ];
        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento2['orcamento'],
            'tipodivisao' => 1,
            'valorservico' => 10,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente2['cliente']
                ]
            ]
        ];
        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento3['orcamento'],
            'tipodivisao' => 1,
            'valorservico' => 15,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ];
        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento4['orcamento'],
            'tipodivisao' => 1,
            'valorservico' => 20,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 20,
                    'responsavelfinanceiro' => $cliente2['cliente']
                ]
            ]
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/salvarlote/?grupoempresarial={$this->grupoempresarial}" , $dados_requisicao, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        
        //Verifico no banco se meus registros estão lá
        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento1['orcamento'],
            'tipodivisao' =>  $dados_requisicao['responsabilidadesfinanceiras'][0]['tipodivisao'],
            'valorservico' =>  $dados_requisicao['responsabilidadesfinanceiras'][0]['valorservico'],
            // 'propostaitem' => $dados_requisicao['responsabilidadesfinanceiras'][0]['propostaitem'],
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'responsavelfinanceiro' => $cliente['cliente'],
            'valorpagar' => 5,
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento2['orcamento'],
            'tipodivisao' =>  $dados_requisicao['responsabilidadesfinanceiras'][1]['tipodivisao'],
            'valorservico' =>  $dados_requisicao['responsabilidadesfinanceiras'][1]['valorservico'],
            // 'propostaitem' => $dados_requisicao['responsabilidadesfinanceiras'][1]['propostaitem'],
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'responsavelfinanceiro' => $cliente2['cliente'],
            'valorpagar' => 10,
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento3['orcamento'],
            'tipodivisao' =>  $dados_requisicao['responsabilidadesfinanceiras'][2]['tipodivisao'],
            'valorservico' =>  $dados_requisicao['responsabilidadesfinanceiras'][2]['valorservico'],
            // 'propostaitem' => $dados_requisicao['responsabilidadesfinanceiras'][2]['propostaitem'],
            // 'propostaitemfamilia' => $dados_requisicao['responsabilidadesfinanceiras'][2]['propostaitemfamilia'],
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'responsavelfinanceiro' => $cliente['cliente'],
            'valorpagar' => 15,
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento4['orcamento'],
            'tipodivisao' =>  $dados_requisicao['responsabilidadesfinanceiras'][3]['tipodivisao'],
            'valorservico' =>  $dados_requisicao['responsabilidadesfinanceiras'][3]['valorservico'],
            // 'propostaitem' => $dados_requisicao['responsabilidadesfinanceiras'][3]['propostaitem'],
            // 'propostaitemfuncao' => $dados_requisicao['responsabilidadesfinanceiras'][3]['propostaitemfuncao'],
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'responsavelfinanceiro' => $cliente2['cliente'],
            'valorpagar' => 20,
            'tenant' => $this->tenant_numero
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.responsabilidadesfinanceirasvalores', [
            'tenant' => $this->tenant_numero
        ]); 
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento1['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento2['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento3['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento4['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Salva, atualiza ou exclui os dados da responsabilidade financeira em lote, de acordo com os registros previamente cadastrados
     * @param FunctionalTester $I
     * @return void
     */
    public function atualizarLoteResponsabilidadeFinanceiraAtc(FunctionalTester $I){
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);

        //Crio mais de um responsável financeiro
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $cliente2 = $I->haveInDatabaseCliente($I, $municipio, $pais);

        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $composicao = $I->haveInDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $funcao = $I->haveInDatabaseFuncao($I);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
        
        //Crio mais de um serviço
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem2 = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem3 = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem3, $familia, $composicao, $composicaofamilia);
        $propostaitemfuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem2, $funcao, $composicao, $composicaofuncao);


        $orcamento1 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento2 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento3 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $orcamento4 = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => null,
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 1,
            'valorreceber' => 1,
            'status' => 1,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'faturar' => false,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento1);
        $I->haveInDatabase('crm.orcamentos', $orcamento2);
        $I->haveInDatabase('crm.orcamentos', $orcamento3);
        $I->haveInDatabase('crm.orcamentos', $orcamento4);

        //Crio algumas responsabilidades financeiras no banco
        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente2['cliente']
                ]
            ]
        ], $atc, $orcamento1);
        $responsabilidadefinanceira2 = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento2);
        $responsabilidadefinanceira3 = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 10,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 10,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ], $atc, $orcamento3);

        $dados_requisicao = [
            'negocio' => $atc['negocio'],
            'responsabilidadesfinanceiras' => []
        ];

        //Dados que devem ser atualizados
        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'responsabilidadefinanceira' => $responsabilidadefinanceira['responsabilidadefinanceira'],
            'negocio' => $responsabilidadefinanceira['negocio'],
            'orcamento' =>  $responsabilidadefinanceira['orcamento'],
            // 'propostaitem' => $responsabilidadefinanceira['propostaitem'],
            'tipodivisao' => $responsabilidadefinanceira['tipodivisao'],
            'valorservico' => 30,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 30,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ];
        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'responsabilidadefinanceira' => $responsabilidadefinanceira2['responsabilidadefinanceira'],
            'negocio' => $responsabilidadefinanceira2['negocio'],
            'orcamento' =>  $responsabilidadefinanceira2['orcamento'],
            // 'propostaitem' => $responsabilidadefinanceira2['propostaitem'],
            'tipodivisao' => $responsabilidadefinanceira['tipodivisao'],
            'valorservico' => 20,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'responsabilidadefinanceiravalor' => $responsabilidadefinanceira2['responsabilidadesfinanceirasvalores'][0]['responsabilidadefinanceiravalor'],
                    'valorpagar' => 20,
                    'responsavelfinanceiro' => $cliente['cliente']
                ]
            ]
        ];

        // Dados que devem ser criados
        $dados_requisicao['responsabilidadesfinanceiras'][] = [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento4['orcamento'],
            // 'propostaitem' => $propostaitem2['propostaitem'],
            // 'propostaitemfuncao' => $propostaitemfuncao['propostaitemfuncao'],
            'tipodivisao' => $responsabilidadefinanceira['tipodivisao'],
            'valorservico' => 15,
            'faturamentotipo' => 2,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente2['cliente']
                ]
            ]
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_responsabilidadesfinanceiras}/salvarlote/?grupoempresarial={$this->grupoempresarial}" , $dados_requisicao, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        
        //Verifica se os dados foram atualizados
        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $responsabilidadefinanceira['responsabilidadefinanceira'],
            'negocio' => $responsabilidadefinanceira['negocio'],
            'valorservico' => 30,
            'orcamento' =>  $responsabilidadefinanceira['orcamento'],
            // 'propostaitem' => $responsabilidadefinanceira['propostaitem'],
            'tenant' => $this->tenant_numero
        ]);

        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $responsabilidadefinanceira2['responsabilidadefinanceira'],
            'negocio' => $responsabilidadefinanceira2['negocio'],
            'valorservico' => 20,
            'orcamento' =>  $responsabilidadefinanceira2['orcamento'],
            // 'propostaitem' => $responsabilidadefinanceira2['propostaitem'],
            'tenant' => $this->tenant_numero
        ]);

        //Verifica se os dados foram criados
        $I->canSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento4['orcamento'],
            'valorservico' => 15,
            // 'propostaitem' => $dados_requisicao['responsabilidadesfinanceiras'][2]['propostaitem'],
            // 'propostaitemfuncao' => $dados_requisicao['responsabilidadesfinanceiras'][2]['propostaitemfuncao'],
            'tenant' => $this->tenant_numero
        ]);

        //Verifica se os dados foram criados
        $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'responsavelfinanceiro' => $cliente2['cliente'],
            'valorpagar' => 15,
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'tenant' => $this->tenant_numero
        ]);
        
        //Verifica se os itens foram deletados
        $I->cantSeeInDatabase('crm.responsabilidadesfinanceiras', [
            'responsabilidadefinanceira' => $responsabilidadefinanceira3['responsabilidadefinanceira'],
            'tenant' => $this->tenant_numero
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.responsabilidadesfinanceirasvalores', [
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $this->id_grupoempresarial
        ]); 

        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento1['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento2['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento3['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.responsabilidadesfinanceiras', [
            'negocio' => $atc['negocio'],
            'orcamento' =>  $orcamento4['orcamento'],
            'tenant' => $this->tenant_numero
        ]);
    }
}