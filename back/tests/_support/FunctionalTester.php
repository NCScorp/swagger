<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    public $tenant_numero = '47';
    public $tenant = 'gednasajon';
    public $id_grupoempresarial = '95cd450c-30c5-4172-af2b-cdece39073bf';
    public $grupoempresarial = 'FMA';
    private $composicao = '96c93b1b-4250-4af0-af3c-9278457f8ff2'; // origem: _data/dump.sql

    /* Métodos sobre fornecedores */

    /**
     * Cria estrutura de fornecedor no banco e devolve o array com os dados
     * A criação de fornecedor obedece a regra de conjuntos
     * @todo melhorar estrutura de fornecedor
     * @param FunctionalTester $I
     * @return array com dados do fornecedor
     */
    public function haveInDatabaseFornecedor(FunctionalTester $I, $esperapagamento = null, $estabelecimento = null, $dados = [])
    {
        $id = $I->generateUuidV4();
        $fornecedor = [
            "id" => $id,
            "pessoa" => isset($dados['pessoa']) ? $dados['pessoa'] : "101",
            "nome" => isset($dados['nome']) ? $dados['nome'] : "F101",
            "nomefantasia" => isset($dados['nomefantasia']) ? $dados['nomefantasia'] : "Fornecedor 101",
            "cnpj" => isset($dados['cnpj']) ? $dados['cnpj'] : "41960275000154",
            "inscricaomunicipal" => "101",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'esperapagamentoseguradora' =>  ($esperapagamento != null ? true : null),
            'estabelecimentoid' => ($estabelecimento != null ? $estabelecimento : null),
            'clienteativado' => 1,
            'fornecedorativado' => 1,
            "id_formapagamento_fornecedor" => isset($dados['formapagamento']) ? $dados['formapagamento'] : null
        ];
        $conjunto_fornecedor = [
            'conjuntofornecedor' => $I->generateUuidV4(),
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];
        $I->haveInDatabase("ns.pessoas", $fornecedor);
        $I->haveInDatabase("ns.conjuntosfornecedores", $conjunto_fornecedor);

        /* Formato aceito pelo sistema */
        $fornecedor['fornecedor'] = $fornecedor['id'];
        $fornecedor['razaosocial'] = $fornecedor['nome'];
        $fornecedor['codigofornecedores'] = $fornecedor['pessoa'];
        $fornecedor['cadastro'] = "1";

        unset($fornecedor['updated_at']);
        unset($fornecedor['updated_by']);
        unset($fornecedor['created_at']);
        unset($fornecedor['created_by']);

        return $fornecedor;
    }

    public function haveInDatabaseFornecedorComDocumento(FunctionalTester $I, $fornecedor, $documento)
    {

        //Criando a associação entre fornecedor e documento, inserindo na tabela fornecedoresdocumentos
        $id = $I->generateUuidV4();
        $documentoFornecedor = [
            'fornecedordocumento' => $id,
            'fornecedor' => $fornecedor['fornecedor'],
            'tipodocumento' => $documento['tipodocumento'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'copiasimples' => true,
            'copiaautenticada' => true,
            'original' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $I->haveInDatabase("ns.fornecedoresdocumentos", $documentoFornecedor);

        //montando o objeto fornecedor com o documento associado
        $fornecedor['fornecedoresdocumentos'] = [
            "fornecedordocumento" => $id,
            "copiasimples" => $documentoFornecedor['copiasimples'],
            "copiaautenticada" => $documentoFornecedor['copiaautenticada'],
            "original" => $documentoFornecedor['original'],
            "fornecedor" => $documentoFornecedor['fornecedor'],
            "tipodocumento" => [
                 "tipodocumento" => $documento['tipodocumento'],
                 "nome" => $documento['nome'],
                 "emissaonoprocesso" => $documento['emissaonoprocesso']
            ],
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        return $fornecedor;
    }

    /**
     * Cria estrutura de fornecedor com tipos de atividades no banco e devolve o array com os dados
     * A criação de fornecedor obedece a regra de conjuntos
     * @todo melhorar estrutura de fornecedor
     * @param FunctionalTester $I
     * @return array com dados do fornecedor
     */
    public function haveInDatabaseFornecedorComAtividades(FunctionalTester $I)
    {
        $id = $I->generateUuidV4();
        $fornecedor = [
            "id" => $id,
            "pessoa" => "101",
            "nome" => "F101",
            "nomefantasia" => "Fornecedor 101",
            "cnpj" => "41.960.275/0001-54",
            "inscricaomunicipal" => "101",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'clienteativado' => 1
        ];
        $endereco = [
            "endereco" => "784b6f3f-d9f3-43c3-98e4-f81a3ea3a49a",
            "logradouro" => "Rua 101",
            "numero" => "101",
            "complemento" => "Proximo ao Bairro 202",
            "cep" => "69084-461",
            "bairro" => "Bairro 101",
            "referencia" => "Apos a rua 0",
            "cidade_id" => "0f381248-1f3c-46e9-b075-3445d7de6288",
            "tenant" => $this->tenant_numero,
            "id_pessoa" => $id
        ];
        $conjunto_fornecedor = [
            'conjuntofornecedor' => '93b361fb-fcae-4a65-ac99-cb7aa10e269f',
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];

        $pessoas_tipos_atividades1 = [
            'pessoatipoatividade' => $I->generateUuidV4(),
            'tipoatividade'       => '66eab2c7-dce2-469c-aef9-a0347f755a16', // funerárias
            'pessoa'              => $id,
            'tenant'              => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        $pessoas_tipos_atividades2 = [
            'pessoatipoatividade' => $I->generateUuidV4(),
            'tipoatividade'       => 'cd113949-10bf-4265-a17d-4c37eeb77701', // seguradora
            'pessoa'              => $id,
            'tenant'              => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        $I->haveInDatabase("ns.pessoas", $fornecedor);
        $I->haveInDatabase("ns.enderecos", $endereco);
        $I->haveInDatabase("ns.conjuntosfornecedores", $conjunto_fornecedor);
        $I->haveInDatabase("ns.pessoastiposatividades", $pessoas_tipos_atividades1);
        $I->haveInDatabase("ns.pessoastiposatividades", $pessoas_tipos_atividades2);

        /* Formato aceito pelo sistema */
        $fornecedor['endereco'] = $endereco;
        $fornecedor['fornecedor'] = $fornecedor['id'];
        $fornecedor['cidade'] = ['cidade' => $endereco['cidade_id']];
        unset($fornecedor['updated_at']);
        unset($fornecedor['updated_by']);
        unset($fornecedor['created_at']);
        unset($fornecedor['created_by']);

        return $fornecedor;
    }

    /**
     * Suspende fornecedor, inserindo a suspensão na tabela de fornecedores suspensos.
     * Por regra de atc, cada fornecedor só pode ter um registro nessa tabela de suspensão
     * @param FunctionalTester $I
     * @param type $id_fornecedor
     * @return type
     */
    public function haveinDatabaseFornecedorSuspenso(FunctionalTester $I, $id_fornecedor)
    {
        $fornecedor_suspenso = [
            "fornecedorsuspenso" => $I->generateUuidV4(),
            "fornecedor_id" => $id_fornecedor,
            "tipo" => 1,
            "data_fim" => "2019-06-12",
            "motivo_suspensao" => "motivo 1",
            "tenant" => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'

        ];

        $I->haveInDatabase("ns.fornecedoressuspensos", $fornecedor_suspenso);

        return $fornecedor_suspenso;
    }

   
    /* -----
     * Métodos sobre clientes 
     * **/

    public function haveInDatabaseCliente(FunctionalTester $I, $dados = [])
    {
        $id = $I->generateUuidV4();
        $cliente = [
            "id" => $id,
            "pessoa" => "777",
            "nome" => "C777",
            "nomefantasia" => "Cliente 777",
            "cnpj" => "93732241000106",
            "inscricaomunicipal" => "777",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'clienteativado' => 1,
            'diasparavencimento' => 30,
            'vendedor' => isset($dados['vendedor']) ? $dados['vendedor']['idvendedor'] : null,
            'id_conta_receber' =>  isset($dados['conta']) ? $dados['conta']['conta'] : null,
        ];
        $conjunto_cliente = [
            'conjuntocliente' => $I->generateUuidV4(),
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];
        $I->haveInDatabase("ns.pessoas", $cliente);
        $I->haveInDatabase("ns.conjuntosclientes", $conjunto_cliente);
        /* Formato aceito pelo sistema */
        $cliente['cliente'] = $cliente['id'];
        $cliente['cadastro'] = "1";
        $cliente['vendedor'] = isset($dados['vendedor']) ? $dados['vendedor'] : $cliente['vendedor'];
        // $cliente['cidadeestrangeira'] = ['cidadeestrangeira' => $endereco['cidadeestrangeira']];
        unset($cliente['updated_at']);
        unset($cliente['updated_by']);
        unset($cliente['created_at']);
        unset($cliente['created_by']);
        return $cliente;
    }

    public function haveInDatabaseClienteComVendedorAssociado(FunctionalTester $I, $vendedorId)
    {
        $id = $I->generateUuidV4();
        $cliente = [
            "id" => $id,
            "pessoa" => "Cliente V",
            "nome" => "Cliente Vendedor",
            "nomefantasia" => "Cliente com Vendedor",
            "cnpj" => "75037128000129",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'clienteativado' => 1,
            'diasparavencimento' => 30,
            'vendedor' => $vendedorId
        ];
        $conjunto_cliente = [
            'conjuntocliente' => $I->generateUuidV4(),
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];
        $I->haveInDatabase("ns.pessoas", $cliente);
        $I->haveInDatabase("ns.conjuntosclientes", $conjunto_cliente);
        /* Formato aceito pelo sistema */
        $cliente['cliente'] = $cliente['id'];
        $cliente['cadastro'] = "1";
        // $cliente['cidadeestrangeira'] = ['cidadeestrangeira' => $endereco['cidadeestrangeira']];
        unset($cliente['updated_at']);
        unset($cliente['updated_by']);
        unset($cliente['created_at']);
        unset($cliente['created_by']);
        return $cliente;
    }


    public function haveInDatabaseClienteComDocumento(FunctionalTester $I, $cliente, $documento)
    {

        //Criando a associação entre cliente e documento, inserindo na tabela clientesdocumentos
        $id = $I->generateUuidV4();
        $documentoCliente = [
            'clientedocumento' => $id,
            'cliente' => $cliente['cliente'],
            'tipodocumento' => $documento['tipodocumento'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'copiasimples' => true,
            'copiaautenticada' => true,
            'original' => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $I->haveInDatabase("ns.clientesdocumentos", $documentoCliente);

        //montando o objeto cliente com o documento associado
        $cliente['clientesdocumentos'] = [
            "clientedocumento" => $id,
            "copiasimples" => $documentoCliente['copiasimples'],
            "copiaautenticada" => $documentoCliente['copiaautenticada'],
            "original" => $documentoCliente['original'],
            "cliente" => $documentoCliente['cliente'],
            "tipodocumento" => [
                 "tipodocumento" => $documento['tipodocumento'],
                 "nome" => $documento['nome'],
                 "emissaonoprocesso" => $documento['emissaonoprocesso']
            ],
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        return $cliente;
    }
    
    public function haveInDatabaseClientecomCidadeEstrangeira(FunctionalTester $I)
    {
        $id = $I->generateUuidV4();
        $cliente = [
            "id" => $id,
            "pessoa" => "777",
            "nome" => "C777",
            "nomefantasia" => "Cliente 777",
            "cnpj" => "93.732.241/0001-06",
            "inscricaomunicipal" => "777",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'clienteativado' => 1
        ];
        $endereco = [
            "endereco" => "784b6f3f-d9f3-43c3-98e4-f81a3ea3a49a",
            "logradouro" => "Rua 777",
            "numero" => "777",
            "complemento" => "Proximo ao Bairro 776",
            "cep" => "69084-461",
            "bairro" => "Bairro 777",
            "referencia" => "Apos a rua 66",
            "cidadeestrangeira" => "0f381248-1f3c-46e9-b075-3445d7de6288",
            "pais" => "002",
            "tenant" => $this->tenant_numero,
            "id_pessoa" => $id
        ];
        $conjunto_cliente = [
            'conjuntocliente' => 'e8d81b86-cb12-4a88-8db1-8ae20dd5f0d8',
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];
        $I->haveInDatabase("ns.pessoas", $cliente);
        $I->haveInDatabase("ns.enderecos", $endereco);
        $I->haveInDatabase("ns.conjuntosclientes", $conjunto_cliente);
        /* Formato aceito pelo sistema */
        $cliente['endereco'] = $endereco;
        $cliente['cliente'] = $cliente['id'];
        // $cliente['cidadeestrangeira'] = ['cidadeestrangeira' => $endereco['cidadeestrangeira']];
        unset($cliente['updated_at']);
        unset($cliente['updated_by']);
        unset($cliente['created_at']);
        unset($cliente['created_by']);
        return $cliente;
    }

    public function haveInDatabaseClienteComTiposAtividades(FunctionalTester $I)
    {
        $id = $I->generateUuidV4();
        $cliente = [
            "id" => $id,
            "pessoa" => "777",
            "nome" => "C777",
            "nomefantasia" => "Cliente 777",
            "cnpj" => "93.732.241/0001-06",
            "inscricaomunicipal" => "777",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'clienteativado' => 1
        ];
        $endereco = [
            "endereco" => "784b6f3f-d9f3-43c3-98e4-f81a3ea3a49a",
            "logradouro" => "Rua 777",
            "numero" => "777",
            "complemento" => "Proximo ao Bairro 776",
            "cep" => "69084-461",
            "bairro" => "Bairro 777",
            "referencia" => "Apos a rua 66",
            "cidade_id" => "0f381248-1f3c-46e9-b075-3445d7de6288",
            "tenant" => $this->tenant_numero,
            "id_pessoa" => $id
        ];
        $conjunto_cliente = [
            'conjuntocliente' => 'e8d81b86-cb12-4a88-8db1-8ae20dd5f0d8',
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];

        $pessoas_tipos_atividades1 = [
            'pessoatipoatividade' => $I->generateUuidV4(),
            'tipoatividade'       => '66eab2c7-dce2-469c-aef9-a0347f755a16', // funerárias
            'pessoa'              => $id,
            'tenant'              => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        $pessoas_tipos_atividades2 = [
            'pessoatipoatividade' => $I->generateUuidV4(),
            'tipoatividade'       => 'cd113949-10bf-4265-a17d-4c37eeb77701', // seguradora
            'pessoa'              => $id,
            'tenant'              => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        $I->haveInDatabase("ns.pessoas", $cliente);
        $I->haveInDatabase("ns.enderecos", $endereco);
        $I->haveInDatabase("ns.conjuntosclientes", $conjunto_cliente);
        $I->haveInDatabase("ns.pessoastiposatividades", $pessoas_tipos_atividades1);
        $I->haveInDatabase("ns.pessoastiposatividades", $pessoas_tipos_atividades2);

        /* Formato aceito pelo sistema */
        $cliente['endereco'] = $endereco;
        $cliente['cliente'] = $cliente['id'];
        $cliente['cidade'] = ['cidade' => $endereco['cidade_id']];
        unset($cliente['updated_at']);
        unset($cliente['updated_by']);
        unset($cliente['created_at']);
        unset($cliente['created_by']);

        return $cliente;
    }

    public function haveInDatabaseClienteComTelefones(FunctionalTester $I)
    {
        $id = $I->generateUuidV4();
        $cliente = [
            "id" => $id,
            "pessoa" => "666",
            "nome" => "C666",
            "nomefantasia" => "Cliente 666",
            "cnpj" => "93732241000106",
            "inscricaomunicipal" => "666",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'clienteativado' => 1
        ];
        $conjunto_cliente = [
            'conjuntocliente' => 'e8d81b86-cb12-4a88-8db1-8ae20dd5f0d8',
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];

        $idContato = $I->generateUuidV4();
        $contato = [
            'id'        => $idContato,
            'nome'      => 'Contato Teste 666',
            'primeironome' => 'Contato Teste 666',
            'id_pessoa' => $id,
            'tenant'    => $this->tenant_numero,
        ];

        $idTelefone = $I->generateUuidV4();
        $telefone =  [
            'id'       => $idTelefone,
            'ddd'      => '21',
            'telefone' => '99999',
            'contato'  => $idContato,
            'tenant'   => $this->tenant_numero,
            'id_pessoa' => $id,
        ];

        $I->haveInDatabase("ns.pessoas", $cliente);
        $I->haveInDatabase("ns.conjuntosclientes", $conjunto_cliente);
        $I->haveInDatabase("ns.contatos", $contato);
        $I->haveInDatabase("ns.telefones", $telefone);

        /* Formato aceito pelo sistema */
        $cliente['cliente']  = $cliente['id'];
        $cliente['cadastro'] = "1";
        unset($cliente['updated_at']);
        unset($cliente['updated_by']);
        unset($cliente['created_at']);
        unset($cliente['created_by']);

        $telefone['telefone_id'] = $telefone['id'];
        $contato['telefones'][] = $telefone;
        $contato['contato'] = $contato['id'];
        $cliente['contatos'][] = $contato;

        return $cliente;
    }

    public function haveInDatabaseDocumento(FunctionalTester $I)
    {
        $id = $I->generateUuidV4();
        $nome = $I->generateUuidV4();
        $nome = "documento".substr($nome, 0, 5);
        $documento = [
            "tipodocumento" => $id,
            "nome" => $nome,
            "emissaonoprocesso" => false,
            "tenant" => $this->tenant_numero,
            "dominio" => null,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $I->haveInDatabase("ns.tiposdocumentos", $documento);

        return $documento;
    }

    public function haveInDatabaseMalote(FunctionalTester $I, $clienteId, $codigo = null){
        $id = $I->generateUuidV4();
        $codigo = ($codigo) ? $codigo : "Malote 0";

        $malote = [
            "malote" => $id,
            "codigo" => $codigo,
            "requisitantecliente" => $clienteId,
            "status" => 0,
            "created_by" => '{"nome":"usuario"}',
            "updated_by" => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            "tenant" => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];

        $I->haveInDatabase("crm.malotes", $malote);

        return $malote;
    }

    /**
     * Recebe como parametro $I, o id do cliente e o modo de envio desejado para criação do malote enviado
     *
     * @param FunctionalTester $I
     * @param $clienteId
     * @param $envio
     * @return void
     */
    public function haveInDatabaseMaloteEnviado(FunctionalTester $I, $clienteId, $envio){
        $id = $I->generateUuidV4();
        
        //Em mãos
        if($envio == 0){
            $malote = [
                "malote" => $id,
                "codigo" => 'Malote Enviado Em Mãos',
                "requisitantecliente" => $clienteId,
                "dtenvio" => date('Y-m-d'),
                "status" => 1,
                "created_by" => '{"nome":"usuario"}',
                "updated_by" => '{"nome":"usuario"}',
                'created_at' => date('Y-m-d'),
                'updated_at' => date('Y-m-d'),
                'enviomodal' => 0, 
                'enviorecebimentodata' => date('Y-m-d'),
                'enviorecebidopornome' => 'Receptor do Malote Em Mãos',
                'enviorecebidoporcargo' => 'Cargo Malote Em Mãos',
                "tenant" => $this->tenant_numero,
                "grupoempresarial" => $this->id_grupoempresarial
            ];
        }
        //Correio
        else if($envio == 1){
            $malote = [
                "malote" => $id,
                "codigo" => 'Malote Enviado Correio',
                "requisitantecliente" => $clienteId,
                "dtenvio" => date('Y-m-d'),
                "status" => 1,
                "created_by" => '{"nome":"usuario"}',
                "updated_by" => '{"nome":"usuario"}',
                'created_at' => date('Y-m-d'),
                'updated_at' => date('Y-m-d'),
                'enviomodal' => 1, 
                'enviorecebimentodata' => date('Y-m-d'),
                'enviocodigorastreio' => 'ABC123DEFBR',
                'enviodata' => date('Y-m-d'),
                "tenant" => $this->tenant_numero,
                "grupoempresarial" => $this->id_grupoempresarial
            ];
        }
        //Email
        else{
            $malote = [
                "malote" => $id,
                "codigo" => 'Malote Enviado Email',
                "requisitantecliente" => $clienteId,
                "dtenvio" => date('Y-m-d'),
                "status" => 1,
                "created_by" => '{"nome":"usuario"}',
                "updated_by" => '{"nome":"usuario"}',
                'created_at' => date('Y-m-d'),
                'updated_at' => date('Y-m-d'),
                'enviomodal' => 2,
                'enviodata' => date('Y-m-d'),
                "tenant" => $this->tenant_numero,
                "grupoempresarial" => $this->id_grupoempresarial
            ];
        }

        $I->haveInDatabase("crm.malotes", $malote);

        return $malote;
    }

    /**
    * Utiliza dados do dump
    * @todo otimizar dump
    */
    public function haveInDatabasePais(FunctionalTester $I, $nome = 'Brasil')
    {
        if($nome != "Brasil"){
            return ['pais'=> "002", 'nome' => 'Bolivia'];
        }
        return [ "pais" => "1058", "nome" =>"Brasil"];
    }

    /**
    * Utiliza dados do dump
    * @todo otimizar dump
    */
    public function haveInDatabaseEstado(FunctionalTester $I)
    {
        $estado = [
            "uf" => "RJ",
            "nome" =>"Rio de Janeiro",
        ];
        return $estado;
    }

    /**
    * Utiliza dados do dump
    * @todo otimizar dump
    */
    public function haveInDatabaseMunicipio(FunctionalTester $I, $estado)
    {
        $municipio = [
            "ibge" => "3304557",
            "nome" =>"Rio de Janeiro",
            "uf" => $estado["uf"],
            "federal" => "11"
        ];
        return $municipio;
    }

    public function haveInDatabaseMunicipioUnico(FunctionalTester $I, $estado, $ibge = "0000000", $federal = '11')
    {
        $municipio = [
            "ibge" => $ibge != "0000000" ? $ibge : "0000000",
            "nome" =>"Município Único",
            "uf" => $estado["uf"],
            "federal" => $federal != '11' ? $federal : '11'
        ];
        $I->haveInDatabase('ns.municipios', $municipio);
        return $municipio;
    }

    /**
     * Insere um estado no banco de dados
     */
    public function haveInDatabaseNsEstados(FunctionalTester $I, $dados = []){
        $estado = [
            "uf" => isset($dados['uf']) ? $dados['uf'] : "RJ",
            "nome" => isset($dados['nome']) ? $dados['nome'] : "Rio de Janeiro"
        ];

        $I->haveInDatabase("ns.estados", $estado);

        return $estado;
    }

    public function haveInDataBaseEndereco(FunctionalTester $I)
    {   $pais = [
        "pais" => "1058",
        "nome" =>"Brasil"
    ];
    $I->haveInDatabase("ns.paises", $pais);
    $estado = [
        "uf" => "RJ",
        "nome" =>"Rio de Janeiro"
    ];
    $I->haveInDatabase("ns.estados", $estado);
    $municipio = [
        "ibge" => "3304557",
        "ddd" => "21",
        "nome" =>"Rio de Janeiro",
        "federal" => "11"
    ];
        $endereco = [
        'endereco' => $this->generateUuidV4(),
        'cep' => '24127355',
        'logradouro' => 'Rua 3',
        'bairro' => 'Bairro',
        'numero' => '20',
        'complemento' => 'apartamento 101',
        'referencia' => 'perto do mercado',
        'nome' => 'Novo',
        'uf' => 'RJ',
        'pais' => '1058',
        'municipio' => $municipio['ibge'],
        'tenant' => $this->tenant_numero
    ];
    $I->haveInDatabase('ns.municipios', $municipio);
    $I->haveInDatabase('ns.enderecos', $endereco);
        return $endereco;
    }
    
    public function haveInDataBaseEnderecoEstrangeiro(FunctionalTester $I, $cliente)
    {   $pais = [
        "pais" => "005",
        "nome" =>"Texas"
    ];
    $I->haveInDatabase("ns.paises", $pais);
    $cidadeestrangeira = [
        'cidadeestrangeira' => $I->generateUuidV4(),
        'nome' => 'Liberty Hill',
        'pais' => $pais['pais'],
        'created_at' => date('Y-m-d'),
        'updated_at' => date('Y-m-d')
    ];
    $I->haveInDatabase('ns.cidadesestrangeiras', $cidadeestrangeira);
        $endereco = [
        'endereco' => $this->generateUuidV4(),
        'cep' => '24127355',
        'logradouro' => 'Rua 3',
        'tipologradouro' => 'A',
        'id_pessoa' => $cliente['cliente'],
        'bairro' => 'Bairro',
        'numero' => '20',
        'complemento' => 'apartamento 101',
        'referencia' => 'perto do mercado',
        'nome' => 'Novo',
        'pais' => $pais['pais'],
        'cidadeestrangeira' => $cidadeestrangeira['cidadeestrangeira'],
        'tenant' => $this->tenant_numero
    ];
    $I->haveInDatabase('ns.enderecos', $endereco);
        return $endereco;
    }

    public function haveInDatabaseDocumentoNecessario(FunctionalTester $I)
    {
        $id_pai = $I->generateUuidV4();
        $documento = [
            "tipodocumento" => $id_pai,
            "nome" => 'certidao',
            "emissaonoprocesso" => true,
            "tenant" => $this->tenant_numero,
            "dominio" => null,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $I->haveInDatabase("ns.tiposdocumentos", $documento);


        $documentofilho = [
            "tipodocumento" => $I->generateUuidV4(),
            "nome" => 'RG',
            "emissaonoprocesso" => false,
            "tenant" => $this->tenant_numero,
            "dominio" => null,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        $documentofilho2 = [
            "tipodocumento" => $I->generateUuidV4(),
            "nome" => 'CT',
            "emissaonoprocesso" => false,
            "tenant" => $this->tenant_numero,
            "dominio" => null,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];

        $id = $I->generateUuidV4();
        $documentoNecessario = [
            "id" => $id,
            "documento" => $id_pai,
            "documentonecessario" => $documentofilho['tipodocumento'],
            "tenant" => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $id = $I->generateUuidV4();
        $documentoNecessario2 = [
            "id" => $id,
            "documento" => $id_pai,
            "documentonecessario" => $documentofilho2['tipodocumento'],
            "tenant" => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];


        $I->haveInDatabase("ns.tiposdocumentos", $documentofilho);
        $I->haveInDatabase("ns.tiposdocumentos", $documentofilho2);
        $I->haveInDatabase("ns.documentosnecessarios", $documentoNecessario);
        $I->haveInDatabase("ns.documentosnecessarios", $documentoNecessario2);
        $documento['documentosnecessarios'] = [];
        array_push($documento['documentosnecessarios'], ['documentonecessario' => $documentofilho]);
        array_push($documento['documentosnecessarios'], ['documentonecessario' => $documentofilho2]);
        return $documento;
    }


    /* ---- */

    /* Método para atc */

    /**
     * Grava temporariamente área de atc no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @param array $area 
     * @param array $origem
     * @return type
     */
    public function haveInDatabaseAtc(FunctionalTester $I, $area, $origem, $cliente, $negociopai = null, $estabelecimento = ['estabelecimento'=>'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'], $codigo = "2", $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {

        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 2',
            'codigo' => $codigo,
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'negociopai' => $negociopai,
            'estabelecimento' => $estabelecimento['estabelecimento'],
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        $I->haveInDatabase('crm.atcs', $atc);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        $atc['estabelecimento'] = $estabelecimento;
        return $atc;
    }

    /**
     * Grava temporariamente área de atc no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @param array $area 
     * @param array $origem
     * @return type
     */
    public function haveInDatabaseAtcComContratoTaxaAdm(FunctionalTester $I, $area, $origem, $cliente, $dadosTaxaAdm, $estabelecimento = ['estabelecimento'=>'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'], $codigo = "2", $id_grupoempresarial = ['id_grupoempresarial'=>'3964bfdc-e09e-4386-9655-5296062e632d'])
    {

        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 2',
            'codigo' => $codigo,
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'negociopai' => null,
            'estabelecimento' => $estabelecimento['estabelecimento'],
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        $I->haveInDatabase('crm.atcs', $atc);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        $atc['estabelecimento'] = $estabelecimento;
        return $atc;
    }

    /**
     * @param FunctionalTester $I
     * @param $atc
     * @param $tipoDocumento
     * @param $grupoEmpresarial
     * @param $nomeArquivo
     * @return type
     */
    public function haveInDatabaseAtcDocumento(FunctionalTester $I, $atc, $tipoDocumento, $grupoEmpresarial, $arquivo = 'arquivo'){
        
        $atcDocumento = [
            "negociodocumento" => $I->generateUuidV4(),
            "negocio" => $atc['negocio'],
            "tipodocumento" => $tipoDocumento['tipodocumento'],
            "url" => $I->tenant_numero . "/" . $atc['negocio'] . "/documentos/" . $arquivo . ".pdf", //url montada
            "status" => "1", //recebido
            "tenant" => $I->tenant_numero,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            "id_grupoempresarial" => $grupoEmpresarial
        ];

        unset($atcDocumento['created_at']);
        unset($atcDocumento['created_by']);
        unset($atcDocumento['updated_at']);
        unset($atcDocumento['updated_by']);

        return $atcDocumento;
    }

    /**
     * @param FunctionalTester $I
     * @param $atc
     * @param $tipoDocumento
     * @param $grupoEmpresarial
     * @param $nomeArquivo
     * @return type
     */
    public function haveInDatabaseCrmAtcDocumento(FunctionalTester $I, $dados =[]){
        
        $atcDocumento = [
            "negociodocumento" => $I->generateUuidV4(),
            "negocio" => $dados['negocio'],
            "tipodocumento" => $dados['tipodocumento'],
            "url" => $I->tenant_numero . "/" . $dados['negocio'] . "/documentos/" 
                . (isset($dados['arquivo']) ? $dados['arquivo'] : 'arquivo')
                . ".pdf", //url montada
            "status" => "1", //recebido
            "tenant" => $I->tenant_numero,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            "id_grupoempresarial" => $this->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.atcsdocumentos', $atcDocumento);

        unset($atcDocumento['created_at']);
        unset($atcDocumento['created_by']);
        unset($atcDocumento['updated_at']);
        unset($atcDocumento['updated_by']);

        return $atcDocumento;
    }

   

    /**
     * @param FunctionalTester $I
     * @param $negocio
     * @param $tipoDocumento
     * @param $grupoEmpresarial
     * @param $nomeArquivo
     * @return type
     */
    public function haveInDatabaseNegocioDocumento(FunctionalTester $I, $negocio, $tipoDocumento, $grupoEmpresarial, $arquivo = 'arquivo'){
        
        $negocioDocumento = [
            "negociodocumento" => $I->generateUuidV4(),
            "negocio" => $negocio['negocio'],
            "tipodocumento" => $tipoDocumento['tipodocumento'],
            "url" => $I->tenant_numero . "/" . $negocio['negocio'] . "/documentos/" . $arquivo . ".pdf", //url montada
            "status" => "1", //recebido
            "tenant" => $I->tenant_numero,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            "id_grupoempresarial" => $grupoEmpresarial
        ];

        unset($negocioDocumento['created_at']);
        unset($negocioDocumento['created_by']);
        unset($negocioDocumento['updated_at']);
        unset($negocioDocumento['updated_by']);

        return $negocioDocumento;
    }

    /** 
     * Retorna o atc com responsável financeiro dentro do array. Para testes que precisarem de ambas as informações
     */
    public function haveInDatabaseAtcComResponsavelFinanceiro(FunctionalTester $I, $area, $origem, $cliente, $estabelecimento = ['estabelecimento'=>'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'], $negociopai = null, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 1',
            'codigo' => 'N1',
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'negociopai' => $negociopai,
            'estabelecimento' => $estabelecimento['estabelecimento'],
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        $responsaveisfinanceiros = 
            [
                [
                'negocioresponsavelfinanceiro' => $I->generateUuidV4(),
                'negocio' => $atc['negocio'],
                'responsavelfinanceiro' => $cliente['cliente'],
                'created_at' => date('Y-m-d'),
                'created_by' => '{"nome":"usuario"}',
                'tenant' => $this->tenant_numero,
                'principal' => true,
                'grupoempresarial' => $id_grupoempresarial['id_grupoempresarial']
                ],
                [
                'negocioresponsavelfinanceiro' => $I->generateUuidV4(),
                'negocio' => $atc['negocio'],
                'responsavelfinanceiro' => $cliente['cliente'],
                'created_at' => date('Y-m-d'),
                'created_by' => '{"nome":"usuario"}',
                'tenant' => $this->tenant_numero,
                'principal' => false,
                'grupoempresarial' => $id_grupoempresarial['id_grupoempresarial']
                ]
        ];
        $I->haveInDatabase('crm.atcs', $atc);
        $I->haveInDatabase('crm.atcsresponsaveisfinanceiros', $responsaveisfinanceiros[0]);
        $I->haveInDatabase('crm.atcsresponsaveisfinanceiros', $responsaveisfinanceiros[1]);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        unset($responsaveisfinanceiros[0]['created_at']);
        unset($responsaveisfinanceiros[0]['created_by']);
        unset($responsaveisfinanceiros[1]['created_at']);
        unset($responsaveisfinanceiros[1]['created_by']);
        $atc['responsavelfinanceiro'] = $responsaveisfinanceiros;
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        $atc['estabelecimento'] = $estabelecimento;
        return $atc;
    }
    /**
     * 
     * @param FunctionalTester $I
     * @param array $area 
     * @param array $origem
     * @return type
     */
    public function haveInDatabaseAtcComLocalizacaoNaoSalva(FunctionalTester $I, $area, $origem, $cliente, $estado, $municipio, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        $pais = [
            "pais" => "1058",
            "nome" =>"Brasil",
        ];
        $tipologradouro = [
            "tipologradouro" => 'A',
            "descricao" => NULL
        ];
        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 1',
            'codigo' => 'N1',
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'localizacaocep' => '24127355',
            'localizacaobairro' => 'Bairro',
            'localizacaotipologradouro' => $tipologradouro['tipologradouro'], 
            'localizacaorua' => 'Rua 3',
            'localizacaonumero' => '45',
            'localizacaocomplemento' => 'Casa 20',
            'localizacaoreferencia' => 'Perto do mercado',
            'localizacaopais' => '1058',
            'localizacaoestado' => $estado['uf'],
            'localizacaomunicipio' => $municipio['ibge'],
            'localizacaonome' => 'Endereço 01',
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        $I->haveInDatabase('crm.atcs', $atc);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        $atc['localizacaopais'] = $pais;
        $atc['localizacaoestado'] = $estado;
        $municipio['codigo'] = $municipio['ibge'];
        $atc['localizacaomunicipio'] = $municipio;
        $atc['localizacaotipologradouro'] = $tipologradouro;
        return $atc;
    }
    /**
     * 
     * @param FunctionalTester $I
     * @param array $area 
     * @param array $origem
     * @return type
     */
    public function haveInDatabaseAtcComLocalizacaoSalva(FunctionalTester $I, $area, $origem, $cliente, $estado, $municipio, $pais, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        $cliente['endereco']['uf'] = $estado['uf'];
        $cliente['endereco']['pais'] = $pais['pais'];
        $cliente['endereco']['municipio'] = $municipio['ibge'];
        $tipologradouro = [
            "tipologradouro" => 'A',
            "descricao" => NULL
        ];
        $local = [
            'localizacaocep' => '24127355',
            'localizacaobairro' => 'Bairro',
            'localizacaotipologradouro' => $tipologradouro['tipologradouro'],
            'localizacaorua' => 'Rua 3',
            'localizacaonumero' => '45',
            'localizacaocomplemento' => 'Casa 20',
            'localizacaoreferencia' => 'Perto do mercado',
            'localizacaopais' => $pais['pais'],
            'localizacaoestado' => $estado['uf'],
            'localizacaomunicipio' => $municipio['ibge'],
            'localizacao' => $this->generateUuidV4()
        ];
        $endereco = [
            'endereco' => $local['localizacao'],
            'cep' => $local['localizacaocep'],
            'logradouro' => $local['localizacaorua'],
            'tipologradouro' => $tipologradouro['tipologradouro'],
            'bairro' => $local['localizacaobairro'],
            'numero' => $local['localizacaonumero'],
            'complemento' => $local['localizacaocomplemento'],
            'referencia' => $local['localizacaoreferencia'],
            'nome' => 'Novo',
            'uf' =>  $estado['uf'],
            'pais' => $pais['pais'],
            'municipio' => $municipio['ibge'],
            'tenant' => $this->tenant_numero
        ];
        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 1',
            'codigo' => 'N1',
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        $atc = array_merge($atc, $local);
        $I->haveInDatabase('ns.enderecos', $endereco);
        $I->haveInDatabase('crm.atcs', $atc);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        $atc['localizacaopais'] = $pais;
        $atc['localizacaoestado'] = $estado;
        $atc['localizacaotipologradouro'] = $tipologradouro;
        $municipio['codigo'] = $municipio['ibge'];
        $atc['localizacaomunicipio'] = $municipio;
        $atc['localizacaonome'] = $endereco['nome'];
        return $atc;
    }

    /**
     * Proposta confirmada = pedido utilizado no atendimento comercial
     * Cria a proposta com capítulo
     */
    public function haveInDatabaseProposta(FunctionalTester $I,  $atc)
    {

        $proposta = [
            'proposta' => $I->generateUuidV4(),
            'numero' => '1',
            'valor' => '80',
            'status' => 2,
            'negocio' => $atc['negocio'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.propostas', $proposta);

        unset($proposta['created_at']);
        unset($proposta['created_by']);
        unset($proposta['updated_at']);
        unset($proposta['updated_by']);
        $proposta['negocio'] = $atc;

        //cria o capítulo raiz
        $capitulo = [
            'propostacapitulo' => $I->generateUuidV4(),
            'nome' => 'Pedido',
            'proposta'=> $proposta['proposta'],
            'pai' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.propostascapitulos', $capitulo);

        $proposta['propostacapitulo'] = $capitulo;
        return $proposta;
    }

    /**
     * Proposta item
     * Baseado no dump
     */
    public function haveInDatabasePropostaItem(FunctionalTester $I, $atc, $proposta, $fornecedor = null, $orcamento = null, $itemcontrato = null, $tarefa = null, $nomeServAlterado = false)
    {
        $fornecedor_ = ($fornecedor != null ? $fornecedor : $this->haveInDatabaseFornecedor($I));
        // insert into ns.pessoas (id, pessoa, nome, nomefantasia, cnpj, tenant) values ('460f64b5-e296-4ec6-8833-b93edd9310a7', '10', 'FMA', 'FMA', '33856147000137', '47' );
        $propostaitens = [
            'propostaitem' => $I->generateUuidV4(),
            'proposta' => $proposta['proposta'],
            'propostacapitulo' => $proposta['propostacapitulo']['propostacapitulo'],
            'composicao' =>  'adc67791-c178-47f0-81e8-522e2864c3b2',
            'fornecedor' => $fornecedor_['fornecedor'],
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
            'servicoorcamento' => ($orcamento != null ? $orcamento : null),
            'itemcontratoapagar' => ($itemcontrato != null ? $itemcontrato : null),
            'previsaodatahorainicio' => date('Y-m-d H:i:s'),
            'previsaodatahorafim' => date('Y-m-d H:i:s'),
            'id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf',
            'tarefa' => $tarefa['tarefa'],
            'nomeservicoalterado' => $nomeServAlterado
        ];

        if (!is_null($fornecedor)) {
            $propostaitens['fornecedor'] = $fornecedor['fornecedor'];
        }

        $I->haveInDatabase('crm.propostasitens', $propostaitens);
        unset($propostaitens['created_at']);
        unset($propostaitens['created_by']);
        unset($propostaitens['updated_at']);
        unset($propostaitens['updated_by']);
        $propostaitens['negocio'] = $atc;

        //cria o capítulo raiz
        $capitulo = [
            'propostacapitulo' => $I->generateUuidV4(),
            'nome' => 'Pedido',
            'pai' => null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.propostascapitulos', $capitulo);

        $propostaitens['propostacapitulo'] = $capitulo;
        return $propostaitens;
    }

    /**
     * Proposta item familia
     */
    public function haveInDatabasePropostaItemFamilia(FunctionalTester $I, $propostaitem, $familia, $composicao, $composicaofamilia, $dados = []){
        $propostaitemfamilia = [
            'propostaitemfamilia' => $I->generateUuidV4(),
            'propostaitem' => $propostaitem['propostaitem'],
            'familia' => $familia['familia'],
            'quantidade' => 5,
            'valor' => 10,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'composicaofamilia' => $composicaofamilia['composicaofamilia'],
            'composicao' => $composicao['composicao'],
            'grupoempresarial' => $this->id_grupoempresarial,
            'nome' => isset($dados['nome']) ? $dados['nome'] : $familia['descricao'],
            'nomefamiliaalterado' => isset($dados['nomefamiliaalterado']) ? $dados['nomefamiliaalterado'] : false
        ];

        $I->haveInDatabase('crm.propostasitensfamilias', $propostaitemfamilia);

        return $propostaitemfamilia;
    }

    /**
     * Proposta item função
     */
    public function haveInDatabasePropostaItemFuncao(FunctionalTester $I, $propostaitem, $funcao, $composicao, $composicaofuncao, $dados = []){
        $propostaitemfuncao = [
            'propostaitemfuncao' => $I->generateUuidV4(),
            'propostaitem' => $propostaitem['propostaitem'],
            'funcao' => $funcao['funcao'],
            'quantidade' => 5,
            'valor' => 10,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'composicaofuncao' => $composicaofuncao['composicaofuncao'],
            'composicao' => $composicao['composicao'],
            'grupoempresarial' => $this->id_grupoempresarial,
            'nome' => isset($dados['nome']) ? $dados['nome'] : 'Nome Prop Item Função',
            'nomefuncaoalterado' => isset($dados['nomefuncaoalterado']) ? $dados['nomefuncaoalterado'] : false
        ];

        $I->haveInDatabase('crm.propostasitensfuncoes', $propostaitemfuncao);

        return $propostaitemfuncao;
    }

    public function haveInDatabaseProjetoEscopo(FunctionalTester $I, $dados){
        
        $projetoescopo = [
            'projetoescopo' => $I->generateUuidV4(),
            'projeto' => $dados['projeto'],
            'tenant' => $this->tenant_numero,
            'ordem' => 0,
            'tipo' => 6,
            'descricao' => $dados['descricao'],
        ];

        $I->haveInDatabase('gp.projetosescopo', $projetoescopo);

        return $projetoescopo;
    }

    public function haveInDatabaseTarefa(FunctionalTester $I, $projetoescopo){
        
        $tarefa = [
            'tarefa' => $I->generateUuidV4(),
            'projetoescopo' => $projetoescopo['projetoescopo'],
            'numero' => 521,
            'situacao' => 0,
            'tenant' => $this->tenant_numero
        ];

        $I->haveInDatabase('gp.tarefas', $tarefa);

        return $tarefa;
    }

    /**
     * Fornecedor envolvido(Fornecedor acionado na ficha financeira)
     * Baseado no dump
     */
    public function haveInDatabaseFornecedorEnvolvido(FunctionalTester $I, $atc, $fornecedor, $acionamentoMetodo, $acionamentoData = null, $acionamentoAceito = true, $dados=[]){
        $fornecedorenvolvido = [
            'fornecedorenvolvido' => $I->generateUuidV4(),
            'negocio' => $atc['negocio'],
            'fornecedor' => $fornecedor['fornecedor'],
            'acionamentoaceito' => $acionamentoAceito,
            'acionamentometodo' => $acionamentoMetodo,
            'acionamentorespostaprazo' => 10,
            'acionamentodata' => ($acionamentoData != null ? $acionamentoData : date('Y-m-d H:i:s')),
            'possuidescontoparcial' => isset($dados['possuidescontoparcial']) ? $dados['possuidescontoparcial'] : false,
            'possuidescontoglobal' => isset($dados['possuidescontoglobal']) ? $dados['possuidescontoglobal'] : false,
            'descontoglobal' => isset($dados['descontoglobal']) ? $dados['descontoglobal'] : 0,
            'descontoglobaltipo' => isset($dados['descontoglobaltipo']) ? $dados['descontoglobaltipo'] : 1,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d H:i:s'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial'=> $this->id_grupoempresarial,
            'descontoglobal' => isset($dados['descontoglobal']) ? $dados['descontoglobal'] : 0
        ];
        
        $I->haveInDatabase('crm.fornecedoresenvolvidos', $fornecedorenvolvido);
       
        return $fornecedorenvolvido;
    }

    /**
     * Responsabilidade financeira (Atribuição da responsabilidade financeira dos contratos de acordo com o serviço/cliente)
     */
    // public function haveInDatabaseResponsabilidadeFinanceira(FunctionalTester $I, $dados, $atc, $proposta, $propostaitem, $propostaitemfamilia = null, $propostaitemfuncao = null, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf']){
    public function haveInDatabaseResponsabilidadeFinanceira(FunctionalTester $I, $dados, $atc, $orcamento, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf']){
        $responsabilidadefinanceira = [
            'responsabilidadefinanceira' => $I->generateUuidV4(),
            'negocio' => $atc['negocio'],
            // 'proposta' => $proposta['proposta'],
            'orcamento' => $orcamento['orcamento'],
            'tipodivisao' => isset($dados['tipodivisao']) ? $dados['tipodivisao'] : '0',
            'valorservico' => isset($dados['valorservico']) ? $dados['valorservico'] : '0',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d H:i:s'),
            'geranotafiscal' => isset($dados['geranotafiscal']) ? $dados['geranotafiscal'] : false,
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        
        $I->haveInDatabase('crm.responsabilidadesfinanceiras', $responsabilidadefinanceira);
        $responsabilidadefinanceira['responsabilidadesfinanceirasvalores'] = [];
        foreach ($dados['responsabilidadesfinanceirasvalores'] as $responsabilidadeValor) {
            $responsabilidadeValor['id_grupoempresarial'] = $id_grupoempresarial['id_grupoempresarial'];
            $responsabilidadeValorBD = $I->haveInDatabaseResponsabilidadeFinanceiraValor($I, $responsabilidadefinanceira, $responsabilidadeValor);
            $responsabilidadefinanceira['responsabilidadesfinanceirasvalores'][] = $responsabilidadeValorBD;
        }
       
        return $responsabilidadefinanceira;
    }

    /**
     * Responsabilidade financeira valor (Rateio da atribuição da responsabilidade financeira dos contratos de acordo com o serviço/cliente)
     */
    public function haveInDatabaseResponsabilidadeFinanceiraValor(FunctionalTester $I, $responsabilidadeFinanceira, $dados = []){
        $responsabilidadefinanceiravalor = [
            'responsabilidadefinanceiravalor' => $I->generateUuidV4(),
            'responsabilidadefinanceira' => $responsabilidadeFinanceira['responsabilidadefinanceira'],
            'responsavelfinanceiro' => $dados['responsavelfinanceiro'],
            'valorpagar' => $dados['valorpagar'],
            'contrato' => isset($dados['contrato']) ? $dados['contrato'] : null,
            'contratoapagar' => isset($dados['contratoapagar']) ? $dados['contratoapagar'] : null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d H:i:s'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial'=> $dados['id_grupoempresarial']
        ];
        
        $I->haveInDatabase('crm.responsabilidadesfinanceirasvalores', $responsabilidadefinanceiravalor);

        return $responsabilidadefinanceiravalor;
    }


    /**
     * Inclui dados para a seguradora, quando o atc possui seguradora envolvida
     */
    public function haveInDatabaseDadosParaSeguradora(FunctionalTester $I, $atc, $produtoseguradora, $apolice, $vinculo, $dados = [])
    {
        $dadosParaSeguro = [
            'negociodadosseguradora' => $I->generateUuidV4(),
            'negocio' => $atc['negocio'],
            'seguradora' => $atc['cliente']['cliente'],
            'produtoseguradora' => $produtoseguradora['templatepropostagrupo'],
            'apolice' => $apolice['templateproposta'],
            'apolicetipo' => '1',
            'sinistro' => '123',
            'apoliceconfirmada' => false,
            'titularnome' => 'Fulano',
            'titulartipodocumento' => '1',
            'titularcpf' => '00000000191',
            'titularcnpj' => '',
            'titularvinculo' => $vinculo['vinculo'],
            'titularcontatos' => '{}',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf',
            'valorautorizado' => 5000,
            'valorapolice' => 5000,
            'beneficiario' => isset($dados['beneficiario']) ? $dados['beneficiario'] : null
        ];
        $I->haveInDatabase('crm.atcsdadosseguradoras', $dadosParaSeguro);
        return $dadosParaSeguro;
        // $negocio['negociodadosseguradora'] = $dadosParaSeguro['negociodadosseguradora'];
        // $negocio['seguradoraprodutoseguradora'] = $produtoseguradora;
        // $negocio['seguradoraapolice'] = $apolice;
        // $negocio['seguradoraapolicetipo'] = $dadosParaSeguro['apolicetipo'];
        // $negocio['seguradorasinistro'] = $dadosParaSeguro['sinistro'];
        // $negocio['seguradoratitularnome'] = $dadosParaSeguro['titularnome'];
        // $negocio['seguradoratitulartipodocumento'] = $dadosParaSeguro['titulartipodocumento'];
        // $negocio['seguradoratitularcpf'] = $dadosParaSeguro['titularcpf'];
        // $negocio['seguradoratitularcnpj'] = $dadosParaSeguro['titularcnpj'];
        // $negocio['seguradoratitularvinculo'] = $vinculo;
        // return $negocio;
    }

    /**
     * Cria grupo de template de proposta (produto seguradora)
     */
    public function haveInDatabaseTemplatepropostagrupo(FunctionalTester $I, $cliente)
    {
        $propostatemplategrupo = [
            'templatepropostagrupo' => $I->generateUuidV4(),
            'nome' => 'Produto Seguradora 1',
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'email' => 'emailgrupo@email.com',
            'grupoempresarial' => $I->id_grupoempresarial,
        ];
        $I->haveInDatabase('crm.templatespropostasgrupos', $propostatemplategrupo);
        unset($propostatemplategrupo['created_at']);
        unset($propostatemplategrupo['created_by']);
        unset($propostatemplategrupo['updated_at']);
        unset($propostatemplategrupo['updated_by']);
        $propostatemplategrupo['cliente'] = $cliente;
        return $propostatemplategrupo;
    }

    /**
     * Cria template de proposta (apólice)
     */
    public function haveInDatabaseTemplateproposta(FunctionalTester $I, $templatepropostagrupo, $dados = [])
    {
        $propostatemplate = [
            'templateproposta' => $I->generateUuidV4(),
            'nome' => 'Apólice 1',
            'templatepropostagrupo' => $templatepropostagrupo['templatepropostagrupo'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $I->id_grupoempresarial,
        ];
        $I->haveInDatabase('crm.templatespropostas', $propostatemplate);
        unset($propostatemplate['created_at']);
        unset($propostatemplate['created_by']);
        unset($propostatemplate['updated_at']);
        unset($propostatemplate['updated_by']);
        $propostatemplate['templatepropostagrupo'] = $templatepropostagrupo;

        // Documentos
        if (isset($dados['templatespropostasdocumentos']) && count($dados['templatespropostasdocumentos']) > 0) {
            $propostatemplate['templatespropostasdocumentos'] = [];
            foreach ($dados['templatespropostasdocumentos'] as $doc) {
                $doc['templateproposta'] = $propostatemplate['templateproposta'];
                $docInserido = $I->haveInDatabaseTemplatePropostaDocumento($I, $doc);
                $propostatemplate['templatespropostasdocumentos'][] = $docInserido;
            }
        }

        return $propostatemplate;
    }

    public function haveInDatabaseTemplatePropostaDocumento(FunctionalTester $I, $dados = [])
    {
        $templatepropostadocumento = [
            'templatepropostadocumento' => $I->generateUuidV4(),
            'templateproposta' => $dados['templateproposta'],
            'tipodocumento' => $dados['tipodocumento']['tipodocumento'],
            'copiasimples' => (int) isset($dados['copiasimples']) ? $dados['copiasimples'] : false,
            'copiaautenticada' => (int) isset($dados['copiaautenticada']) ? $dados['copiaautenticada'] : false,
            'original' => (int) isset($dados['original']) ? $dados['original'] : false,
            'permiteenvioemail' => (int) isset($dados['permiteenvioemail']) ? $dados['permiteenvioemail'] : false,
            'pedirinformacoesadicionais' => (int) isset($dados['pedirinformacoesadicionais']) ? $dados['pedirinformacoesadicionais'] : false,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $I->id_grupoempresarial,
        ];

        $I->haveInDatabase('crm.templatespropostasdocumentos', $templatepropostadocumento);
        $templatepropostadocumento['tipodocumento'] = $dados['tipodocumento'];

        return $templatepropostadocumento;
    }

    public function haveInDatabaseVinculo(FunctionalTester $I)
    {
        $vinculo = [
            'vinculo' => $I->generateUuidV4(),
            'nome' => 'Midia I',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.vinculos', $vinculo);
        unset($vinculo['created_at']);
        unset($vinculo['created_by']);
        unset($vinculo['updated_at']);
        unset($vinculo['updated_by']);
        return $vinculo;
    }

    /**
     * Grava temporariamente área de atc no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseAreaDeAtc(FunctionalTester $I, $estabelecimento = null)
    {
        $area = [
            'negocioarea' => $I->generateUuidV4(),
            'nome' => 'Area 1',
            'descricao' => 'Descricao da area 1',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial,
            'possuiseguradora' => true,
            'estabelecimento' => isset($estabelecimento['estabelecimento']) ? $estabelecimento['estabelecimento'] : null
        ];
        $I->haveInDatabase('crm.atcsareas', $area);
        unset($area['created_at']);
        unset($area['created_by']);
        unset($area['updated_at']);
        unset($area['updated_by']);
        return $area;
    }

    
    

    /**
     * Grava temporariamente área de atc no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseAreaDeAtcComLocalizacao(FunctionalTester $I)
    {
        $area = [
            'negocioarea' => $I->generateUuidV4(),
            'nome' => 'Area 1',
            'descricao' => 'Descricao da area 1',
            'localizacao' => 'true',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.atcsareas', $area);
        unset($area['created_at']);
        unset($area['created_by']);
        unset($area['updated_at']);
        unset($area['updated_by']);
        return $area;
    }


    /**
     * Grava temporariamente mídia no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseMidia(FunctionalTester $I, $dados = [], $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        $midia = [
            'midiaorigem' => $I->generateUuidV4(),
            // 'nome' => 'Midia 1',
            'codigo' => isset($dados['codigo']) ? $dados['codigo'] : 'Midia 1',
            'descricao' => 'Descricao da mídia 1',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $id_grupoempresarial['id_grupoempresarial']
        ];
        $I->haveInDatabase('crm.midiasorigem', $midia);
        unset($midia['created_at']);
        unset($midia['created_by']);
        unset($midia['updated_at']);
        unset($midia['updated_by']);
        return $midia;
    }

    /**
     * Grava temporariamente tipo de acionamento no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseTipoAcionamento(FunctionalTester $I, $nome = null)
    {
        $nome = ($nome) ? $nome : "Tipo de Acionamento 123";

        $tipoacionamento = [
            'tiposacionamento' => $I->generateUuidV4(),
            'nome' => $nome,
            'descricao' => 'Descrição Tipo de Acionamento 123',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.tiposacionamentos', $tipoacionamento);

        //Não são usados
        unset($tipoacionamento['created_at']);
        unset($tipoacionamento['created_by']);
        unset($tipoacionamento['updated_at']);
        unset($tipoacionamento['updated_by']);

        return $tipoacionamento;
    }
    /**
     * Grava temporariamente um histórico padrão no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseHistoricoPadrao(FunctionalTester $I, $codigo = null)
    {
        $codigo = ($codigo) ? $codigo : "Código 0123";

        $historicopadrao = [
            'historicopadrao' => $I->generateUuidV4(),
            'codigo' => $codigo,
            'descricao' => 'Descrição Histórico Padrão 0123',
            'texto' => 'Texto Histórico Padrão 0123',
            'tipo' => 102,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.historicospadrao', $historicopadrao);

        //Não são usados
        unset($historicopadrao['created_at']);
        unset($historicopadrao['created_by']);
        unset($historicopadrao['updated_at']);
        unset($historicopadrao['updated_by']);

        return $historicopadrao;
    }

    /**
     * Retorna o objeto correto de responsável financeiro para enviar no banco.
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseResponsavelFinanceiro(FunctionalTester $I, $cliente)
    {
        $responsavelfinanceiro = 
        [ 
            [
            'principal' => true,
            'responsavelfinanceiro' => ['cliente' => $cliente['id']],
            'negocio' => null,
            'tenant' => $this->tenant_numero
            ]
        ];
        return $responsavelfinanceiro;
    }
    /** ----- 
    * Métodos sobre atcs 
    */

    public function haveInDatabaseAtcCompleto(FunctionalTester $I, $area, $origem, $cliente, $negociopai = null, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        $pais = [
            "pais" => "005",
            "nome" =>"Texas",
            'lastupdate' => date('Y-m-d'),
        ];
        $I->haveInDatabase('ns.paises', $pais);
        $cidadeestrangeira = [
            "cidadeestrangeira" => $this->generateUuidV4(),
            "nome" => "Boniv",
            "pais" => $pais['pais'],
            'updated_at' => date('Y-m-d'),
        ];
        $cliente['endereco']['pais'] = $pais['pais'];
        $cliente['endereco']['cidadeestrangeira'] = $cidadeestrangeira['cidadeestrangeira'];
        $I->haveInDatabase("ns.cidadesestrangeiras", $cidadeestrangeira);
        $endereco = [
            'endereco' => $this->generateUuidV4(),
            'cep' => '24127355',
            'logradouro' => 'Rua 3',
            'bairro' => 'Bairro',
            'numero' => '20',
            'complemento' => 'apartamento 101',
            'referencia' => 'perto do mercado',
            'nome' => 'Novo',
            'pais' => $pais['pais'],
            'cidadeestrangeira' => $cidadeestrangeira['cidadeestrangeira'],
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('ns.enderecos', $endereco);
        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 1',
            'codigo' => 'N1',
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'localizacao' => $endereco['endereco'],
            'negociopai' => $negociopai,
            'localizacaocep' => '24127355',
            'localizacaobairro' => 'Bairro',
            'localizacaorua' => 'Rua 3',
            'localizacaonumero' => '45',
            'localizacaocomplemento' => 'Casa 20',
            'localizacaoreferencia' => 'Perto do mercado',
            'referenciaexterna' => '20550',
            'localizacaopais' => $pais['pais'],
            'localizacaocidadeestrangeira' => $cidadeestrangeira['cidadeestrangeira'],
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];
        $I->haveInDatabase('crm.atcs', $atc);
        $responsavelfinanceiro = [
            'negocioresponsavelfinanceiro' => $I->generateUuidV4(),
            'negocio' => $atc['negocio'],
            'responsavelfinanceiro' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'principal' => true,
            'grupoempresarial' => $id_grupoempresarial['id_grupoempresarial']
        ];
        $I->haveInDatabase('crm.atcsresponsaveisfinanceiros', $responsavelfinanceiro);
        unset($responsavelfinanceiro['created_at']);
        unset($responsavelfinanceiro['created_by']);
        $atc['responsavelfinanceiro'] = $responsavelfinanceiro; //salvando referência do responsavelfinanceiro
        unset($responsavelfinanceiro);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        unset($pais['lastupdate']);
        unset($cidadeestrangeira['updated_at']);
        unset($cidadeestrangeira['pais']);
        $atc['localizacaopais'] = $pais;
        $atc['localizacaocidadeestrangeira'] = $cidadeestrangeira;
        return $atc;
    }



    /**
     * Grava temporariamente uma lista de pendências em atc
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseAtcpendencialista(FunctionalTester $I, $atc)
    {
        $pendencialista = [
            'negociopendencialista' => $I->generateUuidV4(),
            'nome' => 'Dados do local',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.atcspendenciaslistas', $pendencialista);
        unset($pendencialista['created_at']);
        unset($pendencialista['created_by']);
        unset($pendencialista['updated_at']);
        unset($pendencialista['updated_by']);
        $pendencialista['negocio'] = $atc;
        return $pendencialista;
    }

    /**
     * Grava temporariamente uma lista de pendências em atc
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseAtcpendencia(FunctionalTester $I, $atcpendencialista, $atc, $prioridade, $realizada = false)
    {
        $pendencia = [
            'negociopendencia' => $I->generateUuidV4(),
            'texto' => 'Pendencia 1',
            'negociopendencialista' => $atcpendencialista['negociopendencialista'],
            'realizada' => $realizada,
            'negocio' => $atc['negocio'],
            'prioridade' => $prioridade['prioridade'],
            'prazo' => 30,
            'impeditivo' => 2, //Impeditivo Forcenedor
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'temponotificaexpiracao' => 10,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.atcspendencias', $pendencia);
        unset($pendencia['created_at']);
        unset($pendencia['created_by']);
        unset($pendencia['updated_at']);
        unset($pendencia['updated_by']);
        $pendencia['negociopendencialista'] = $atcpendencialista;
        return $pendencia;
    }

    /**
     * Grava temporariamente uma lista de pendências com uma pendência em atc
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseAtcpendencialistaComPendencia(FunctionalTester $I, $atc)
    {
        $pendencialista = [
            'negociopendencialista' => $I->generateUuidV4(),
            'nome' => 'Dados do local',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.atcspendenciaslistas', $pendencialista);
        $pendencia = [
            'negociopendencia' => $I->generateUuidV4(),
            'texto' => 'Ligar para o cliente e perguntar sobre ponto de referencia',
            'negociopendencialista' => $pendencialista['negociopendencialista'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('crm.atcspendencias', $pendencia);
        unset($pendencialista['created_at']);
        unset($pendencialista['created_by']);
        unset($pendencialista['updated_at']);
        unset($pendencialista['updated_by']);
        $pendencialista['negocio'] = $atc;
        return $pendencialista;
    }

    /* ---- */

    /**
     * Grava temporariamente área de atc no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabaseComposicao(FunctionalTester $I, $dados = [])
    {
        $composicao = [
            'composicao' => $I->generateUuidV4(),
            'nome' => isset($dados['nome']) ? $dados['nome'] : 'Comp genérica',
            'descricao' => isset($dados['descricao']) ? $dados['descricao'] : 'Ao infinito e além',
            'codigo' => isset($dados['codigo']) ? $dados['codigo'] : '123',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'servicotecnico' => '37ea071a-c2cd-4dba-87e8-5300a5be7af3',
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial,
            'servicocoringa' => isset($dados['servicocoringa']) ? $dados['servicocoringa'] : false,
            'servicoexterno' => isset($dados['servicoexterno']) ? $dados['servicoexterno'] : false,
            'servicoprestadoraacionada' => isset($dados['servicoprestadoraacionada']) ? $dados['servicoprestadoraacionada'] : false,
            'itemfaturamento' => isset($dados['itemfaturamento']) ? $dados['itemfaturamento'] : NULL
        ];
        $I->haveInDatabase('crm.composicoes', $composicao);
        unset($composicao['created_at']);
        unset($composicao['created_by']);
        unset($composicao['updated_at']);
        unset($composicao['updated_by']);
        $composicao['servicotecnico'] = [ 'servicotecnico' => $composicao['servicotecnico']];
        return $composicao;
    }

    /** 
     * Métodos sobre estoque
     */
    public function haveInDatabaseFamilia(FunctionalTester $I, $dados = [])
    {
        $familia = [
            'familia' => $I->generateUuidV4(),
            'codigo' => isset($dados['codigo']) ? $dados['codigo'] : '123',
            'descricao' => isset($dados['descricao']) ? $dados['descricao'] : 'familia de testes',
            'familiacoringa' => isset($dados['familiacoringa']) ? $dados['familiacoringa'] : false,
            'lastupdate' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'valor' => isset($dados['valor']) ? $dados['valor'] : '100.00',
            'familiacoringa' => isset($dados['familiacoringa']) ? $dados['familiacoringa'] : false
        ];
        $I->haveInDatabase('estoque.familias', $familia);
        unset($familia['lastupdate']);
        unset($familia['tenant']);
        return $familia;
    }

    public function haveInDatabaseComposicaoFamilia(FunctionalTester $I, $familia, $composicao)
    {
        $composicaofamilia = [
            'composicaofamilia' => $I->generateUuidV4(),
            'composicao' => $composicao['composicao'],
            'familia' => $familia['familia'],
            'quantidade' => 4,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => '47'
        ];
        $I->haveInDatabase('crm.composicoesfamilias', $composicaofamilia);
        return $composicaofamilia;
    }

    /**
     * Métodos sobre gp
     */
    public function haveInDatabaseFuncao(FunctionalTester $I, $codigo = null)
    {
        $codigo = ($codigo) ? : "123";
        $funcao = [
            'funcao' => $I->generateUuidV4(),
            'codigo' => $codigo,
            'descricao' => 'teste',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'lastupdate' => date('Y-m-d H:i:s'),
            'funcaocoringa' => false
        ];
        $I->haveInDatabase('gp.funcoes', $funcao);
        unset($funcao['created_at']);
        unset($funcao['created_by']);
        unset($funcao['updated_at']);
        unset($funcao['updated_by']);
        unset($funcao['lastupdate']);
        unset($funcao['tenant']);
        return $funcao;
    }
    public function haveInDatabaseFuncaoComLastUpdate(FunctionalTester $I, $codigo = null)
    {
        $codigo = ($codigo) ? : "123";
        $funcao = [
            'funcao' => $I->generateUuidV4(),
            'codigo' => $codigo,
            'descricao' => 'teste',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'updated_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'lastupdate' => date('Y-m-d H:i:s'),
            'funcaocoringa' => false
        ];
        $I->haveInDatabase('gp.funcoes', $funcao);
        unset($funcao['created_at']);
        unset($funcao['created_by']);
        unset($funcao['updated_at']);
        unset($funcao['updated_by']);
        return $funcao;
    }
    public function haveInDatabaseComposicaoFuncao(FunctionalTester $I, $funcao, $composicao)
    {
        $composicaofuncao = [
            'composicaofuncao' => $I->generateUuidV4(),
            'composicao' => $composicao['composicao'],
            'funcao' => $funcao['funcao'],
            'quantidade' => 4,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => '47'
        ];
        $I->haveInDatabase('crm.composicoesfuncoes', $composicaofuncao);
        unset($composicaofuncao['created_at']);
        unset($composicaofuncao['created_by']);
        unset($composicaofuncao['tenant']);
        return $composicaofuncao;
    }

    /**
     * Métodos sobre Cidade 
     * @todo melhorar recuperação do dado
     */

    public function haveInDatabaseCidadeEstrangeira(FunctionalTester $I, $pais)
    {
        $cidadeestrangeira = [
            'cidadeestrangeira' => '0f381248-1f3c-46e9-b075-3445d7de6288',
            'nome' => 'La Paz',
            'pais' => '002',
        ];
        return $cidadeestrangeira;
    }

    /* Contrato */

    public function haveInDatabaseClienteCommunicipioPrestacao(FunctionalTester $I, $cliente){
        $municipio = [
            'pessoamunicipio' => $I->generateUuidV4(),
            'pessoa' => $cliente['cliente'],
            'ibge' => '3304557',
            'tenant' => '47',
            'lastupdate' => date('Y-m-d'),
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        $I->haveInDatabase('ns.pessoasmunicipios', $municipio);
        return $municipio;
    }

    /**
     * Grava temporariamente prioridade no banco para auxiliar o teste
     * @param FunctionalTester $I
     * @return type
     */
    public function haveInDatabasePrioridade(FunctionalTester $I)
    {
        $prioridade = [
            'prioridade' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'nome' => 'Necessidade de ação imediata',
            'ordem' => 0,
            'cor' => '#ef4e17',
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $I->haveInDatabase('ns.prioridades', $prioridade);
        return $prioridade;
    }

    public function haveInDatabaseBanco(FunctionalTester $I)
    {
        $banco = [
            'banco' => $I->generateUuidV4(),
            'numero' => 101,
            'nome' => 'Nubank',
            'codigo' => 11,
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('financas.bancos', $banco);
        return $banco;
    }

    /**
     * Forma pagamento
     * Recupera do dump.sql
     */
    public function haveInDatabaseFormapagamento(FunctionalTester $I){
        return [
            'formapagamento' => '4d22e7b0-79c3-421d-9df4-0b4ae9a472b2',
            'codigo' => 'Boleto',
            'descricao' => 'Boleto bancário',
            'bloquado' => false,
            'tenant' => 47
        ];
    }

    /**
     * Forma pagamento
     * Recupera do dump.sql
     */
    public function haveInDatabaseContrato(FunctionalTester $I, $formapagamento, $municipioprestacao, $atc, $grupoempresarial, $tipo = null, $dados = []){
        $contrato = [
            'contrato' => $I->generateUuidV4(),
            'codigo' => $atc['codigo'],
            'descricao' => $atc['nome'],
            'datainicial' => date("Y-m-d H:i:s") ,
            'diainicioreferencia' => date("d") ,
            'datacontrolesistema' => date("Y-m-d H:i:s") ,
            'tipocontrato' => ($tipo != null ? $tipo : 1),
            'definicaocontratante' => 'Cliente',
            'participante' => $atc['cliente']['cliente'],
            'pessoamunicipio' => $municipioprestacao['pessoamunicipio'],
            'id_formapagamento' => $formapagamento['formapagamento'],
            'definicaobeneficiario' => 'Estabelecimento',
            'estabelecimento' => $atc['estabelecimento']['estabelecimento'],
            'conta' => null,
            'layoutcobranca' => null,
            'qtddiasdesconto' => '1',
            'qtddiasmulta' => '1',
            'qtddiasjurosdiarios' => '0',
            'unidadenatureza' => '0',
            'unidadeintervalonatureza' => '0',
            'quantidadeintervalonatureza' => '0',
            'tipovencimento' => '0',
            'diavencimento' => '0',
            'adicaomesesvencimento' => '0',
            'tipocobranca' => '0',
            'emitirnotafiscal' => true,
            'familia' => $grupoempresarial,
            'processado' => '1',
            'cancelado' => false,
            'origem' => '4',
            'tipocontabilizacao' => '0',
            'numero' => isset($dados['numero']) ? $dados['numero'] : '100',
            'perfilcontrato' => 'S',
            'lastupdate' => date("Y-m-d H:i:s"),
            'tenant' => '47',
            'created_by' => '{"nome":"usuario"}',
            'created_at' => date("Y-m-d H:i:s"),
            'descontoglobalitensnaofaturados' => isset($dados['descontoglobalitensnaofaturados']) ? $dados['descontoglobalitensnaofaturados'] : 0
        ];
         
            
        $I->haveInDatabase('financas.contratos', $contrato);
        return $contrato;
    }

    /**
     * Forma pagamento
     * Recupera do dump.sql
     */
    public function haveInDatabaseContratoDePagamento(FunctionalTester $I, $formapagamento, $municipioprestacao, $atc, $grupoempresarial, $dados = []){
        $contrato = [
            'contrato' => $I->generateUuidV4(),
            'codigo' => $atc['codigo'],
            'descricao' => $atc['nome'],
            'datainicial' => date("Y-m-d H:i:s") ,
            'diainicioreferencia' => date("d") ,
            'datacontrolesistema' => date("Y-m-d H:i:s") ,
            'tipocontrato' => '0',
            'definicaocontratante' => 'Cliente',
            'participante' => $atc['cliente']['cliente'],
            'pessoamunicipio' => $municipioprestacao['pessoamunicipio'],
            'id_formapagamento' => $formapagamento['formapagamento'],
            'definicaobeneficiario' => 'Estabelecimento',
            'estabelecimento' => $atc['estabelecimento']['estabelecimento'],
            'conta' => null,
            'layoutcobranca' => null,
            'qtddiasdesconto' => '1',
            'qtddiasmulta' => '1',
            'qtddiasjurosdiarios' => '0',
            'unidadenatureza' => '0',
            'unidadeintervalonatureza' => '0',
            'quantidadeintervalonatureza' => '0',
            'tipovencimento' => '0',
            'diavencimento' => '0',
            'adicaomesesvencimento' => '0',
            'tipocobranca' => '0',
            'emitirnotafiscal' => true,
            'familia' => $grupoempresarial,
            'processado' => '1',
            'cancelado' => false,
            'origem' => '4',
            'tipocontabilizacao' => '0',
            'numero' => isset($dados['numero']) ? $dados['numero'] : '100',
            'perfilcontrato' => 'S',
            'lastupdate' => date("Y-m-d H:i:s"),
            'tenant' => '47',
            'created_by' => '{"nome":"usuario"}',
            'created_at' => date("Y-m-d H:i:s")
        ];
         
            
        $I->haveInDatabase('financas.contratos', $contrato);
        return $contrato;
    }

    /**
     * Insere item de contrato
     * @todo melhorar recuperação dos dados dos itens de faturamento
     * 
     * Recupera do dump.sql
     */
    public function haveInDatabaseItemContrato(FunctionalTester $I, $contrato, $propostaitem, $atc, $faturado = 0){
        $itemcontrato = [
            'itemcontrato' => $I->generateUuidV4(),
            'contrato' => $contrato['contrato'],
            'servico' => '37ea071a-c2cd-4dba-87e8-5300a5be7af3',
            'codigo' => 'velorio',
            'valor' => $propostaitem['valor'],
            'processado' => false,
            'cancelado' => false,
            'quantidade' => 1,
            'recorrente' => false,
            'tipovalor' => -1 ,       
            'unidadenatureza' => 0,
            'unidadeintervalonatureza' => 0,
            'quantidadeintervalonatureza' => 0,
            'tipovencimento' => 0,
            'diavencimento' => 0,       
            'adicaomesesvencimento' => 0,
            'tipocobranca' => 0,
            'ultimadataprocessamento' => date("Y-m-d"),
            'diaultimadataprocessamento' =>date("d"),
            'numerodiasparavencimento' => $atc['cliente']['diasparavencimento'],
            'previsaovencimento' => date('Y-m-d', strtotime(date("Y-m-d") . '+ '.$atc['cliente']['diasparavencimento'].'days')),
            'situacaofaturamento' => 0,
            'origemnaorecorrente' => 0,
            'lastupdate' => date("Y-m-d H:i:s"),
            'tenant' => '47',
            'created_by' => '{"nome":"usuario"}',
            'created_at' => date("Y-m-d H:i:s"),
            'situacaofaturamento' => $faturado
        ];       
            
        $I->haveInDatabase('financas.itenscontratos', $itemcontrato);
        if(isset($propostaitem['propostaitem'])){
            $I->updateInDatabase('crm.propostasitens', ['itemcontrato'=> $itemcontrato['itemcontrato']], ['propostaitem' => $propostaitem['propostaitem']]);
        }
        return $itemcontrato;
    }
     /**
     * Insere item de orçamento
     * @todo melhorar recuperação dos dados dos itens de orçamento
     */
    public function haveInDatabaseOrcamento(FunctionalTester $I, $dados = [])
    {
        $fornecedor = isset($dados['fornecedor']) ? $dados['fornecedor'] : $this->haveInDatabaseFornecedor($I);
        
        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'atc' => $dados['atc'],
            'fornecedor' => $fornecedor['fornecedor'],
            'composicao' => isset($dados['composicao']) ? $dados['composicao'] : null,
            'familia' => isset($dados['familia']) ? $dados['familia'] : null,
            'propostaitem' => isset($dados['propostaitem']) ? $dados['propostaitem'] : null,
            'custo' => isset($dados['custo']) ? $dados['custo'] : 0,
            'servicotipo' => isset($dados['servicotipo']) ? $dados['servicotipo'] : null,
            'fornecedorterceirizado' => isset($dados['fornecedorterceirizado']) ? $dados['fornecedorterceirizado'] : null,
            'valor' => isset($dados['valor']) ? $dados['valor'] : 10,
            'valorreceber' => isset($dados['valorreceber']) ? $dados['valorreceber'] : 10,
            'status' => isset($dados['status']) ? $dados['status'] : 2,
            'acrescimo' => isset($dados['acrescimo']) ? $dados['acrescimo'] : 0,
            'desconto' => isset($dados['desconto']) ? $dados['desconto'] : 0,
            'descontoglobal' => isset($dados['descontoglobal']) ? $dados['descontoglobal'] : 0,
            'acrescimomotivo' => isset($dados['acrescimomotivo']) ? $dados['acrescimomotivo'] : null,
            'descontomotivo' => isset($dados['descontomotivo']) ? $dados['descontomotivo'] : null,
            'motivo' => isset($dados['motivo']) ? $dados['motivo'] : null,
            'faturar' => isset($dados['faturar']) ? $dados['faturar'] : false,
            'faturamentotipo' => isset($dados['faturamentotipo']) ? $dados['faturamentotipo'] : 2,
            'descricaomanual' => isset($dados['descricaomanual']) ? $dados['descricaomanual'] : false,
            'descricao' => isset($dados['descricao']) ? $dados['descricao'] : 'Item do Orçamento',
            'itemfaturamento' => isset($dados['itemfaturamento']) ? $dados['itemfaturamento'] : null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        
        $I->haveInDatabase('crm.orcamentos', $orcamento);
        unset($orcamento['created_at']);
        unset($orcamento['created_by']);
        unset($orcamento['updated_at']);
        unset($orcamento['updated_by']);
        unset($orcamento['fornecedor']);
        unset($orcamento['propostaitem']);
        unset($orcamento['status']);
        return $orcamento;
    }

    /**
     * Insere item de contas a pagar
     */
    public function haveInDatabaseCrmAtcContaPagar(FunctionalTester $I, $dados = [])
    {
        $contapagar = [
            'atccontaapagar' => $I->generateUuidV4(),
            'atc' => $dados['atc'],
            'prestador' => $dados['prestador']['fornecedor'],
            'descricao' => isset($dados['descricao']) ? $dados['descricao'] : 'Serviço 01',
            'servico' => $dados['servico'],
            'quantidade' => isset($dados['quantidade']) ? $dados['quantidade'] : 1,
            'valoracordado' => isset($dados['valoracordado']) ? $dados['valoracordado'] : 10,
            'valorpagar' => isset($dados['valorpagar']) ? $dados['valorpagar'] : 10,
            'orcamento' => isset($dados['orcamento']) ? $dados['orcamento'] : null,
            'itemprocessarcontapagar' => isset($dados['itemprocessarcontapagar']) ? $dados['itemprocessarcontapagar'] : null,
            'negociodocumento' => isset($dados['negociodocumento']) ? $dados['negociodocumento'] : null,
            'numerodocumento' => isset($dados['numerodocumento']) ? $dados['numerodocumento'] : null,
            'datadocumento' => isset($dados['datadocumento']) ? $dados['datadocumento'] : null,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial
        ];
        
        $I->haveInDatabase('crm.atcscontasapagar', $contapagar);
        unset($contapagar['created_at']);
        unset($contapagar['created_by']);
        unset($contapagar['updated_at']);
        unset($contapagar['updated_by']);
        
        $contapagar['prestador'] = $dados['prestador'];
        return $contapagar;
    }

     /**
     * Insere item advertencia
     * @todo melhorar recuperação dos dados dos itens de advertencia
     * 
     */
    public function haveInDatabaseAdvertencia(FunctionalTester $I, $fornecedor)
    {
        $advertencia = [
            'advertencia' => $I->generateUuidV4(),
            'fornecedor_id' => $fornecedor['fornecedor'],
            'motivo' => "1233",
            'status' => 0,
            'nome' => 47,
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];
        $I->haveInDatabase('ns.advertencias', $advertencia);
        $temp = $advertencia['advertencia'];
        $advertencia['motivoremocao'] = "111";
        $advertencia['nome'] = '1233';
        $advertencia['fornecedores'] =  [ 'fornecedor' => $fornecedor];
        $advertencia['fornecedor'] = $fornecedor['fornecedor'];
        $advertencia['id_grupoempresarial'] = $I->id_grupoempresarial;
        return $advertencia;
    }
    
    public function haveInDatabaseTipoAtividade(FunctionalTester $I)
    {
        $tipoatividade = [
            'tipoatividade' => $I->generateUuidV4(),
            'nome' => 'TestagemAvançada',
            'descricao' => 'teste',
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
            'tipo' => 0,
            'id_grupoempresarial' =>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $I->haveInDatabase('ns.tiposatividades', $tipoatividade);
        return $tipoatividade;
    }
    
    public function haveInDatabaseUnidade(FunctionalTester $I){
        $unidade = [
            'unidade' => $I->generateUuidV4(),
            'codigo' => '123',
            'descricao' => 'teste',
            'decimais' => 2,
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
        ];
        $I->haveInDatabase('estoque.unidades', $unidade);

        $conjunto = [
            'conjuntounidade' => $I->generateUuidV4(),
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'registro' => $unidade['unidade'],
            'tenant' => $this->tenant_numero
        ];
        $I->haveInDatabase('ns.conjuntosunidades', $conjunto);
        return $unidade;
    }
    
    /**
     * Insere Template de e-mail para advertir prestadores de serviço no banco de dados
     */
    public function haveInDatabaseTemplateEmailAdvertirPrestadorServico(FunctionalTester $I, $dados = []){
        $mockTabela = [
            "templateemailadvertirprestador" => $I->generateUuidV4(),
            "estabelecimento" => $dados['estabelecimento']['estabelecimento'],
            "enviar_email_ao_advertir" => (int) isset($dados['enviaremailaoadvertir']) ? $dados['enviaremailaoadvertir'] : false,
            "responder_para" => isset($dados['responderpara']) ? $dados['responderpara'] : 'funeraria.xpto@gmail.com.',
            'mensagem' => isset($dados['mensagem']) ? $dados['mensagem'] : 'O prestador de serviço não atendeu os requisitos necessários.',
            'mostrar_motivo_advertencia' => (int) isset($dados['mostrarmotivoadvertencia']) ? $dados['mostrarmotivoadvertencia'] : false,
            'rodape' => isset($dados['rodape']) ? $dados['rodape'] : 'Mensagem enviada automaticamente',
            'assinatura' => isset($dados['assinatura']) ? $dados['assinatura'] : 'Atenciosamente, Funerária XPTO.',
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => '{"nome":"usuario"}',
            'updated_at' => date("Y-m-d H:i:s"),
            'updated_by' => '{"nome":"usuario"}',
            'id_grupoempresarial' => $dados['id_grupoempresarial'],
            'tenant' => $this->tenant_numero
        ];
        
        $I->haveInDatabase('crm.templatesemailadvertirprestador', $mockTabela);

        // Ajusto dados para ficar um objeto da entidade
        $mockTabela['estabelecimento'] = $dados['estabelecimento'];
        $mockTabela['enviaremailaoadvertir'] = $mockTabela['enviar_email_ao_advertir'];
        unset($mockTabela['enviar_email_ao_advertir']);
        $mockTabela['responderpara'] = $mockTabela['responder_para'];
        unset($mockTabela['responder_para']);
        $mockTabela['mostrarmotivoadvertencia'] = $mockTabela['mostrar_motivo_advertencia'];
        unset($mockTabela['mostrar_motivo_advertencia']);
        
        return $mockTabela;
    }

    /**
     * Insere Empresa no banco de dados
     */
    public function haveInDatabaseEmpresa(FunctionalTester $I, $dados = []){
        $empresa = [
            "empresa" => $I->generateUuidV4(),
            "codigo" => (isset($dados['codigo']) ? $dados['codigo'] : 'teste_0001'),
            "raizcnpj" => (isset($dados['raizcnpj']) ? $dados['raizcnpj'] : '000137'),
            "ordemcnpj" => (isset($dados['ordemcnpj']) ? $dados['ordemcnpj'] : '000137'),
            "razaosocial" => (isset($dados['razaosocial']) ? $dados['razaosocial'] : 'TESTE EMPRESA'),
            "tenant" => $I->tenant_numero,
            "grupoempresarial" => $dados['id_grupoempresarial']
        ];

        $I->haveInDatabase('ns.empresas', $empresa);

        return $empresa;
    }

    /**
     * Insere Estabelecimento no banco de dados
     */
    public function haveInDatabaseEstabelecimento(FunctionalTester $I, $dados = []){
        $estabelecimento = [
            "estabelecimento" => $I->generateUuidV4(),
            "nomefantasia" => (isset($dados['codigo']) ? $dados['codigo'] : 'TESTE ESTABELECIMENTO FANTASIA'),
            "codigo" => (isset($dados['codigo']) ? $dados['codigo'] : 'teste_0001'),
            "tenant" => $I->tenant_numero,
            "empresa" => $dados['empresa']['empresa'],
            "raizcnpj" => '43796301',
            "ordemcnpj" => "000186",
            "inscricaoestadual" => "123456IE",
            "inscricaomunicipal" => "123456IM",
            "email" => "email@email.com",
            "site" => "www.site.com.br",
            "tipologradouro" => "TipoLog123",
            "logradouro" => "Logradouro 123",
            "numero" => "123",
            "complemento" => "Complemento 123",
            "bairro" => "Bairro 123",
            "cidade" => "Cidade 123",
            "cep" => "12345678",
            "ibge" => "654321",
            "dddtel" => "21",
            "telefone" => "12345678",
        ];
        $I->haveInDatabase('ns.estabelecimentos', $estabelecimento);
        
        return $estabelecimento;
    }

    /**
     * Insere Atendimento Comercial com Projeto no banco de dados
     */
    public function haveInDatabaseAtcComProjeto(FunctionalTester $I, $area, $origem, $cliente, $negociopai = null, $estabelecimento = ['estabelecimento'=>'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'], $codigo = "N1", $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf']) {
        $atc = [
            'negocio' => $I->generateUuidV4(),
            'nome' => 'Atc 1',
            'codigo' => $codigo,
            'area' => $area['negocioarea'],
            'origem' => $origem['midiaorigem'],
            'cliente' => $cliente['cliente'],
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'negociopai' => $negociopai,
            'estabelecimento' => $estabelecimento['estabelecimento'],
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial']
        ];

        $projeto = [
            "projeto" => $I->generateUuidV4(),
            "tenant" => $I->tenant_numero,
            "codigo" => $atc['codigo'],
            "nome" => $atc['nome'],
            "estabelecimento_id" => $atc['estabelecimento'],
            "cliente_id" => $atc['cliente'],
            // "datainicio" => null,//$negocio['codigo'],
            // "datafim" => null,//$negocio['codigo'],
            // "created_by" => $negocio['codigo'],
            "tempoadquirido" => 50000,
            "tipoprojeto_id" => '62b143c7-edf6-4923-bcfe-3d45eb33d761'
        ];
        $atc['projeto'] = $projeto['projeto'];
        
        $I->haveInDatabase('financas.projetos', $projeto);
        $I->haveInDatabase('crm.atcs', $atc);
        unset($atc['created_at']);
        unset($atc['created_by']);
        unset($atc['updated_at']);
        unset($atc['updated_by']);
        $atc['area'] = $area;
        $atc['origem'] = $origem;
        $atc['cliente'] = $cliente;
        $atc['estabelecimento'] = $estabelecimento;
        $atc['projeto'] = $projeto;
        
        return $atc;
    }

    public function haveInDatabasePromocaoLead(FunctionalTester $I, $dados = []){
        $promocaoLead = [
            'promocaolead' => $I->generateUuidV4(),
            'nome' => isset($dados['nome']) ? $dados['nome'] : 'promocao lead 1',
            'codigo' => isset($dados['codigo']) ? $dados['codigo'] : 'promocao lead codigo 1',
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial,
            'visivelfollowups' => 0,
            'visivelpropostas' => 0,
            'visivelleads' => 0,
            'visivelleadsqualificados' => 0,
        ];

        $I->haveInDatabase('crm.promocoesleads', $promocaoLead);
        return $promocaoLead;
    }

    /**
     * Insere um negócio operação no banco de dados
     */
    public function haveInDatabaseNegocioOperacao (FunctionalTester $I, $dados = []){
        $negocioOperacao = [
            'proposta_operacao' => $I->generateUuidV4(),
            'descricao' => (isset($dados['descricao']) ? $dados['descricao'] : 'descrição 1'),
            'codigo' => (isset($dados['codigo']) ? $dados['codigo'] : 'codigo 1'),
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.negociosoperacoes', $negocioOperacao);
        return $negocioOperacao;
    }

    /**
     * Insere um segmento de atuação no banco de dados
     */
    public function haveInDatabaseSegmentoAtuacao (FunctionalTester $I, $dados = [])
    {
        $negocioOperacao = [
            'segmentoatuacao' => $I->generateUuidV4(),
            'descricao' => (isset($dados['descricao']) ? $dados['descricao'] : 'descrição 1'),
            'codigo' => (isset($dados['codigo']) ? $dados['codigo'] : 'codigo 1'),
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.segmentosatuacao', $negocioOperacao);
        return $negocioOperacao;
    }

    public function haveInDatabaseSituacoesprenegocios (FunctionalTester $I, $codigo = null, $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        $situacaoPrenegocio = [
            'situacaoprenegocio' => $I->generateUuidV4(),
            'nome' => 'nome 1',
            'codigo' => $codigo == null ? 'codigo 1' : $codigo,
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial'=> $id_grupoempresarial['id_grupoempresarial'],
            'cor' => 1
        ];

        $I->haveInDatabase('crm.situacoesprenegocios', $situacaoPrenegocio);
        return $situacaoPrenegocio;
    }

    /**
     * Insere uma configuração de lista da vez no banco de dados
     */
    public function haveInDatabaseCrmListaDaVezConfiguracao(FunctionalTester $I, $dados = []){
        $listaDaVezConfiguracao = [
            'listadavezconfiguracao' => $I->generateUuidV4(),            
            'id_listadavezregra' => $dados['listadavezregra']['listadavezregra'],
            'id_listadavezregravalor' => (isset($dados['idlistadavezregravalor']) ? $dados['idlistadavezregravalor']['listadavezregravalor'] : null),
            'id_estado' => (isset($dados['idestado']) ? $dados['idestado']['uf'] : null),
            'id_negociooperacao' => (isset($dados['id_negociooperacao']) ? $dados['id_negociooperacao']['proposta_operacao'] : null),
            'id_segmentoatuacao' => (isset($dados['id_segmentoatuacao']) ? $dados['id_segmentoatuacao']['segmentoatuacao'] : null),
            'id_pai' => (isset($dados['idpai']) ? $dados['idpai'] : null),
            'tiporegistro' => (isset($dados['tiporegistro']) ? $dados['tiporegistro'] : 0),
            'ordem' => (isset($dados['ordem']) ? $dados['ordem'] : 0),
            'vendedorfixo' => (isset($dados['vendedorfixo']) ? $dados['vendedorfixo'] : false),
            'listadavezvendedor' => (isset($dados['listadavezvendedor']) ? $dados['listadavezvendedor']['listadavezvendedor'] : null),
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.listadavezconfiguracoes', $listaDaVezConfiguracao);

        // Ajusto diferenças entra dados de banco e dados da entidade
        $listaDaVezConfiguracao['listadavezregra'] = [
            'listadavezregra' => $listaDaVezConfiguracao['id_listadavezregra']
        ];
        $listaDaVezConfiguracao['idlistadavezregravalor'] = [
            'listadavezregravalor' => $listaDaVezConfiguracao['id_listadavezregravalor']
        ];
        $listaDaVezConfiguracao['idestado'] = [
            'uf' => $listaDaVezConfiguracao['id_estado']
        ];
        $listaDaVezConfiguracao['idnegociooperacao'] = [
            'proposta_operacao' => $listaDaVezConfiguracao['id_negociooperacao']
        ];
        $listaDaVezConfiguracao['idsegmentoatuacao'] = [
            'segmentoatuacao' => $listaDaVezConfiguracao['id_segmentoatuacao']
        ];
        $listaDaVezConfiguracao['idpai'] = $listaDaVezConfiguracao['id_pai'];
        $listaDaVezConfiguracao['listadavezvendedor'] = [
            'listadavezvendedor' => $listaDaVezConfiguracao['listadavezvendedor']
        ];
        $listaDaVezConfiguracao['idpainovo'] = null;
        $listaDaVezConfiguracao['listadavezconfiguracaonovo'] = null;
        unset($listaDaVezConfiguracao['id_listadavezregra']);
        unset($listaDaVezConfiguracao['id_listadavezregravalor']);
        unset($listaDaVezConfiguracao['id_estado']);
        unset($listaDaVezConfiguracao['id_negociooperacao']);
        unset($listaDaVezConfiguracao['id_segmentoatuacao']);
        unset($listaDaVezConfiguracao['id_pai']);

        return $listaDaVezConfiguracao;
    }

    public function haveInDatabaseNegocio(FunctionalTester $I, $dados = [])
    {
        $operacao = isset($dados['operacao']) ? $dados['operacao'] : $I->haveInDatabaseNegocioOperacao($I);
        $midia = isset($dados['midia']) ? $dados['midia'] : $I->haveInDatabaseMidia($I);
        $situacaoPrenegocio = isset($dados['situacaoprenegocio']) ? $dados['situacaoprenegocio'] : $this->haveInDatabaseSituacoesprenegocios($I);
        $id_grupoempresarial = isset($dados['id_grupoempresarial']) ? $dados['id_grupoempresarial'] : [
            'id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $estabelecimento = isset($dados['estabelecimento']) ? $dados['estabelecimento'] : 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';

        $negocio = [
            'documento' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $id_grupoempresarial['id_grupoempresarial'],
            'id_operacao' => isset($dados['operacao']) ? $dados['operacao']['proposta_operacao'] : $operacao['proposta_operacao'],
            //'numero' => "1", Agora é automático
            'id_estabelecimento' => $estabelecimento,
            'id_cliente' => isset($dados['id_cliente']) ? $dados['id_cliente']['cliente'] : null,//['cliente' => $cliente['cliente']],
            'id_codigodepromocao' => isset($dados['id_codigodepromocao']) ? $dados['id_codigodepromocao']['promocaolead'] : null,
            'id_midiadeorigem' => $midia['midiaorigem'],
            'id_tipoacionamento' => isset($dados['tipoacionamento']) ? $dados['tipoacionamento']['tiposacionamento'] : null,
            'cliente_codigo' => "1",
            'cliente_companhia' => "Companhia",
            'cliente_nomefantasia' => "Nome Fantasia Cliente",
            'cliente_qualificacao' => "1",
            'cliente_documento' => "Documento Cliente",
            'cliente_email' => "email cliente",
            'cliente_site' => "site cliente",
            'cliente_captador' => isset($dados['cliente_captador']) ? $dados['cliente_captador']['vendedor_id'] : null,
            'cliente_segmentodeatuacao' => isset($dados['cliente_segmentodeatuacao']) ? $dados['cliente_segmentodeatuacao']['segmentoatuacao'] : null,//object
            'cliente_receitaanual' => isset($dados['cliente_receitaanual']) ? $dados['cliente_receitaanual'] : 5000000,
            'uf' => isset($dados['uf']) ? $dados['uf']['uf'] : "RJ",
            'prenegocio' => 1,
            'ehcliente' => isset($dados['ehcliente']) ? $dados['ehcliente'] : true,
            'observacao' => "obs",
            'cliente_municipioibge' => isset($dados['clientemunicipioibge']) ? $dados['clientemunicipioibge']['codigo'] : "00000000",
            'created_by' => '{}',
            'created_at' => isset($dados['created_at']) ? $dados['created_at'] : '2020-08-31 15:05:49.881',
            'situacaoprenegocio' => $situacaoPrenegocio['situacaoprenegocio'],
            'prenegocio' => true,
            'observacao' => 'obs',
            'tipoqualificacao_pn' => isset($dados['tipoqualificacao_pn']) ? $dados['tipoqualificacao_pn'] : 0,
            'created_at_qualificacao_pn' => isset($dados['created_at_qualificacao_pn']) ? $dados['created_at_qualificacao_pn'] : null,
            'id_motivodesqualificacao_pn' => isset($dados['id_motivodesqualificacao_pn']) ? $dados['id_motivodesqualificacao_pn'] : null,
          ];

        $I->haveInDatabase('crm.negocios', $negocio);


        $negocio['operacao']['proposta_operacao'] = $negocio['id_operacao'];
        unset($negocio['id_operacao']);

        $negocio['estabelecimento']['estabelecimento'] = $negocio['id_estabelecimento'];
        unset($negocio['id_estabelecimento']);

        $negocio['clientereceitaanual'] = $negocio['cliente_receitaanual'];
        unset($negocio['cliente_receitaanual']);

        $negocio['clientecaptador'] = $negocio['cliente_captador'];
        unset($negocio['cliente_captador']);

        $negocio['clientesegmentodeatuacao'] = $negocio['cliente_segmentodeatuacao'];
        unset($negocio['cliente_segmentodeatuacao']);

        $negocio['uf'] = [
            'uf' => $negocio['uf']
        ];
        $negocio['clientemunicipioibge'] = [
            'codigo' => $negocio['cliente_municipioibge']
        ];
        unset($negocio['cliente_municipioibge']);
        

        if ($negocio['id_cliente'] != null) {
            $negocio['cliente']['cliente'] = $negocio['id_cliente'];
        } else {
            $negocio['cliente'] = null;
        }
        
        unset($negocio['id_cliente']);
        
        $negocio['codigodepromocao']['codigodepromocao'] = $negocio['id_codigodepromocao'];
        unset($negocio['id_codigodepromocao']);

        $negocio['midiaorigem']['midiaorigem'] = $negocio['id_midiadeorigem'];
        unset($negocio['id_midiadeorigem']);

        $negocio['situacaoprenegocio'] = ['situacaoprenegocio' => $negocio['situacaoprenegocio']];

        return $negocio;
    }

    public function haveInDatabaseNegocioComSegmentoDeAtuacao(FunctionalTester $I, $segmento)
    {
        $operacao = $I->haveInDatabaseNegocioOperacao($I);
        $midia = $I->haveInDatabaseMidia($I);
        $situacaoPrenegocio = $this->haveInDatabaseSituacoesprenegocios($I);
        $id_grupoempresarial = isset($dados['id_grupoempresarial']) ? $dados['id_grupoempresarial'] : [
            'id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'
        ];
        $estabelecimento = isset($dados['estabelecimento']) ? $dados['estabelecimento'] : 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';

        $negocio = [
            'documento' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $id_grupoempresarial['id_grupoempresarial'],
            'id_operacao' => $operacao['proposta_operacao'],
            //'numero' => "1", Agora é automático
            'id_estabelecimento' => $estabelecimento,
            'id_cliente' => null,//['cliente' => $cliente['cliente']],
            'id_codigodepromocao' => null, //object
            'id_midiadeorigem' => $midia['midiaorigem'],
            'cliente_codigo' => "1",
            'cliente_companhia' => "Companhia",
            'cliente_nomefantasia' => "Nome Fantasia Cliente",
            'cliente_qualificacao' => "1",
            'cliente_documento' => "Documento Cliente",
            'cliente_email' => "email cliente",
            'cliente_site' => "site cliente",
            'cliente_captador' => null, //object
            'cliente_segmentodeatuacao' => $segmento['segmentoatuacao'],
            'cliente_receitaanual' => 5000000,
            'uf' => isset($dados['uf']) ? $dados['uf']['uf'] : "RJ",
            'id_segmentodeatuacao' => $segmento['segmentoatuacao'],
            'prenegocio' => 1,
            'ehcliente' => 1,
            'observacao' => "obs",
            'cliente_municipioibge' => isset($dados['clientemunicipioibge']) ? $dados['clientemunicipioibge']['codigo'] : "00000000",
            'created_by' => '{}',
            'created_at' => '2020-08-31 15:05:49.881',
            'situacaoprenegocio' => $situacaoPrenegocio['situacaoprenegocio'],
            'prenegocio' => true,
            'ehcliente' => true,
            'observacao' => 'obs',
            'tipoqualificacao_pn' => 0,
          ];

        $I->haveInDatabase('crm.negocios', $negocio);

        $negocio['operacao']['proposta_operacao'] = $negocio['id_operacao'];
        unset($negocio['id_operacao']);

        $negocio['estabelecimento']['estabelecimento'] = $negocio['id_estabelecimento'];
        unset($negocio['id_estabelecimento']);

        $negocio['clientereceitaanual'] = $negocio['cliente_receitaanual'];
        unset($negocio['cliente_receitaanual']);

        $negocio['clientecaptador'] = $negocio['cliente_captador'];
        unset($negocio['cliente_captador']);

        $negocio['clientesegmentodeatuacao'] = $negocio['cliente_segmentodeatuacao'];
        unset($negocio['cliente_segmentodeatuacao']);

        $negocio['uf'] = [
            'uf' => $negocio['uf']
        ];
        $negocio['clientemunicipioibge'] = [
            'codigo' => $negocio['cliente_municipioibge']
        ];
        unset($negocio['cliente_municipioibge']);
        

        if ($negocio['id_cliente'] != null) {
            $negocio['cliente']['cliente'] = $negocio['id_cliente'];
        } else {
            $negocio['cliente'] = null;
        }
        
        unset($negocio['id_cliente']);
        
        $negocio['codigodepromocao']['codigodepromocao'] = $negocio['id_codigodepromocao'];
        unset($negocio['id_codigodepromocao']);

        $negocio['midiaorigem']['midiaorigem'] = $negocio['id_midiadeorigem'];
        unset($negocio['id_midiadeorigem']);

        $negocio['situacaoprenegocio'] = ['situacaoprenegocio' => $negocio['situacaoprenegocio']];

        return $negocio;
    }

    public function haveInDatabaseNegocioContato (FunctionalTester $I, $dados = [], $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'])
    {
        
        $negocio = isset($dados['negocio']) ? $dados['negocio'] : $I->haveInDatabaseNegocio($I);

        $negocioContato = [
            'id' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $id_grupoempresarial['id_grupoempresarial'],
            'id_negocio' => isset($dados['negocio']) ? $dados['negocio']['documento'] : $negocio['documento'],
            "nome" => 'Nome do contato',
            "sobrenome" => 'Sobrenome do contato',
            "cargo" => isset($dados['cargo']) ? $dados['cargo'] : "Sócio/Proprietário/CEO",
            "email" => 'email@do.contato',
            "ddi" => '55',
            "ddd" => '21',
            "telefone" => '987654321',
            "ramal" => '',
        ];
        $I->haveInDatabase('crm.negocioscontatos', $negocioContato);


        $negocioContato['negocio']['documento'] = $negocioContato['id_negocio'];
        unset($negocioContato['id_negocio']);
        
        return $negocioContato;
    }

    
    public function haveInDatabaseMotivoDesqualificacao($I, $dados = [])
    {
        $motivo = [
            'motivodesqualificacaoprenegocio' => $I->generateUuidV4(),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial,
            "codigo" => '1',
            "descricao" => 'Negado',
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => null,
            'created_at' => date('Y-m-d'),
            'updated_at' => null,
        ];
        $I->haveInDatabase('crm.motivosdesqualificacoesprenegocios', $motivo);
        return $motivo;
    }

    public function haveInDatabaseFollowupNegocio($I, $negocio_id, $historico = 'Histórico 123'){

        $followUpNegocio = [
            'followup' => $I->generateUuidV4(),
            'data' => date('Y-m-d'),
            'tenant' => $this->tenant_numero,
            'proposta' => $negocio_id,
            "historico" => $historico,
            "participante" => null,
            "receptor" => '1',
            "meiocomunicacao" => '1',
            "figuracontato" => '2',
        ];

        $I->haveInDatabase('ns.followups', $followUpNegocio);
        return $followUpNegocio;

    }

    public function haveInDatabaseListadaVezRegra (FunctionalTester $I, $dados = [])
    {
        $listadavezregra = [
            'listadavezregra' => $I->generateUuidV4(),
            'nome' => (isset($dados['nome']) ? $dados['nome'] : 'Regra 1'),
            "tipoentidade" => (isset($dados['tipoentidade']) ? $dados['tipoentidade'] : '0'),
            "totalvalores" => (isset($dados['totalvalores']) ? $dados['totalvalores'] : '0'),
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.listadavezregras', $listadavezregra);
        return $listadavezregra;
    }

    public function haveInDatabaseListadaVezRegraValor (FunctionalTester $I, $dados = []){
        $listadavezregravalor = [
            'listadavezregravalor' => $I->generateUuidV4(),
            'id_listadavezregra' => $dados['listadavezregra']['listadavezregra'],
            'nome' => (isset($dados['nome']) ? $dados['nome'] : 'Sim'),
            'valor' => (isset($dados['valor']) ? $dados['valor'] : '1'),
            'created_at' => date('Y-m-d'),
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'updated_at' => date('Y-m-d'),
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.listadavezregrasvalores', $listadavezregravalor);
        return $listadavezregravalor;
    }

    public function haveInDatabaseListadavezvendedor($I, $dados = []){

        $listadavezvendedor = [
            'listadavezvendedor' => $I->generateUuidV4(),
            'nome' => isset($dados['nome']) ? $dados['nome'] : 'nome vendedor',
            'totalmembros' => isset($dados['totalmembros']) ? $dados['totalmembros'] : 0,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => null,
            'created_at' => date('Y-m-d'),
            'updated_at' => null,
            'tenant' => $I->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.listadavezvendedores', $listadavezvendedor);
        return $listadavezvendedor;
    }


    public function haveInDatabaseListadavezvendedoritem($I, $dados, $posicao = '1', $id_grupoempresarial = ['id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf']){

        $listadavezvendedoritem = [
            'listadavezvendedoritem' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $I->id_grupoempresarial,
            'id_listadavezvendedor' => $dados['listadavezvendedor'],
            'id_vendedor' => isset($dados['idvendedor']) ? $dados['idvendedor'] : 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758',
            'posicao' => isset($dados['posicao']) ? $dados['posicao'] : $posicao,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => null,
            'created_at' => date('Y-m-d'),
            'updated_at' => null
        ];

        $I->haveInDatabase('crm.listadavezvendedoresitens', $listadavezvendedoritem);
        return $listadavezvendedoritem;
    }

    /**
     * Função para permitir criar 2 fornecedores diferentes para os testes nas cidades informações funerárias, através dos parâmetros recebidos
     * @param FunctionalTester $I
     * @return array com dados do fornecedor
     */
    public function haveInDatabaseFornecedorCidadeInformacaoFuneraria($I, $pessoa = "101", $nome = "F101", $nomefantasia = "Fornecedor 101", $cnpj = "41.960.275/0001-54", $inscricaomunicipal = "101"){

        $id = $I->generateUuidV4();
        $fornecedor = [
            "id" => $id,
            "pessoa" => $pessoa != "101" ? $pessoa : "101",
            "nome" => $nome != "F101" ? $nome : "F101",
            "nomefantasia" => $nomefantasia != "Fornecedor 101" ? $nomefantasia : "Fornecedor 101",
            "cnpj" => $cnpj != "41.960.275/0001-54" ? $cnpj : "41.960.275/0001-54",
            "inscricaomunicipal" => $inscricaomunicipal != "101" ? $inscricaomunicipal : "101",
            "tenant" => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}',
            'updated_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'esperapagamentoseguradora' =>  null,
            'estabelecimentoid' => null,
            'clienteativado' => 1
        ];
        $conjunto_fornecedor = [
            'conjuntofornecedor' => $I->generateUuidV4(),
            'registro' => $id,
            'conjunto' => '904fc4f3-01e6-4260-ab33-315a8d0f8efb',
            'tenant' => '47'
        ];
        $I->haveInDatabase("ns.pessoas", $fornecedor);
        $I->haveInDatabase("ns.conjuntosfornecedores", $conjunto_fornecedor);

        /* Formato aceito pelo sistema */
        $fornecedor['fornecedor'] = $fornecedor['id'];
        unset($fornecedor['updated_at']);
        unset($fornecedor['updated_by']);
        unset($fornecedor['created_at']);
        unset($fornecedor['created_by']);

        return $fornecedor;
    }

    public function haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $id_grupoempresarial, $perfilfunerario = 1){

        $cidade = [
            'cidadeinformacaofuneraria' => $I->generateUuidV4(),
            'pais' => $pais['pais'],
            'estado' => $estado['uf'],
            'municipio' => $municipio['ibge'],
            'possuisvo' => true,
            'possuicrematorio' => true,
            'possuicemiteriomunicipal' => true,
            'possuicapelamunicipal' => true,
            'trabalhacomfloresnaturais' => true,
            'possuiiml' => true,
            'perfilfunerario' => $perfilfunerario != 1 ? $perfilfunerario : 1,
            'tenant' => $this->tenant_numero,
            'totalrankeados' => 0,
            'id_grupoempresarial' => $id_grupoempresarial
          ];

          $I->haveInDatabase("ns.cidadesinformacoesfunerarias", $cidade);

          $municipio['codigo'] = $municipio['ibge']; //Objeto enviado tem o campo com nome de código
          unset($municipio['federal'], $municipio['ibge']); //Não serão usados

          //Formato que será enviado na requisição
          $cidade['pais'] = $pais;
          $cidade['estado'] = $estado;
          $cidade['municipio'] = $municipio;

          return $cidade;

    }

    public function haveInDatabaseCidadesInfoFunerariasFornecedores($I, $cidade, $fornecedor, $id_grupoempresarial, $ordem = 1){
        
        $cidade_fornecedor = [
            'cidadeinfofunerariafornecedor' => $I->generateUuidV4(),
            'id_fornecedor' => $fornecedor['fornecedor'],
            'id_grupoempresarial' => $id_grupoempresarial,
            'id_cidadefuneraria' => $cidade['cidadeinformacaofuneraria'],
            'tenant' => $this->tenant_numero,
            'ordem' => $ordem != 1 ? $ordem : 1,
        ];

        $I->haveInDatabase("ns.cidadesinfofunerariasfornecedores", $cidade_fornecedor);        
        return $cidade_fornecedor;

    }

    public function haveInDatabaseAtcsConfiguracoesDocumentos($I, $id_grupoempresarial, $dados = []){
        $atcConfiguracaoDocumento = [
            'atcconfiguracaodocumento' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $id_grupoempresarial,
            'tipo_geracao_prestadora' => (isset($dados['tipo_geracao_prestadora']) ? $dados['tipo_geracao_prestadora'] : 1),
            'tipo_geracao_seguradora' => (isset($dados['tipo_geracao_seguradora']) ? $dados['tipo_geracao_seguradora'] : 1),
            'emailpadrao' => (isset($dados['emailpadrao']) ? $dados['emailpadrao'] : 'Texto do envio padrão configuração documento'),
        ];
        
        $I->haveInDatabase('crm.atcsconfiguracoesdocumentos', $atcConfiguracaoDocumento);
        
        /* Formato aceito na requisição */
        $atcConfiguracaoDocumento['tipogeracaoprestadora'] = $atcConfiguracaoDocumento['tipo_geracao_prestadora'];
        $atcConfiguracaoDocumento['tipogeracaoseguradora'] = $atcConfiguracaoDocumento['tipo_geracao_seguradora'];

        /* Não serão usados */
        unset($atcConfiguracaoDocumento['tipo_geracao_prestadora'], $atcConfiguracaoDocumento['tipo_geracao_seguradora']);

        return $atcConfiguracaoDocumento;

    }

    public function haveInDatabaseDocumentosFopEntidades($I, $dados = []){
        $documentofopentidade = [
            'documentoentidade' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'entidade' => (isset($dados['entidade']) ? $dados['entidade'] : 'Entidade 1'),
            'urlfontedadosxsd' => (isset($dados['urlfontedadosxsd']) ? $dados['urlfontedadosxsd'] : 'tetandourlfontedadosxsd.com'),
            'fontedadosxml' => (isset($dados['fontedadosxml']) ? $dados['fontedadosxml'] : 'fonte dados xml 1'),
            'created_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d')
        ];

        $I->haveInDatabase('ns.documentosfopentidades', $documentofopentidade);

        return $documentofopentidade;

    }

    public function haveInDatabaseDocumentosFop($I, $documentoentidade, $dados = []){
        $documentofop = [
            'documentofop' => $I->generateUuidV4(),
            'documentoentidade' => $documentoentidade,
            'tenant' => $this->tenant_numero,
            'sistema' => (isset($dados['sistema']) ? $dados['sistema'] : 'Sistema 123'),
            'codigodocumento' => (isset($dados['codigodocumento']) ? $dados['codigodocumento'] : 'CodDoc123'),
            'nomedocumento' => (isset($dados['nomedocumento']) ? $dados['nomedocumento'] : 'NomeDocumento123'),
            'created_by' => '{"nome":"usuario"}',
            'created_at' => date('Y-m-d')
        ];

        $I->haveInDatabase('ns.documentosfop', $documentofop);

        return $documentofop;

    }

    public function haveInDatabaseAtcsConfiguracoesDocumentosItens($I, $id_grupoempresarial, $atcconfiguracaodocumento, $documentofop, $dados = []){
        $atcConfiguracaoDocumentoItem = [
            'atcconfiguracaodocumentoitem' => $I->generateUuidV4(),
            'atcconfiguracaodocumento' => $atcconfiguracaodocumento,
            'documentofop' => $documentofop,
            'tenant' => $this->tenant_numero,
            'id_grupoempresarial' => $id_grupoempresarial,
            'tipo' => (isset($dados['tipo']) ? $dados['tipo'] : 1),
        ];

        $I->haveInDatabase('crm.atcsconfiguracoesdocumentositens', $atcConfiguracaoDocumentoItem);

        return $atcConfiguracaoDocumentoItem;

    }

    public function haveInDatabasePrioridades($I, $dados = []){

        $prioridade = [
            'prioridade' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'nome' => (isset($dados['nome']) ? $dados['nome'] : 'Prioridade 1'),
            'cor' => (isset($dados['cor']) ? $dados['cor'] : '#ffffff'),
            'ordem' => (isset($dados['ordem']) ? $dados['ordem'] : 0),
            'id_grupoempresarial' => $this->id_grupoempresarial,
            'prazoexpiracao' => (isset($dados['prazoexpiracao']) ? $dados['prazoexpiracao'] : 60),
            'notificarexpiracaofaltando' => (isset($dados['notificarexpiracaofaltando']) ? $dados['notificarexpiracaofaltando'] : 30),
            'prioridadepadrao' => (isset($dados['prioridadepadrao']) ? $dados['prioridadepadrao'] : false),
            'descricao' => (isset($dados['descricao']) ? $dados['descricao'] : 'Descrição da prioridade'),
        ];

        $I->haveInDatabase('ns.prioridades', $prioridade);

        return $prioridade;
    
    }


    public function haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, $dados = []){

        $cliente = $I->haveInDatabaseCliente($I, ['conta' => $dados['conta'] ?? null]);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $configTaxaAdmin = [
            'configuracaotaxaadm' => $I->generateUuidV4(),
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->id_grupoempresarial,
            'estabelecimento' => $estabelecimento['estabelecimento'],
            'seguradora' => $cliente['cliente'],
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'formapagamento' => $formapagamento['formapagamento'],
            'municipioprestacao' => $municipioprestacao['pessoamunicipio'],
            'valor' => (isset($dados['valor']) ? $dados['valor'] : 300),
        ];

        $I->haveInDatabase('crm.configuracoestaxasadministrativas', $configTaxaAdmin);
        return $configTaxaAdmin;
    
    }

    public function haveInDatabaseAtcTipoDocumentoRequisitante(FunctionalTester $I, $negocioId, $tipodocumentoId, $dados = []){

        $tipoDocReq = [
            'negociotipodocumentorequisitante' => $I->generateUuidV4(),
            'negocio' => $negocioId,
            'tipodocumento' => $tipodocumentoId,
            'requisitantecliente' => isset($dados['requisitantecliente']) ? $dados['requisitantecliente'] : null,
            'requisitantefornecedor' => isset($dados['requisitantefornecedor']) ? $dados['requisitantefornecedor'] : null,
            'requisitantenegocio' => isset($dados['requisitantenegocio']) ? $dados['requisitantenegocio'] : false,
            'copiasimples' => isset($dados['copiasimples']) ? $dados['copiasimples'] : false,
            'copiaautenticada' => isset($dados['copiaautenticada']) ? $dados['copiaautenticada'] : false,
            'original' => isset($dados['original']) ? $dados['original'] : false,
            'permiteenvioemail' => isset($dados['permiteenvioemail']) ? $dados['permiteenvioemail'] : false,
            'pedirinformacoesadicionais' => isset($dados['pedirinformacoesadicionais']) ? $dados['pedirinformacoesadicionais'] : false,
            'status' => isset($dados['status']) ? $dados['status'] : 0,
            'tenant' => $this->tenant_numero,
            "created_at" => date('Y-m-d'),
            "created_by" => '{"nome":"usuario"}',
            "updated_at" => date('Y-m-d'),
            "updated_by" => '{"nome":"usuario"}',
            'requisitanteapolice' => isset($dados['requisitanteapolice']) ? $dados['requisitanteapolice'] : null,
            'id_grupoempresarial' => $this->id_grupoempresarial
        ];

        $I->haveInDatabase('crm.atcstiposdocumentosrequisitantes', $tipoDocReq);
        return $tipoDocReq;

    }


    public function haveInDatabaseConta(FunctionalTester $I, $estabelecimento, $codigo = null)
    {
        $codigo = ($codigo) ? $codigo : '0001';

        $conta = [
            'conta' => $I->generateUuidV4(),
            'codigo' => $codigo,
            'nome' => 'conta finan 0001',
            'bloqueado' => false,
            'estabelecimento' => $estabelecimento,
            'tenant' => $I->tenant_numero
        ];

        $I->haveInDatabase('financas.contas', $conta);
        
        return $conta;
     
    }

}
