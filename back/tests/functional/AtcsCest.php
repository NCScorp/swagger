<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa Atcs
 */
class AtcsCest
{

    private $url_base = '/api/gednasajon/atcs/';
    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';
    private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';

    /**
     *
     * @param FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        $I->amSamlLoggedInAs('usuario@nasajon.com.br', [EnumAcao::ATCS_CREATE, EnumAcao::ATCS_INDEX, EnumAcao::ATCS_PUT, EnumAcao::ATCS_GET, EnumAcao::PROPOSTASITENS_GET, EnumAcao::PROPOSTASITENS_CREATE, EnumAcao::CONTRATOSTAXASADM_GERENCIAR]);
    }

    /**
     *
     * @param FunctionalTester $I
     */
    public function _after(FunctionalTester $I)
    {
        $I->deleteAllFromDatabase('crm.responsabilidadesfinanceirasvalores');
        $I->deleteAllFromDatabase('crm.responsabilidadesfinanceiras');
        $I->deleteAllFromDatabase('crm.fornecedoresenvolvidos');
        $I->deleteAllFromDatabase('crm.atcsresponsaveisfinanceiros');
        $I->deleteAllFromDatabase('crm.historicoatcs');
        $I->deleteAllFromDatabase('crm.atcsdadosseguradoras');

        $I->updateInDatabase('crm.propostasitens', ['servicoorcamento' => null], []); //removendo constraint para não prender a deleção de orçamento
        $I->deleteAllFromDatabase('crm.orcamentos');
        $I->deleteAllFromDatabase('crm.propostasitensfamilias');
        $I->deleteAllFromDatabase('crm.propostasitensfuncoes');
        $I->deleteAllFromDatabase('crm.propostasitens');
        $I->deleteAllFromDatabase('crm.propostascapitulos');
        $I->deleteAllFromDatabase('crm.propostas');
        $I->deleteAllFromDatabase('crm.atcstiposdocumentosrequisitantes');
        $I->deleteAllFromDatabase('crm.atcs');
        $I->deleteAllFromDatabase('crm.midiasorigem');
        $I->deleteAllFromDatabase('financas.itenscontratos');
        $I->deleteAllFromDatabase('financas.contratos');
        $I->deleteAllFromDatabase('financas.projetos');
    }


    /**
     * Método que formata os dados do objeto para a estrutura que será verificada no banco
     * @param type $atc
     */
    private function getAtcDados($atc, $comparacodigo = 0)
    {
        $atcsDados['negocio'] = $atc['negocio'];
        if ($comparacodigo) {
            $atcsDados['codigo'] = $atc['codigo'];
        }

        $atcsDados['area'] = $atc['area']['negocioarea'];
        $atcsDados['origem'] = $atc['origem']['midia'];
        $atcsDados['cliente'] = $atc['cliente']['cliente'];
        $atcsDados['estabelecimento'] = isset($atc['estabelecimento']['estabelecimento']) ? $atc['estabelecimento']['estabelecimento'] : null;
        $atcsDados['localizacaocidadeestrangeira'] = isset($atc['localizacaocidadeestrangeira']['cidadeestrangeira']) ? $atc['localizacaocidadeestrangeira']['cidadeestrangeira'] : null;
        $atcsDados['localizacaomunicipio'] = isset($atc['localizacaomunicipio']['municipio']) ? $atc['localizacaomunicipio']['municipio'] : null;
        $atcsDados['localizacaopais'] = isset($atc['localizacaopais']['pais']) ? $atc['localizacaopais']['pais'] : null;
        $atcsDados['localizacaoestado'] = isset($atc['localizacaoestado']['uf']) ? $atc['localizacaoestado']['uf'] : null;
        $atcsDados['localizacaocep'] = isset($atc['localizacaocep']) ? $atc['localizacaocep'] : null;
        $atcsDados['localizacaobairro'] = isset($atc['localizacaobairro']) ? $atc['localizacaobairro'] : null;
        $atcsDados['localizacaorua'] = isset($atc['localizacaorua']) ? $atc['localizacaorua'] : null;
        $atcsDados['localizacaotipologradouro'] = isset($atc['localizacaotipologradouro']['tipologradouro']) ? $atc['localizacaotipologradouro']['tipologradouro'] : null;
        $atcsDados['localizacaonumero'] = isset($atc['localizacaonumero']) ? $atc['localizacaonumero'] : null;
        $atcsDados['localizacaocomplemento'] = isset($atc['localizacaocomplemento']) ? $atc['localizacaocomplemento'] : null;
        $atcsDados['localizacaoreferencia'] = isset($atc['localizacaoreferencia']) ? $atc['localizacaoreferencia'] : null;
        $atcsDados['localizacao'] = isset($atc['localizacao']['endereco']) ? $atc['localizacao']['endereco'] : null;
        $atcsDados['camposcustomizados'] = isset($atc['camposcustomizados']) ? $atc['camposcustomizados'] : null;
        $atcsDados['negociopai'] = isset($atc['negociopai']['negocio']) ? $atc['negociopai']['negocio'] : null;
        $atcsDados['referenciaexterna'] = isset($atc['referenciaexterna']) ? $atc['referenciaexterna'] : null;
        $atcsDados['observacoes'] = isset($atc['observacoes']) ? $atc['observacoes'] : null;
        $atcsDados['projeto'] = isset($atc['projeto']) ? $atc['projeto'] : null;
        $atcsDados = array_filter($atcsDados, function ($dado) {
            return !empty($dado);
        });
        return $atcsDados;
    }

    /**
     * Método que captura apenas os dados referentes a seguradora dentro do atc
     * @param type $atc
     */
    private function getSeguradoraDadosNoAtc($atc)
    {
        $seguradora['negocio'] = $atc['negocio'];
        $seguradora['seguradora'] = $atc['cliente']['cliente'];
        $seguradora['produtoseguradora'] = $atc['seguradoraprodutoseguradora']['templatepropostagrupo'];
        $seguradora['apolice'] = $atc['seguradoraapolice']['templateproposta'];
        $seguradora['apolicetipo'] = isset($atc['seguradoraapolicetipo']) ? $atc['seguradoraapolicetipo'] : null;
        $seguradora['sinistro'] = isset($atc['seguradorasinistro']) ? $atc['seguradorasinistro'] : null;
        $seguradora['apoliceconfirmada'] = isset($atc['seguradoraapoliceconfirmada']) ? $atc['seguradoraapoliceconfirmada'] : null;
        $seguradora['titularnome'] = isset($atc['seguradoratitularnome']) ? $atc['seguradoratitularnome'] : null;
        $seguradora['titulartipodocumento'] = isset($atc['seguradoratitulartipodocumento']) ? $atc['seguradoratitulartipodocumento'] : null;
        $seguradora['titularcpf'] = isset($atc['seguradoratitularcpf']) ? $atc['seguradoratitularcpf'] : null;
        $seguradora['titularcnpj'] = isset($atc['seguradoratitularcnpj']) ? $atc['seguradoratitularcnpj'] : null;
        $seguradora['titularvinculo'] = isset($atc['seguradoratitularvinculo']['vinculo']) ? $atc['seguradoratitularvinculo']['vinculo'] : null;
        $seguradora = array_filter($seguradora, function ($dado) {
            return !empty($dado);
        });
        return $seguradora;
    }

    /**
     * Método que formata dadosSeguradora para ser checado pelo canseeindatabase
     * @param type $dadosSeguradora
     */
    private function getAtcsDadosSeguradoras($dados_criados)
    {
        $dadosSeguradora['negociodadosseguradora'] = $dados_criados["negociodadosseguradora"];
        $dadosSeguradora['negocio'] = $dados_criados['negocio'];
        $dadosSeguradora['seguradora'] = $dados_criados['seguradora']['cliente'];
        $dadosSeguradora['produtoseguradora'] = $dados_criados['produtoseguradora']['templatepropostagrupo'];
        $dadosSeguradora['apolice'] = $dados_criados['apolice']['templateproposta'];
        $dadosSeguradora['apolicetipo'] = isset($dados_criados['apolicetipo']) ? $dados_criados['apolicetipo'] : null;
        $dadosSeguradora['sinistro'] = isset($dados_criados['sinistro']) ? $dados_criados['sinistro'] : null;
        $dadosSeguradora['apoliceconfirmada'] = isset($dados_criados['apoliceconfirmada']) ? $dados_criados['apoliceconfirmada'] : null;
        $dadosSeguradora['titularnome'] = isset($dados_criados['titularnome']) ? $dados_criados['titularnome'] : null;
        $dadosSeguradora['titulartipodocumento'] = isset($dados_criados['titulartipodocumento']) ? $dados_criados['titulartipodocumento'] : null;
        $dadosSeguradora['titularcpf'] = isset($dados_criados['titularcpf']) ? $dados_criados['titularcpf'] : null;
        $dadosSeguradora['titularcnpj'] = isset($dados_criados['titularcnpj']) ? $dados_criados['titularcnpj'] : null;
        $dadosSeguradora['titularvinculo'] = isset($dados_criados['titularvinculo']['vinculo']) ? $dados_criados['titularvinculo']['vinculo'] : null;
        $dadosSeguradora['valorautorizado'] = isset($dados_criados['valorautorizado']) ? $dados_criados['valorautorizado'] : null;
        $dadosSeguradora['beneficiario'] = isset($dados_criados['beneficiario']) ? $dados_criados['beneficiario'] : null;
        $dadosSeguradora['valorapolice'] = isset($dados_criados['valorapolice']) ? $dados_criados['valorapolice'] : null;
        $dadosSeguradora = array_filter($dadosSeguradora, function ($dado) {
            return !empty($dado);
        });
        return $dadosSeguradora;
    }

    /**
     * Método que formata os dados do objeto para a estrutura que será verificada no banco
     * @param type $atc
     */
    private function getLocalDados($atc)
    {
        return [
            'cep' => $atc['localizacaocep'],
            'logradouro' => $atc['localizacaorua'],
            'tipologradouro' => $atc['localizacaotipologradouro']['tipologradouro'],
            'bairro' => $atc['localizacaobairro'],
            'numero' => $atc['localizacaonumero'],
            'complemento' => $atc['localizacaocomplemento'],
            'referencia' => $atc['localizacaoreferencia'],
            'nome' => isset($atc['localizacaonome']) ? $atc['localizacaonome'] : null,
            'tenant' => $atc['tenant'],
            'uf' => $atc['localizacaoestado']['uf'],
            'pais' => $atc['localizacaopais']['pais']
        ];
    }

    /**
     * @param FunctionalTester $I
     */
    public function criaAtc(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
            'nome' => 'Atc 1',
            //O Código agora é automatico.
            // 'codigo' => '1', 
            'area' => ['negocioarea' => $area['negocioarea']],
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'tenant' => $this->tenant_numero,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];

        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $atc['negocio'] = $atc_criado['negocio']; //colocando chave primária no array original para verificar se todas as informações estão no banco
        $responsavelfinanceiro[0]['negocio'] = $atc_criado['negocio']; //colocando também no responsavelfinanceiro
        $responsavelfinanceiro[0]['responsavelfinanceiro'] = $cliente['id']; //modificando objeto para ficar de acordo com o objeto que vem do banco
        $atc = $this->getAtcDados($atc, 0);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->canSeeInDatabase('crm.atcsresponsaveisfinanceiros', $responsavelfinanceiro[0]);
    }

    /**
     * @param FunctionalTester $I
     */
    public function criaAtcSemProjeto(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
            'nome' => 'Atc 1',
            'codigo' => '1',
            'area' => ['negocioarea' => $area['negocioarea']],
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'tenant' => $this->tenant_numero,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];

        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $projeto['projeto'] = $atc_criado['projeto']['projeto'];
        $projeto['tenant'] = $this->tenant_numero;
        $I->cantSeeInDatabase('financas.projetos', $projeto);
    }
    

    /**
     * @param FunctionalTester $I
     */
    public function cancelaAtcEProjeto(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComProjeto($I, $area, $origem, $cliente);

        /* execução da funcionalidade */
        //Post("/atcs/{id}/atcStatusCancelado")
        $I->sendRaw(
            'POST',
            $this->url_base .
                $atc['negocio'] .
                '/atcStatusCancelado' .
                '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial,
            $atc,
            [],
            [],
            null
        );

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK); //status 4
        $projeto = [
            'projeto' => $atc['projeto']['projeto'],
            'situacao' => 1
        ];
        $I->canSeeInDatabase('financas.projetos', $projeto);
    }


    /**
     * @param FunctionalTester $I
     */
    public function criaAtcComNegociopai(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $estado = $I->haveInDataBaseEstado($I);
        $pais = $I->haveInDataBasePais($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc2 = $I->haveInDatabaseAtc($I, $area, $midia, $cliente); // Atc criado para servir de Pai
        $atc = [
            'nome' => 'Atc 1',
            //O Código agora é automatico.
            //'codigo' => '1',
            'area' => ['negocioarea' => $area['negocioarea']],
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'tenant' => $this->tenant_numero,
            'negociopai' => ['negocio' => $atc2['negocio'], 'projeto' => null],
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];

        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $atc['negocio'] = $atc_criado['negocio']; //colocando chave primária no array original para verificar se todas as informações estão no banco
        $responsavelfinanceiro[0]['negocio'] = $atc_criado['negocio']; //colocando também no responsavelfinanceiro
        $responsavelfinanceiro[0]['responsavelfinanceiro'] = $cliente['id']; //modificando objeto para ficar de acordo com o objeto que vem do banco
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->canSeeInDatabase('crm.atcsresponsaveisfinanceiros', $responsavelfinanceiro[0]);
    }

    /**
     * Cria o Atc sem salvar os dados da localização
     * @param FunctionalTester $I
     */
    public function criaAtcComLocalizacaoSemSalvarOLocal(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $midia = $I->haveInDatabaseMidia($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I, 'Brasil');
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
            'nome' => 'Atc 1',
            //O Código agora é automatico.
            //'codigo' => '1',
            'area' => $area,
            'origem' => ['midia' => $midia['midiaorigem'], 'nome' => 'E-mail'],
            'cliente' => ['cliente' => $cliente['cliente']],
            'localizacaocep' => '24127355',
            'localizacaobairro' => 'Bairro',
            'localizacaotipologradouro' => ['tipologradouro' => "A"],
            'localizacaorua' => 'Rua 3',
            'localizacaonumero' => '45',
            'clienteresponsavelfinanceiro' => true,
            'localizacaocomplemento' => 'Casa 20',
            'localizacaoreferencia' => 'Perto do mercado',
            'localizacaonovasalvar' => 1,
            'localizacaosalvar' => 1,
            'localizacaopais' => ['pais' => '1058', 'nome' => 'Brasil'],
            'localizacaoestado' => ['uf' => $estado['uf'], 'nome' => $estado['nome']],
            'localizacaomunicipio' => ['codigo' => $municipio['ibge'], 'nome' => $municipio['nome']],
            'localizacaonome' => 'Endereço 01',
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'tenant' => $this->tenant_numero,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];
        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $atc['negocio'] = $atc_criado['negocio']; //colocando chave primária no array original para verificar se todas as informações estão no banco
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
    }

    /**
     * Cria o Atc sem salvar os dados da localização
     * @param FunctionalTester $I
     */
    public function criaAtcComLocalizacaoESalvarOLocal(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $midia = $I->haveInDatabaseMidia($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
            'nome' => 'Atc 1',
            //O Código agora é automatico.
            //'codigo' => '1',
            'area' => $area,
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'tenant' => $this->tenant_numero,
            'localizacaocep' => '24127355',
            'localizacaobairro' => 'Bairro',
            'localizacaotipologradouro' => ['tipologradouro' => "A"],
            'localizacaorua' => 'Rua 3',
            'localizacaonumero' => '45',
            'clienteresponsavelfinanceiro' => true,
            'localizacaocomplemento' => 'Casa 20',
            'localizacaoreferencia' => 'Perto do mercado',
            'localizacaonovasalvar' => 2,
            'localizacaosalvar' => 2,
            'localizacaopais' => ['pais' => '1058', 'nome' => 'Brasil'],
            'localizacaoestado' => ['uf' => $estado['uf'], 'nome' => $estado['nome']],
            'localizacaomunicipio' => ['codigo' => $municipio['ibge'], 'nome' => $municipio['nome']],
            'localizacaonome' => 'Endereço 01',
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'localizacaonome' => 'novo',
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];
        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $atc['negocio'] = $atc_criado['negocio']; //colocando chave primária no array original para verificar se todas as informações estão no banco
        $local = $this->getLocalDados($atc);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->canSeeInDatabase('ns.enderecos', $local);
    }


    /**
     * @param FunctionalTester $I
     */
    public function naoCriaAtcSemCliente(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        /* execução da funcionalidade */
        $atc = [
            'nome' => 'Atc 1',
            'codigo' => '1',
            'area' => ['negocioarea' => $area['negocioarea']],
            'origem' => ['midia' => $midia['midiaorigem']],
            'tenant' => $this->tenant_numero,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }


    /**
     * @param FunctionalTester $I
     */
    public function criaAtcComSeguradora(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        // $midia = $I->haveInDatabaseMidia($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $origem = $I->haveInDatabaseMidia($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);

        $dadosSeguradora = [
            'seguradora' => ['cliente' => $cliente['cliente']],
            'produtoseguradora' => ['templatepropostagrupo' => $produtoseguradora['templatepropostagrupo']],
            'apolice' => ['templateproposta' => $apolice['templateproposta']],
            'apolicetipo' => 1,
            'sinistro' => '0001',
            'titularnome' => 'Juca',
            'titulartipodocumento' => 1,
            'titularcpf' => '00000000191',
            'titularvinculo' => ['vinculo' => $vinculo['vinculo']],
            'valorautorizado' => '5000',
            'valorapolice' => '5000',
            'beneficiario' => 'Nome do Beneficiário'
        ];

        /* execução da funcionalidade */
        $url = "/api/gednasajon/$atc[negocio]/atcsdadosseguradoras/";
        $dadosSeguradora_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dadosSeguradora, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $dadosSeguradora = $this->getAtcsDadosSeguradoras($dadosSeguradora_criado);
        $I->canSeeInDatabase('crm.atcsdadosseguradoras', $dadosSeguradora);
    }

    /**
     * @param FunctionalTester $I
     * @todo testar demais campos
     */
    public function editaAtc(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
    }

    /**
     * @param FunctionalTester $I
     */
    public function criaTipoDocumentoRequisitante(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $documento = $I->haveInDatabaseDocumento($I);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $tipoDocRequisitante = [
            'tipodocumento' => ['tipodocumento' => $documento['tipodocumento']],
            'requisitantenegocio' => true,
            'copiasimples' => true,
            'copiaautenticada' => true,
            'original' => true,
            'permiteenvioemail' => true,
            'pedirinformacoesadicionais' => true
        ];

        $docCriado = $I->sendRaw('POST', '/api/gednasajon/' . $atc['negocio'] . '/atcstiposdocumentosrequisitantes/' . '?grupoempresarial=' . $this->grupoempresarial, $tipoDocRequisitante, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->canSeeInDatabase('crm.atcstiposdocumentosrequisitantes', 
            ['negocio' => $atc['negocio']],
            ['tipodocumento' => ['tipodocumento' => $documento['tipodocumento']]],
            ['requisitantenegocio' => true],
            ['copiasimples' => true],
            ['copiaautenticada' => true],
            ['original' => true],
            ['permiteenvioemail' => true],
            ['pedirinformacoesadicionais' => true]
        );

        // Apagando dado criado do banco
        $I->deleteFromDatabase('crm.atcstiposdocumentosrequisitantes', ['negocio' => $atc['negocio']]);
        $I->deleteFromDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento['tipodocumento']]);

    }

    /**
     * @param FunctionalTester $I
     */
    public function listaTiposDocumentosRequisitante(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];
        $documento = $I->haveInDatabaseDocumento($I);
        $documento2 = $I->haveInDatabaseDocumento($I);

        $I->haveInDatabaseAtcTipoDocumentoRequisitante($I, $atc['negocio'], $documento['tipodocumento'], [
            'requisitantenegocio' => true,
            'copiasimples' => true,
            'copiaautenticada' => true,
            'original' => true,
            'permiteenvioemail' => true,
            'pedirinformacoesadicionais' => true
        ]);
        $I->haveInDatabaseAtcTipoDocumentoRequisitante($I, $atc['negocio'], $documento2['tipodocumento']);

        $countAtual = $I->grabNumRecords('crm.atcstiposdocumentosrequisitantes', ['negocio' => $atc['negocio']]);

        $listaDocs = $I->sendRaw('GET', '/api/gednasajon/' . $atc['negocio'] . '/atcstiposdocumentosrequisitantes/' . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount($countAtual, $listaDocs);

    }

    /**
     * @param FunctionalTester $I
     */
    public function retornaTipoDocumentoRequisitante(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];
        $documento = $I->haveInDatabaseDocumento($I);

        $tipoDocReq = $I->haveInDatabaseAtcTipoDocumentoRequisitante($I, $atc['negocio'], $documento['tipodocumento'], [
            'requisitantenegocio' => true,
            'copiasimples' => true,
            'copiaautenticada' => true,
            'original' => true,
            'permiteenvioemail' => true,
            'pedirinformacoesadicionais' => true
        ]);

        $tipoDocReqRetornado = $I->sendRaw('GET', '/api/gednasajon/' . $atc['negocio'] . '/atcstiposdocumentosrequisitantes/' . $tipoDocReq['negociotipodocumentorequisitante'] . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($tipoDocReq['negociotipodocumentorequisitante'], $tipoDocReqRetornado['negociotipodocumentorequisitante']);

    }

    /**
     * @param FunctionalTester $I
     */
    public function deletaTipoDocumentoRequisitante(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];
        $documento = $I->haveInDatabaseDocumento($I);

        $tipoDocReq = $I->haveInDatabaseAtcTipoDocumentoRequisitante($I, $atc['negocio'], $documento['tipodocumento'], [
            'requisitantenegocio' => true,
            'copiasimples' => true,
            'copiaautenticada' => true,
            'original' => true,
            'permiteenvioemail' => true,
            'pedirinformacoesadicionais' => true
        ]);

        $I->sendRaw('DELETE', '/api/gednasajon/' . $atc['negocio'] . '/atcstiposdocumentosrequisitantes/' . $tipoDocReq['negociotipodocumentorequisitante'] . '?grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->cantSeeInDatabase('crm.atcstiposdocumentosrequisitantes', ['negociotipodocumentorequisitante' => $tipoDocReq['negociotipodocumentorequisitante']]);

    }

    /**
     * @param FunctionalTester $I
     * Testa a mudança de um atendimento particular para um com seguradora
     * @todo remover o teste criaAtcComSeguradora pois não se cria mais um atc com seguradora.
     */
    public function adicionaSeguradoraNoAtc(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);

        /* execução da funcionalidade */
        $dadosSeguradora = [
            'seguradora' => ['cliente' => $cliente['cliente']],
            'produtoseguradora' => ['templatepropostagrupo' => $produtoseguradora['templatepropostagrupo']],
            'apolice' => ['templateproposta' => $apolice['templateproposta']],
            'apolicetipo' => 1,
            'sinistro' => '0001',
            'titularnome' => 'Juca',
            'titulartipodocumento' => 1,
            'titularcpf' => '00000000191',
            'titularvinculo' => ['vinculo' => $vinculo['vinculo']],
            'valorautorizado' => 5000,
            'valorapolice' => 5000,
            'beneficiario' => 'Nome do Beneficiário'
        ];

        $url = "/api/gednasajon/$atc[negocio]/atcsdadosseguradoras/";
        $dadosSeguradora_criado = $I->sendRaw('POST', $url . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dadosSeguradora, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $dadosSeguradora = $this->getAtcsDadosSeguradoras($dadosSeguradora_criado);
        $I->canSeeInDatabase('crm.atcsdadosseguradoras', $dadosSeguradora);
    }

    /**
     * @param FunctionalTester $I
     * Testa a mudança de um atendimento com seguradora para um particular
     */
    public function retiraDadosSobreSeguroDoAtc(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $dadosSeguradora = $I->haveInDatabaseDadosParaSeguradora($I, $atc, $produtoseguradora, $apolice, $vinculo);

        /* execução da funcionalidade */
        $dadosSeguradora['seguradora'] = ['cliente' => $dadosSeguradora['seguradora']];
        $dadosSeguradora['produtoseguradora'] = ['templatepropostagrupo' => $dadosSeguradora['produtoseguradora']];
        $dadosSeguradora['apolice'] = ['templateproposta' => $dadosSeguradora['apolice']];
        $dadosSeguradora['titularvinculo'] = ['vinculo' => $dadosSeguradora['titularvinculo']];
        $dadosSeguradora['valorautorizado'] = 5000;

        $url = "/api/gednasajon/$atc[negocio]/atcsdadosseguradoras/$dadosSeguradora[negociodadosseguradora]";
        $response = $I->sendRaw('DELETE', $url . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dadosSeguradora, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $dadosSeguradora = $this->getAtcsDadosSeguradoras($dadosSeguradora);
        $I->cantSeeInDatabase('crm.atcsdadosseguradoras', $dadosSeguradora);
    }

    /**
     * @param FunctionalTester $I
     * Testa a mudança de um atendimento com seguradora para um particular
     */
    public function editaDadosSobreSeguroDoAtc(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $dadosSeguradora_editado = $I->haveInDatabaseDadosParaSeguradora($I, $atc, $produtoseguradora, $apolice, $vinculo);

        /* execução da funcionalidade */
        $dadosSeguradora_editado['titularnome'] = 'Joaquim';

        /* execução da funcionalidade */

        $dadosSeguradora_editado['seguradora'] = ['cliente' => $dadosSeguradora_editado['seguradora']];
        $dadosSeguradora_editado['produtoseguradora'] = ['templatepropostagrupo' => $dadosSeguradora_editado['produtoseguradora']];
        $dadosSeguradora_editado['apolice'] = ['templateproposta' => $dadosSeguradora_editado['apolice']];
        $dadosSeguradora_editado['titularvinculo'] = ['vinculo' => $dadosSeguradora_editado['titularvinculo']];
        $dadosSeguradora_editado['valorautorizado'] = 5000;
        $dadosSeguradora_editado['valorapolice'] = 3000;

        $url = "/api/gednasajon/$atc[negocio]/atcsdadosseguradoras/$dadosSeguradora_editado[negociodadosseguradora]";
        $response = $I->sendRaw('PUT', $url . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dadosSeguradora_editado, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $dadosSeguradora = $this->getAtcsDadosSeguradoras($dadosSeguradora_editado);
        $I->canSeeInDatabase('crm.atcsdadosseguradoras', $dadosSeguradora);
    }

    /**
     * Teste para verificar se os Atcs listados pertencem a área de Atc listada
     * @param FunctionalTester $I
     */
    public function listaAtcsComFiltro(FunctionalTester $I)
    {
        // cenario
        $origem = $I->haveInDatabaseMidia($I);
        $area1 = $I->haveInDatabaseAreaDeAtc($I);
        $area2 = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc1 = $I->haveInDatabaseAtc($I, $area1, $origem, $cliente);
        $atc2 = $I->haveInDatabaseAtc($I, $area2, $origem, $cliente);

        // funcionalidade testada
        $url = $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial . '&filter=&area=' . $area1['negocioarea'];

        $atcs = $I->sendRaw('GET', $url, [], [], [], null);

        // verificação do teste
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertCount(1, $atcs);
    }

    /**
     * @param FunctionalTester $I
     */
    public function adicionaNegociopaiAoAtc(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc2 = $I->haveInDatabaseAtc($I, $area, $origem, $cliente); // Atc criado para servir de Pai

        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente, null, ['estabelecimento' => 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'], "N2");

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['negociopai'] = ['negocio' => $atc2['negocio'], 'projeto' => null];
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
    }

    /**
     * @param FunctionalTester $I
     */
    public function removeNegociopaiDoAtc(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc2 = $I->haveInDatabaseAtc($I, $area, $origem, $cliente); // Atc criado para servir de Pai
        $negociopai = $I->generateUuidV4();
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente, $negociopai, ['estabelecimento' => 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f'], "N2");

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['negociopai'] = null;
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
    }

    /**
     * @param FunctionalTester $I
     */
    public function naoPermiteEdicaoDoNegiocioSemCliente(FunctionalTester $I)
    {

        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['cliente'] = '';
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function naoCriaAtcSemPrincipal(FunctionalTester $I)
    {
        /* prepara cenário */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        unset($responsavelfinanceiro[0]['principal']);
        $atc = [
            'nome' => 'Atc 1',
            'codigo' => 'N1',
            'area' => ['negocioarea' => $area['negocioarea']],
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];

        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
    }

    /**
     * @param FunctionalTester $I
     * @todo testar demais campos
     */
    public function editaAtcComLocalizacaoNaoSalvaEPermaneceSemSalvar(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComLocalizacaoNaoSalva($I, $area, $origem, $cliente, $estado, $municipio);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'novo';
        $atc['localizacaosalvar'] = $atc['localizacaonovasalvar'] = 1;
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $local = $this->getLocalDados($atc);
        $atc = $this->getAtcDados($atc);
        $atc['nome'] = 'novo';
        $atc['tenant'] = 47;
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->cantSeeInDatabase('ns.enderecos', $local);
    }

    /**
     * @param FunctionalTester $I
     * @todo testar demais campos
     */
    public function editaAtcComLocalizacaoNaoSalvaESalva(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComLocalizacaoNaoSalva($I, $area, $origem, $cliente, $estado, $municipio);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['localizacaonome'] = 'Nome';
        $atc['localizacaosalvar'] =  $atc['localizacaonovasalvar'] = 2;
        $local = $this->getLocalDados($atc);
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->canSeeInDatabase('ns.enderecos', $local);
    }

    /**
     * @param FunctionalTester $I
     * @todo testar demais campos
     */
    public function editaAtcComLocalizacaoSalvaEDeixaDeSalvar(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComLocalizacaoSalva($I, $area, $origem, $cliente, $estado, $municipio, $pais);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['localizacaorua'] = 'R2';
        $atc['localizacaosalvar'] =  $atc['localizacaoexistentesalvar'] = 1;
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $local = $this->getLocalDados($atc);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->cantSeeInDatabase('ns.enderecos', $local);
    }

    /**
     * @param FunctionalTester $I
     * @todo testar demais campos
     */
    public function editaAtcComLocalizacaoSalvaESalva(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComLocalizacaoSalva($I, $area, $origem, $cliente, $estado, $municipio, $pais);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['localizacaonome'] = 'Nome2';
        $atc['localizacaorua'] = 'R2';
        $atc['localizacaosalvar'] =  $atc['localizacaoexistentesalvar'] = 2;
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $local = $this->getLocalDados($atc);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->canSeeInDatabase('ns.enderecos', $local);
    }

    /**
     * @param FunctionalTester $I
     * @todo comparar a localizacao antes e depois (endereco uuid)
     */
    public function editaAtcComLocalizacaoSalvaESalvaComoUmNovoLocal(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComLocalizacaoSalva($I, $area, $origem, $cliente, $estado, $municipio, $pais);
        $local_antigo = $this->getLocalDados($atc);

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $atc['localizacaonome'] = 'Nome2';
        $atc['localizacaorua'] = 'R2';
        $atc['localizacaosalvar'] =  $atc['localizacaoexistentesalvar'] = 3;
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $local = $this->getLocalDados($atc);
        $atc = $this->getAtcDados($atc);
        $I->canSeeInDatabase('crm.atcs', $atc);
        $I->canSeeInDatabase('ns.enderecos', $local_antigo);
        $I->canSeeInDatabase('ns.enderecos', $local);
    }

    /**
     * @param FunctionalTester $I
     * @todo desvincula um responsável financeiro de um Atc.
     */
    public function desvinculaResponsavelFinanceiro(FunctionalTester $I)
    {
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $responsaveisfinanceiros = $atc['responsavelfinanceiro'];

        /* ajustando midia/midiaorigem */
        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];

        /* montagem do objeto */
        $responsavelfinanceironovo =
            [
                [
                    'responsavelfinanceiro' => [
                        'cliente' => $cliente['id'],
                        'nomefantasia' => $cliente['nomefantasia']
                    ],
                    'principal' => true
                ]
            ];
        $atc['responsaveisfinanceiros'] = $responsavelfinanceironovo;
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_base . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->cantSeeInDatabase('crm.atcsresponsaveisfinanceiros', $responsaveisfinanceiros[0]);
        $I->cantSeeInDatabase('crm.atcsresponsaveisfinanceiros', $responsaveisfinanceiros[1]);
    }

    /**
     * @param FunctionalTester $I
     * @todo Testa se todos os campos obrigatórios de um Atc estão sendo retornados corretamente de um GET.
     */
    public function exibeAtc(FunctionalTester $I)
    {
        $recebido = [];
        $esperado = [];
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $responsaveisfinanceiros = $atc['responsavelfinanceiro'];
        /* execução da funcionalidade */

        $atc_Recebido = $I->sendRaw('GET', '/api/gednasajon/atcs/' . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($atc_Recebido['negocio'], $atc['negocio']);
        $I->assertEquals($atc_Recebido['tenant'], $atc['tenant']);
        $I->assertEquals($atc_Recebido['area']['negocioarea'], $area['negocioarea']);
        $I->assertEquals($atc_Recebido['origem']['midia'], $origem['midiaorigem']);
        $I->assertEquals($atc_Recebido['cliente']['cliente'], $cliente['cliente']);
        array_push($recebido, $atc_Recebido['responsaveisfinanceiros'][0]['negocioresponsavelfinanceiro']);
        array_push($recebido, $atc_Recebido['responsaveisfinanceiros'][1]['negocioresponsavelfinanceiro']);
        array_push($esperado, $responsaveisfinanceiros[0]['negocioresponsavelfinanceiro']);
        array_push($esperado, $responsaveisfinanceiros[1]['negocioresponsavelfinanceiro']);
        sort($esperado); //Sorts são usados pois embora sejam iguais, podem vir fora de ordem no banco
        sort($recebido);
        $I->assertEquals($esperado, $recebido);
    }

    /**
     * @param FunctionalTester $I
     * @todo Testa se todos os campos obrigatórios e não obrigatórios de um Atc estão sendo retornados corretamente de um GET.
     */
    public function exibeAtcCompleto(FunctionalTester $I)
    {
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $negociopai = $I->haveInDatabaseAtc($I, $area, $origem, $cliente); // Atc criado para servir de Pai
        $atc = $I->haveInDatabaseAtcCompleto($I, $area, $origem, $cliente, $negociopai['negocio']);
        $responsavelfinanceiro = $atc['responsavelfinanceiro'];

        /* execução da funcionalidade */
        $atc_Recebido = $I->sendRaw('GET', '/api/gednasajon/atcs/' . $atc['negocio'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, [], [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($atc_Recebido['negocio'], $atc['negocio']);
        $I->assertEquals($atc_Recebido['tenant'], $atc['tenant']);
        $I->assertEquals($atc_Recebido['area']['negocioarea'], $area['negocioarea']);
        $I->assertEquals($atc_Recebido['origem']['midia'], $origem['midiaorigem']);
        $I->assertEquals($atc_Recebido['cliente']['cliente'], $cliente['cliente']);
        $I->assertEquals($atc_Recebido['responsaveisfinanceiros'][0]['negocioresponsavelfinanceiro'], $responsavelfinanceiro['negocioresponsavelfinanceiro']);
        $I->assertEquals($atc_Recebido['nome'], $atc['nome']);
        $I->assertEquals($atc_Recebido['localizacaocep'], $atc['localizacaocep']);
        $I->assertEquals($atc_Recebido['localizacaocep'], $atc['localizacaocep']);
        $I->assertEquals($atc_Recebido['localizacaobairro'], $atc['localizacaobairro']);
        $I->assertEquals($atc_Recebido['localizacao']['rua'], $atc['localizacaorua']);
        $I->assertEquals($atc_Recebido['localizacaonumero'], $atc['localizacaonumero']);
        $I->assertEquals($atc_Recebido['localizacaocomplemento'], $atc['localizacaocomplemento']);
        $I->assertEquals($atc_Recebido['localizacaoreferencia'], $atc['localizacaoreferencia']);
        $I->assertEquals($atc_Recebido['localizacaopais'], $atc['localizacaopais']);
        $I->assertEquals($atc_Recebido['localizacaocidadeestrangeira'], $atc['localizacaocidadeestrangeira']);
        $I->assertEquals($atc_Recebido['referenciaexterna'], $atc['referenciaexterna']);
        $I->assertEquals($atc_Recebido['negociopai']['negocio'], $negociopai['negocio']);
    }

    public function criaAtcCompleto(FunctionalTester $I)
    {
        $area = $I->haveInDatabaseAreaDeAtcComLocalizacao($I);
        $midia = $I->haveInDatabaseMidia($I);
        $estado = $I->haveInDataBaseEstado($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $endereco = $I->haveInDataBaseEnderecoEstrangeiro($I, $cliente);
        //Creio que precisa ter um atc criado antes com localizacao para as localizações ficarem disponiveis
        $negociopai = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
            'nome' => 'Atc 1',
            'codigo' => 'N2',
            'area' => $area,
            'id_pessoa' => $cliente,
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'localizacaocarregar' => ['endereco' => $endereco['endereco']],
            'localizacaocep' => '24127355',
            'localizacaobairro' => 'Bairro',
            'localizacaotipologradouro' => ['tipologradouro' => "A"],
            'localizacaorua' => 'Rua 3',
            'localizacaonumero' => '45',
            'localizacaocomplemento' => 'Casa 20',
            'localizacaoreferencia' => 'Perto do mercado',
            'localizacaonovasalvar' => 1,
            'localizacaosalvar' => 2,
            'localizacaopais' => ['pais' => $endereco['pais'], 'nome' => 'Texas'],
            'localizacaocidadeestrangeira' => ['cidadeestrangeira' => $endereco['cidadeestrangeira']],
            'localizacaonome' => 'Endereço 01',
            'negociopai' => ['negocio' => $negociopai['negocio'], 'projeto' => null],
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            //Campo foi ocultado do formulário
            // 'referenciaexterna' => '20550',
            'observacoes' => 'Muitas observacoes',
            'tenant' => $this->tenant_numero,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];
        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);
        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);
        $I->assertEquals($atc_criado['tenant'], $atc['tenant']);
        $I->assertEquals($atc_criado['area']['negocioarea'], $area['negocioarea']);
        $I->assertEquals($atc_criado['origem']['midia'], $midia['midiaorigem']);
        $I->assertEquals($atc_criado['cliente']['cliente'], $cliente['cliente']);
        $I->assertEquals($atc_criado['nome'], $atc['nome']);
        $I->assertEquals($atc_criado['localizacaonome'], $atc['localizacaonome']);
        $I->assertEquals($atc_criado['localizacaocep'], $atc['localizacaocep']);
        $I->assertEquals($atc_criado['localizacaobairro'], $atc['localizacaobairro']);
        $I->assertEquals($atc_criado['localizacao']['rua'], $atc['localizacaorua']);
        $I->assertEquals($atc_criado['localizacaonumero'], $atc['localizacaonumero']);
        $I->assertEquals($atc_criado['localizacaocomplemento'], $atc['localizacaocomplemento']);
        $I->assertEquals($atc_criado['localizacaoreferencia'], $atc['localizacaoreferencia']);
        // $I->assertEquals($negocio_criado['referenciaexterna'], $negocio['referenciaexterna']);
        $I->assertEquals($atc_criado['negociopai']['negocio'], $negociopai['negocio']);
        $I->assertEquals($atc_criado['observacoes'], $atc['observacoes']);
        $I->assertEquals($atc_criado['localizacaopais'], $atc['localizacaopais']);
        $I->assertEquals($atc_criado['localizacaocidadeestrangeira']['cidadeestrangeira'], $atc['localizacaocidadeestrangeira']['cidadeestrangeira']);
        $responsavelfinanceiro[0]['negocio'] = $atc_criado['negocio']; //colocando também no responsavelfinanceiro
        $responsavelfinanceiro[0]['responsavelfinanceiro'] = $cliente['id']; //modificando objeto para ficar de acordo com o objeto que vem do banco
        $I->canSeeInDatabase('crm.atcsresponsaveisfinanceiros', $responsavelfinanceiro[0]);
    }

    //Verifica se ao criar um Atc, um pedido é adicionado simultaneamente
    public function verificaCriacaoDoPedido(FunctionalTester $I)
    {
        /* inicializações */
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
            'nome' => 'Atc com pedido',
            'codigo' => 'N1',
            'area' => ['negocioarea' => $area['negocioarea']],
            'origem' => ['midia' => $midia['midiaorigem']],
            'cliente' => ['cliente' => $cliente['cliente']],
            'responsaveisfinanceiros' => $responsavelfinanceiro,
            'tenant' => $this->tenant_numero,
            'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
            'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id]
        ];

        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_base . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::CREATED);

        //pegando chave primária do Atc para buscar por ele na tabela de propostas 
        $atc['negocio'] = $atc_criado['negocio'];
        $I->canSeeInDatabase('crm.propostas', ['negocio' => $atc['negocio']], ['status' => 2], ['valor' => 0.00]);
    }

    /**
     * Cria um pedido associando-a a um Atc já existente
     * Pedido = Proposta confirmada
     *
     * @param FunctionalTester $I
     * @return void
     */
    public function criaProposta(FunctionalTester $I)
    {
        //Preparação do cenário
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);

        $pedido = [
            'negocio' => $atc['negocio'],
            'tenant' => $this->tenant_numero
        ];

        //Execução da funcionalidade
        $pedido_criado = $I->sendRaw('POST', '/api/' . $this->tenant . '/' . $pedido['negocio'] . '/propostas/?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $pedido, [], [], null);

        //Validação do resultado
        $I->canSeeInDatabase('crm.propostas', ['proposta' => $pedido_criado['proposta']], ['negocio' => $pedido_criado['negocio']], ['status' => 2], ['valor' => 0.00]);
    }

    /**
     * Integração finanças
     */



    /**
     * Gera contrato no finanças
     * @todo função que tenta criar contrato para um projeto que possui um contrato existente, verificar com mendonça tarefa para criar mais de um contrato
     *
     * @param FunctionalTester $I
     * @return void
     */
    // #REFATORACAO_FICHA_FINANCEIRA
    // public function criaContratoDeRecebimento(FunctionalTester $I){
    //     //Preparação do cenário
    //     $origem = $I->haveInDatabaseMidia($I);
    //     $area = $I->haveInDatabaseAreaDeAtc($I);
    //     $estado = $I->haveInDataBaseEstado($I);
    //     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    //     $pais = $I->haveInDatabasePais($I);
    //     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    //     $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
    //     $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    //     $fornecedor = $I->haveInDatabaseFornecedor($I, 1, $atc['estabelecimento']['estabelecimento']);
    //     $proposta = $I->haveInDatabaseProposta($I, $atc);
    //     $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    //     $composicao = $I->haveinDatabaseComposicao($I);
    //     $orcamento = $I->haveInDatabaseOrcamento($I, [
    //         'atc' => $atc['negocio'],
    //         'fornecedor' => $fornecedor,
    //         'composicao' => $composicao['composicao'],
    //         'propostaitem' => $propostaitem['propostaitem'],
    //         'faturar' => true
    //     ]);
    //     // $propostaitem['Servicoorcamento'] = [ 'Orcamento' => $orcamento];
    //     // $I->updateInDatabase('crm.propostasitens', ['servicoorcamento' => $orcamento['orcamento']], ['propostaitem' => $propostaitem['propostaitem']]);
    //     $familia = $I->haveInDatabaseFamilia($I);
    //     $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    //     $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    //     $formapagamento = $I->haveInDatabaseFormapagamento($I);
    //     $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1);
    //     $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
    //         'tipodivisao' => 1,
    //         'valorservico' => 10,
    //         'responsabilidadesfinanceirasvalores' => [
    //             [
    //                 'valorpagar' => 10,
    //                 'responsavelfinanceiro' => $cliente['cliente']
    //             ]
    //         ]
    //     ], $atc, $proposta, $propostaitem);

    //     $orcamentos = [
    //         'orcamento' => $I->generateUuidV4(),
    //         'fornecedor' => $fornecedor['fornecedor'],
    //         'propostaitemfamilia' => $propostaitemfamilia['propostaitemfamilia'],
    //         'propostaitemfuncao' => null,
    //         'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
    //         'propostaitem' => $propostaitem['propostaitem'],
    //         'valor' => 1,
    //         'valorreceber' => 1,
    //         'status' => 1,
    //         'acrescimo' => 0,
    //         'desconto' => 0,
    //         'acrescimomotivo' => null,
    //         'descontomotivo' => null,
    //         'motivo' => null,
    //         'created_at' => date('Y-m-d'),
    //         'created_by' => '{"nome":"usuario"}',
    //         'updated_by' => '{"nome":"usuario"}',
    //         'updated_at' => date('Y-m-d'),
    //         'faturar' => true,
    //         'tenant' => $this->tenant_numero
    //     ];

    //     $I->haveInDatabase('crm.orcamentos', $orcamentos);

    //     $dados = [
    //         'negocio' => $atc['negocio'],
    //         'nome' => $atc['nome'],
    //         'id_pessoa' => $cliente['cliente'],
    //         'municipioprestacao' => $municipioprestacao,
    //         'formapagamento' => $formapagamento,
    //         'tenant' => $this->tenant_numero,
    //         'contratante' => $cliente['cliente'],
    //         'proposta' => $proposta['proposta'],
    //         'propostaitem' => $propostaitem
    //     ];
    //     //Execução da funcionalidade
    //     $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

    //     //Validação do resultado
    //     $I->canSeeResponseCodeIs(HttpCode::OK);
    //     $I->updateInDatabase('crm.propostasitens', ['servicoorcamento' => null], ['propostaitem' => $propostaitem['propostaitem']]); //removendo constraint para não prender a deleção

    //     $contratosRecebimento = $I->grabColumnFromDatabase('financas.contratos', 'contrato', [
    //         'tipocontrato' => '1',
    //         'tenant' => $this->tenant_numero
    //     ]);
    //     $I->assertEquals(count($contratosRecebimento), 1);
    //     $contratoRecebimento = $contratosRecebimento[0];
    //     $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
    //         'tenant' => $this->tenant_numero,
    //         'contrato' => $contratoRecebimento,
    //         'responsavelfinanceiro' => $dados['contratante']
    //     ]);
    //     $I->canSeeInDatabase('financas.itenscontratos', [
    //         'tenant' => $this->tenant_numero,
    //         'contrato' => $contratoRecebimento,
    //         'valor' => 10 //Valor que está na tabela de rateio da responsabilidade financeira
    //     ]);
    // }

    /**
     * Gera contrato no finanças
     * @todo função que tenta criar contrato para um projeto que possui um contrato existente, verificar com mendonça tarefa para criar mais de um contrato
     *
     * @param FunctionalTester $I
     * @return void
     */
    // #REFATORACAO_FICHA_FINANCEIRA
    // public function criaContratoDeRecebimentoEPagamento(FunctionalTester $I){

    //     //Preparação do cenário
    //     $fornecedor = $I->haveInDatabaseFornecedor($I);
    //     $origem = $I->haveInDatabaseMidia($I);
    //     $area = $I->haveInDatabaseAreaDeAtc($I);
    //     $estado = $I->haveInDataBaseEstado($I);
    //     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    //     $pais = $I->haveInDatabasePais($I);
    //     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    //     $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
    //     $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
    //     $proposta = $I->haveInDatabaseProposta($I, $atc);
    //     $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
    //     $composicao = $I->haveinDatabaseComposicao($I);
    //     $orcamento = $I->haveInDatabaseOrcamento($I, [
    //         'atc' => $atc['negocio'],
    //         'fornecedor' => $fornecedor,
    //         'composicao' => $composicao['composicao'],
    //         'propostaitem' => $propostaitem['propostaitem'],
    //         'faturar' => true
    //     ]);
    //     // $propostaitem['servicoorcamento'] = $orcamento['orcamento'];
    //     // $I->updateInDatabase('crm.propostasitens', ['servicoorcamento' => $orcamento['orcamento']], ['propostaitem' => $propostaitem['propostaitem']]);
    //     $propostaitem['Servicoorcamento'] = [ 'Orcamento' => $orcamento];
    //     $familia = $I->haveInDatabaseFamilia($I);
    //     $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    //     $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    //     $formapagamento = $I->haveInDatabaseFormapagamento($I);
    //     $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1);
    //     $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
    //         'tipodivisao' => 1,
    //         'valorservico' => 15,
    //         'responsabilidadesfinanceirasvalores' => [
    //             [
    //                 'valorpagar' => 15,
    //                 'responsavelfinanceiro' => $cliente['cliente']
    //             ]
    //         ]
    //     ], $atc, $proposta, $propostaitem);

    //     $orcamentos = [
    //         'orcamento' => $I->generateUuidV4(),
    //         'fornecedor' => $fornecedor['fornecedor'],
    //         'propostaitemfamilia' => $propostaitemfamilia['propostaitemfamilia'],
    //         'propostaitemfuncao' => null,
    //         'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
    //         'propostaitem' => $propostaitem['propostaitem'],
    //         'valor' => 1,
    //         'valorreceber' => 1,
    //         'status' => 1,
    //         'acrescimo' => 0,
    //         'desconto' => 0,
    //         'acrescimomotivo' => null,
    //         'descontomotivo' => null,
    //         'motivo' => null,
    //         'created_at' => date('Y-m-d'),
    //         'created_by' => '{"nome":"usuario"}',
    //         'updated_by' => '{"nome":"usuario"}',
    //         'updated_at' => date('Y-m-d'),
    //         'faturar' => true,
    //         'tenant' => $this->tenant_numero
    //     ];
    //     $I->haveInDatabase('crm.orcamentos', $orcamentos);

    //     $dados = [
    //         'negocio' => $atc['negocio'],
    //         'nome' => $atc['nome'],
    //         'municipioprestacao' => $municipioprestacao,
    //         'formapagamento' => $formapagamento,
    //         'tenant' => $this->tenant_numero,
    //         'contratante' => $cliente['cliente'],
    //         'proposta' => $proposta['proposta'],
    //         'propostaitem' => $propostaitem
    //     ];

    //     //Execução da funcionalidade
    //     $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

    //     //Validação do resultado
    //     $I->canSeeResponseCodeIs(HttpCode::OK);
    //     $I->updateInDatabase('crm.propostasitens', ['servicoorcamento' => null], ['propostaitem' => $propostaitem['propostaitem']]); //removendo constraint para não prender a deleção

    //     $countAtual = $I->grabNumRecords('financas.contratos', ['tenant' => $this->tenant_numero]);
    //     $I->assertEquals($countAtual, 2); //Verificando que os dois contratos estão presentes

    //     //Validação do contrato de recebimento
    //     $contratosRecebimento = $I->grabColumnFromDatabase('financas.contratos', 'contrato', [
    //         'tipocontrato' => '1',
    //         'tenant' => $this->tenant_numero
    //     ]);
    //     $I->assertEquals(count($contratosRecebimento), 1);
    //     $contratoRecebimento = $contratosRecebimento[0];
    //     $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
    //         'tenant' => $this->tenant_numero,
    //         'contrato' => $contratoRecebimento,
    //         'responsavelfinanceiro' => $dados['contratante']
    //     ]);
    //     $I->canSeeInDatabase('financas.itenscontratos', [
    //         'tenant' => $this->tenant_numero,
    //         'contrato' => $contratoRecebimento,
    //         'valor' => 15
    //     ]);

    //     //Validação do contrato de pagamento
    //     $contratosPgto = $I->grabColumnFromDatabase('financas.contratos', 'contrato', [
    //         'tipocontrato' => '0',
    //         'tenant' => $this->tenant_numero
    //     ]);
    //     $I->assertEquals(count($contratosPgto), 1);
    //     $contratoPgto = $contratosPgto[0];
    //     $I->canSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
    //         'tenant' => $this->tenant_numero,
    //         'contratoapagar' => $contratoPgto,
    //         'responsavelfinanceiro' => $dados['contratante']
    //     ]);
    //     $I->canSeeInDatabase('financas.itenscontratos', [
    //         'tenant' => $this->tenant_numero,
    //         'contrato' => $contratoPgto,
    //         'valor' => $orcamento['valor']
    //     ]);

    // }
    /**
     * COMENTADO - Não permite a criação do Contrato se faltarem orçamentos, como o orçamento de propostaitem
     * @todo não deve ser erro 500 e sim outro erro
     */
    //   public function naoCriaContratoEmAtcSemOrcamento(FunctionalTester $I){

    //     //Preparação do cenário
    //     $fornecedor = $I->haveInDatabaseFornecedor($I);
    //     $origem = $I->haveInDatabaseMidia($I);
    //     $area = $I->haveInDatabaseAreaDeAtc($I);
    //     $estado = $I->haveInDataBaseEstado($I);
    //     $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    //     $pais = $I->haveInDatabasePais($I);
    //     $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    //     $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
    //     $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
    //     $proposta = $I->haveInDatabaseProposta($I, $atc);
    //     $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
    //     $familia = $I->haveInDatabaseFamilia($I);
    //     $composicao = $I->haveInDatabaseComposicao($I);
    //     $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    //     $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    //     $formapagamento = $I->haveInDatabaseFormapagamento($I);
    //     $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1);
    //     $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
    //         'tipodivisao' => 1,
    //         'valorservico' => 15,
    //         'responsabilidadesfinanceirasvalores' => [
    //             [
    //                 'valorpagar' => 15,
    //                 'responsavelfinanceiro' => $cliente['cliente']
    //             ]
    //         ]
    //     ], $atc, $proposta, $propostaitem);

    //     $orcamentos = [
    //       'orcamento' => $I->generateUuidV4(),
    //       'fornecedor' => $fornecedor['fornecedor'],
    //       'propostaitemfamilia' => $propostaitemfamilia['propostaitemfamilia'],
    //       'propostaitemfuncao' => null,
    //       'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
    //       'propostaitem' => $propostaitem['propostaitem'],
    //       'valor' => 1,
    //       'valorreceber' => 1,
    //       'status' => 1,
    //       'acrescimo' => 0,
    //       'desconto' => 0,
    //       'acrescimomotivo' => null,
    //       'descontomotivo' => null,
    //       'motivo' => null,
    //       'created_at' => date('Y-m-d'),
    //       'created_by' => '{"nome":"usuario"}',
    //       'updated_by' => '{"nome":"usuario"}',
    //       'updated_at' => date('Y-m-d'),
    //       'faturar' => true,
    //       'tenant' => $this->tenant_numero
    //     ];
    //     $I->haveInDatabase('crm.orcamentos', $orcamentos);

    //     $dados = [
    //         'negocio' => $atc['negocio'],
    //         'nome' => $atc['nome'],
    //         'id_pessoa' => $cliente['cliente'],
    //         'municipioprestacao' => $municipioprestacao,
    //         'formapagamento' => $formapagamento,
    //         'tenant' => $this->tenant_numero,
    //         'contratante' => $cliente['cliente'],
    //         'proposta' => $proposta['proposta'],
    //         'propostaitem' => $propostaitem
    //     ];
    //     //Execução da funcionalidade
    //     $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);
    //     //Validação do resultado
    //     $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    //     $I->cantSeeInDatabase('financas.contratos', ['id_formapagamento' => $formapagamento['formapagamento']]); 
    //   }
    

    /**
     * @param FunctionalTester $I
     * @return void
     */
    public function criaContratosComUmaResponsabilidade(FunctionalTester $I)
    {
        //Preparação do cenário
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $cliente = $I->haveInDatabaseCliente($I, ['conta' => $conta]);//$municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveInDatabaseComposicao($I, ['itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006']);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1, null, true, ['descontoglobal' => 100.01]);
        // $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, 1, ['numero' => 98]);
        // $contratoPgto = $I->haveInDatabaseContratoDePagamento($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, ['numero' => 99]);

        // $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 0); //nao faturado
        $orcamento = [
            'atc' => $atc['negocio'],
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'composicao' => $composicao['composicao'],
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
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->grupoempresarial_id
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);
        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'geranotafiscal' => false,
            'tipodivisao' => 1,
            'valorservico' => 15,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente['cliente'],
                    // 'contrato' => $contrato['contrato'],
                    // 'contratoapagar' => $contratoPgto['contrato']
                ]
            ]
        ], $atc, $orcamento);

        $dados = [
            // 'id_pessoa' => $cliente['cliente'],
            'municipioprestacao' => $municipioprestacao,
            'formapagamento' => $formapagamento,
            'contratante' => $cliente['cliente'],
        ];


        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);
        //Validação do resultado

        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->canSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'descontoglobalitensnaofaturados' => 100.01,
            'contrato' => $contratogerado['contrato']['contrato'],
        ]);
        $I->canSeeInDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato']['contrato'],
        ]);
        //limpando banco
        $I->updateInDatabase('crm.atcs', 
            [ //set
                'contrato' => null,
            ],
            [ //where
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]
        );
        $I->deleteAllFromDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato'],
        ]);
        $I->deleteAllFromDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato'],
        ]);
    }

    /**
     * @param FunctionalTester $I
     * @return void
     */
    public function criaContratosComDuasResponsabilidadesPorOrcamento(FunctionalTester $I)
    {
        //Preparação do cenário
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $cliente = $I->haveInDatabaseCliente($I, ['conta' => $conta]);//$municipio, $pais);
        $cliente2 = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveInDatabaseComposicao($I, ['itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006']);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1, null, true, ['descontoglobal' => 100.01]);
        // $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, 1, ['numero' => 98]);
        // $contratoPgto = $I->haveInDatabaseContratoDePagamento($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, ['numero' => 99]);

        // $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 0); //nao faturado
        $orcamento = [
            'atc' => $atc['negocio'],
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'composicao' => $composicao['composicao'],
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 300,
            'valorreceber' => 300,
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
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->grupoempresarial_id
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);
        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'geranotafiscal' => false,
            'tipodivisao' => 1,
            'valorservico' => 150,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 150,
                    'responsavelfinanceiro' => $cliente['cliente'],
                ],
                [
                    'valorpagar' => 150,
                    'responsavelfinanceiro' => $cliente2['cliente'],
                ]
            ]
        ], $atc, $orcamento);

        $dados = [
            // 'id_pessoa' => $cliente['cliente'],
            'municipioprestacao' => $municipioprestacao,
            'formapagamento' => $formapagamento,
            'contratante' => $cliente['cliente'],
        ];


        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);
        //Validação do resultado

        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->canSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'descontoglobalitensnaofaturados' => 50.01, //gerará primeiro o que possui resto
            'contrato' => $contratogerado['contrato']['contrato'],
        ]);

        $I->canSeeInDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato']['contrato'],
        ]);
        //limpando banco
        $I->updateInDatabase('crm.atcs', 
            [ //set
                'contrato' => null,
            ],
            [ //where
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]
        );
        $I->deleteAllFromDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato'],
        ]);
        $I->deleteAllFromDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato'],
        ]);
    }

    /**
     * @param FunctionalTester $I
     * @return void
     */
    public function criaContratosComDuasResponsabilidadesPorOrcamentoComContrato(FunctionalTester $I)
    {
        //Preparação do cenário
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $cliente = $I->haveInDatabaseCliente($I, ['conta' => $conta]);//$municipio, $pais);
        $cliente2 = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveInDatabaseComposicao($I, ['itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006']);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1, null, true, ['descontoglobal' => 100.01]);
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, 1, ['numero' => 98, 'descontoglobalitensnaofaturados' => 50]);
        // $contratoPgto = $I->haveInDatabaseContratoDePagamento($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, ['numero' => 99]);

        // $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 0); //nao faturado
        $orcamento = [
            'atc' => $atc['negocio'],
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'composicao' => $composicao['composicao'],
            'propostaitemfuncao' => null,
            'itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006',
            'propostaitem' => $propostaitem['propostaitem'],
            'valor' => 300,
            'valorreceber' => 300,
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
            'tenant' => $this->tenant_numero,
            'grupoempresarial' => $this->grupoempresarial_id
        ];
        $I->haveInDatabase('crm.orcamentos', $orcamento);
        $responsabilidadefinanceira = $I->haveInDatabaseResponsabilidadeFinanceira($I, [
            'geranotafiscal' => false,
            'tipodivisao' => 1,
            'valorservico' => 150,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 150,
                    'responsavelfinanceiro' => $cliente['cliente'],
                ],
                [
                    'valorpagar' => 150,
                    'responsavelfinanceiro' => $cliente2['cliente'],
                    'contrato' => $contrato['contrato'],
                ]
            ]
        ], $atc, $orcamento);

        $dados = [
            // 'id_pessoa' => $cliente['cliente'],
            'municipioprestacao' => $municipioprestacao,
            'formapagamento' => $formapagamento,
            'contratante' => $cliente['cliente'],
        ];


        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);
        //Validação do resultado

        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->canSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'descontoglobalitensnaofaturados' => 50.01,
            // 'contrato' => $contratogerado['contrato']['contrato'],
        ]);
        $I->canSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'descontoglobalitensnaofaturados' => 50.00,
            // 'contrato' => $contratogerado['contrato']['contrato'],
        ]);

        $I->canSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'descontoglobalitensnaofaturados' => 50,
            // 'contrato' => $contratogerado['contrato']['contrato'],
        ]);

        $I->canSeeInDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato']['contrato'],
        ]);
        //limpando banco
        $I->updateInDatabase('crm.atcs', 
            [ //set
                'contrato' => null,
            ],
            [ //where
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]
        );
        $I->deleteAllFromDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato'],
        ]);
        $I->deleteAllFromDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratogerado['contrato'],
        ]);
    }

    /**
     * Exclui contratos no finanças de pagamento e recebimento, cujo item de contrato não está faturado,
     * de acordo com o contratante passado
     * @param FunctionalTester $I
     * @return void
     */
    public function excluiContratos(FunctionalTester $I)
    {
        //Preparação do cenário
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveInDatabaseComposicao($I, ['itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006']);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1);
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, 1, ['numero' => 98]);
        $contratoPgto = $I->haveInDatabaseContratoDePagamento($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id, ['numero' => 99]);

        // $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 0); //nao faturado
        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => $propostaitemfamilia['propostaitemfamilia'],
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
            'valorservico' => 15,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente['cliente'],
                    'contrato' => $contrato['contrato'],
                    'contratoapagar' => $contratoPgto['contrato']
                ]
            ]
        ], $atc, $orcamento);
        $dados = [
            'contratante' => $cliente['cliente'],
        ];

        //Execução da funcionalidade
        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/excluiContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);
        //Validação do resultado
        $I->canSeeResponseCodeIs(HttpCode::NO_CONTENT);
        // Não pode ver contrato de recebimento
        $I->cantSeeInDatabase('financas.contratos', ['contrato' => $contrato['contrato']]);
        // Não pode ver contrato de pagamento
        $I->cantSeeInDatabase('financas.contratos', ['contrato' => $contratoPgto['contrato']]);
        $I->cantSeeInDatabase('financas.contratos', ['tipocontrato' => 0]);
        $countAtual = $I->grabNumRecords('financas.contratos', ['tenant' => $this->tenant_numero]);
        // Não pode ver em responsabilidade financeira valores a relação com contrato de recebimento
        $I->cantSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'contrato' => $contrato['contrato']
        ]);
        // Não pode ver em responsabilidade financeira valores a relação com contrato de pagamento
        $I->cantSeeInDatabase('crm.responsabilidadesfinanceirasvalores', [
            'contratoapagar' => $contratoPgto['contrato']
        ]);

        $I->assertEquals($countAtual, 0); //Verificando que não tem nenhum contrato presente
    }

    /**
     * não exclui contrato no finanças porque tem item faturado
     * @param FunctionalTester $I
     * @return void
     */
    public function nãoExcluiContratoQueTemItemFaturado(FunctionalTester $I)
    {
        //Preparação do cenário
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);
        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveInDatabaseComposicao($I, ['itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006']);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, 1);
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id);
        $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $atc, 1);

        // $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 0); //nao faturado
        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => $propostaitemfamilia['propostaitemfamilia'],
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
            'valorservico' => 15,
            'responsabilidadesfinanceirasvalores' => [
                [
                    'valorpagar' => 15,
                    'responsavelfinanceiro' => $cliente['cliente'],
                    'contrato' => $contrato['contrato'],
                ]
            ]
        ], $atc, $orcamento);

        $dados = [
            'negocio' => $atc,
            'tenant' => $this->tenant_numero,
            'created_by' => '{"nome":"usuario"}'
        ];
        //Execução da funcionalidade
        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/excluiContrato?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $contrato, [], [], null);
        //Validação do resultado
        $I->canSeeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /**
     * Retorna proposta item
     * @param FunctionalTester $I
     * @return void
     */
    public function getPropostaItem(FunctionalTester $I)
    {
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

        /* execução da funcionalidade */
        $propostaitemrecebida = $I->sendRaw('GET', "/api/{$this->tenant}/{$atc['negocio']}/{$proposta['proposta']}/propostasitens/{$propostaitem['propostaitem']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->assertEquals($propostaitemrecebida['nome'], $propostaitem['nome']);
        $I->assertEquals($propostaitemrecebida['descricao'], $propostaitem['descricao']);
        $I->assertEquals($propostaitemrecebida['codigo'], $propostaitem['codigo']);
        $I->assertEquals($propostaitemrecebida['valor'], $propostaitem['valor']);
        $I->assertEquals($propostaitemrecebida['negocio']['negocio'], $propostaitem['negocio']['negocio']);
        $I->assertEquals($propostaitemrecebida['fornecedor']['fornecedor'], $propostaitem['fornecedor']);
        $I->assertEquals($propostaitemrecebida['tenant'], $propostaitem['tenant']);
    }

    /**
     * COMENTADO - adaptação do teste ao novo modelo de contrato a ser realizado na tarefa 37330
     * adiciona item contrato
     * @param FunctionalTester $I
     * @return void
     */
    // public function addItemContrato(FunctionalTester $I){
    //   //Preparação do cenário
    //   $origem = $I->haveInDatabaseMidia($I);
    //   $area = $I->haveInDatabaseAreaDeNegocio($I);
    //   $estado = $I->haveInDataBaseEstado($I);
    //   $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    //   $pais = $I->haveInDatabasePais($I);
    //   $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    //   $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
    //   $formapagamento = $I->haveInDatabaseFormapagamento($I);
    //   $negocio = $I->haveInDatabaseNegocio($I, $area, $origem, $cliente);
    //   $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $negocio, $this->grupoempresarial_id);
    //   $proposta = $I->haveInDatabaseProposta($I, $negocio);
    //   $propostaitem = $I->haveInDatabasePropostaItem($I, $negocio, $proposta);
    //   $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 1); //faturado

    //   $dados = [
    //       "proposta" => $proposta['proposta'],
    //       "propostacapitulo" => $proposta['propostacapitulo'],
    //       "composicao" => ['composicao'=>"adc67791-c178-47f0-81e8-522e2864c3b2"],
    //       "nome" => "velorio",
    //       "created_by" => "logged_user",
    //       "tenant" => '47',
    //       "valor" => 1,
    //       'itemdefaturamentovalor' => 1,
    //       'negocio' => $negocio['negocio'],
    //       'especificarendereco' => 0
    //   ];

    //   //Execução da funcionalidade
    //   $propostaitemnova = $I->sendRaw('POST', "/api/{$this->tenant}/{$negocio['negocio']}/{$proposta['proposta']}/propostasitens/?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

    //   //Validação do resultado
    //   $I->canSeeResponseCodeIs(HttpCode::CREATED);

    //   $I->canSeeInDatabase('financas.itenscontratos',
    //     [
    //       'contrato' => $contrato['contrato'],
    //       'situacaofaturamento' => 0
    //     ]      
    //   );

    //   $I->assertNotNull($propostaitemnova['itemcontrato']);
    //   $I->assertEquals($propostaitemnova['escolhacliente'], false);
    // }

    /**
     * Define escolha do cliente verdade na composição do pedido
     * @param FunctionalTester $I
     * @return void
     */
    public function DefineEscolhaDoClienteVerdadeNaComposicaoDoPedido(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);

        $dados = [
            "propostaitem" => $propostaitem['propostaitem'],
            "escolhacliente" => "true"
        ];


        //Execução da funcionalidade
        $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$proposta['proposta']}/propostasitens/{$propostaitem['propostaitem']}/propostasitensfornecedorescolhacliente?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->seeInDatabase(
            'crm.propostasitens',
            [
                'propostaitem' => $propostaitem['propostaitem'],
                'tenant' => $this->tenant_numero,
                'escolhacliente' => "true"
            ]
        );
    }


    /**
     * Define escolha do cliente falsa na composição do pedido
     * @param FunctionalTester $I
     * @return void
     */
    public function DefineEscolhaDoClienteFalsaNaComposicaoDoPedido(FunctionalTester $I)
    {
        /* inicializações */
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);

        $dados = [
            "propostaitem" => $propostaitem['propostaitem'],
            "escolhacliente" => "false"
        ];

        //Execução da funcionalidade
        $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$proposta['proposta']}/propostasitens/{$propostaitem['propostaitem']}/propostasitensfornecedorescolhacliente?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);

        /* validação do resultado */
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->seeInDatabase(
            'crm.propostasitens',
            [
                'propostaitem' => $propostaitem['propostaitem'],
                'tenant' => $this->tenant_numero,
                'escolhacliente' => "false"
            ]
        );
    }

    /**
     * COMENTADO - adaptação do teste ao novo modelo de contrato a ser realizado na tarefa 37330
     * exclui item contrato
     * @param FunctionalTester $I
     * @return void
     */
    // public function excluiItemContrato(FunctionalTester $I){
    //   //Preparação do cenário
    //   $origem = $I->haveInDatabaseMidia($I);
    //   $area = $I->haveInDatabaseAreaDeNegocio($I);
    //   $estado = $I->haveInDataBaseEstado($I);
    //   $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    //   $pais = $I->haveInDatabasePais($I);
    //   $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    //   $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
    //   $formapagamento = $I->haveInDatabaseFormapagamento($I);
    //   $negocio = $I->haveInDatabaseNegocio($I, $area, $origem, $cliente);
    //   $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $negocio, $this->grupoempresarial_id);
    //   $proposta = $I->haveInDatabaseProposta($I, $negocio);
    //   $propostaitem = $I->haveInDatabasePropostaItem($I, $negocio, $proposta);
    //   $itemcontrato1 =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, '0'); //não se pode excluir o único item do contrato
    //   $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, '0'); //não faturado

    //   //Execução da funcionalidade
    //   $I->sendRaw('DELETE', "/api/{$this->tenant}/{$negocio['negocio']}/{$proposta['proposta']}/propostasitens/{$propostaitem['propostaitem']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

    //   //Validação do resultado
    //   $I->canSeeResponseCodeIs(HttpCode::OK);

    //   $I->cantSeeInDatabase('financas.itenscontratos',
    //       [
    //         'itemcontrato' => $itemcontrato['itemcontrato'],
    //         'tenant' => $this->tenant_numero
    //       ]      
    //     );
    //   $I->cantSeeInDatabase('crm.propostasitens',
    //       [
    //         'propostaitem' => $propostaitem['propostaitem'],
    //         'tenant' => $this->tenant_numero
    //       ]      
    //     );

    //    }

    /**
     * nao exclui item contrato se estiver faturado
     * @param FunctionalTester $I
     * @return void
     */
    public function naoExcluiItemContratoQuandoFaturado(FunctionalTester $I)
    {
        //Preparação do cenário
        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta);

        $familia = $I->haveInDatabaseFamilia($I);
        $composicao = $I->haveInDatabaseComposicao($I, ['itemfaturamento' => 'baa4ff9a-6f59-4963-853d-6d318fe83006']);
        $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
        $propostaitemfamilia = $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
        $fornecedor = $I->haveInDatabaseFornecedor($I);

        // $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $negocio, 0); //nao faturado
        $orcamento = [
            'orcamento' => $I->generateUuidV4(),
            'fornecedor' => $fornecedor['fornecedor'],
            'propostaitemfamilia' => $propostaitemfamilia['propostaitemfamilia'],
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
                    'responsavelfinanceiro' => $cliente['cliente'],
                    'contrato' => $contrato['contrato']
                ]
            ]
        ], $atc, $orcamento);
        $itemcontrato1 =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $atc, '0'); //não se pode excluir o único item do contrato
        $itemcontrato =  $I->haveInDatabaseItemContrato($I, $contrato, $propostaitem, $atc, 1); //faturado

        //Execução da funcionalidade
        $I->sendRaw('DELETE', "/api/{$this->tenant}/{$atc['negocio']}/{$proposta['proposta']}/propostasitens/{$propostaitem['propostaitem']}?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}", [], [], [], null);

        //Validação do resultado
        $I->canSeeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);

        $I->canSeeInDatabase(
            'financas.itenscontratos',
            [
                'itemcontrato' => $itemcontrato['itemcontrato'],
                'tenant' => $this->tenant_numero
            ]
        );
        $I->canSeeInDatabase(
            'crm.propostasitens',
            [
                'propostaitem' => $propostaitem['propostaitem'],
                'tenant' => $this->tenant_numero
            ]
        );
    }

    /**
     * @param FunctionalTester $I
     * @return void
     */
    public function criaContratoTaxaAdministrativa(FunctionalTester $I){
        //Preparação do cenário
        $estabelecimento = ['estabelecimento' => $this->estabelecimento];

        $conta = $I->haveInDatabaseConta($I, 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f', '0001');
        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 1000, 'conta' => $conta]);

        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        // $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $cliente = ['cliente' => $configuracaotaxaadm['seguradora']];
        // $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $municipioprestacao = ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']];
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, ['cliente' => $configuracaotaxaadm['seguradora']]);

        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $dadosSeguradora = $I->haveInDatabaseDadosParaSeguradora($I, $atc, $produtoseguradora, $apolice, $vinculo);

        $dados = [
            'contratante' => $cliente['cliente'],
            'configuracaotaxaadm' => ['configuracaotaxaadm' => $configuracaotaxaadm['configuracaotaxaadm']],
            'formapagamentotaxaadm' => ['formapagamento' => $configuracaotaxaadm['formapagamento']],
            'municipioprestacaotaxaadm' => ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']],
            'pessoa' => $configuracaotaxaadm['seguradora'],
            'valortaxaadm' => $configuracaotaxaadm['valor'],
        ];

        //Execução da funcionalidade
        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/geraContratoTaxaAdministrativa?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        //Validação do resultado
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $contratosRecebimento = $I->grabColumnFromDatabase('financas.contratos', 'contrato', [
            'tipocontrato' => '1',
            'tenant' => $this->tenant_numero
        ]);
        $I->assertEquals(count($contratosRecebimento), 1);
        $contratoRecebimento = $contratosRecebimento[0];
        $I->canSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratoRecebimento,
        ]);
        $I->canSeeInDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratoRecebimento,
            'valor' => $configuracaotaxaadm['valor'] //Valor que está na tabela de rateio da responsabilidade financeira
        ]);
        $I->canSeeInDatabase('crm.atcs', [
            'negocio' => $atc['negocio'],
            'contratotaxaadm' => $contratoRecebimento,
            'tenant' => $this->tenant_numero
        ]);
        //limpando banco
        $I->updateInDatabase('crm.atcs', 
            [ //set
                'contratotaxaadm' => null,
                'formapagamentotaxaadm' => null,
                'municipioprestacaotaxaadm' => null,
                'valortaxaadm' => null,
            ],
            [ //where
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]
        );
        $I->deleteAllFromDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratoRecebimento,
            'valor' => $configuracaotaxaadm['valor'] //Valor que está na tabela de rateio da responsabilidade financeira
        ]);
        $I->deleteAllFromDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contratoRecebimento,
        ]);
    }

    /**
     * @param FunctionalTester $I
     * @return void
     */
    public function salvaContratoTaxaAdministrativa(FunctionalTester $I){
        //Preparação do cenário
        $estabelecimento = ['estabelecimento' => $this->estabelecimento];
        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 1000]);

        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        // $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $cliente = ['cliente' => $configuracaotaxaadm['seguradora']];
        // $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $municipioprestacao = ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']];
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, ['cliente' => $configuracaotaxaadm['seguradora']]);

        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $dadosSeguradora = $I->haveInDatabaseDadosParaSeguradora($I, $atc, $produtoseguradora, $apolice, $vinculo);

        $dados = [
            'contratante' => $cliente['cliente'],
            'configuracaotaxaadm' => ['configuracaotaxaadm' => $configuracaotaxaadm['configuracaotaxaadm']],
            'formapagamentotaxaadm' => ['formapagamento' => $configuracaotaxaadm['formapagamento']],
            'municipioprestacaotaxaadm' => ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']],
            'pessoa' => $configuracaotaxaadm['seguradora'],
            'valortaxaadm' => $configuracaotaxaadm['valor'],
        ];

        //Execução da funcionalidade
        $contratogerado = $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/salvaContratoTaxaAdministrativa?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        //Validação do resultado
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->canSeeInDatabase('crm.atcs', [
            'negocio' => $atc['negocio'],
            'municipioprestacaotaxaadm' => $configuracaotaxaadm['municipioprestacao'],
            'formapagamentotaxaadm' => $configuracaotaxaadm['formapagamento'],
            'valortaxaadm' => $configuracaotaxaadm['valor'],
            'tenant' => $this->tenant_numero
        ]);
    }

    /**
     * @param FunctionalTester $I
     * @return void
     */
    public function excluiContratoTaxaAdministrativa(FunctionalTester $I){
        //Preparação do cenário
        $estabelecimento = ['estabelecimento' => $this->estabelecimento];
        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 1000]);

        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        // $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $cliente = ['cliente' => $configuracaotaxaadm['seguradora']];
        // $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);
        $municipioprestacao = ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']];
        $formapagamento = ['formapagamento' => $configuracaotaxaadm['formapagamento']];
        
        //criar atc
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, ['cliente' => $configuracaotaxaadm['seguradora']]);
        $atc['cliente']['diasparavencimento'] = 4;

        $produtoseguradora = $I->haveInDatabaseTemplatepropostagrupo($I, $cliente);
        $apolice = $I->haveInDatabaseTemplateproposta($I, $produtoseguradora);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $dadosSeguradora = $I->haveInDatabaseDadosParaSeguradora($I, $atc, $produtoseguradora, $apolice, $vinculo);

        //criar contrato e item contrato
        $contrato = $I->haveInDatabaseContrato($I, $formapagamento, $municipioprestacao, $atc, $this->grupoempresarial_id);
        $itemcontrato = $I->haveInDatabaseItemContrato($I, $contrato, ['valor' => $configuracaotaxaadm['valor']], $atc, 0);

        $I->updateInDatabase('crm.atcs', 
            [ //set
                'contratotaxaadm' => $contrato['contrato'],
                'formapagamentotaxaadm' => $contrato['id_formapagamento'],
                'municipioprestacaotaxaadm' => $contrato['pessoamunicipio'],
                'valortaxaadm' => $itemcontrato['valor'],
            ],
            [ //where
                'negocio' => $atc['negocio'],
                'tenant' => $this->tenant_numero
            ]
        );

        $dados = [
            'negocio' => $atc['negocio'],
            'nome' => $atc['nome'],
            'tenant' => $this->tenant_numero,
            'contratante' => $cliente['cliente'],
            'configuracaotaxaadm' => ['configuracaotaxaadm' => $configuracaotaxaadm['configuracaotaxaadm']],
            'formapagamentotaxaadm' => ['formapagamento' => $configuracaotaxaadm['formapagamento']],
            'municipioprestacaotaxaadm' => ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']],
            'pessoa' => $configuracaotaxaadm['seguradora'],
            'valortaxaadm' => $configuracaotaxaadm['valor'],
        ];

        //Execução da funcionalidade
        $I->sendRaw('POST', "/api/{$this->tenant}/atcs/{$atc['negocio']}/excluiContratoTaxaAdministrativa?tenant={$this->tenant}&grupoempresarial={$this->grupoempresarial}" , $dados, [], [], null);

        //Validação do resultado
        $I->canSeeResponseCodeIs(HttpCode::OK);

        $I->cantSeeInDatabase('financas.contratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contrato['contrato'],
        ]);
        $I->cantSeeInDatabase('financas.itenscontratos', [
            'tenant' => $this->tenant_numero,
            'contrato' => $contrato['contrato'],
            'valor' => $itemcontrato['valor'] //Valor que está na tabela de rateio da responsabilidade financeira
        ]);
        $I->cantSeeInDatabase('crm.atcs', [
            'negocio' => $atc['negocio'],
            'contratotaxaadm' => $contrato['contrato'],
            'tenant' => $this->tenant_numero
        ]);
        $I->canSeeInDatabase('crm.atcs', [
            'negocio' => $atc['negocio'],
            'contratotaxaadm' => null,
            'tenant' => $this->tenant_numero
        ]);
    }
}
