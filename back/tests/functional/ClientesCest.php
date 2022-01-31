<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * @todo Testar correção de contatos em clientes quando corrigido
 * criar simples( sem objectlist) - FEITO
 * criar apenas com dados obrigatorios - FEITO
 * testar exception quando não for enviado um dado obrigatorio - FEITO
 * editar - FEITO
 */
class ClientesCest
{

    private $url = '/api/gednasajon/clientes/';
    private $tenant = 'gednasajon';
    private $tenant_numero = '47';
    private $grupoempresarial = 'FMA';
    private $id_grupoempresarial = '95cd450c-30c5-4172-af2b-cdece39073bf';
    private $id_grupoempresarial_sem_permissao = 'b4c12f6c-e637-48e3-a858-cf5a04e12603';
    private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';


    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::CLIENTES_CREATE, EnumAcao::CLIENTES_INDEX, EnumAcao::CLIENTES_PUT]);
    }


    /**
     * 
     * @param FunctionalTester $I
     */
    public function criaCliente(FunctionalTester $I)
    {
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        /* prepara cenário */
        $cliente = [
            "codigo" => "201",
            "razaosocial" => "C201",
            "nomefantasia" => "Cliente 201",
            "cadastro" => "1",
            "cnpj" => "50941436000153",
            "inscricaomunicipal" => "130",
            "tenant" => $this->tenant
        ];
        /* execução da funcionalidade */
        $cliente_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant . '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($cliente['codigo'], $cliente_criado['codigo']);
        $I->assertEquals($cliente['razaosocial'], $cliente_criado['razaosocial']);
        $I->assertEquals($cliente['nomefantasia'], $cliente_criado['nomefantasia']);
        $I->assertEquals($cliente['cnpj'], $cliente_criado['cnpj']);
        $I->assertEquals($this->tenant_numero, $cliente_criado['tenant']);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente_criado['cliente']]);
        /* remove cliente criado */
        $I->deleteFromDatabase('ns.conjuntosclientes', ['registro' => $cliente_criado['cliente']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $cliente_criado['cliente']]);
    }

    /**
     * 
     * @param FunctionalTester $I
     */
    public function criaClienteSomenteComDadosObrigatorios(FunctionalTester $I)
    {
        /* prepara cenário */
        $cliente = [
            "codigo" => "101",
            "razaosocial" => "C101",
            "nomefantasia" => "Cliente 101",
            "cadastro" => "1",
            "cnpj" => "67548070000150",
            "inscricaomunicipal" => "130",
            "tenant" => $this->tenant,
        ];

        /* execução da funcionalidade */
        $cliente_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($cliente['codigo'], $cliente_criado['codigo']);
        $I->assertEquals($cliente['razaosocial'], $cliente_criado['razaosocial']);
        $I->assertEquals($cliente['nomefantasia'], $cliente_criado['nomefantasia']);
        $I->assertEquals($cliente['cnpj'], $cliente_criado['cnpj']);
        $I->assertEquals($this->tenant_numero, $cliente_criado['tenant']);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente_criado['cliente']]);
        /* remove cliente criado */
        $I->deleteFromDatabase('ns.conjuntosclientes', ['registro' => $cliente_criado['cliente']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $cliente_criado['cliente']]);
    }

    public function naoCriaClienteSemRazaoSocial(FunctionalTester $I)
    {

        /* prepara cenário */
        $cliente_nao_criado = [
            "nomefantasia" => "Cliente 07",
            "codigo" => "007",
            "cadastro" => "1",
            "cnpj" => "54718785000161",
            "inscricaomunicipal" => "130",
            "tenant" => $this->tenant,
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente_nao_criado, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function editaCliente(FunctionalTester $I)
    {

        /* prepara cenário */
        $cliente = $I->haveInDatabaseCliente($I);
        $cliente["codigo"] = "101";
        $cliente["nomefantasia"] = "Cliente 101";
        $cliente["cnpj"] = "67548070000150";
        $cliente["inscricaomunicipal"] = "130";
        $cliente["codigo"] = $this->tenant;

        /* executa funcionalidade */
        $I->sendRaw('PUT', $this->url . $cliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $cliente['cliente'], "nomefantasia" => $cliente['nomefantasia']]);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente['cliente']]);
    }
    /**
     * 
     * @param FunctionalTester $I
     * Cria um cliente com documentos e verifica se estão no banco
     */
    public function exibeDocumentosCliente(FunctionalTester $I)
    {   
        $documento = $I->haveInDatabaseDocumento($I);
        $clientesdocumentos = 
        [ 
            [
            "copiasimples" => true,
            "copiaautenticada" => true,
            "original" => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            "naoexibiremrelatorios" => true,
            "tipodocumento" => ["tipodocumento" => $documento['tipodocumento']],
            'id_grupoempresarial' => '95cd450c-30c5-4172-af2b-cdece39073bf'
            ]
        ];

        $cliente = [
            "codigo" => "201",
            "razaosocial" => "C201",
            "nomefantasia" => "Cliente 201",
            "cadastro" => "1",
            "cnpj" => "50941436000153",
            "inscricaomunicipal" => "130",
            "tenant" => $this->tenant,
            "rua" => "Rua 201",
            "numero" => "201",
            "complemento" => "Proximo ao Bairro302",
            "cep" => "69084-461",
            "bairro" => "Bairro 201",
            "referencia" => "Apos a rua 0",
            "clientesdocumentos" => $clientesdocumentos
        ];
        /* execução da funcionalidade */
        $cliente_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        unset($clientesdocumentos[0]["tipodocumento"]);
        $I->canSeeInDatabase('ns.clientesdocumentos', $clientesdocumentos[0]);       
        /* remove cliente criado */
        $I->deleteFromDatabase('ns.conjuntosclientes', ['registro' => $cliente_criado['cliente']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $cliente_criado['cliente']]);
    }
    /**
     * 
     * @param FunctionalTester $I
     * Edita um cliente adicionando a ele um documento
     */
    public function editaClienteAdicionandoDocumento(FunctionalTester $I)
    {   
        /* prepara cenário */
        $cliente = $I->haveInDatabaseCliente($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $documentocliente = 
        [ 
            [
            "copiasimples" => true,
            "copiaautenticada" => true,
            "original" => true,
            "permiteenvioemail" => true,
            "pedirinformacoesadicionais" => true,
            "naoexibiremrelatorios" => true,
            "tipodocumento" => ["tipodocumento" => $documento['tipodocumento']]
            ]
        ];

        /* executa funcionalidade */
        $cliente["clientesdocumentos"] = $documentocliente;
        $I->sendRaw('PUT', $this->url . $cliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $cliente['cliente'], "nomefantasia" => $cliente['nomefantasia']]);
        $I->canSeeInDatabase('ns.clientesdocumentos', [
            'cliente' => $cliente['cliente'],
            "tipodocumento" => $documento['tipodocumento'],
            "naoexibiremrelatorios" => true,
        ]);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente['cliente']]);
    }

    /**
     * 
     * @param FunctionalTester $I
     * Edita um cliente removendo o seu documento
     */
    public function editaClienteRemovendoDocumento(FunctionalTester $I)
    {   
        //Criando o cliente, criando o tipo de documento e criando a relação entre eles
        $cliente = $I->haveInDatabaseCliente($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $objCliente = $I->haveInDatabaseClienteComDocumento($I, $cliente, $documento);

        //Removendo o documento do objeto cliente e fazendo o put
        $objCliente['clientesdocumentos'] = null;

        //Execução da funcionalidade
        $I->sendRaw('PUT', $this->url . $objCliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $objCliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $objCliente['cliente'], "nomefantasia" => $objCliente['nomefantasia']]);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $objCliente['cliente']]);
        $I->cantSeeInDatabase('ns.clientesdocumentos', ['tipodocumento' => $documento['tipodocumento'], 'cliente' => $objCliente['cliente']]);

    }

    /**
     * 
     * @param FunctionalTester $I
     * Retorna os dados do documento do cliente
     */
    public function retornaDocumentoDoCliente(FunctionalTester $I)
    {   
        //Criando o cliente, criando o tipo de documento e criando a relação entre eles
        $cliente = $I->haveInDatabaseCliente($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $objCliente = $I->haveInDatabaseClienteComDocumento($I, $cliente, $documento);

        //Obtendo o id do documento do cliente
        $idDocumento = $objCliente['clientesdocumentos']['clientedocumento'];

        //Execução da funcionalidade
        $docRetornado = $I->sendRaw('GET', '/api/gednasajon/' . $objCliente['cliente'] . '/clientesdocumentos/' . $idDocumento . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

    }

    /**
     * 
     * @param FunctionalTester $I
     * Retorna todos os documentos do cliente
     */
    public function listaDocumentosDoCliente(FunctionalTester $I)
    {   
        //Criando o cliente, criando os tipos de documento e criando a relação entre eles
        $cliente = $I->haveInDatabaseCliente($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $documento2 = $I->haveInDatabaseDocumento($I);
        $I->haveInDatabaseClienteComDocumento($I, $cliente, $documento);
        $I->haveInDatabaseClienteComDocumento($I, $cliente, $documento2);

        // Retornando a quantidade de documentos para o cliente
        $countAtual = $I->grabNumRecords('ns.clientesdocumentos', ['cliente' => $cliente['cliente']]);

        //Execução da funcionalidade
        $listaDocs = $I->sendRaw('GET', '/api/gednasajon/' . $cliente['cliente'] . '/clientesdocumentos/' . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual, $listaDocs);

    }

    /**
     * Teste para verificar se Clientes com relacionamentos um para muitos são listados sem duplicidade
     * @param FunctionalTester $I
     */
    public function listaClientesSemDuplicidade(FunctionalTester $I)
    {
        $countAtual = $I->grabNumRecords('ns.vw_clientes', ['tenant' => $this->tenant_numero, 'id_grupoempresarial' => $this->id_grupoempresarial]);

        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $I->haveInDatabaseCliente($I, ['conta' => $conta]);

        // funcionalidade testada
        $url          = $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial;
        $clientesRest = $I->sendRaw('GET', $url, [], [], [], null);
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual + 1, $clientesRest);
    }
    
    /**
     * @param FunctionalTester $I
     */
    public function buscaCliente(FunctionalTester $I)
    {
        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $cliente = $I->haveInDatabaseCliente($I, ['conta' => $conta]);

        // funcionalidade testada
        $url          = $this->url . $cliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial;
        $clientesRest = $I->sendRaw('GET', $url, [], [], [], null);
        $I->assertNotNull($clientesRest['conta']);
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($cliente['cliente'], $clientesRest['cliente']);
    }

    public function editaClienteSemApagarTelefone(FunctionalTester $I)
    {

        /* prepara cenário */
        $cliente = $I->haveInDatabaseClienteComTelefones($I);
        $cliente["codigo"] = "101";
        $cliente["nomefantasia"] = "Cliente 101";
        $cliente["cnpj"] = "67548070000150";
        $cliente["inscricaomunicipal"] = "130";

        /* executa funcionalidade */
        $I->sendRaw('PUT', $this->url . $cliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $cliente['contatos'][0]['telefones'][0]['id'] = $cliente['contatos'][0]['telefones'][0]['telefone_id'];
        unset($cliente['contatos'][0]['telefones'][0]['telefone_id']);
        $I->canSeeInDatabase('ns.telefones', $cliente['contatos'][0]['telefones'][0]);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente['cliente']]);
    }

    public function getClienteComVendedorAssociado(FunctionalTester $I){

        /* Inicializações */
        $idVendedor = 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'; //guid do vendedor no dump.sql - ns.pessoas
        $cliente = $I->haveInDatabaseClienteComVendedorAssociado($I, $idVendedor);

        $clienteRetornado = $I->sendRaw('GET', $this->url . $cliente['cliente']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($idVendedor, $clienteRetornado['vendedor']['vendedor_id']);

    }

    public function criarClienteComConta(FunctionalTester $I)
    {
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        /* prepara cenário */
        $cliente = [
            "codigo" => "201",
            "razaosocial" => "C201",
            "nomefantasia" => "Cliente 201",
            "cadastro" => "1",
            "cnpj" => "50941436000153",
            "inscricaomunicipal" => "130",
            "tenant" => $this->tenant,
            'conta' => $conta
        ];
        /* execução da funcionalidade */
        $cliente_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant . '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($cliente['codigo'], $cliente_criado['codigo']);
        $I->assertEquals($cliente['razaosocial'], $cliente_criado['razaosocial']);
        $I->assertEquals($cliente['nomefantasia'], $cliente_criado['nomefantasia']);
        $I->assertEquals($cliente['cnpj'], $cliente_criado['cnpj']);
        $I->assertEquals($conta['conta'], $cliente_criado['conta']['conta']);
        $I->assertEquals($this->tenant_numero, $cliente_criado['tenant']);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente_criado['cliente']]);
        /* remove cliente criado */
        $I->deleteFromDatabase('ns.conjuntosclientes', ['registro' => $cliente_criado['cliente']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $cliente_criado['cliente']]);
    }


    public function editarClienteComConta(FunctionalTester $I)
    {

        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $conta2 = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0002');
        /* prepara cenário */
        $cliente = $I->haveInDatabaseCliente($I, ['conta' => $conta]);
        $cliente["codigo"] = "101";
        $cliente["nomefantasia"] = "Cliente 101";
        $cliente["cnpj"] = "67548070000150";
        $cliente["inscricaomunicipal"] = "130";
        $cliente["codigo"] = $this->tenant;
        $cliente['conta'] = $conta2;

        /* executa funcionalidade */
        $I->sendRaw('PUT', $this->url . $cliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $cliente['cliente'], "nomefantasia" => $cliente['nomefantasia']]);
        $I->cantSeeInDatabase('ns.vw_clientes', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'cliente' => $cliente['cliente']]);
    }
}
