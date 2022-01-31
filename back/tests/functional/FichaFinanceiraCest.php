<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa fornecedores vinculados
 * 
 * [ok] Aciona fornecedor via sistema
 * [ok] Aciona fornecedor via email
 * [ok] Aciona fornecedor via telefone
 * [ok] Cancela acionamento do fornecedor
 * [ok] get acionamento do fornecedor
 * [ok] getAll acionamento do fornecedor
 */
class FichaFinanceiraCest
{
    private $url_base = '/api/gednasajon';
    private $url_complemento_fornecedoresenvolvidos = 'fornecedoresenvolvidos';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

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
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::FORNECEDORESENVOLVIDOS_CREATE, EnumAcao::FORNECEDORESENVOLVIDOS_DELETE, EnumAcao::ORCAMENTOS_REABRIR]);
    }

    /**
    *
    * @param FunctionalTester $I
    */
    public function _after(FunctionalTester $I){
    }

    /**
     * Aciona fornecedor via sistema
     * @param FunctionalTester $I
     * @return void
     */
    public function acionaFornecedorViaSistema(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $proposta = $I->haveInDatabaseProposta($I, $atc);

        $acionamentoMetodo = $this->getAcionamentoMetodo('sistema');

        $dados = [
            'negocio' => $atc['negocio'],
            'fornecedor' => [
                'fornecedor' => $fornecedor['fornecedor']
            ],
            'proposta' => $proposta['proposta'],
            'acionamentometodo' => $acionamentoMetodo,
            'acionamentorespostaprazo' => 10
        ];

        /* execução da funcionalidade */
        $fornecedorenvolvido = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/?grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $I->assertEquals($fornecedorenvolvido['acionamentometodo'], $dados['acionamentometodo']);
        $I->assertEquals(floatval($fornecedorenvolvido['acionamentorespostaprazo']), floatval($dados['acionamentorespostaprazo']));
        $I->assertEquals($fornecedorenvolvido['acionamentoaceito'], false);
        $I->assertEquals($fornecedorenvolvido['negocio']['negocio'], $dados['negocio']);
        $I->assertEquals($fornecedorenvolvido['fornecedor']['fornecedor'], $dados['fornecedor']['fornecedor']);
        $I->assertEquals($fornecedorenvolvido['tenant'], $this->tenant_numero);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.fornecedoresenvolvidos', ['fornecedorenvolvido' => $fornecedorenvolvido['fornecedorenvolvido']]);
        $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        $I->deleteFromDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor']
        ]);
    }

    /**
     * Aciona fornecedor via email
     * @param FunctionalTester $I
     * @return void
     */
    public function acionaFornecedorViaEmail(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $proposta = $I->haveInDatabaseProposta($I, $atc);

        $acionamentoMetodo = $this->getAcionamentoMetodo('email');

        $dados = [
            'negocio' => $atc['negocio'],
            'fornecedor' => [
                'fornecedor' => $fornecedor['fornecedor']
            ],
            'proposta' => $proposta['proposta'],
            'acionamentometodo' => $acionamentoMetodo,
            'acionamentorespostaprazo' => 10
        ];

        /* execução da funcionalidade */
        $fornecedorenvolvido = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/?grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $I->assertEquals($fornecedorenvolvido['acionamentometodo'], $dados['acionamentometodo']);
        $I->assertEquals(floatval($fornecedorenvolvido['acionamentorespostaprazo']), floatval($dados['acionamentorespostaprazo']));
        $I->assertEquals($fornecedorenvolvido['acionamentoaceito'], false);
        $I->assertEquals($fornecedorenvolvido['negocio']['negocio'], $dados['negocio']);
        $I->assertEquals($fornecedorenvolvido['fornecedor']['fornecedor'], $dados['fornecedor']['fornecedor']);
        $I->assertEquals($fornecedorenvolvido['tenant'], $this->tenant_numero);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.fornecedoresenvolvidos', ['fornecedorenvolvido' => $fornecedorenvolvido['fornecedorenvolvido']]);
        $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        $I->deleteFromDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor']
        ]);
    }

    /**
     * Aciona fornecedor via telefone
     * @param FunctionalTester $I
     * @return void
     */
    public function acionaFornecedorViaTelefone(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $proposta = $I->haveInDatabaseProposta($I, $atc);

        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');

        $dados = [
            'negocio' => $atc['negocio'],
            'fornecedor' => [
                'fornecedor' => $fornecedor['fornecedor']
            ],
            'proposta' => $proposta['proposta'],
            'acionamentometodo' => $acionamentoMetodo,
            'acionamentorespostaprazo' => 10
        ];

        /* execução da funcionalidade */
        $fornecedorenvolvido = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/?grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        $I->assertEquals($fornecedorenvolvido['acionamentometodo'], $dados['acionamentometodo']);
        $I->assertEquals(floatval($fornecedorenvolvido['acionamentorespostaprazo']), floatval($dados['acionamentorespostaprazo']));
        $I->assertEquals($fornecedorenvolvido['acionamentoaceito'], true);
        $I->assertEquals($fornecedorenvolvido['negocio']['negocio'], $dados['negocio']);
        $I->assertEquals($fornecedorenvolvido['fornecedor']['fornecedor'], $dados['fornecedor']['fornecedor']);
        $I->assertEquals($fornecedorenvolvido['tenant'], $this->tenant_numero);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.fornecedoresenvolvidos', ['fornecedorenvolvido' => $fornecedorenvolvido['fornecedorenvolvido']]);
        $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        $I->deleteFromDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor']
        ]);
    }

    /**
     * Cancela acionamento do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function cancelaAcionamentoFornecedor(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $SITUACAO_ORCAMENTO_ABERTO = 0;
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento = $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'propostaitem' => $propostaitem['propostaitem'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => $SITUACAO_ORCAMENTO_ABERTO
        ]);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('DELETE', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedorenvolvido['fornecedorenvolvido']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        try {
            $I->cantSeeInDatabase('crm.fornecedoresenvolvidos', ['fornecedorenvolvido' => $fornecedorenvolvido['fornecedorenvolvido']]);
            $I->cantSeeInDatabase('crm.orcamentos', [
                'fornecedor' => $fornecedor['fornecedor'],
                'atc' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
    }

    /**
     * Testa o cancelamento do acionamento do fornecedor que possui orçamento aprovado, reprovado ou em análise.
     * O acionamento não pode ocorrer nesses casos.
     * @param FunctionalTester $I
     * @return void
     */
    public function cancelaAcionamentoFornecedorComOrcamentoDiferenteAberto(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento = $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'propostaitem' => $propostaitem['propostaitem']
        ]);
        
        $atc2 = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $propostaitem2 = $I->haveInDatabasePropostaItem($I, $atc2, $proposta);
        $orcamento2 = $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc2['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'propostaitem' => $propostaitem2['propostaitem']
        ]);
        
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('DELETE', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedorenvolvido['fornecedorenvolvido']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);

        try {
            $I->canSeeInDatabase('crm.fornecedoresenvolvidos', ['fornecedorenvolvido' => $fornecedorenvolvido['fornecedorenvolvido']]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
    }

    /**
     * get acionamento do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function getAcionamentoFornecedor(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedorenvolvido['fornecedorenvolvido']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($fornecedorenvolvido['acionamentometodo'], $retorno['acionamentometodo']);
        $I->assertEquals(floatval($fornecedorenvolvido['acionamentorespostaprazo']), floatval($retorno['acionamentorespostaprazo']));
        $I->assertEquals($fornecedorenvolvido['acionamentoaceito'], $retorno['acionamentoaceito']);
        $I->assertEquals($fornecedorenvolvido['negocio'], $retorno['negocio']['negocio']);
        $I->assertEquals($fornecedorenvolvido['fornecedor'], $retorno['fornecedor']['fornecedor']);
        $I->assertEquals($fornecedorenvolvido['tenant'], $retorno['tenant']);
        $I->assertEquals(false, $retorno['acionamentorespostaprazoflag']);
    }

    /**
     * get acionamento do fornecedor com prazo resposta expirado
     * @param FunctionalTester $I
     * @return void
     */
    public function getAcionamentoFornecedorComPrazoRespostaExpirado(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('email');

        $dataAcionamento = new \DateTime();
        //Removo 12 minutos da data de acionamento e seto acionamentoaceito como 'false'. Como o prazo é de 10 minutos, tem que retornar que está expirado
        $dataAcionamento->sub(new \DateInterval('PT'.'12'.'M'));
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo, $dataAcionamento->format('Y-m-d H:i:s'), false);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedorenvolvido['fornecedorenvolvido']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(true, $retorno['acionamentorespostaprazoflag']);
    }

    /**
     * getAll acionamento do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function getAllAcionamentoFornecedor(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');

        $arrFornecedoresEnvolvidos = [];
        $arrFornecedoresEnvolvidos[] = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);
        $arrFornecedoresEnvolvidos[] = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);
        $arrFornecedoresEnvolvidos[] = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $dataAcionamento = new \DateTime();
        //Removo 12 minutos da data de acionamento e seto acionamentoaceito como 'false'. Como o prazo é de 10 minutos, tem que retornar que está expirado
        $dataAcionamento->sub(new \DateInterval('PT'.'12'.'M'));
        $arrFornecedoresEnvolvidos[] = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo, $dataAcionamento->format('Y-m-d H:i:s'), false);

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('GET', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals(true, is_array($retorno));
        $I->assertEquals(count($arrFornecedoresEnvolvidos), count($retorno));

        //Valido quantos itens possuem prazo expirado
        $arrPrazoExpirado = array_filter($retorno, function($item) {
            return ($item['acionamentorespostaprazoflag'] == true);
        });

        $I->assertEquals(1, count($arrPrazoExpirado));
    }

    /**
     * Reabre todos os orçamentos do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function aprovarOrcamentosDoFornecedor(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento'], [
            'nomefantasia' => "Funeraria Bom Descanso"
        ]);
        $fornecedorTerceirizado2 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento'], [
            'nomefantasia' => "Funeraria Soneca pra sempre"
        ]);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
        $familia = $I->haveInDatabaseFamilia($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto,
            'servicotipo' => 1, // Serviço Externo
            'fornecedorterceirizado' => $fornecedorTerceirizado['fornecedor']
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'servicotipo' => 1, // Serviço Externo
            'fornecedorterceirizado' => $fornecedorTerceirizado2['fornecedor']
        ]);
        $orcamento3= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'familia' => $familia['familia'],
            'status' => 0 // Aberto
        ]);

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $dados = [
            'fornecedor' => [
                'fornecedor' => $fornecedor['fornecedor']
            ],
            'proposta' => $proposta['proposta']
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/aprovarOrcamentosNegocioFornecedor?grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);
        
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        try {
            // Valido alteração de situação do orçamento
            $I->canSeeInDatabase('crm.orcamentos', [
                'atc' => $atc['negocio'],
                'fornecedor' => $fornecedor['fornecedor'],
                'status' => 2 // Aprovado
            ]);
            // Valido criação de tipos de documento para prestadores terceirizados
            $I->canSeeInDatabase('ns.tiposdocumentos', [
                'prestador' => $fornecedorTerceirizado['fornecedor'],
                'emissaonoprocesso' => true
            ]);
            $I->canSeeInDatabase('ns.tiposdocumentos', [
                'prestador' => $fornecedorTerceirizado2['fornecedor'],
                'emissaonoprocesso' => true
            ]);
            // Valido criação de documentos no atendimento para prestadores terceirizados
            $I->canSeeInDatabase('crm.atcstiposdocumentosrequisitantes', [
                'negocio' => $atc['negocio'],
                'requisitantefornecedor' => $fornecedorTerceirizado['fornecedor'],
                'pedirinformacoesadicionais' => true
            ]);
            $I->canSeeInDatabase('crm.atcstiposdocumentosrequisitantes', [
                'negocio' => $atc['negocio'],
                'requisitantefornecedor' => $fornecedorTerceirizado2['fornecedor'],
                'pedirinformacoesadicionais' => true
            ]);
            // Valido criação de contas a pagar para prestadores terceirizados
            $I->canSeeInDatabase('crm.atcscontasapagar', [
                'atc' => $atc['negocio'],
                'prestador' => $fornecedorTerceirizado['fornecedor']
            ]);
            $I->canSeeInDatabase('crm.atcscontasapagar', [
                'atc' => $atc['negocio'],
                'prestador' => $fornecedorTerceirizado2['fornecedor']
            ]);
            $I->canSeeInDatabase('crm.historicoatcs', [
                'negocio' => $atc['negocio'],
                'secao' => 'contasapagar',
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
            $I->deleteFromDatabase('crm.atcscontasapagar', ['atc' => $atc['negocio']]);
            $I->deleteFromDatabase('crm.atcstiposdocumentosrequisitantes', ['negocio' => $atc['negocio']]);
            $I->deleteFromDatabase('ns.tiposdocumentos', [
                'tenant' => $this->tenant_numero,
                'prestador' => $fornecedorTerceirizado['fornecedor']
            ]);
            $I->deleteFromDatabase('ns.tiposdocumentos', [
                'tenant' => $this->tenant_numero,
                'prestador' => $fornecedorTerceirizado2['fornecedor']
            ]);
        }
    }

    /**
     * Reabre todos os orçamentos do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function reabrirOrcamentosDoFornecedor(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado1 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado2 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2, // Aprovado
            'servicotipo' => 1, // Serviço Externo
            'fornecedorterceirizado' => $fornecedorTerceirizado1['fornecedor']
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2, // Aprovado
            'servicotipo' => 2, // Prestadora acionada
            'fornecedorterceirizado' => $fornecedorTerceirizado2['fornecedor']
        ]);
        $ctPagar1= $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado1,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento1['orcamento']
        ]);
        $ctPagar2= $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado2,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento2['orcamento']
        ]);

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $dados = [];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/reabrirorcamentosnegociofornecedor?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
            $I->canSeeInDatabase('crm.orcamentos', [
                'atc' => $atc['negocio'],
                'fornecedor' => $fornecedor['fornecedor'],
                'status' => 0 // Aberto
            ]);

            // Deve apagar registros atrelados de contas a pagar
            $I->cantSeeInDatabase('crm.atcscontasapagar', [
                'atc' => $atc['negocio'],
                'prestador' => $fornecedorTerceirizado1['fornecedor']
            ]);
            $I->cantSeeInDatabase('crm.atcscontasapagar', [
                'atc' => $atc['negocio'],
                'prestador' => $fornecedorTerceirizado2['fornecedor']
            ]);

            // Deve criar histórico da operação
            $I->canSeeInDatabase('crm.historicoatcs', [
                'negocio' => $atc['negocio'],
                'secao' => 'orçamento',
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
    }

    /**
     * Reabre todos os orçamentos do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function naoReabreOrcamentosDoFornecedorComContrato(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2 // Aprovado
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2 // Aprovado
        ]);

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);

        $contrato1 = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, 1, ['numero' => 98]);
        $contrato2 = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, 1, ['numero' => 99]);
        $I->haveInDatabaseItemContrato($I, $contrato1, $propostaitem, $atc, 1);
        $I->haveInDatabaseItemContrato($I, $contrato2, $propostaitem, $atc, 1);

        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 15,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente['cliente'],
                    'contrato' => $contrato1['contrato'],
                ]
            ]
        ], $atc, $orcamento1);
        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'tipodivisao' => 1,
            'valorservico' => 15,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente['cliente'],
                    'contrato' => $contrato2['contrato'],
                ]
            ]
        ], $atc, $orcamento2);

        $dados = [];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/reabrirorcamentosnegociofornecedor?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->canSeeInDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'status' => 2 // Aprovado
        ]);
        $I->cantSeeInDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'secao' => 'orçamento',
            'tenant' => $this->tenant_numero
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
    }

    /**
     * Reabre todos os orçamentos do fornecedor
     * @param FunctionalTester $I
     * @return void
     */
    public function naoReabreOrcamentosDoFornecedorContaPagarDocumentoVinculado(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado1 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $fornecedorTerceirizado2 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2, // Aprovado
            'servicotipo' => 1, // Serviço Externo
            'fornecedorterceirizado' => $fornecedorTerceirizado1['fornecedor']
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 2, // Aprovado
            'servicotipo' => 2, // Prestadora acionada
            'fornecedorterceirizado' => $fornecedorTerceirizado2['fornecedor']
        ]);
        $tipoDocumento = $I->haveInDatabaseDocumento($I);
        $atcDocumento = $I->haveInDatabaseCrmAtcDocumento($I, [
            'negocio' => $atc['negocio'],
            'tipodocumento' => $tipoDocumento['tipodocumento']
        ]);
        $ctPagar1= $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado1,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento1['orcamento'],
            'negociodocumento' => $atcDocumento['negociodocumento']
        ]);
        $ctPagar2= $I->haveInDatabaseCrmAtcContaPagar($I, [
            'atc' => $atc['negocio'],
            'prestador' => $fornecedorTerceirizado2,
            'servico' => $composicao['composicao'],
            'orcamento' => $orcamento2['orcamento']
        ]);

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $dados = [];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/reabrirorcamentosnegociofornecedor?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
        try {
            // Não deve mudar situação do orçamento
            $I->canSeeInDatabase('crm.orcamentos', [
                'atc' => $atc['negocio'],
                'fornecedor' => $fornecedor['fornecedor'],
                'status' => 2 // Aprovado
            ]);

            // Não Deve apagar registros atrelados de contas a pagar
            $I->canSeeInDatabase('crm.atcscontasapagar', [
                'atc' => $atc['negocio'],
                'prestador' => $fornecedorTerceirizado1['fornecedor']
            ]);
            $I->canSeeInDatabase('crm.atcscontasapagar', [
                'atc' => $atc['negocio'],
                'prestador' => $fornecedorTerceirizado2['fornecedor']
            ]);

            // Não deve criar histórico da operação
            $I->cantSeeInDatabase('crm.historicoatcs', [
                'negocio' => $atc['negocio'],
                'secao' => 'orçamento',
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
    }

    /**
     * Atualiza configuração de descontos do fornecedor envolvido, com desconto de valor
     * @param FunctionalTester $I
     * @return void
     */
    public function atualizarConfiguracaoDescontoValor(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
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
        $orcamento3= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 25,
            'faturamentotipo' => 1 // Não faturar
        ]);

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $dados = [
            'fornecedor' => $fornecedor,
            'possuidescontoparcial' => false,
            'possuidescontoglobal' => true,
            'descontoglobal' => 10,
            'descontoglobaltipo' => 2 // Percentual
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/fornecedorenvolvidoatualizarconfiguracaodescontos?grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($retorno['descontoglobalunitario'], 5);
        $I->assertEquals($retorno['descontoglobalresto'], 5);
        $I->canSeeInDatabase('crm.fornecedoresenvolvidos', [
            'fornecedorenvolvido' => $fornecedoreEnvolvido['fornecedorenvolvido'],
            'possuidescontoparcial' => $dados['possuidescontoparcial'],
            'possuidescontoglobal' => $dados['possuidescontoglobal'],
            'descontoglobal' => $dados['descontoglobal'],
            'descontoglobaltipo' => $dados['descontoglobaltipo'],
            'tenant' => $this->tenant_numero
        ]);
        $I->canSeeInDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'status' => 0, // Aberto
            'descontoglobal' => 5
        ]);
        $I->canSeeInDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'status' => 0, // Aberto
            'descontoglobal' => 5,
            'orcamento' => $retorno['descontoglobalrestoorcamento']
        ]);
        $I->canSeeInDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'status' => 0, // Aberto
            'descontoglobal' => 0,
            'orcamento' => $orcamento3['orcamento']
        ]);
        $I->canSeeInDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'secao' => 'orçamento',
            'tenant' => $this->tenant_numero
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio']
        ]);
    }

    /**
     * Atualiza configuração de descontos do fornecedor envolvido
     * @param FunctionalTester $I
     * @return void
     */
    public function atualizarConfiguracaoDescontoPorcentagem(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
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

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        $dados = [
            'fornecedor' => $fornecedor,
            'possuidescontoparcial' => false,
            'possuidescontoglobal' => true,
            'descontoglobal' => 10,
            'descontoglobaltipo' => 2 // Porcentagem
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/fornecedorenvolvidoatualizarconfiguracaodescontos?grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($retorno['descontoglobalunitario'], 5);
        $I->assertEquals($retorno['descontoglobalresto'], 5);
        $I->canSeeInDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'status' => 0, // Aberto
            'descontoglobal' => 5
        ]);
        $I->canSeeInDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'secao' => 'orçamento',
            'tenant' => $this->tenant_numero
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio']
        ]);
    }

    /**
     * Atualiza configuração de descontos do fornecedor envolvido
     * @param FunctionalTester $I
     * @return void
     */
    public function atualizarConfiguracaoDescontoDesativarDescontoGlobal(FunctionalTester $I){
        /* Mock de banco */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = $this->getAcionamentoMetodo('telefone');
        $composicao = $I->haveinDatabaseComposicao($I);
        $orcamento1= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 50,
            'descontoglobal' => 5
        ]);
        $orcamento2= $I->haveInDatabaseOrcamento($I, [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor,
            'composicao' => $composicao['composicao'],
            'status' => 0, // Aberto
            'valor' => 50,
            'descontoglobal' => 5
        ]);

        $fornecedoreEnvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo, null, true, [
            'possuidescontoglobal' => true
        ]);

        $dados = [
            'fornecedor' => $fornecedor,
            'possuidescontoparcial' => false,
            'possuidescontoglobal' => false,
            'descontoglobal' => 0,
            'descontoglobaltipo' => 1
        ];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_complemento_fornecedoresenvolvidos}/{$fornecedoreEnvolvido['fornecedorenvolvido']}/fornecedorenvolvidoatualizarconfiguracaodescontos?grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('crm.orcamentos', [
            'atc' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'status' => 0, // Aberto
            'descontoglobal' => 0
        ]);
        $I->canSeeInDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio'],
            'secao' => 'orçamento',
            'tenant' => $this->tenant_numero
        ]);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.historicoatcs', [
            'negocio' => $atc['negocio']
        ]);
    }
}