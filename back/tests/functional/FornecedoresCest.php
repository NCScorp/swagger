<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/*
 * Testando se após a edição a prestadora mantém seu status - FEITO
 * criar simples( sem objectlist) - FEITO
 * criar apenas com dados obrigatorios - FEITO
 * testar exception quando não for enviado um dado obrigatorio - FEITO
 * testar cnpj unico - FEITO
 * testar codigo, razãosocial unicos - FEITO
 * editar - FEITO
 * criar com dados bancarios,contatos e tipos de atividades - FEITO
 * suspender - FEITO
 * advertir  - FEITO
 * reativar - FEITO
 */

class FornecedoresCest
{

    private $url = '/api/gednasajon/fornecedores/';
    private $tenant = 'gednasajon';
    private $tenant_numero = '47';
    private $grupoempresarial = 'FMA';
    private $id_grupoempresarial_sem_permissao = 'b4c12f6c-e637-48e3-a858-cf5a04e12603';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';

    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::FORNECEDORES_INDEX, EnumAcao::FORNECEDORES_CREATE, EnumAcao::FORNECEDORES_PUT,
                                                        EnumAcao::FORNECEDORES_SUSPENDER, EnumAcao::FORNECEDORES_ADVERTIR, EnumAcao::FORNECEDORES_REATIVAR, EnumAcao::FORNECEDORESSUSPENSOS_INDEX]);
    }

    /**
     * Criando fornecedor com os dados básicos (sem conta bancária, contatos e tipos de atividades, isto é, objectlist)
     * 
     * Criar array com o novo fornecedor
     * Enviar o array via post para a rota de criação
     * Recuperar o fornecedor criado
     * verificar se o status ativo está presente no fornecedor
     * @param FunctionalTester $I
     */
    public function criaFornecedor(FunctionalTester $I)
    {
        /* prepara cenário */
        $formapagamento = $I->haveInDatabaseFormapagamento($I);

        $fornecedor = [
            "codigofornecedores" => "101",
            "razaosocial" => "F101",
            "nomefantasia" => "Fornecedor 101",
            "cadastro" => "1",
            "cnpj" => "41960275000154",
            "incricaomunicipal" => "101",
            "formapagamento" => ['formapagamento' => $formapagamento['formapagamento']],
            "tenant" => $this->tenant,
        ];

        /* execução da funcionalidade */
        $fornecedor_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant . '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);
        /* validação do resultado */
        $I->assertEquals(0, $fornecedor_criado['status']);
        $I->cantSeeInDatabase('ns.vw_fornecedores', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'fornecedor' => $fornecedor_criado['fornecedor']]);
        /* remove fornecedor criado */
        $I->deleteFromDatabase('ns.conjuntosfornecedores', ['registro' => $fornecedor_criado['fornecedor']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $fornecedor_criado['fornecedor']]);
    }

    /**
     * Criando fornecedor preenchendo apenas os dados básicos obrigatórios (sem conta bancária, contatos e tipos de atividades, isto é, objectlist)
     * 
     * @param FunctionalTester $I
     */
    public function criaFornecedorObrigatorio(FunctionalTester $I)
    {
        /* prepara cenário */
        $fornecedor_novo = [
            "nomefantasia" => "Fornecedor 102",
            "razaosocial" => "F102",
            "codigofornecedores" => "102",
            "cadastro" => "1",
            "cnpj" => "18492167000182",
            "tenant" => $this->tenant,
        ];

        /* execução da funcionalidade */
        $fornecedor_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor_novo, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        
        $I->assertEquals($fornecedor_novo['nomefantasia'], $fornecedor_criado['nomefantasia']);
        $I->assertEquals($fornecedor_novo['razaosocial'], $fornecedor_criado['razaosocial']);
        $I->assertEquals($fornecedor_novo['codigofornecedores'], $fornecedor_criado['codigofornecedores']);
        $I->assertEquals($fornecedor_novo['cnpj'], $fornecedor_criado['cnpj']);
        $I->assertEquals($this->tenant_numero, $fornecedor_criado['tenant']);
        $I->cantSeeInDatabase('ns.vw_fornecedores', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'fornecedor' => $fornecedor_criado['fornecedor']]);
        /* remove fornecedor criado */
        $I->deleteFromDatabase('ns.conjuntosfornecedores', ['registro' => $fornecedor_criado['fornecedor']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $fornecedor_criado['fornecedor']]);
    }
    /**
     * 
     * @param FunctionalTester $I
     *  Garante que não é possível criar um fornecedor sem um dado obrigatório, no caso, razão social
     */
    public function naoCriaFornecedorSemRazaoSocial(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor_nao_criado = [
            "nomefantasia" => "Fornecedor Obrigatorio",
            "codigofornecedores" => "321",
            "cadastro" => "1",
            "cnpj" => "57261950000197",
            "tenant" => $this->tenant,
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor_nao_criado, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * 
     * @param FunctionalTester $I
     * Garante que não é possível criar um fornecedor com um CNPJ existente
     */
    public function naoCriaComCnpjDuplicado(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $cnpj_duplicado = [
            "nomefantasia" => "Fornecedor 02",
            "razaosocial" => "F02",
            "codigofornecedores" => "002",
            "cadastro" => "1",
            "cnpj" => $fornecedor['cnpj'],
            "tenant" => $this->tenant
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cnpj_duplicado, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * 
     * @param FunctionalTester $I
     * Garante que não é possível criar um fornecedor com um código existente
     */
    public function naoCriaComCodigoDuplicado(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $codigo_duplicado = [
            "nomefantasia" => "Fornecedor 04",
            "razaosocial" => "F04",
            "codigofornecedores" => $fornecedor['pessoa'],
            "cadastro" => "1",
            "cnpj" => "88628963000185",
            "tenant" => $this->tenant
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $codigo_duplicado, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * 
     * @param FunctionalTester $I
     * pode criar com razão social duplicada
     */
    public function podeCriarComRazaoSocialDuplicada(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $razao_social_duplicada = [
            "nomefantasia" => "Fornecedor 06",
            "razaosocial" => $fornecedor['nome'],
            "codigofornecedores" => "006",
            "cadastro" => "1",
            "cnpj" => "38628854000190",
            "tenant" => $this->tenant
        ];

        /* execução da funcionalidade */
        $fornecedor_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $razao_social_duplicada, [], [], null);

        /* validação do resultado */
        $I->assertEquals(0, $fornecedor_criado['status']);
        $I->cantSeeInDatabase('ns.vw_fornecedores', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'fornecedor' => $fornecedor_criado['fornecedor']]);
        $I->deleteFromDatabase('ns.conjuntosfornecedores', ['registro' => $fornecedor_criado['fornecedor']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $fornecedor_criado['fornecedor']]);
    }

    /**
     * Teste edição de fornecedor e verifica código HTTP de resposta, se o campo foi editado e se os demais permanecem os mesmos.
     * @param FunctionalTester $I
     */
    public function editaFornecedor(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $fornecedor['nomefantasia'] = "Fornecedor 103";
        $fornecedor["nomefantasia"] = "Fornecedor 103";
        $fornecedor["razaosocial"] = "F103";
        $fornecedor["codigofornecedores"] = "103";
        $fornecedor["cnpj"] = "18492167000182";
        $fornecedor["tenant"] = $this->tenant;
        $fornecedor["formapagamento"] = ['formapagamento' => $formapagamento['formapagamento']];

        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url . $fornecedor['fornecedor'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $fornecedor['fornecedor'], "nomefantasia" => $fornecedor['nomefantasia']]);
        $I->cantSeeInDatabase('ns.vw_fornecedores', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'fornecedor' => $fornecedor['fornecedor']]);
    }

    /**
     * 
     * @param FunctionalTester $I
     * Um fornecedor ativo é criado e depois suspenso. Depois da suspensão
     * é verificado se o fornecedor está na tabela de fornecedores suspensos
     */
    public function suspendeFornecedor(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $suspensao = [
            "datafimsuspensao" => "2019-06-12",
            "fornecedor" => $fornecedor['id'],
            "tiposuspensao" => 1,
            "motivosuspensao" => "suspensão 1"

        ];
        $I->cantSeeInDatabase('ns.fornecedoressuspensos', ['fornecedor_id' => $fornecedor['fornecedor']]);

        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url . $fornecedor['fornecedor'] . '/suspender?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $suspensao, [], [], null);
        $I->canSeeResponseCodeIs(HttpCode::OK);

        /* validação do resultado */
        $I->canSeeInDatabase('ns.fornecedoressuspensos', ['fornecedor_id' => $fornecedor['fornecedor']]);
        $I->deleteFromDatabase('ns.fornecedoressuspensos', ['fornecedor_id' => $fornecedor['fornecedor']]);
    }
    /**
     * 
     * @param FunctionalTester $I
     * Um fornecedor ativo é obtido e suspenso. Depois reativo e checo se ele não está mais na tabela
     * de fornecedores suspensos
     */
    public function reativaFornecedor(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);

        /* execução da funcionalidade */
        $reativacao = [
            "motivoremocaosuspensao" => "Motivo 1"
        ];
        $I->sendRaw('POST', $this->url . $fornecedor['fornecedor'] . '/fornecedorreativar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $reativacao, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->cantSeeInDatabase('ns.fornecedoressuspensos', ['fornecedor_id' => $fornecedor['fornecedor']]);
    }
    /**
     * 
     * @param FunctionalTester $I
     * Vários fornecedores ativos são obtido e suspensos. Depois checo se o número da listagem está sendo retornado corretamente
     */
    public function listaFornecedoresSuspensos(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);
        $countAtual = $I->grabNumRecords('ns.fornecedoressuspensos', ['tenant' => $this->tenant_numero]);
        /* execução da funcionalidade */
        
        $lista = $I->sendRaw('GET', '/api/gednasajon/fornecedoressuspensos/' . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual, $lista);
    }
    /**
     * 
     * @param FunctionalTester $I
     * Obtenho um fornecedor e depois o advirto
     */
    public function adverteFornecedor(FunctionalTester $I)
    {
        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $empresa = $I->haveInDatabaseEmpresa($I, [
            'id_grupoempresarial' => $this->grupoempresarial_id
        ]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, [
            'empresa' => $empresa
        ]);

        /* execução da funcionalidade */
        $advertencia = [
            "fornecedor" => $fornecedor['fornecedor'],
            "motivoadvertencia" => "motivo 1",
            "nomeadvertencia" => "advertência 1",
            "estabelecimentoid" => [
                "estabelecimento" => $estabelecimento['estabelecimento']
            ]
        ];
        $I->sendRaw('POST', $this->url . $fornecedor['fornecedor'] . '/fornecedoradvertir?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $advertencia, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        try {
            $I->canSeeInDatabase('ns.advertencias', [
                'fornecedor_id' => $fornecedor['fornecedor'],
                'estabelecimento' => $estabelecimento['estabelecimento'],
                'tenant' => $this->tenant_numero
            ]);
            $I->canSeeInDatabase('ns.historicofornecedores', [
                'fornecedor_id' => $fornecedor['fornecedor'],
                'tenant' => $this->tenant_numero
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            //Excluo dados criados a partir da minha requisição
            $I->deleteFromDatabase('ns.advertencias', [
                'fornecedor_id' => $fornecedor['fornecedor'],
                'estabelecimento' => $estabelecimento['estabelecimento'],
                'tenant' => $this->tenant_numero
            ]);
            $I->deleteFromDatabase('ns.historicofornecedores', [
                'fornecedor_id' => $fornecedor['fornecedor'],
                'tenant' => $this->tenant_numero
            ]);
        }
    }

    /**
     * 
     * @param FunctionalTester $I
     * Criando um fornecedor com contatos, dados bancários e tipos de atividades
     */
    public function criaFornecedorComContatosDadosBancariosETiposDeAtividades(FunctionalTester $I)
    {

        /* prepara cenário */
        $banco = $I->haveInDatabaseBanco($I);

        $fornecedor = [
            "codigofornecedores" => "130",
            "razaosocial" => "F130",
            "nomefantasia" => "Fornecedor 130",
            "cadastro" => "1",
            "cnpj" => "64338908000192",
            "inscricaomunicipal" => "130",
            "tenant" => $this->tenant,
            "contatos" => [
                [
                    "nome" => "Contato 130",
                    "primeironome" => "Cont 130",
                    "sobrenome" => "Sobrenome 130"
                ]
            ],

            "dadosbancarios" => [
                [
                    "agenciadv" => "130",
                    "agencianome" => "A130",
                    "agencianumero" => "AN130",
                    "banco" => "341",
                    "contadv" => "130",
                    "contanumero" => "130",
                    "tipoconta" => "1",
                    "id_banco" => $banco
                ]
            ],
            "tipoatividades" => [
                [
                    "tipoatividade" => "66eab2c7-dce2-469c-aef9-a0347f755a16"
                ]
            ]
        ];

        /* execução da funcionalidade */
        $fornecedor_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);

        /* validação do resultado */
        $I->assertEquals(0, $fornecedor_criado['status']);
        /* remove fornecedor criado */
        $I->deleteFromDatabase('ns.conjuntosfornecedores', ['registro' => $fornecedor_criado['fornecedor']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $fornecedor_criado['fornecedor']]);
    }

    /**
     * 
     * @param FunctionalTester $I
     * Cria um fornecedor com documentos e verifica se estão no banco
     */
    public function exibeDocumentosFornecedor(FunctionalTester $I)
    {
        $documento = $I->haveInDatabaseDocumento($I);
        $fornecedoresdocumentos = 
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

        /* prepara cenário */
        $fornecedor = [
            "codigofornecedores" => "408",
            "razaosocial" => "G102",
            "nomefantasia" => "Fornecedor 102",
            "cadastro" => "1",
            "cnpj" => "94946511000144",
            "incricaomunicipal" => "202",
            "tenant" => $this->tenant,
            "fornecedoresdocumentos" => $fornecedoresdocumentos
        ];

        /* execução da funcionalidade */
        $fornecedor_criado = $I->sendRaw('POST', $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        unset($fornecedoresdocumentos[0]["tipodocumento"]);
        $I->canSeeInDatabase('ns.fornecedoresdocumentos', $fornecedoresdocumentos[0]);
        /* remove fornecedor criado */
        $I->deleteFromDatabase('ns.conjuntosfornecedores', ['registro' => $fornecedor_criado['fornecedor']]);
        $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $fornecedor_criado['fornecedor']]);
    }
    /**
     * 
     * @param FunctionalTester $I
     * Edita um fornecedor adicionando a ele um documento
     */
    public function editaFornecedorAdicionandoDocumento(FunctionalTester $I)
    {

        /* prepara cenário */
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $tipodocumentoId = $documento['tipodocumento'];
        $documentofornecedor = 
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
        $fornecedor['fornecedoresdocumentos'] = $documentofornecedor;
        $I->sendRaw('PUT', $this->url . $fornecedor['fornecedor'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $fornecedor['fornecedor'], "nomefantasia" => $fornecedor['nomefantasia']]);
        $I->canSeeInDatabase('ns.fornecedoresdocumentos', [
            'fornecedor' => $fornecedor['fornecedor'],
            "tipodocumento" => $documento['tipodocumento'],
            "naoexibiremrelatorios" => true,
        ]);
        $I->cantSeeInDatabase('ns.vw_fornecedores', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'fornecedor' => $fornecedor['fornecedor']]);

        /* apagando dado do banco */
        $I->deleteFromDatabase('ns.fornecedoresdocumentos', ['tipodocumento' => $tipodocumentoId]);

    }
    
    /**
     * 
     * @param FunctionalTester $I
     * Edita um fornecedor removendo o seu documento
     */
    public function editaFornecedorRemovendoDocumento(FunctionalTester $I)
    {   
        //Criando o fornecedor, criando o tipo de documento e criando a relação entre eles
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $objFornecedor = $I->haveInDatabaseFornecedorComDocumento($I, $fornecedor, $documento);

        //Removendo o documento do objeto fornecedor e fazendo o put
        $objFornecedor['fornecedoresdocumentos'] = null;

        //Execução da funcionalidade
        $I->sendRaw('PUT', $this->url . $objFornecedor['fornecedor'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $objFornecedor, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeInDatabase('ns.pessoas', ['id' => $objFornecedor['fornecedor'], "nomefantasia" => $objFornecedor['nomefantasia']]);
        $I->cantSeeInDatabase('ns.vw_fornecedores', ['id_grupoempresarial' => $this->id_grupoempresarial_sem_permissao, 'tenant' => $this->tenant_numero, 'fornecedor' => $objFornecedor['fornecedor']]);
        $I->cantSeeInDatabase('ns.fornecedoresdocumentos', ['tipodocumento' => $documento['tipodocumento'], 'fornecedor' => $objFornecedor['fornecedor']]);

    }
    
    /**
     * 
     * @param FunctionalTester $I
     * Retorna o documento do fornecedor
     */
    public function retornaDocumentoDoFornecedor(FunctionalTester $I)
    {   
        //Criando o fornecedor, criando o tipo de documento e criando a relação entre eles
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $objFornecedor = $I->haveInDatabaseFornecedorComDocumento($I, $fornecedor, $documento);

        //Obtendo o id do documento do fornecedor
        $idDocumento = $objFornecedor['fornecedoresdocumentos']['fornecedordocumento'];

        //Execução da funcionalidade
        $docRetornado = $I->sendRaw('GET', '/api/gednasajon/' . $objFornecedor['fornecedor'] . '/fornecedoresdocumentos/' . $idDocumento . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

    }
    
    /**
     * 
     * @param FunctionalTester $I
     * Retorna todos os documentos do fornecedor
     */
    public function listaDocumentosDoFornecedor(FunctionalTester $I)
    {   
        //Criando o fornecedor, criando os tipos de documento e criando a relação entre eles
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $documento = $I->haveInDatabaseDocumento($I);
        $documento2 = $I->haveInDatabaseDocumento($I);
        $I->haveInDatabaseFornecedorComDocumento($I, $fornecedor, $documento);
        $I->haveInDatabaseFornecedorComDocumento($I, $fornecedor, $documento2);

        // Retornando a quantidade de documentos para o fornecedor
        $countAtual = $I->grabNumRecords('ns.fornecedoresdocumentos', ['fornecedor' => $fornecedor['fornecedor']]);

        //Execução da funcionalidade
        $listaDocs = $I->sendRaw('GET', '/api/gednasajon/' . $fornecedor['fornecedor'] . '/fornecedoresdocumentos/' . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual, $listaDocs);

    }

    /**
     * Teste para verificar se Fornecedores com mais de um tipo de atividade são listados sem duplicidade
     * @param FunctionalTester $I
     */
    public function listaFornecedoresSemDuplicidade(FunctionalTester $I)
    {
        $countAtual = $I->grabNumRecords('ns.vw_fornecedores', ['tenant' => $this->tenant_numero]);

        $fornecedor = $I->haveInDatabaseFornecedor($I);

        // funcionalidade testada
        $url          = $this->url . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial;
        $clientesRest = $I->sendRaw('GET', $url, [], [], [], null);

        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual + 1, $clientesRest);
    }
}
