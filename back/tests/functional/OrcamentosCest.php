<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Funções
 */
class OrcamentosCest{
    private $url_base = '/api/gednasajon/orcamentos/';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = 'b4c12f6c-e637-48e3-a858-cf5a04e12603';
    private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'; 

    /**
     *
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::ORCAMENTOS_CREATE, EnumAcao::ORCAMENTOS_APROVAR, EnumAcao::ORCAMENTOS_ENVIAR]);
    }

    /**
     *
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
        $I->deleteAllFromDatabase('crm.orcamentos');
    }

    /**
     * Cria um orçamento para um serviço
     * @param FunctionalTester $I
     */
    public function criarOrcamentoDeServico(FunctionalTester $I){
        /* inicializações */
        $cliente = $I->haveinDatabaseCliente($I);
        $area = $I->haveinDatabaseAreaDeAtc($I);
        $origem = $I->haveinDatabaseMidia($I);
        $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveinDatabaseProposta($I, $atc);
        $fornecedor = $I->haveinDatabaseFornecedor($I);
        $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $composicao = $I->haveinDatabaseComposicao($I, [
            'servicoprestadoraacionada' => true
        ]);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 50
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 25
        ]);
        $orcamento3= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 0,
            'faturamentotipo' => 1 // Não faturar
        ]);
        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1, null, true, [
            'possuidescontoglobal' => true,
            'descontoglobal' => 10,
            'descontoglobaltipo' => 1 // Valor
        ]);

        $orcamento = [
            'fornecedor' => [ "fornecedor" => $fornecedor['fornecedor']],
            'composicao' => $composicao['composicao'],
            'itemfaturamento' => null,
            'propostaitem' => $propostaItem['propostaitem'],
            'atc' => $atc['negocio'],
            'quantidade' => 1,
            'valorunitario' => 25,
            'valorreceber' => 10,
            'faturamentotipo' => 2,
            'status' => 2,
            'acrescimo' => 0,
            'desconto' => 5,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ];

        /* execução da funcionalidade */
        $orcamentoCriado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        try {
            $I->assertEquals($fornecedor['fornecedor'], $orcamentoCriado['fornecedor']['fornecedor']);
            $I->assertEquals($orcamento['atc'], $orcamentoCriado['atc']);
            $I->assertEquals($orcamento['composicao'], $orcamentoCriado['composicao']);
            $I->assertEquals($composicao['nome'], $orcamentoCriado['descricao']);
            $I->assertEquals($orcamento['valorunitario'], $orcamentoCriado['valorunitario']);
            $I->assertEquals($orcamento['quantidade'], $orcamentoCriado['quantidade']);
            $I->assertEquals(25, $orcamentoCriado['valor']);
            $I->assertEquals($orcamento['valorreceber'], $orcamentoCriado['valorreceber']);
            $I->assertEquals($orcamento['acrescimo'], $orcamentoCriado['acrescimo']);
            $I->assertEquals($orcamento['desconto'], $orcamentoCriado['desconto']);
            $I->assertEquals(2, $orcamentoCriado['servicotipo']);
            $I->assertEquals($orcamento['tenant'], $orcamentoCriado['tenant']);
            $I->assertEquals($orcamentoCriado['descontoglobalunitario'], 3.33);
            $I->assertEquals($orcamentoCriado['descontoglobalresto'], 3.34);
    
            $I->canSeeInDatabase('crm.orcamentos', [
                'orcamento' => $orcamentoCriado['orcamento'],
                'descontoglobal' => ($orcamentoCriado['orcamento'] == $orcamentoCriado['descontoglobalrestoorcamento']) ? 3.34 : 3.33,
                'tenant' => $this->tenant_numero
            ]);
    
            $I->canSeeInDatabase('crm.orcamentos', [
                'descontoglobal' => ($orcamentoCriado['orcamento'] == $orcamentoCriado['descontoglobalrestoorcamento']) ? 3.33 : 3.34,
                'atc' => $atc['negocio'],
                'fornecedor' => $fornecedor['fornecedor'],
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Excluo registros
            $I->deleteFromDatabase('crm.orcamentos', [
                'atc' => $atc['negocio'],
                'fornecedor' => $fornecedor['fornecedor'],
                'tenant' => $this->tenant_numero
            ]);
            $I->deleteFromDatabase('crm.historicoatcs', [
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]);
        }
    }

    /**
     * Cria um orçamento para um serviço, sem passar o propostaitem
     * Nesse caso o propostaitem deve ser criado e vinculado ao fornecedor
     * @param FunctionalTester $I
     */
    public function criarOrcamentoDeServicoSemPropostaItem(FunctionalTester $I){
        /* inicializações */
        $cliente = $I->haveinDatabaseCliente($I);
        $area = $I->haveinDatabaseAreaDeAtc($I);
        $origem = $I->haveinDatabaseMidia($I);
        $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveinDatabaseProposta($I, $atc);
        $fornecedor = $I->haveinDatabaseFornecedor($I);
        $composicao = $I->haveinDatabaseComposicao($I);

        $orcamento = [
            'fornecedor' => [ "fornecedor" => $fornecedor['fornecedor']],
            'composicao' => $composicao['composicao'],
            'itemfaturamento' => null,
            'proposta' => $proposta['proposta'],
            'atc' => $atc['negocio'],
            'quantidade' => 2,
            'valorunitario' => 10,
            'valorreceber' => 10,
            'status' => 2,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ];

        /* execução da funcionalidade */
        $orcamentoCriado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($fornecedor['fornecedor'], $orcamentoCriado['fornecedor']['fornecedor']);
        $I->assertEquals($orcamento['atc'], $orcamentoCriado['atc']);
        $I->assertEquals($orcamento['composicao'], $orcamentoCriado['composicao']);
        $I->assertEquals($composicao['nome'], $orcamentoCriado['descricao']);
        $I->assertEquals($orcamento['valorunitario'], $orcamentoCriado['valorunitario']);
        $I->assertEquals($orcamento['quantidade'], $orcamentoCriado['quantidade']);
        $I->assertEquals(20, $orcamentoCriado['valor']);
        $I->assertEquals($orcamento['valorreceber'], $orcamentoCriado['valorreceber']);
        $I->assertEquals($orcamento['acrescimo'], $orcamentoCriado['acrescimo']);
        $I->assertEquals($orcamento['desconto'], $orcamentoCriado['desconto']);
        $I->assertEquals($orcamento['tenant'], $orcamentoCriado['tenant']);
        $I->canSeeInDatabase('crm.propostasitens', [
            'propostaitem' => $orcamentoCriado['propostaitem'],
            'negocio' => $orcamentoCriado['atc'],
            'fornecedor' => $fornecedor['fornecedor'],
            'tenant' => $this->tenant_numero
        ]);

        // Excluo registros
        $I->deleteFromDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.propostasitens', [
            'negocio' => $atc['negocio'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Cria um orçamento para um serviço
     * @param FunctionalTester $I
     */
    public function criarOrcamentoDeMercadoria(FunctionalTester $I){
        /* inicializações */
        $cliente = $I->haveinDatabaseCliente($I);
        $area = $I->haveinDatabaseAreaDeAtc($I);
        $origem = $I->haveinDatabaseMidia($I);
        $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveinDatabaseProposta($I, $atc);
        $fornecedor = $I->haveinDatabaseFornecedor($I);
        $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $familia = $I->haveInDatabaseFamilia($I);

        $orcamento = [
            'fornecedor' => [ "fornecedor" => $fornecedor['fornecedor']],
            'familia' => $familia['familia'],
            'itemfaturamento' => null,
            'propostaitem' => $propostaItem['propostaitem'],
            'atc' => $atc['negocio'],
            'quantidade' => 2,
            'valorunitario' => 10,
            'valorreceber' => 10,
            'status' => 2,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ];

        /* execução da funcionalidade */
        $orcamentoCriado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($fornecedor['fornecedor'], $orcamentoCriado['fornecedor']['fornecedor']);
        $I->assertEquals($orcamento['atc'], $orcamentoCriado['atc']);
        $I->assertEquals($orcamento['familia'], $orcamentoCriado['familia']);
        $I->assertEquals($familia['descricao'], $orcamentoCriado['descricao']);
        $I->assertEquals($orcamento['valorunitario'], $orcamentoCriado['valorunitario']);
        $I->assertEquals($orcamento['quantidade'], $orcamentoCriado['quantidade']);
        $I->assertEquals(20, $orcamentoCriado['valor']);
        $I->assertEquals($orcamento['valorreceber'], $orcamentoCriado['valorreceber']);
        $I->assertEquals($orcamento['acrescimo'], $orcamentoCriado['acrescimo']);
        $I->assertEquals($orcamento['desconto'], $orcamentoCriado['desconto']);
        $I->assertEquals($orcamento['tenant'], $orcamentoCriado['tenant']);

        // Excluo registros
        $I->deleteFromDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Tenta criar um orçamento para um serviço
     * @param FunctionalTester $I
     */
    public function criarOrcamentoSemServicoMercadoriaErro(FunctionalTester $I){
        /* inicializações */
        $cliente = $I->haveinDatabaseCliente($I);
        $area = $I->haveinDatabaseAreaDeAtc($I);
        $origem = $I->haveinDatabaseMidia($I);
        $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveinDatabaseProposta($I, $atc);
        $fornecedor = $I->haveinDatabaseFornecedor($I);
        $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $familia = $I->haveInDatabaseFamilia($I);

        $orcamento = [
            'fornecedor' => [ "fornecedor" => $fornecedor['fornecedor']],
            'itemfaturamento' => null,
            'propostaitem' => $propostaItem['propostaitem'],
            'atc' => $atc['negocio'],
            'quantidade' => 2,
            'valorunitario' => 10,
            'valorreceber' => 10,
            'status' => 2,
            'acrescimo' => 0,
            'desconto' => 0,
            'acrescimomotivo' => null,
            'descontomotivo' => null,
            'motivo' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $this->grupoempresarial_id
        ];

        /* execução da funcionalidade */
        $orcamentoCriado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);

        // Excluo registros
        $I->deleteFromDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'tenant' => $this->tenant_numero
        ]);
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Alterar um orçamento
     * @param FunctionalTester $I
     */
    public function alterar(FunctionalTester $I){
        /* inicializações */
        $cliente = $I->haveinDatabaseCliente($I);
        $area = $I->haveinDatabaseAreaDeAtc($I);
        $origem = $I->haveinDatabaseMidia($I);
        $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveinDatabaseProposta($I, $atc);
        $fornecedor = $I->haveinDatabaseFornecedor($I);
        $composicao = $I->haveinDatabaseComposicao($I);
        
        $orcamento = $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao']
        ]);
        $orcamento['valorunitario'] = 10;
        $orcamento['quantidade'] = 2;
        $orcamento['valorreceber'] = 30;
        $orcamento['desconto'] = 5;
        $orcamento['faturamentotipo'] = 2;
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_base . $orcamento['orcamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('crm.orcamentos', [
            'orcamento' => $orcamento['orcamento'], 
            'valorunitario' => $orcamento['valorunitario'],
            'quantidade' => $orcamento['quantidade'],
            'valor' => 20,
            'valorreceber' => $orcamento['valorreceber'],
            'desconto' => $orcamento['desconto'],
            'faturamentotipo' => $orcamento['faturamentotipo']
        ]);

        // Excluo registros
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * Exclui um orçamento
     * @param FunctionalTester $I
     */
    public function excluir(FunctionalTester $I){
        /* inicializações */
        $cliente = $I->haveinDatabaseCliente($I);
        $area = $I->haveinDatabaseAreaDeAtc($I);
        $origem = $I->haveinDatabaseMidia($I);
        $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
        $proposta = $I->haveinDatabaseProposta($I, $atc);
        $fornecedor = $I->haveinDatabaseFornecedor($I);
        $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveinDatabaseComposicao($I);
        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1, null, true, [
            'possuidescontoglobal' => true,
            'descontoglobal' => 10,
            'descontoglobaltipo' => 1 // Valor
        ]);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 50
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 50
        ]);
        $orcamento3 = $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'], 
            'status' => 0, // Aberto
            'valor' => 25,
            'propostaitem' => $propostaItem['propostaitem'],
            'familia' => $familia['familia']

        ]);
        
        /* execução da funcionalidade */
        $retorno = $I->sendRaw('DELETE', $this->url_base . $orcamento3['orcamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        try {
            $I->assertEquals($retorno['descontoglobalunitario'], 5);
            $I->assertEquals($retorno['descontoglobalresto'], 5);

            $I->cantSeeInDatabase('crm.orcamentos', [
                'orcamento' => $orcamento3['orcamento']
            ]);
            $I->canSeeInDatabase('crm.orcamentos', [
                'descontoglobal' => 5,
                'atc' => $atc['negocio'],
                'fornecedor' => $fornecedor['fornecedor'],
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Excluo registros
            $I->deleteFromDatabase('crm.historicoatcs', [
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]);
        }
    }

    /**
   * @param FunctionalTester $I
   */
  public function aprovarOrcamento(FunctionalTester $I)
  {
    /* inicializações */
    $cliente = $I->haveinDatabaseCliente($I);
    $area = $I->haveinDatabaseAreaDeAtc($I);
    $origem = $I->haveinDatabaseMidia($I);
    $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
    $proposta = $I->haveinDatabaseProposta($I, $atc);
    $fornecedor = $I->haveinDatabaseFornecedor($I);
    $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveinDatabaseComposicao($I);
    $orcamento = $I->haveInDatabaseOrcamento($I, [
        'atc' => $atc['negocio'],
        'composicao' => $composicao['composicao']
    ]);
    $orcamentoaprovar = [
        'orcamento' => $orcamento['orcamento'],
        'tenant' => $this->tenant_numero,
        'id_grupoempresarial' => $this->grupoempresarial_id
    ];
   
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url_base . $orcamento['orcamento'] . '/aprovar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentoaprovar, [], [], null);
    
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $orcamento['status'] = 2;
    $I->canSeeInDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento'], 'status' => 2]); /*validando se o status está 2*/
  }

    /**
   * @param FunctionalTester $I
   */
  public function enviarOrcamento(FunctionalTester $I)
  {
    /* inicializações */
    $cliente = $I->haveinDatabaseCliente($I);
    $area = $I->haveinDatabaseAreaDeAtc($I);
    $origem = $I->haveinDatabaseMidia($I);
    $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
    $proposta = $I->haveinDatabaseProposta($I, $atc);
    $fornecedor = $I->haveinDatabaseFornecedor($I);
    $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveinDatabaseComposicao($I);
    $orcamento = $I->haveInDatabaseOrcamento($I, [
        'atc' => $atc['negocio'],
        'composicao' => $composicao['composicao']
    ]);
    $orcamentoenviar = [
        'orcamento' => $orcamento['orcamento'],
        'tenant' => $this->tenant_numero,
        'id_grupoempresarial' => $this->grupoempresarial_id
    ];
   
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url_base . $orcamento['orcamento'] . '/enviar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentoenviar, [], [], null);
    
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $orcamento['status'] = 1;
    $I->canSeeInDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento'], 'status' => 1]); /*validando se o status está 1*/
  }

    /**
   * @param FunctionalTester $I
   */
  public function renegociarOrcamento(FunctionalTester $I)
  {
    /* inicializações */
    $cliente = $I->haveinDatabaseCliente($I);
    $area = $I->haveinDatabaseAreaDeAtc($I);
    $origem = $I->haveinDatabaseMidia($I);
    $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
    $proposta = $I->haveinDatabaseProposta($I, $atc);
    $fornecedor = $I->haveinDatabaseFornecedor($I);
    $composicao = $I->haveinDatabaseComposicao($I);
    $orcamento = $I->haveInDatabaseOrcamento($I, [
        'atc' => $atc['negocio'],
        'composicao' => $composicao['composicao']
    ]);
    $orcamentorenegociar = [
        'orcamento' => $orcamento['orcamento'],
        'tenant' => $this->tenant_numero,
        'id_grupoempresarial' => $this->grupoempresarial_id
    ];
   
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url_base . $orcamento['orcamento'] . '/renegociar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentorenegociar, [], [], null);
    
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $orcamento['status'] = 3;
    $I->canSeeInDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento'], 'status' => 3]); /*validando se o status está 3*/
  }

    /**
   * @param FunctionalTester $I
   */
  public function reprovarOrcamento(FunctionalTester $I)
  {
    /* inicializações */
    $cliente = $I->haveinDatabaseCliente($I);
    $area = $I->haveinDatabaseAreaDeAtc($I);
    $origem = $I->haveinDatabaseMidia($I);
    $atc = $I->haveinDatabaseAtc($I, $area, $origem, $cliente);
    $proposta = $I->haveinDatabaseProposta($I, $atc);
    $fornecedor = $I->haveinDatabaseFornecedor($I);
    $propostaItem = $I->haveinDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveinDatabaseComposicao($I);
    $orcamento = $I->haveInDatabaseOrcamento($I, [
        'atc' => $atc['negocio'],
        'composicao' => $composicao['composicao']
    ]);
    $orcamentoreprovar = [
        'orcamento' => $orcamento['orcamento'],
        'tenant' => $this->tenant_numero,
        'id_grupoempresarial' => $this->grupoempresarial_id
    ];
   
    /* execução da funcionalidade */
    $I->sendRaw('POST', $this->url_base . $orcamento['orcamento'] . '/reprovar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentoreprovar, [], [], null);
    
    // /* validação do resultado */
    $I->canSeeResponseCodeIs(HttpCode::OK);
    $orcamento['status'] = 4;
    $I->canSeeInDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento'], 'status' => 4]); /*validando se o status está 4*/
  }
  
}