<?php

use Codeception\Util\HttpCode;
use Doctrine\Common\Annotations\Annotation\Enum;
use Nasajon\AppBundle\Enum\EnumAcao;

/**
 * Testa permissões dos usuários para cada área do sistema
 */
class PermissaoCest{

    /**
     * Lista de ações permitidas:
     * EnumAcao::ATCS_INDEX
     * EnumAcao::ATCS_CREATE
     * EnumAcao::ATCS_PUT
     * EnumAcao::ATCS_GET
     * EnumAcao::FORNECEDORESENVOLVIDOS_CREATE
     * EnumAcao::FORNECEDORESENVOLVIDOS_DELETE
     * EnumAcao::FORNECEDORES_INDEX
     * EnumAcao::FORNECEDORES_CREATE
     * EnumAcao::FORNECEDORES_SUSPENDER
     * EnumAcao::FORNECEDORES_ADVERTIR
     * EnumAcao::FORNECEDORES_REATIVAR
     * EnumAcao::FORNECEDORESSUSPENSOS_INDEX
     * EnumAcao::CLIENTES_INDEX
     * EnumAcao::CLIENTES_CREATE
     * EnumAcao::CLIENTES_PUT
     * EnumAcao::COMPOSICOES_INDEX
     * EnumAcao::COMPOSICOES_CREATE
     * EnumAcao::COMPOSICOES_PUT
     * EnumAcao::DOCUMENTOS_CREATE
     * EnumAcao::DOCUMENTOS_PUT
     * EnumAcao::VINCULOS_CREATE
     * EnumAcao::VINCULOS_PUT
     * EnumAcao::MIDIAS_CREATE
     * EnumAcao::MIDIAS_PUT
     * EnumAcao::ATCSAREAS_CREATE
     * EnumAcao::ATCSAREAS_PUT
     * EnumAcao::ORCAMENTOS_CREATE
     * EnumAcao::ORCAMENTOS_APROVAR
     * EnumAcao::ORCAMENTOS_ENVIAR
     * EnumAcao::UNIDADES_INDEX
     * EnumAcao::UNIDADES_CREATE
     * EnumAcao::UNIDADES_PUT
     * EnumAcao::TIPOSATIVIDADES_INDEX
     * EnumAcao::TIPOSATIVIDADES_CREATE
     * EnumAcao::TIPOSATIVIDADES_PUT
     * EnumAcao::FORNECEDORES_GET
     * EnumAcao::DOCUMENTOS_INDEX
     * EnumAcao::VINCULOS_INDEX
     * EnumAcao::MIDIAS_INDEX
     * EnumAcao::ATCSAREAS_INDEX
     * EnumAcao::UNIDADES_INDEX,
     * EnumAcao::UNIDADES_CREATE,
     * EnumAcao::UNIDADES_PUT,
     * EnumAcao::TIPOSATIVIDADES_INDEX,
     * EnumAcao::TIPOSATIVIDADES_CREATE,
     * EnumAcao::TIPOSATIVIDADES_PUT,
     * EnumAcao::ADVERTENCIAS_ARQUIVAR,
     * EnumAcao::ADVERTENCIAS_EXCLUIR,
     * EnumAcao::PROPOSTASCAPITULOS_CREATE,
     * EnumAcao::PROPOSTASCAPITULOS_PUT,
     * EnumAcao::PROPOSTASCAPITULOS_DELETE,
     * EnumAcao::PROPOSTASITENS_CREATE,
     * EnumAcao::PROPOSTASITENS_PUT,
     * EnumAcao::PROPOSTASITENS_DELETE,
     * EnumAcao::PROPOSTASITENS_GET,
     * EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR,
     * EnumAcao::PROPOSTASITENSFUNCOES_CREATE,
     * EnumAcao::PROPOSTASITENSFUNCOES_PUT,
     * EnumAcao::PROPOSTASITENSFUNCOES_DELETE,
     * EnumAcao::PROPOSTASITENSFAMILIAS_CREATE,
     * EnumAcao::FUNCOES_INDEX,
     * EnumAcao::FUNCOES_CREATE,
     * EnumAcao::FUNCOES_PUT,
     * EnumAcao::FUNCOES_GET,
     * EnumAcao::FUNCOES_DELETE
     * EnumAcao::TIPOSACIONAMENTOS_INDEX,
     * EnumAcao::TIPOSACIONAMENTOS_GET,
     * EnumAcao::TIPOSACIONAMENTOS_CREATE,
     * EnumAcao::TIPOSACIONAMENTOS_PUT,
     * EnumAcao::TIPOSACIONAMENTOS_DELETE,
     * EnumAcao::HISTORICOSPADRAO_INDEX,
     * EnumAcao::HISTORICOSPADRAO_GET,
     * EnumAcao::HISTORICOSPADRAO_CREATE,
     * EnumAcao::HISTORICOSPADRAO_PUT,
     * EnumAcao::HISTORICOSPADRAO_DELETE,
     * EnumAcao::MALOTES_INDEX,
     * EnumAcao::MALOTES_CREATE,
     * EnumAcao::MALOTES_PUT,
     * EnumAcao::MALOTES_ENVIAR,
     * EnumAcao::MALOTES_CANCELARENVIO,
     * EnumAcao::MALOTES_APROVAR,
     * EnumAcao::NEGOCIOS_INDEX,
     * EnumAcao::NEGOCIOS_GET,
     * EnumAcao::NEGOCIOS_CREATE,
     * EnumAcao::NEGOCIOS_PUT,
     * EnumAcao::NEGOCIOS_DELETE,
     * EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO,
     * EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO,
     * EnumAcao::FOLLOWUPSNEGOCIOS_INDEX,
     * EnumAcao::FOLLOWUPSNEGOCIOS_CREATE,
     * EnumAcao::SEGMENTOSATUACAO_INDEX,
     * EnumAcao::SEGMENTOSATUACAO_GET,
     * EnumAcao::SEGMENTOSATUACAO_CREATE,
     * EnumAcao::SEGMENTOSATUACAO_PUT,
     * EnumAcao::SEGMENTOSATUACAO_DELETE,
     * EnumAcao:: SITUACOESPRENEGOCIOS_INDEX,
     * EnumAcao:: SITUACOESPRENEGOCIOS_GET,
     * EnumAcao:: SITUACOESPRENEGOCIOS_CREATE,
     * EnumAcao:: SITUACOESPRENEGOCIOS_PUT,
     * EnumAcao:: SITUACOESPRENEGOCIOS_DELETE,
     * EnumAcao:: PROMOCOESLEADS_INDEX,
     * EnumAcao:: PROMOCOESLEADS_GET,
     * EnumAcao:: PROMOCOESLEADS_CREATE,
     * EnumAcao:: PROMOCOESLEADS_PUT,
     * EnumAcao:: PROMOCOESLEADS_DELETE
     * EnumAcao::CIDADESINFORMACOESFUNERARIAS_INDEX,
     * EnumAcao::CIDADESINFORMACOESFUNERARIAS_GET,
     * EnumAcao::CIDADESINFORMACOESFUNERARIAS_CREATE,
     * EnumAcao::CIDADESINFORMACOESFUNERARIAS_PUT
     * EnumAcao::PRIORIDADES_INDEX,
     * EnumAcao::PRIORIDADES_GET,
     * EnumAcao::PRIORIDADES_CREATE,
     * EnumAcao::PRIORIDADES_PUT,
     * EnumAcao::PRIORIDADES_DELETE
     * EnumAcao::CFGTAXASADM_GERENCIAR
     * EnumAcao::CONTRATOSTAXASADM_GERENCIAR
     * EnumAcao::ATCSCONTASPAGAR_GERENCIAR
     */

    private $tenant = "gednasajon";
    private $tenant_numero = "47";
    private $grupoempresarial = 'FMA';
    private $grupoempresarial_id = '95cd450c-30c5-4172-af2b-cdece39073bf';
    private $estabelecimento = 'b7ba5398-845d-4175-9b5b-96ddcb5fed0f';
    private $servicotecnico = '37ea071a-c2cd-4dba-87e8-5300a5be7af3';
    private $url_atcs = '/api/gednasajon/atcs/';
    private $url_fornecedoresenvolvidos = 'fornecedoresenvolvidos';
    private $url_fornecedores = '/api/gednasajon/fornecedores/';
    private $url_fornecedoressuspensos = '/api/gednasajon/fornecedoressuspensos/';
    private $url_clientes = '/api/gednasajon/clientes/';
    private $url_composicoes = '/api/gednasajon/composicoes/';
    private $url_documentos = '/api/gednasajon/tiposdocumentos/';
    private $url_vinculos = '/api/gednasajon/vinculos/';
    private $url_midias = '/api/gednasajon/midias/';
    private $url_atcsareas = '/api/gednasajon/atcsareas/';
    private $url_orcamentos = '/api/gednasajon/orcamentos/';
    private $url_funcoes = '/api/gednasajon/funcoes/';
    private $url_advertencias = '/api/gednasajon/advertencias/';
    private $url_tiposatividades = '/api/gednasajon/tiposatividades/';
    private $url_unidades = '/api/gednasajon/unidades/';
    private $url_tiposacionamentos = '/api/gednasajon/tiposacionamentos/';
    private $url_historicospadrao = '/api/gednasajon/historicospadrao/';
    private $url_malotes = '/api/gednasajon/malotes/';
    private $url_negocios = '/api/gednasajon/negocios/';
    private $url_followupsnegocios = '/api/gednasajon/followupsnegocios/';
    private $url_negocioscontatos = '/negocioscontatos/';
    private $url_segmentos = '/api/gednasajon/segmentosatuacao/';
    private $url_situacoes = '/api/gednasajon/situacoesprenegocios/';
    private $url_promocoes = '/api/gednasajon/promocoesleads/';
    private $url_listadavezvendedores = '/api/gednasajon/listadavezvendedores/';
    private $url_listadavezconfiguracoes = '/api/gednasajon/lista-da-vez-configuracoes/';
    private $url_painelmarketing = '/api/gednasajon/paineis/marketing/';
    private $url_cidadesinformacoesfunerarias = '/api/gednasajon/cidadesinformacoesfunerarias/';
    private $url_prioridades = '/api/gednasajon/prioridades/';
    private $url_contaspagar = '/api/gednasajon/{atc}/atcscontasapagar/';

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
        $I->deleteAllFromDatabase('crm.atcscontasapagar');
        $I->deleteAllFromDatabase('crm.orcamentos');
        $I->deleteAllFromDatabase('crm.propostasitensfamilias');
        $I->deleteAllFromDatabase('crm.propostasitensfuncoes');
        $I->deleteAllFromDatabase('crm.propostasitens');
        $I->deleteAllFromDatabase('crm.propostascapitulos');
        $I->deleteAllFromDatabase('crm.propostas');
        $I->deleteAllFromDatabase('crm.malotes');
        $I->deleteAllFromDatabase('crm.atcs');
        $I->deleteAllFromDatabase('financas.itenscontratos');
        $I->deleteAllFromDatabase('financas.contratos');
        $I->deleteAllFromDatabase('financas.projetos');
        
        $I->deleteAllFromDatabase('crm.historicosnegocios');
        $I->deleteAllFromDatabase('crm.negociospropostasvendedores');
        $I->deleteAllFromDatabase('crm.negociostelefones');
        $I->deleteAllFromDatabase('crm.negocioscontatos');
        $I->deleteAllFromDatabase('crm.negocios');
        $I->deleteAllFromDatabase('crm.segmentosatuacao');
        $I->deleteAllFromDatabase('crm.midiasorigem');

    }
    /**
     * @return array
     */
    protected function UsuarioProvider()
    {
        return [
            [
                'usuario' => 'usuario@nasajon.com.br',
                'permissoes' => [EnumAcao::ATCS_INDEX, EnumAcao::ATCS_PUT, EnumAcao::FORNECEDORESENVOLVIDOS_CREATE,
                                EnumAcao::FORNECEDORES_INDEX, EnumAcao::FORNECEDORES_PUT, EnumAcao::FORNECEDORES_ADVERTIR,
                                EnumAcao::CLIENTES_INDEX, EnumAcao::CLIENTES_PUT, EnumAcao::COMPOSICOES_CREATE, EnumAcao::DOCUMENTOS_PUT,
                                EnumAcao::VINCULOS_PUT, EnumAcao::MIDIAS_PUT, EnumAcao::ATCSAREAS_PUT,
                                EnumAcao::ORCAMENTOS_ENVIAR, EnumAcao::ORCAMENTOS_REABRIR, EnumAcao::FUNCOES_INDEX, EnumAcao::FUNCOES_PUT, EnumAcao::FUNCOES_GET, 
                                EnumAcao::ADVERTENCIAS_ARQUIVAR, EnumAcao::TIPOSATIVIDADES_INDEX, EnumAcao::TIPOSATIVIDADES_PUT, EnumAcao::UNIDADES_INDEX,  EnumAcao::UNIDADES_PUT,
                                EnumAcao::TIPOSACIONAMENTOS_INDEX, EnumAcao::TIPOSACIONAMENTOS_PUT, EnumAcao::HISTORICOSPADRAO_INDEX, EnumAcao::HISTORICOSPADRAO_PUT,
                                EnumAcao::MALOTES_INDEX, EnumAcao::MALOTES_PUT, EnumAcao::MALOTES_CANCELARENVIO, EnumAcao::NEGOCIOS_INDEX, EnumAcao::NEGOCIOS_CREATE,
                                EnumAcao::NEGOCIOS_DELETE, EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO, EnumAcao:: FOLLOWUPSNEGOCIOS_INDEX, EnumAcao::SEGMENTOSATUACAO_INDEX,
                                EnumAcao::SEGMENTOSATUACAO_CREATE, EnumAcao::SEGMENTOSATUACAO_DELETE, EnumAcao:: SITUACOESPRENEGOCIOS_INDEX, EnumAcao:: SITUACOESPRENEGOCIOS_CREATE,
                                EnumAcao:: SITUACOESPRENEGOCIOS_DELETE, EnumAcao::PROMOCOESLEADS_INDEX, EnumAcao::PROMOCOESLEADS_CREATE, EnumAcao::PROMOCOESLEADS_DELETE,
                                EnumAcao:: LISTADAVEZVENDEDORES_INDEX, EnumAcao:: LISTADAVEZVENDEDORES_CREATE, EnumAcao:: LISTADAVEZVENDEDORES_DELETE, EnumAcao::PRIORIDADES_INDEX, EnumAcao::PRIORIDADES_CREATE,
                                EnumAcao::PRIORIDADES_DELETE, EnumAcao::CFGTAXASADM_GERENCIAR],
                'id' => 1
            ],
            [
                'usuario' => 'usuario@nasajon.com.br',
                'permissoes' => [EnumAcao::ATCS_CREATE, EnumAcao::ATCS_GET, EnumAcao::FORNECEDORESENVOLVIDOS_DELETE,
                                EnumAcao::FORNECEDORES_CREATE, EnumAcao::FORNECEDORES_SUSPENDER, EnumAcao::FORNECEDORES_REATIVAR,
                                EnumAcao::CLIENTES_CREATE, EnumAcao::COMPOSICOES_INDEX, EnumAcao::COMPOSICOES_PUT, EnumAcao::DOCUMENTOS_CREATE,
                                EnumAcao::VINCULOS_CREATE, EnumAcao::MIDIAS_CREATE, EnumAcao::ATCSAREAS_CREATE, EnumAcao::ORCAMENTOS_CREATE, 
                                EnumAcao::ORCAMENTOS_APROVAR, EnumAcao::FUNCOES_CREATE, EnumAcao::FUNCOES_DELETE, 
                                EnumAcao::ADVERTENCIAS_EXCLUIR, EnumAcao::TIPOSATIVIDADES_CREATE, EnumAcao::UNIDADES_CREATE, EnumAcao::TIPOSACIONAMENTOS_CREATE,
                                EnumAcao::TIPOSACIONAMENTOS_DELETE, EnumAcao::HISTORICOSPADRAO_CREATE, EnumAcao::HISTORICOSPADRAO_DELETE, EnumAcao::MALOTES_CREATE,
                                EnumAcao::MALOTES_ENVIAR, EnumAcao::MALOTES_APROVAR, EnumAcao::NEGOCIOS_GET, EnumAcao::NEGOCIOS_PUT, EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO,
                                EnumAcao::FOLLOWUPSNEGOCIOS_CREATE, EnumAcao::SEGMENTOSATUACAO_GET, EnumAcao::SEGMENTOSATUACAO_PUT, EnumAcao:: SITUACOESPRENEGOCIOS_GET, EnumAcao:: SITUACOESPRENEGOCIOS_PUT,
                                EnumAcao::PROMOCOESLEADS_GET, EnumAcao::PROMOCOESLEADS_PUT, EnumAcao:: LISTADAVEZVENDEDORES_GET, EnumAcao:: LISTADAVEZVENDEDORES_PUT, EnumAcao::PRIORIDADES_GET, EnumAcao::PRIORIDADES_PUT,
                                EnumAcao::CONTRATOSTAXASADM_GERENCIAR], 
                'id' => 2
            ]
        ];
    }

    /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
    public function verificaSeUsuarioPodeListarAtcs(FunctionalTester $I, \Codeception\Example $usuario)
    {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

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
        $url = $this->url_atcs . '?tenant=' . $this->tenant . '&grupoempresarial='.$this->grupoempresarial;
        $atcs = $I->sendRaw('GET', $url, [], [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ATCS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
    }

   /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
    public function verificaSeUsuarioPodeCriarAtc(FunctionalTester $I, \Codeception\Example $usuario)
    {
        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = [
        'nome' => 'Atc 1',
        'codigo' => 'N1',
        'area' => ['negocioarea' => $area['negocioarea']],
        'origem' => ['midia' => $midia['midiaorigem']],
        'cliente' => ['cliente' => $cliente['cliente']],
        'responsaveisfinanceiros' => $responsavelfinanceiro,
        'tenant' => $this->tenant_numero,
        'estabelecimento' => ['estabelecimento' => $this->estabelecimento]
        ];

        /* execução da funcionalidade */
        $atc_criado = $I->sendRaw('POST', $this->url_atcs . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $atc, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ATCS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('crm.atcsresponsaveisfinanceiros', ['negocio' => $atc_criado['negocio']]);
            $proposta = $I->grabFromDatabase('crm.propostas', 'proposta', ['negocio' => $atc_criado['negocio']]);
            $I->deleteFromDatabase('crm.propostascapitulos', ['proposta' => $proposta]);
            $I->deleteFromDatabase('crm.propostas', ['negocio' => $atc_criado['negocio']]);
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc_criado['negocio']]);
            $I->deleteFromDatabase('crm.atcs', ['negocio' => $atc_criado['negocio']]);
        }

    }

    /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
    public function verificaSeUsuarioPodeEditarAtc(FunctionalTester $I, \Codeception\Example $usuario)
    {

        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);

        $atc['origem']['midia'] = $atc['origem']['midiaorigem'];
        unset($atc['origem']['midiaorigem']);
        /* execução da funcionalidade */
        $atc['nome'] = 'Nome editado';
        $I->sendRaw('PUT', $this->url_atcs . $atc['negocio'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $atc, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ATCS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

    }

    /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirAtc(FunctionalTester $I, \Codeception\Example $usuario)
  {

    // cenario
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I);
    $atc = $I->haveInDatabaseAtcComResponsavelFinanceiro($I, $area, $origem, $cliente);
    $responsaveisfinanceiros = $atc['responsavelfinanceiro'];
    
    /* execução da funcionalidade */
    $atc_Recebido = $I->sendRaw('GET', $this->url_atcs . $atc['negocio'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::ATCS_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeAcionarFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {

        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $proposta = $I->haveInDatabaseProposta($I, $atc);

        $acionamentoMetodo = 1; //1 - sistema, 2 - email, 3 - telefone

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
        $fornecedorenvolvido = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_fornecedoresenvolvidos}/?grupoempresarial={$this->grupoempresarial}", $dados, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORESENVOLVIDOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        //Excluo dados do banco
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('crm.fornecedoresenvolvidos', ['fornecedorenvolvido' => $fornecedorenvolvido['fornecedorenvolvido']]);
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCancelarAcionamentoFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {

        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        $origem = $I->haveInDatabaseMidia($I);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $acionamentoMetodo = 2; //1 - sistema, 2 - email, 3 - telefone
        $fornecedorenvolvido = $I->haveInDatabaseFornecedorEnvolvido($I, $atc, $fornecedor, $acionamentoMetodo);

        /* execução da funcionalidade */
        $I->sendRaw('DELETE', "/api/{$this->tenant}/{$atc['negocio']}/{$this->url_fornecedoresenvolvidos}/{$fornecedorenvolvido['fornecedorenvolvido']}?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORESENVOLVIDOS_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        //Excluo dados criados a partir da minha requisição
        if($httpRetorno == HttpCode::OK ){
            $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
        }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarFornecedores(FunctionalTester $I, \Codeception\Example $usuario)
  {
     
      // cenario
      $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

      $I->haveInDatabaseFornecedor($I);
      $I->haveInDatabaseFornecedor($I);

      // funcionalidade testada
      $url = $this->url_fornecedores . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial;
      $I->sendRaw('GET', $url, [], [], [], null);

      /* validação do resultado */
      $httpRetorno = in_array(EnumAcao::FORNECEDORES_INDEX, $usuario['permissoes']) ? HttpCode::OK : HttpCode::FORBIDDEN;
      $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        $fornecedor = [
            "codigofornecedores" => "101",
            "razaosocial" => "F101",
            "nomefantasia" => "Fornecedor 101",
            "cadastro" => "1",
            "cnpj" => "41960275000154",
            "incricaomunicipal" => "101",
            "tenant" => $this->tenant,
        ];

        /* execução da funcionalidade */
        $fornecedor_criado = $I->sendRaw('POST', $this->url_fornecedores . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $fornecedor, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
        
        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('ns.conjuntosfornecedores', ['registro' => $fornecedor_criado['fornecedor']]);
            $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $fornecedor_criado['fornecedor']]);
        }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $fornecedor['nomefantasia'] = "Fornecedor 103";
        $fornecedor["razaosocial"] = "F103";
        $fornecedor["codigofornecedores"] = "103";
        $fornecedor["cnpj"] = "18492167000182";
        $fornecedor["tenant"] = $this->tenant;

        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_fornecedores . $fornecedor['fornecedor'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $fornecedor, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeSuspenderFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $suspensao = [
            "datafimsuspensao" => "2019-06-12",
            "fornecedor" => $fornecedor['id'],
            "tiposuspensao" => 1,
            "motivosuspensao" => "suspensão 1"

        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_fornecedores . $fornecedor['fornecedor'] . '/suspender?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $suspensao, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORES_SUSPENDER, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* validação do resultado */
        if($httpRetorno == HttpCode::OK){
            $I->deleteFromDatabase('ns.fornecedoressuspensos', ['fornecedor_id' => $fornecedor['fornecedor']]);
        }
        

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeAdvertirFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $fornecedor = $I->haveInDatabaseFornecedor($I);

        /* execução da funcionalidade */
        $advertencia = [
            "fornecedor" => $fornecedor['fornecedor'],
            "motivoadvertencia" => "motivo 1",
            "nomeadvertencia" => "advertência 1",
            "estabelecimentoid" => [
                "estabelecimento" => $this->estabelecimento
            ]
        ];
        $I->sendRaw('POST', $this->url_fornecedores . $fornecedor['fornecedor'] . '/fornecedoradvertir?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $advertencia, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORES_ADVERTIR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::OK){
            $I->deleteFromDatabase('ns.advertencias', ['fornecedor_id' => $fornecedor['fornecedor']]);
        }


  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeReativarFornecedor(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);

        /* execução da funcionalidade */
        $reativacao = [
            "motivoremocaosuspensao" => "Motivo 1"
        ];
        $I->sendRaw('POST', $this->url_fornecedores . $fornecedor['fornecedor'] . '/fornecedorreativar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $reativacao, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORES_REATIVAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarFornecedoresSuspensos(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $I->haveinDatabaseFornecedorSuspenso($I, $fornecedor['fornecedor']);

        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_fornecedoressuspensos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $fornecedor, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FORNECEDORES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
  * @param FunctionalTester $I
  * @dataProvider UsuarioProvider
  */
  public function verificaSeUsuarioPodeListarMalotes(FunctionalTester $I, \Codeception\Example $usuario){
      
        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $malote = $I->haveInDatabaseMalote($I, $cliente['cliente'], 0);
        $malote = $I->haveInDatabaseMalote($I, $cliente['cliente'], 1, "Malote 1");

        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_malotes .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $malote, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MALOTES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
  * @param FunctionalTester $I
  * @dataProvider UsuarioProvider
  */
  public function verificaSeUsuarioPodeCriarMalote(FunctionalTester $I, \Codeception\Example $usuario){

        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);

        $malote = [
        'dtenvio' => '2020-03-06',
        'codigo' => 'Malote 777',
        'tenant' => $this->tenant_numero,
        'requisitantecliente'=> $cliente
        ];

        /* execução da funcionalidade */
        $malote_criado = $I->sendRaw('POST', $this->url_malotes . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $malote, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MALOTES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('crm.malotes', ['malote' => $malote_criado['malote']]);
        }
  }

  /**
  * @param FunctionalTester $I
  * @dataProvider UsuarioProvider
  */
  public function verificaSeUsuarioPodeEditarMalote(FunctionalTester $I, \Codeception\Example $usuario){

        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $malote = $I->haveInDatabaseMalote($I, $cliente['cliente']);

        //Objeto que será enviado na requisição, o cliente precisa ser um objeto
        $malote['requisitantecliente'] = $cliente;

        /* execução da funcionalidade */
        $malote['codigo'] = 'Código 777';
        $I->sendRaw('PUT', $this->url_malotes . $malote['malote'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $malote, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MALOTES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
  * @param FunctionalTester $I
  * @dataProvider UsuarioProvider
  */
  public function verificaSeUsuarioPodeEnviarMalote(FunctionalTester $I, \Codeception\Example $usuario){

        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $malote = $I->haveInDatabaseMalote($I, $cliente['cliente']);

        //Objeto enviado na requisição
        $malote_enviado = [
        'enviodata' => '2020-12-31',
        'enviomodal' => 2,
        'malote' => $malote['malote']
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST',  $this->url_malotes . $malote_enviado['malote'] . '/maloteEnviar?' . 'grupoempresarial='.$this->grupoempresarial, $malote_enviado, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MALOTES_ENVIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
  * @param FunctionalTester $I
  * @dataProvider UsuarioProvider
  */
  public function verificaSeUsuarioPodeCancelarEnvioDoMalote(FunctionalTester $I, \Codeception\Example $usuario){

        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 2);

        //Objeto enviado na requisição
        $malote_enviado = [
        'malote' => $malote['malote'],
        'dtenvio' => $malote['dtenvio'],
        'dtresposta' => null,
        'codigo' => $malote['codigo'],
        'created_at' => $malote['created_at'],
        'created_by' => $malote['created_by'],
        'updated_at' => $malote['updated_at'],
        'updated_by' => $malote['updated_by'],
        "requisitantenome" => null,
        "requisitantecargo" => null,
        "requisitanteobservacoes" => null,
        "status" => $malote['status'],
        "tenant" => $this->tenant_numero,
        "enviomodal" => $malote['enviomodal'],
        "enviocodigorastreio" => null,
        "enviodata" => $malote['enviodata'],
        "enviorecebimentodata" => null,
        "enviorecebidopornome" => null,
        "enviorecebidoporcargo" => null,
        "requisitantecliente" => $cliente,
        "requisitantefornecedor" => null,
        "requisitantemidia" => null,
        "statusLabel" => "Enviado",
        "documentos" => []
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST',  $this->url_malotes . $malote_enviado['malote'] . '/malotecancelaenvio?' . 'grupoempresarial='.$this->grupoempresarial, $malote_enviado, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MALOTES_CANCELARENVIO, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
  * @param FunctionalTester $I
  * @dataProvider UsuarioProvider
  */
  public function verificaSeUsuarioPodeSalvarRespostaDoRequisitanteDoMalote(FunctionalTester $I, \Codeception\Example $usuario){

        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
        $midia = $I->haveInDatabaseMidia($I);
        $malote = $I->haveInDatabaseMaloteEnviado($I, $cliente['cliente'], 2);

        //Objeto enviado na requisição
        $malote_enviado = [
        'malote' => $malote['malote'],
        "requisitantenome" => "Nome Requisitante 777",
        "requisitantecargo" => "Cargo Requisitante 777",
        "statusaprova" => 2, //aceito
        'requisitantemidia' => [
            "midia" => $midia['midiaorigem'],
            "nome" => $midia['codigo'],
            "full_count" => 1
        ],
        'requisitanteobservacoes' => "Requisitante Observações 777",
        ];

        /* execução da funcionalidade */
        $I->sendRaw('POST',  $this->url_malotes . $malote_enviado['malote'] . '/maloteAprova?' . 'grupoempresarial='.$this->grupoempresarial, $malote_enviado, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MALOTES_APROVAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarClientes(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $I->haveInDatabaseCliente($I);

        // funcionalidade testada
        $url = $this->url_clientes . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial;
        $I->sendRaw('GET', $url, [], [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CLIENTES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarCliente(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $pais = $I->haveInDatabasePais($I);
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
        $cliente_criado = $I->sendRaw('POST', $this->url_clientes . '?tenant=' . $this->tenant . '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CLIENTES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('ns.conjuntosclientes', ['registro' => $cliente_criado['cliente']]);
            $I->deleteFromDatabase('ns.pessoas', ['pessoa' => $cliente_criado['cliente']]);
        }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarCliente(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $cliente = $I->haveInDatabaseCliente($I);
        $cliente["codigo"] = "101";
        $cliente["nomefantasia"] = "Cliente 101";
        $cliente["cnpj"] = "67548070000150";
        $cliente["inscricaomunicipal"] = "130";
        $cliente["codigo"] = $this->tenant;

        /* executa funcionalidade */
        $I->sendRaw('PUT', $this->url_clientes . $cliente['cliente'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cliente, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CLIENTES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarComposicoes(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $I->haveInDatabaseComposicao($I);
        $I->haveInDatabaseComposicao($I);

        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_composicoes . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::COMPOSICOES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarComposicao(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $composicao = [
            'nome' => 'teste1',
            'descricao' => 'descricao1',
            'codigo' => '123',
            'created_at' => '',
            'created_by' => '',
            'servicotecnico' => [ 'servicotecnico' => $this->servicotecnico],
            'tenant' => $this->tenant_numero,
            'servicocoringa' => true,
            'servicoexterno' => false,
            'servicoprestadoraacionada' => true
        ];

        /* execução da funcionalidade */
        $composicao_criada = $I->sendRaw('POST', $this->url_composicoes . '?tenant=' . $this->tenant. '&grupoempresarial=' . $this->grupoempresarial, $composicao, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::COMPOSICOES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* remove documento criado */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('crm.composicoes', ['composicao' => $composicao_criada['composicao']]);
        }
        
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarComposicao(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $composicao = $I->haveInDatabaseComposicao($I);
        $composicao['nome'] = 'Edição';

        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_composicoes . $composicao['composicao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $composicao, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::COMPOSICOES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarDocumentos(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $documento = $I->haveInDatabaseDocumento($I);
        $documento = $I->haveInDatabaseDocumento($I);;
        $documento = $I->haveInDatabaseDocumento($I);
        
        /* execução da funcionalidade */
        $lista = $I->sendRaw('GET', $this->url_documentos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::DOCUMENTOS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);   
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarDocumento(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $documento = [
            "nome" => "IDRG",
            "emissaonoprocesso" => false,
            "tenant" => $this->tenant,
            "dominio" => null,
        ];
        
        /* execução da funcionalidade */
        $documento_criado = $I->sendRaw('POST', $this->url_documentos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::DOCUMENTOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apaga dado do banco */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('ns.tiposdocumentos', ['tipodocumento' => $documento_criado['tipodocumento']]);
        }
        
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarDocumento(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $documento = $I->haveInDatabaseDocumento($I);
        $documento['nome'] = 'RG';
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_documentos . $documento['tipodocumento'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $documento, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::DOCUMENTOS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarVinculo(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $vinculo = $I->haveInDatabaseVinculo($I);
        $vinculo = $I->haveInDatabaseVinculo($I);;
        $vinculo = $I->haveInDatabaseVinculo($I);

        /* execução da funcionalidade */
        $lista = $I->sendRaw('GET', $this->url_vinculos . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $vinculo, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::VINCULOS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);      
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarVinculo(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $vinculo = [
            'nome' => 'Vinculo 1'
        ];

        /* execução da funcionalidade */
        $vinculo_criado = $I->sendRaw('POST', $this->url_vinculos . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $vinculo, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::VINCULOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apaga dado do banco */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('crm.vinculos', ['vinculo' => $vinculo_criado['vinculo']]);
        }
        
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarVinculo(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $vinculo = $I->haveInDatabaseVinculo($I);

        /* execução da funcionalidade */
        $vinculo['nome'] = 'Nome editado';
        $I->sendRaw('PUT', $this->url_vinculos . $vinculo['vinculo'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $vinculo, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::VINCULOS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }
  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarMidias(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        /* execução da funcionalidade */
        $midia = $I->haveInDatabaseMidia($I);
        $midia = $I->haveInDatabaseMidia($I, ['codigo' => 'midia 2']);
        $midia = $I->haveInDatabaseMidia($I, ['codigo' => 'midia 3']);
        
        $lista = $I->sendRaw('GET', $this->url_midias . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $midia, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MIDIAS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
   
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarMidia(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        /* execução da funcionalidade */
        $midia = [
            'nome' => 'Midia 1',
            'descricao' => 'Descrição da mídia 1'
        ];
        $midia_criada = $I->sendRaw('POST', $this->url_midias . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $midia, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MIDIAS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apaga dado do banco */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('crm.midiasorigem', ['midiaorigem' => $midia_criada['midia']]);
        }        
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarMidia(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $midia = $I->haveInDatabaseMidia($I);

        /* execução da funcionalidade */
        $midia['nome'] = 'Novo nome';
        $I->sendRaw('PUT', $this->url_midias . $midia['midiaorigem'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $midia, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::MIDIAS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarAtcsAreas(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);
        $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);
        $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);
        /* execução da funcionalidade */
        
        $lista = $I->sendRaw('GET', $this->url_atcsareas . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $areaDeAtc, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ATCSAREAS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarAtcArea(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);

        /* execução da funcionalidade */
        $area = [
            'nome' => 'Área' . substr($I->generateUuidV4(), 0, 13), //Feito para criar sempre com nome diferente, porque com nomes iguais quebra na validação
            'descricao' => 'Descrição da área 1',
            'estabelecimento' => $estabelecimento,
            'possuiseguradora' => false
        ];
        $area_criada = $I->sendRaw('POST', $this->url_atcsareas . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $area, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ATCSAREAS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);

        /* apaga dado do banco */
        if($httpRetorno == HttpCode::CREATED){
            $I->deleteFromDatabase('crm.atcsareas', ['negocioarea' => $area_criada['negocioarea']]);
        } 
        
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarAtcArea(FunctionalTester $I, \Codeception\Example $usuario)
  {
        // cenario
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $areaDeAtc = $I->haveInDatabaseAreaDeAtc($I);

        /* execução da funcionalidade */
        $areaDeAtc['nome'] = 'Novo nome';
        $areaDeAtc['possuiseguradora'] = false;
        $I->sendRaw('PUT', $this->url_atcsareas . $areaDeAtc['negocioarea'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $areaDeAtc, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ATCSAREAS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN;
        $I->seeResponseCodeIs($httpRetorno);
  }

    /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarOrcamento(FunctionalTester $I, \Codeception\Example $usuario)
    {
        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
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
            'atc' => $atc['negocio'],
            'propostaitem' => $propostaItem['propostaitem'],
            'familia' => $familia['familia'],
            'valor' => 10,
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
            'execucaodeservico' => false
          ];

        /* execução da funcionalidade */
        $orcamento_criado = $I->sendRaw('POST', $this->url_orcamentos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('crm.orcamentos', ['orcamento' => $orcamento_criado['orcamento']]);
        }

    }

     /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarOrcamento(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
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
        $orcamento['valor'] = 50;
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_orcamentos . $orcamento['orcamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamento, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::OK ){
            $I->deleteFromDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento']]);
      }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeAprovarOrcamento(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
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
            'tenant' => $this->tenant_numero
        ];
        
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_orcamentos . $orcamento['orcamento'] . '/aprovar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentoaprovar, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_APROVAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::OK ){
            $I->deleteFromDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento']]);
      }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEnviarOrcamento(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
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
        $orcamentoenviar = [
            'orcamento' => $orcamento['orcamento'],
            'tenant' => $this->tenant_numero
        ];
        
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_orcamentos . $orcamento['orcamento'] . '/enviar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentoenviar, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_ENVIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::OK ){
            $I->deleteFromDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento']]);
      }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeRenegociarOrcamento(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
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
            'tenant' => $this->tenant_numero
        ];
        
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_orcamentos . $orcamento['orcamento'] . '/renegociar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentorenegociar, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_APROVAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::OK ){
            $I->deleteFromDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento']]);
      }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeReprovarOrcamento(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
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
        $orcamentoreprovar = [
            'orcamento' => $orcamento['orcamento'],
            'tenant' => $this->tenant_numero
        ];
        
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_orcamentos . $orcamento['orcamento'] . '/reprovar' .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $orcamentoreprovar, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_APROVAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::OK ){
            $I->deleteFromDatabase('crm.orcamentos', ['orcamento' => $orcamento['orcamento']]);
      }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $funcao = [
            'codigo' => '1234',
            'descricao' => 'funcao de teste',
            'tenant' => '47',
            'funcaocoringa' => true
        ];
        
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_funcoes  .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);
        /* validação do resultado */
        $funcao_criada = $httpRetorno = in_array(EnumAcao::FUNCOES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('gp.funcoes', ['funcao' => $funcao_criada['funcao']]);
      }
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I);
        $funcao['descricao'] = 'editada';
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_funcoes . $funcao['funcao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FUNCOES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }


  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeDeletarFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I);
        $funcao['descricao'] = 'editada';
        
        /* execução da funcionalidade */
        $I->sendRaw('DELETE', $this->url_funcoes . $funcao['funcao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FUNCOES_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I);
        $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I, '789');
        $funcao = $I->haveInDatabaseFuncaoComLastUpdate($I, '456');
        
        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_funcoes .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $funcao, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::FUNCOES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarTipoDeAcionamento(FunctionalTester $I, \Codeception\Example $usuario)
  {
        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $tipoacionamento = [
            'nome' => 'Tipo de Acionamento 0',
            'descricao' => 'Descrição tipo de acionamento 0',
            'tenant' => '47',
        ];
        
        /* execução da funcionalidade */
        $tipoacionamento_criado = $I->sendRaw('POST', $this->url_tiposacionamentos  .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSACIONAMENTOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('crm.tiposacionamentos', ['tiposacionamento' => $tipoacionamento_criado['tiposacionamento']]);
      }

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarTipoDeAcionamento(FunctionalTester $I, \Codeception\Example $usuario)
  {

      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I);
        $tipoacionamento['descricao'] = 'Descrição editada';
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_tiposacionamentos . $tipoacionamento['tiposacionamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSACIONAMENTOS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeDeletarTipoDeAcionamento(FunctionalTester $I, \Codeception\Example $usuario)
  {

      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I);
        
        /* execução da funcionalidade */
        $I->sendRaw('DELETE', $this->url_tiposacionamentos . $tipoacionamento['tiposacionamento'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSACIONAMENTOS_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarTiposDeAcionamento(FunctionalTester $I, \Codeception\Example $usuario)
  {

      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I);
        $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I, 'Nome 234');
        $tipoacionamento = $I->haveInDatabaseTipoAcionamento($I, 'Nome 567');
        
        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_tiposacionamentos .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoacionamento, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSACIONAMENTOS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
        
  }
  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarHistoricoPadrao(FunctionalTester $I, \Codeception\Example $usuario)
  {
        /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes'], 0);
        $historicopadrao = [
            'codigo' => 'HP002',
            'tipo' => 102, //100-Geral, 101-Acompanhamento, 102-Pendencias
            'descricao' => 'Descrição Histórico Padrão 002',
            'texto' => 'Texto Histórico Padrão 002',
            'tenant' => '47',
        ];
        
        /* execução da funcionalidade */
        $historicopadrao_criado = $I->sendRaw('POST', $this->url_historicospadrao  .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::HISTORICOSPADRAO_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* apagando dado do banco */
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('crm.historicospadrao', ['historicopadrao' => $historicopadrao_criado['historicopadrao']]);
      }

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarHistoricoPadrao(FunctionalTester $I, \Codeception\Example $usuario)
  {

      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I);
        $historicopadrao['descricao'] = 'Descrição editada 7';
        
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_historicospadrao . $historicopadrao['historicopadrao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::HISTORICOSPADRAO_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeDeletarHistoricoPadrao(FunctionalTester $I, \Codeception\Example $usuario)
  {

      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I);
        
        /* execução da funcionalidade */
        $I->sendRaw('DELETE', $this->url_historicospadrao . $historicopadrao['historicopadrao'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::HISTORICOSPADRAO_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarHistoricosPadrao(FunctionalTester $I, \Codeception\Example $usuario)
  {

      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I);
        $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I, 'Código 234');
        $historicopadrao = $I->haveInDatabaseHistoricoPadrao($I, 'Código 567');
        
        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_historicospadrao .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $historicopadrao, [], [], null);
        
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::HISTORICOSPADRAO_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
        
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeArquivarAdvertencia(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $advertencia = $I->haveInDatabaseAdvertencia($I, $fornecedor);
        
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_advertencias . $advertencia['advertencia'] . '/arquivar'.'?tenant=' . $this->tenant.'&grupoempresarial='.$this->grupoempresarial, $advertencia, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ADVERTENCIAS_ARQUIVAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }
  
//   /**
//    * @param FunctionalTester $I
//    * @dataProvider UsuarioProvider
//    */
//   public function verificaSeUsuarioPodeExcluirAdvertencia(FunctionalTester $I, \Codeception\Example $usuario)
//   {
//       /* inicializações */
//         $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
//         $fornecedor = $I->haveInDatabaseFornecedor($I);
//         $advertencia = $I->haveInDatabaseAdvertencia($I, $fornecedor);
        
//         /* execução da funcionalidade */
//         $I->sendRaw('POST', $this->url_advertencias . $advertencia['advertencia'] . '/excluir'.'?tenant=' . $this->tenant.'&grupoempresarial='.$this->grupoempresarial, $advertencia, [], [], null);
//         /* validação do resultado */
//         $httpRetorno = in_array(EnumAcao::ADVERTENCIAS_EXCLUIR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
//         $I->seeResponseCodeIs($httpRetorno);
//   }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarTiposAtividades(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
        /* execução da funcionalidade */
        $lista = $I->sendRaw('GET', $this->url_tiposatividades .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoatividade, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSATIVIDADES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }
  
  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarTipoAtividade(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $tipoatividade = [
        'nome' => 'TestagemAvançada',
        'descricao' => 'teste',
        'created_at' => date("Y-m-d H:i:s"),
        'created_by' => '{"nome":"usuario"}',
        'tenant' => $this->tenant_numero,
        'tipo' => 0
        ];
        
        /* execução da funcionalidade */
        $tipoatividade_criado = $I->sendRaw('POST', $this->url_tiposatividades .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoatividade, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSATIVIDADES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
        if($httpRetorno == HttpCode::CREATED ){
            $I->deleteFromDatabase('ns.tiposatividades', ['tipoatividade' => $tipoatividade_criado['tipoatividade']]);
      }
  }
  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarTipoAtividade(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $tipoatividade = $I->haveInDatabaseTipoAtividade($I);
        $tipoatividade['descricao'] = "editado";
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_tiposatividades .$tipoatividade['tipoatividade'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $tipoatividade, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::TIPOSATIVIDADES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

   /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarUnidades(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $unidade = $I->haveInDatabaseUnidade($I);
        /* execução da funcionalidade */
        $I->sendRaw('GET', $this->url_unidades .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $unidade, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::UNIDADES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarUnidade(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $unidade = $I->haveInDatabaseUnidade($I);
        /* execução da funcionalidade */
        $I->sendRaw('PUT', $this->url_unidades . $unidade['unidade'] .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $unidade, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::UNIDADES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarUnidade(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $unidade = [
            'codigo' => '123',
            'descricao' => 'teste',
            'decimais' => 2,
            'created_at' => date("Y-m-d H:i:s"),
            'created_by' => '{"nome":"usuario"}',
            'tenant' => $this->tenant_numero,
        ];
        /* execução da funcionalidade */
        $I->sendRaw('POST', $this->url_unidades .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $unidade, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::UNIDADES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  
  

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarPropostaCapitulo(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $capitulo = [
        'nome' => 'Pedido2',
        'proposta'=> $proposta['proposta'],
        'pai' => null,
        'created_at' => date('Y-m-d'),
        'created_by' => '{"nome":"usuario"}',
        'updated_by' => '{"nome":"usuario"}',
        'updated_at' => date('Y-m-d'),
        'tenant' => $this->tenant_numero
        ];
        /* execução da funcionalidade */
        $I->sendRaw('POST', '/api/gednasajon/' . $proposta['proposta'] . '/propostascapitulos/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $capitulo, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::PROPOSTASCAPITULOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarPropostaCapitulo(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $proposta['propostacapitulo']['nome'] = 'Editado';
        /* execução da funcionalidade */
        $I->sendRaw('PUT', '/api/gednasajon/' . $proposta['proposta'] . '/propostascapitulos/' . $proposta['propostacapitulo']['propostacapitulo'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $proposta['propostacapitulo'], [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::PROPOSTASCAPITULOS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirPropostaCapitulo(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
      $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
      $area = $I->haveInDatabaseAreaDeAtc($I);
      $midia = $I->haveInDatabaseMidia($I);
      $pais = $I->haveInDataBasePais($I);
      $estado = $I->haveInDataBaseEstado($I);
      $municipio = $I->haveInDatabaseMunicipio($I, $estado);
      $cliente = $I->haveInDatabaseCliente($I);
      $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
      $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
      $proposta = $I->haveInDatabaseProposta($I, $atc);
      /* execução da funcionalidade */
      $I->sendRaw('DELETE', '/api/gednasajon/' . $proposta['proposta'] . '/propostascapitulos/' . $proposta['propostacapitulo']['propostacapitulo'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $proposta['propostacapitulo'], [], [], null);
      /* validação do resultado */
      $httpRetorno = in_array(EnumAcao::PROPOSTASCAPITULOS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
      $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarPropostaItem(FunctionalTester $I, \Codeception\Example $usuario)
  {
    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $composicao = $I->haveInDatabaseComposicao($I);
    $propostaitens = [
        'propostaitem' => $I->generateUuidV4(),
        'proposta' => $proposta,
        'propostacapitulo' => $proposta['propostacapitulo'],
        'composicao' =>  $composicao,
        'fornecedor' => $fornecedor['fornecedor'],
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
        'previsaodatahorainicio' => date('Y-m-d H:i:s'),
        'previsaodatahorafim' => date('Y-m-d H:i:s'),
        'id_grupoempresarial'=>'95cd450c-30c5-4172-af2b-cdece39073bf'
    ];
        /* execução da funcionalidade */
        $I->sendRaw('POST','/api/gednasajon/' . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitens, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::PROPOSTASITENS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarPropostaItem(FunctionalTester $I, \Codeception\Example $usuario)
  {
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    
    /* execução da funcionalidade */
    $propostaitem['previsaodatainicio'] = "2030-04-13";
    $propostaitem['previsaodatafim'] = "2030-04-14";
    $propostaitem['previsaohorainicio'] = "1970-01-01T13:13:00.000Z";
    $propostaitem['previsaohorafim'] = "1970-01-01T14:14:00.000Z";

    $I->sendRaw('PUT', '/api/gednasajon/' . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/'. $propostaitem['propostaitem'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirPropostaItem(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    /* execução da funcionalidade */
    $I->sendRaw('DELETE','/api/gednasajon/' . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem']. '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

   /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarPropostaItemFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaitemfuncao = [
      'propostaitemfuncao' => $I->generateUuidV4(),
      'propostaitem' => $propostaitem,
      'funcao' => $funcao,
      'quantidade' => 5,
      'valor' => 10,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'tenant' => $this->tenant_numero,
      'composicaofuncao' => $composicaofuncao,
      'composicao' => $composicao,
  ];
    /* execução da funcionalidade */
    $I->sendRaw('POST','/api/gednasajon/' . $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemfuncao, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENSFUNCOES_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarPropostaItemFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $propostaItemFuncao['nome'] = "Editado";
    /* execução da funcionalidade */
    $I->sendRaw('PUT','/api/gednasajon/'. $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . $propostaItemFuncao['propostaitemfuncao'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFuncao, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENSFUNCOES_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirPropostaItemFuncao(FunctionalTester $I, \Codeception\Example $usuario)
  {
    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    /* execução da funcionalidade */
    $I->sendRaw('DELETE','/api/gednasajon/'. $propostaitem['propostaitem'] . '/propostasitensfuncoes/' . $propostaItemFuncao['propostaitemfuncao'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFuncao, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENSFUNCOES_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarPropostaItemFamilia(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaitemfamilia = [
      'propostaitem' => $propostaitem,
      'familia' => $familia,
      'quantidade' => 5,
      'valor' => 10,
      'created_at' => date('Y-m-d'),
      'created_by' => '{"nome":"usuario"}',
      'tenant' => $this->tenant_numero,
      'composicaofamilia' => $composicaofamilia,
      'composicao' => $composicao,
  ];
    /* execução da funcionalidade */
    $I->sendRaw('POST','/api/gednasajon/' . $propostaitem['propostaitem'] . '/propostasitensfamilias/' . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemfamilia, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENSFAMILIAS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarPropostaItemFamilia(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaItemFamilia =  $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    $propostaItemFamilia['nome'] = "Editado";
    /* execução da funcionalidade */
    $I->sendRaw('PUT', '/api/gednasajon/'. $propostaitem['propostaitem'] . '/propostasitensfamilias/' . $propostaItemFamilia['propostaitemfamilia'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFamilia, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENSFAMILIAS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirPropostaItemFamilia(FunctionalTester $I, \Codeception\Example $usuario)
  {
    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $midia = $I->haveInDatabaseMidia($I);
    $pais = $I->haveInDataBasePais($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $cliente = $I->haveInDatabaseCliente($I);
    $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
    $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
    $proposta = $I->haveInDatabaseProposta($I, $atc);
    $fornecedor = $I->haveInDatabaseFornecedor($I);
    $funcao = $I->haveInDatabaseFuncao($I);
    $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
    $composicao = $I->haveInDatabaseComposicao($I);
    $composicaofuncao = $I->haveInDatabaseComposicaoFuncao($I, $funcao, $composicao);
    $propostaItemFuncao = $I->haveInDatabasePropostaItemFuncao($I, $propostaitem, $funcao, $composicao, $composicaofuncao);
    $familia = $I->haveInDatabaseFamilia($I);
    $composicaofamilia = $I->haveInDatabaseComposicaoFamilia($I, $familia, $composicao);
    $propostaItemFamilia =  $I->haveInDatabasePropostaItemFamilia($I, $propostaitem, $familia, $composicao, $composicaofamilia);
    /* execução da funcionalidade */
    $I->sendRaw('DELETE', '/api/gednasajon/'. $propostaitem['propostaitem'] . '/propostasitensfamilias/' . $propostaItemFamilia['propostaitemfamilia'] . '?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaItemFamilia, [], [], null);
    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROPOSTASITENSFAMILIAS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeVincularFornecedorPropostasItens(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem['fornecedor'] = $fornecedor;
        /* execução da funcionalidade */
        $I->sendRaw('POST','/api/gednasajon/' . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasItensVincularFornecedor' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeDesvincularFornecedorPropostasItens(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitem['fornecedor'] = $fornecedor;
        /* execução da funcionalidade */
        $I->sendRaw('POST','/api/gednasajon/' . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasItensDesvincularFornecedor' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitem, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::PROPOSTASITENS_VINCULARFORNECEDOR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeSelecionarPropostaItemFornecedorEscolheCliente(FunctionalTester $I, \Codeception\Example $usuario)
  {
      /* inicializações */
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        $area = $I->haveInDatabaseAreaDeAtc($I);
        $midia = $I->haveInDatabaseMidia($I);
        $pais = $I->haveInDataBasePais($I);
        $estado = $I->haveInDataBaseEstado($I);
        $municipio = $I->haveInDatabaseMunicipio($I, $estado);
        $cliente = $I->haveInDatabaseCliente($I);
        $responsavelfinanceiro = $I->haveInDatabaseResponsavelFinanceiro($I, $cliente);
        $atc = $I->haveInDatabaseAtc($I, $area, $midia, $cliente);
        $proposta = $I->haveInDatabaseProposta($I, $atc);
        $fornecedor = $I->haveInDatabaseFornecedor($I);
        $propostaitem = $I->haveInDatabasePropostaItem($I, $atc, $proposta, $fornecedor);
        $propostaitemescolhacliente = [
        'propostaitem' => $propostaitem['propostaitem'],
        'escolhacliente' => true,
        'tenant' => $this->tenant_numero
        ];
        /* execução da funcionalidade */
        $I->sendRaw('POST', '/api/gednasajon/' . $atc['negocio'] . '/' . $proposta['proposta'] . '/propostasitens/' . $propostaitem['propostaitem'].  '/propostasitensfornecedorescolhacliente' .'?tenant=' . $this->tenant. '&grupoempresarial=' .$this->grupoempresarial, $propostaitemescolhacliente, [], [], null);
        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::PROPOSTASITENS_CREATE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $midia = $I->haveInDatabaseMidia($I);
    $operacao = $I->haveInDatabaseNegocioOperacao($I);
    $tipoAcionamento = $I->haveInDatabaseTipoAcionamento($I);
    unset($tipoAcionamento['tenant']); //Não é usado

    $negocio = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'operacao' => ['proposta_operacao' => $operacao['proposta_operacao']],
      'estabelecimento' => ['estabelecimento' => $this->estabelecimento],
      'cliente' => null,
      'codigodepromocao' => null,
      'midiaorigem' => ['midia' => $midia['midiaorigem']],
      'cliente_codigo' => "1",
      'cliente_companhia' => "Companhia",
      'cliente_nomefantasia' => "Nome Fantasia Cliente",
      'cliente_qualificacao' => "1",
      'cliente_documento' => "Documento Cliente",
      'cliente_email' => "email cliente",
      'cliente_site' => "site cliente",
      'cliente_captador' => null,
      'cliente_segmentodeatuacao' => null,
      'cliente_receitaanual' => 5000000,
      'uf' => "RJ",
      'segmentodeatuacao' => null,
      'prenegocio' => 1,
      'ehcliente' => 1,
      'observacao' => "obs",
      'cliente_municipioibge' => 00000000,
      'tipodeacionamento' => $tipoAcionamento
    ];

    /* Execução da funcionalidade */
    $negocio_criado = $I->sendRaw('POST', $this->url_negocios . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I);

    /* Execução da funcionalidade */
    $response = $I->sendRaw('GET', $this->url_negocios . $negocio['documento'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I, [
        'uf' => ['uf' => 'RJ'],
        'clientemunicipioibge' => ['codigo' => '2511905']
    ]);
    $negocio['observacao'] .= ' complemento observacao';

    /* Execução da funcionalidade */
    $response = $I->sendRaw('PUT', $this->url_negocios . $negocio['documento'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I, [
        'uf' => ['uf' => 'RJ'],
        'clientemunicipioibge' => ['codigo' => '2511905']
    ]);

    /* Execução da funcionalidade */
    $response = $I->sendRaw('DELETE', $this->url_negocios . $negocio['documento'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocio, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeQualificarNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I);
    $guidVendedor = 'eaaa0ddd-5baf-4b5e-84fd-d084d006a758'; //guid do vendedor no dump.sql - ns.pessoas

    $dadosQualificacao = [
      "cliente" => ['cliente' => '460f64b5-e296-4ec6-8833-b93edd9310a7'],
      "dataqualificacao_pn" => date('Y-m-d'),
      "vendedor" => ['vendedor_id' => $guidVendedor],
      "periodoqualificacao_pn" => "1",
      "mensagemqualificacao_pn" => "mensagem de qualificação",
    ];

    /* Execução da funcionalidade */
    $response = $I->sendRaw('POST', $this->url_negocios . $negocio['documento'].'/preNegocioQualificar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $dadosQualificacao, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_QUALIFICARPRENEGOCIO, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeDesqualificarNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $motivoDesqualificacao = $I->haveInDatabaseMotivoDesqualificacao($I);
    $negocio = $I->haveInDatabaseNegocio($I);

    $dadosDesqualificacao = [
      'motivodesqualificacao_pn' => ['motivodesqualificacaoprenegocio' => $motivoDesqualificacao['motivodesqualificacaoprenegocio']],
      'observacaodesqualificacao_pn' => "observacao",
    ];

    /* Execução da funcionalidade */
    $response = $I->sendRaw('POST', $this->url_negocios . $negocio['documento'].'/preNegocioDesqualificar?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $dadosDesqualificacao, [], [], null);
    
    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_DESQUALIFICARPRENEGOCIO, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarNegocioContato(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I);

    $negocioContato = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'documento' => ['documento' => $negocio['documento']],
      "nome" => 'Nome do contato',
      "sobrenome" => 'Sobrenome do contato',
      "cargo" => "Sócio/Proprietário/CEO",
      "email" => 'email@do.contato',
      "ddi" => '55',
      "ddd" => '21',
      "telefone" => '987654321',
      "ramal" => '',
    ];

    /* Execução da funcionalidade */
    $negocioContato_criado = $I->sendRaw('POST', '/api/gednasajon/' . $negocio['documento'] . $this->url_negocioscontatos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);
    
    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarNegocioContato(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocioContato = $I->haveInDatabaseNegocioContato($I);
    $negocioContato['sobrenome'] .= ' da Silva';

    /* Execução da funcionalidade */ 
    $response = $I->sendRaw('PUT', '/api/gednasajon/' . $negocioContato['negocio']['documento'] . $this->url_negocioscontatos . $negocioContato['id'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);
    
    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirNegocioContato(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocioContato = $I->haveInDatabaseNegocioContato($I);

    /* Execução da funcionalidade */
    $response = $I->sendRaw('DELETE', '/api/gednasajon/' . $negocioContato['negocio']['documento'] . $this->url_negocioscontatos . $negocioContato['id'].'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $negocioContato, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::NEGOCIOS_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarFollowupNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I);

    $followUpNegocio = [
      'tenant' => $this->tenant_numero,
      'id_grupoempresarial' => ['id_grupoempresarial' => $this->grupoempresarial_id],
      'proposta' => ['documento' => $negocio['documento']],
      "historico" => 'Historico registrado',
      "participante" => null,
      "receptor" => '1',
      "meiocomunicacao" => '2',
      "figuracontato" => '3',
    ];

    /* Execução da funcionalidade */
    $followUpNegocio_criado = $I->sendRaw('POST', "/api/gednasajon/$negocio[documento]/followupsnegocios/" . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $followUpNegocio, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::FOLLOWUPSNEGOCIOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarFollowupsNegocio(FunctionalTester $I, \Codeception\Example $usuario){
    
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $negocio = $I->haveInDatabaseNegocio($I);

    $follow1 = $I->haveInDatabaseFollowupNegocio($I, $negocio['documento'], 'Histórico 1');
    $follow2 = $I->haveInDatabaseFollowupNegocio($I, $negocio['documento']);

    /* Execução da funcionalidade */
    $I->sendRaw('GET', "/api/gednasajon/$negocio[documento]/followupsnegocios/" . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::FOLLOWUPSNEGOCIOS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarSegmentosDeAtuacao(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $listaSegmentosAtuacao = [];
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 2'
    ]);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 3'
    ]);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;

    /* Execução da Funcionalidade */
    $segmentos = $I->sendRaw('GET', $this->url_segmentos . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SEGMENTOSATUACAO_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarSegmentoDeAtuacao(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* Execução da Funcionalidade */
    $segmento = [
      'codigo' => 'SA123',
      'descricao' => 'Descrição SA123'
    ];
    $I->sendRaw('POST', $this->url_segmentos . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SEGMENTOSATUACAO_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarSegmentoDeAtuacao(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $segmento = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'SA789'
    ]);
    
    /* Execução da Funcionalidade */
    $segmento['descricao'] = 'Descrição SA789';
    $I->sendRaw('PUT', $this->url_segmentos . $segmento['segmentoatuacao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SEGMENTOSATUACAO_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirSegmentoDeAtuacao(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $segmento = $I->haveInDatabaseSegmentoAtuacao($I);

    /* Execução da Funcionalidade */
    $I->sendRaw('DELETE', $this->url_segmentos . $segmento['segmentoatuacao'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $segmento, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SEGMENTOSATUACAO_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirSegmentoDeAtuacao(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $listaSegmentosAtuacao = [];
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 2'
    ]);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;
    $segmentoAtuacao = $I->haveInDatabaseSegmentoAtuacao($I, [
        'codigo' => 'cod 3'
    ]);
    $listaSegmentosAtuacao[] = $segmentoAtuacao;

    /* Execução da Funcionalidade */
    $I->sendRaw('GET', $this->url_segmentos . $segmentoAtuacao['segmentoatuacao']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SEGMENTOSATUACAO_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   *
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarSituacoesPreNegocios(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $listaSituacoesprenegocios = [];
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I);
    $listaSituacoesprenegocios[] = $situacoesprenegocios;
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 2');
    $listaSituacoesprenegocios[] = $situacoesprenegocios;
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 3');
    $listaSituacoesprenegocios[] = $situacoesprenegocios;

    /* Execução da Funcionalidade */
    $I->sendRaw('GET', $this->url_situacoes . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SITUACOESPRENEGOCIOS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   *
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirSituacaoPreNegocio(FunctionalTester $I, \Codeception\Example $usuario){
    
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $situacoesprenegocios = $I->haveInDatabaseSituacoesprenegocios($I, 'cod 3');

    /* Execução da Funcionalidade */
    $I->sendRaw('GET', $this->url_situacoes . $situacoesprenegocios['situacaoprenegocio']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::SITUACOESPRENEGOCIOS_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   *
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarSituacaoPreNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* Execução da Funcionalidade */
    $situacao = [
      'codigo' => 'ST123',
      'nome' => 'Nome ST123',
      'cor' => 1
    ];
    $I->sendRaw('POST', $this->url_situacoes . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $situacao, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SITUACOESPRENEGOCIOS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   *
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarSituacaoPreNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $situacaoprenegocio = $I->haveInDatabaseSituacoesprenegocios($I);

    /* Execução da Funcionalidade */
    $situacaoprenegocio['codigo'] = 'EditCod';
    $situacaoprenegocio['cor'] = 10;

    $I->sendRaw('PUT', $this->url_situacoes . $situacaoprenegocio['situacaoprenegocio']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $situacaoprenegocio, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SITUACOESPRENEGOCIOS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   *
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirSituacaoPreNegocio(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $situacaoprenegocio = $I->haveInDatabaseSituacoesprenegocios($I);

    /* Execução da Funcionalidade */
    $I->sendRaw('DELETE', $this->url_situacoes . $situacaoprenegocio['situacaoprenegocio'] . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $situacaoprenegocio, [], [], null);

    /* Validação do Resultado */
    $httpRetorno = in_array(EnumAcao::SITUACOESPRENEGOCIOS_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   *
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarCampanhasDeOrigem(FunctionalTester $I, \Codeception\Example $usuario){

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    $listaPromocoes = [];
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);
    $listaPromocoes[] = $promocaoLead;
    $promocaoLead = $I->haveInDatabasePromocaoLead($I, [
        'codigo' => 'cod 2'
    ]);
    $listaPromocoes[] = $promocaoLead;
    $promocaoLead = $I->haveInDatabasePromocaoLead($I, [
        'codigo' => 'cod 3'
    ]);
    $listaPromocoes[] = $promocaoLead;

    /* Execução da funcionalidade */
    $I->sendRaw('GET', $this->url_promocoes . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROMOCOESLEADS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirCampanhaDeOrigem(FunctionalTester $I, \Codeception\Example $usuario){

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);

    /* Execução da funcionalidade */
    $I->sendRaw('GET', $this->url_promocoes . $promocaoLead['promocaolead']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROMOCOESLEADS_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarCampanhaDeOrigem(FunctionalTester $I, \Codeception\Example $usuario){

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* Inicializações */
    $promocao = [
      "codigo" => 'PromoCodigo1',
      "nome" => 'PromoNome1',
      "bloqueado" => true
    ];
    
    /* Execução da funcionalidade */
    $I->sendRaw('POST', $this->url_promocoes . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $promocao, [], [], null);

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROMOCOESLEADS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarCampanhaDeOrigem(FunctionalTester $I, \Codeception\Example $usuario){

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);

    /* Execução da funcionalidade */
    $promocaoLead['codigo'] = 'CodEditado1';
    $promocaoLead['nome'] = 'NomeEditado1';
    $I->sendRaw('PUT', $this->url_promocoes . $promocaoLead['promocaolead']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $promocaoLead, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROMOCOESLEADS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirCampanhaDeOrigem(FunctionalTester $I, \Codeception\Example $usuario){

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $promocaoLead = $I->haveInDatabasePromocaoLead($I);

    /* Execução da funcionalidade */
    $I->sendRaw('DELETE', $this->url_promocoes . $promocaoLead['promocaolead']. '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $promocaoLead, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PROMOCOESLEADS_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarListaDaVezVendedores(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $I->haveInDatabaseListadavezvendedor($I);
    $I->haveInDatabaseListadavezvendedor($I, [
        'nome' => 'Lista 123'
    ]);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'GET', //método
        $this->url_listadavezvendedores . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
        [], //body
        [],
        [],
        null
    );

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::LISTADAVEZVENDEDORES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirListaDaVezVendedores(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $listavendedor = $I->haveInDatabaseListadavezvendedor($I);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'GET', //método
        $this->url_listadavezvendedores . $listavendedor['listadavezvendedor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
        $listavendedor, //body
        [],
        [],
        null
    );

    /* Validação do resultado */
    $httpRetorno = in_array(EnumAcao::LISTADAVEZVENDEDORES_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarListaDaVezVendedores(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* execução da funcionalidade */
    $listavendedor = [
      'nome' => 'nome vendedor1',
      'totalmembros' => '0'
    ];

    $listavendedor_criado = $I->sendRaw(
        'POST', //método
        $this->url_listadavezvendedores . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
        $listavendedor, //body
        [],
        [],
        null
    );

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::LISTADAVEZVENDEDORES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

    /* remove dado criado no banco, caso ele tenha sido criado */
    if($httpRetorno == HttpCode::CREATED){
      $I->deleteFromDatabase('crm.listadavezvendedores', ['listadavezvendedor' => $listavendedor_criado['listadavezvendedor']]);
    }


  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarListaDaVezVendedores(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $listavendedor = $I->haveInDatabaseListadavezvendedor($I);

    /* Execução da funcionalidade */
    $listavendedor['nome'] = 'nome vendedor editado1';
    $I->sendRaw(
        'PUT', //método
        $this->url_listadavezvendedores . $listavendedor['listadavezvendedor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
        $listavendedor, //body
        [],
        [],
        null
    );

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::LISTADAVEZVENDEDORES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirListaDaVezVendedores(FunctionalTester $I, \Codeception\Example $usuario){
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    $listavendedor = $I->haveInDatabaseListadavezvendedor($I);
    $I->sendRaw(
        'DELETE', //método
        $this->url_listadavezvendedores . $listavendedor['listadavezvendedor'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, //url
        $listavendedor, //body
        [],
        [],
        null
    );

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::LISTADAVEZVENDEDORES_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * Deve proibir usuário de executar a funcionalidade
   * 
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarListaDaVezConfiguracoes(FunctionalTester $I, \Codeception\Example $usuario){
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario']);
    
    // Regra
    $regraFixa = $I->haveInDatabaseListadaVezRegra($I);
    // 1 - Pai
    $config_1 = $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
      'listadavezregra' => $regraFixa
    ]);
    // 1.1 - Caso alternativo
    $I->haveInDatabaseCrmListaDaVezConfiguracao($I, [
        'idpai' => $config_1['listadavezconfiguracao'],
        'tiporegistro' => 2,
        'listadavezregra' => $regraFixa
    ]);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'GET', //método
        $this->url_listadavezconfiguracoes . '?grupoempresarial=' . $this->grupoempresarial, //url
        [], //body
        [],
        [],
        null
    );

    /* validação do resultado */ 
    $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeSalvarListaDaVezConfiguracoes(FunctionalTester $I, \Codeception\Example $usuario){
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario']);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'POST', //método
        $this->url_listadavezconfiguracoes . 'salvarlote/?grupoempresarial=' . $this->grupoempresarial, //url
        [], //body
        [],
        [],
        null
    );

    /* validação do resultado */ 
    $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeBuscarPainelDeMarketing(FunctionalTester $I, \Codeception\Example $usuario){
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario']);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'GET', //método
        $this->url_painelmarketing . '?grupoempresarial=' . $this->grupoempresarial, //url
        [], //body
        [],
        [],
        null
    );

    /* validação do resultado */ 
    $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioNaoPodeListarContasPagar(FunctionalTester $I, \Codeception\Example $usuario){
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario']);
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'GET', //método
        str_replace("{atc}", $atc['negocio'], $this->url_contaspagar) . '?grupoempresarial=' . $this->grupoempresarial, //url
        [], //body
        [],
        [],
        null
    );

    /* validação do resultado */ 
    $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioNaoPodeAlterarContasPagar(FunctionalTester $I, \Codeception\Example $usuario){
    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario']);
    $origem = $I->haveInDatabaseMidia($I);
    $area = $I->haveInDatabaseAreaDeAtc($I);
    $estado = $I->haveInDataBaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipio($I, $estado);
    $pais = $I->haveInDatabasePais($I);
    $cliente = $I->haveInDatabaseCliente($I, $municipio, $pais);
    $atc = $I->haveInDatabaseAtc($I, $area, $origem, $cliente);
    $fornecedor = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
    $fornecedorTerceirizado1 = $I->haveInDatabaseFornecedor($I, true, $atc['estabelecimento']['estabelecimento']);
    $composicao = $I->haveinDatabaseComposicao($I);
    $orcamento1= $I->haveInDatabaseOrcamento($I, [
        'atc' => $atc['negocio'],
        'fornecedor' => $fornecedor,
        'composicao' => $composicao['composicao'],
        'status' => 2, // Aprovado
        'servicotipo' => 1, // Serviço Externo
        'fornecedorterceirizado' => $fornecedorTerceirizado1['fornecedor']
    ]);
    $contapagar= $I->haveInDatabaseCrmAtcContaPagar($I, [
        'atc' => $atc['negocio'],
        'prestador' => $fornecedorTerceirizado1,
        'servico' => $composicao['composicao'],
        'orcamento' => $orcamento1['orcamento']
    ]);

    /* Execução da funcionalidade */
    $I->sendRaw(
        'PUT', //método
        str_replace("{atc}", $atc['negocio'], $this->url_contaspagar) . $contapagar['atccontaapagar'] . '?grupoempresarial=' . $this->grupoempresarial, //url
        $contapagar, //body
        [],
        [],
        null
    );

    /* validação do resultado */ 
    $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
  }
    
  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarCidadeFuneraria(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);

    $municipio['codigo'] = $municipio['ibge'];
    $municipio['uf'] = ['uf' => $estado['uf'], 'nome' => $estado['nome']];
    unset($municipio['federal'], $municipio['ibge']);
    $fornecedores = [];

    /* Execução da funcionalidade */
    $cidade = [
      'fornecedores' => $fornecedores,
      'pais' => $pais,
      'estado' => $estado,
      'municipio' => $municipio,
      'possuisvo' => true,
      'possuicrematorio' => true,
      'possuicemiteriomunicipal' => true,
      'possuicapelamunicipal' => true,
      'trabalhacomfloresnaturais' => true,
      'possuiiml' => true,
      'perfilfunerario' => 1
    ];
    $cidade_criada = $I->sendRaw('POST', $this->url_cidadesinformacoesfunerarias . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::CIDADESINFORMACOESFUNERARIAS_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

    /* remove dado criado no banco, caso ele tenha sido criado */
    if($httpRetorno == HttpCode::CREATED){
      $I->deleteFromDatabase('ns.cidadesinformacoesfunerarias', ['cidadeinformacaofuneraria' => $cidade_criada['cidadeinformacaofuneraria']]);
    }

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarCidadeFuneraria(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->grupoempresarial_id);

    /* execução da funcionalidade */
    $cidade['possuisvo'] = false;
    $cidade['possuiiml'] = false;
    $cidade['possuicrematorio'] = false;
    $I->sendRaw('PUT', $this->url_cidadesinformacoesfunerarias . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::CIDADESINFORMACOESFUNERARIAS_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarCidadesFunerarias(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio1 = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $municipio2 = $I->haveInDatabaseMunicipioUnico($I, $estado, '1111111', '00');

    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio1, $this->grupoempresarial_id);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio2, $this->grupoempresarial_id);

    /* execução da funcionalidade */
    $I->sendRaw('GET', $this->url_cidadesinformacoesfunerarias .'?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $cidade, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::CIDADESINFORMACOESFUNERARIAS_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExibirCidadeFuneraria(FunctionalTester $I, \Codeception\Example $usuario){

    /* Inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $pais = $I->haveInDatabasePais($I);
    $estado = $I->haveInDatabaseEstado($I);
    $municipio = $I->haveInDatabaseMunicipioUnico($I, $estado);
    $cidade = $I->haveInDatabaseCidadeInformacaoFuneraria($I, $pais, $estado, $municipio, $this->grupoempresarial_id);

    /* execução da funcionalidade */
    $I->sendRaw('GET', $this->url_cidadesinformacoesfunerarias . $cidade['cidadeinformacaofuneraria'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::CIDADESINFORMACOESFUNERARIAS_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeListarPrioridades(FunctionalTester $I, \Codeception\Example $usuario) {

    // Inicializações
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* Execução da funcionalidade */
    $I->sendRaw('GET', $this->url_prioridades . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PRIORIDADES_INDEX, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeRetornarPrioridade(FunctionalTester $I, \Codeception\Example $usuario) {

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $prioridade = $I->haveInDatabasePrioridades($I);

    $I->sendRaw('GET', $this->url_prioridades . $prioridade['prioridade'] . '?tenant=' . $this->tenant . "&grupoempresarial=" . $this->grupoempresarial, null, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PRIORIDADES_GET, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeCriarPrioridade(FunctionalTester $I, \Codeception\Example $usuario) {

    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* execução da funcionalidade */
    $prioridade = [
        'nome' => 'Prioridade Teste',
        'ordem' => 0,
        'prazoexpiracao' => 120,
        'notificarexpiracaofaltando' => 30,
        'prioridadepadrao' => false,
        'descricao' => 'Descrição Prioridade Teste',
        'cor' => '#1FCA5D'
    ];

    $prioridade_criada = $I->sendRaw('POST', $this->url_prioridades . '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PRIORIDADES_CREATE, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

    /* remove dado criado no banco, caso ele tenha sido criado */
    if($httpRetorno == HttpCode::CREATED){
      $I->deleteFromDatabase('ns.prioridades', ['prioridade' => $prioridade_criada['prioridade']]);
    }
    
  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeEditarPrioridade(FunctionalTester $I, \Codeception\Example $usuario) {

    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

    /* inicializações */
    $prioridade = $I->haveInDatabasePrioridades($I, []);

    /* execução da funcionalidade */
    $prioridade['nome'] = 'Nome editado';
    $prioridade['ordem'] = 77;
    $prioridade['prazoexpiracao'] = 240;
    $prioridade['notificarexpiracaofaltando'] = 60;
    $prioridade['prioridadepadrao'] = true;
    $prioridade['descricao'] = 'Descrição editada';
    $prioridade['cor'] = '#1FCA5D';

    $I->sendRaw('PUT', $this->url_prioridades .$prioridade['prioridade']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, $prioridade, [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PRIORIDADES_PUT, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

  /**
   * @param FunctionalTester $I
   * @dataProvider UsuarioProvider
   */
  public function verificaSeUsuarioPodeExcluirPrioridade(FunctionalTester $I, \Codeception\Example $usuario){

    /* inicializações */
    $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
    $prioridade = $I->haveInDatabasePrioridades($I);

    $I->sendRaw('DELETE', $this->url_prioridades .$prioridade['prioridade']. '?tenant=' . $this->tenant. '&grupoempresarial='.$this->grupoempresarial, [], [], [], null);

    /* validação do resultado */
    $httpRetorno = in_array(EnumAcao::PRIORIDADES_DELETE, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
    $I->seeResponseCodeIs($httpRetorno);

  }

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
     * Reabre todos os orçamentos do fornecedor
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeReabrirOrcamentosDoFornecedor(FunctionalTester $I, \Codeception\Example $usuario){
        
        $url_complemento_fornecedoresenvolvidos = 'fornecedoresenvolvidos';
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

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

        $dados = [];

        /* execução da funcionalidade */
        $retorno = $I->sendRaw('POST', "/api/{$this->tenant}/{$atc['negocio']}/$url_complemento_fornecedoresenvolvidos/{$fornecedoreEnvolvido['fornecedorenvolvido']}/reabrirorcamentosnegociofornecedor?grupoempresarial={$this->grupoempresarial}" , [], [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::ORCAMENTOS_REABRIR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        //Excluo dados criados a partir da minha requisição
        $I->deleteFromDatabase('crm.historicoatcs', ['negocio' => $atc['negocio']]);
    }


    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeCriarContratoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

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
        $httpRetorno = in_array(EnumAcao::CONTRATOSTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        //limpando banco
        $contratosRecebimento = $I->grabColumnFromDatabase('financas.contratos', 'contrato', [
            'tipocontrato' => '1',
            'tenant' => $this->tenant_numero
        ]);
        if(!empty($contratosRecebimento)){
            $contratoRecebimento = $contratosRecebimento[0];
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
    }

    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeSalvarContratoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

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
        $httpRetorno = in_array(EnumAcao::CONTRATOSTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
    }


    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeSalvarExcluirContratoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

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
        $httpRetorno = in_array(EnumAcao::CONTRATOSTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
    }

    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeCriarConfiguracaoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

        /* execução da funcionalidade */

        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);
        $cliente = $I->haveInDatabaseCliente($I);
        $formapagamento = $I->haveInDatabaseFormapagamento($I);
        $municipioprestacao = $I->haveInDatabaseClienteCommunicipioPrestacao($I, $cliente);

        $dados = [
            'estabelecimento' => $estabelecimento,
            'seguradora' => $cliente,
            'itemfaturamento' => ['servico' => 'baa4ff9a-6f59-4963-853d-6d318fe83006'],
            'formapagamento' => $formapagamento,
            'municipioprestacao' => $municipioprestacao,
            'pessoa' => $cliente['id'],
            'valor' => 300,
        ];
        $configuraxaoTaxaCriada = $I->sendRaw('POST', '/api/gednasajon/configuracoestaxasadministrativas/?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dados, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CFGTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::CREATED : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);

        /* remove dado criado no banco*/
        if(isset($configuraxaoTaxaCriada['configuracaotaxaadm'])){
            $I->deleteFromDatabase('crm.configuracoestaxasadministrativas', ['configuracaotaxaadm' => $configuraxaoTaxaCriada['configuracaotaxaadm']]);
        }
    }

    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeEditaConfiguracaoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);
        
        /* execução da funcionalidade */
        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);
        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento);


        $dadosEnvio = [
            'estabelecimento' => ['estabelecimento' => $configuracaotaxaadm['estabelecimento']],
            'seguradora' => ['cliente' => $configuracaotaxaadm['seguradora']],
            'itemfaturamento' => ['servico' => $configuracaotaxaadm['itemfaturamento']],
            'formapagamento' => ['formapagamento' => $configuracaotaxaadm['formapagamento']],
            'municipioprestacao' => ['pessoamunicipio' => $configuracaotaxaadm['municipioprestacao']],
            'pessoa' => $configuracaotaxaadm['seguradora'],
            'valor' => $configuracaotaxaadm['valor'],
        ];

        $dadosEnvio['valor'] += 100;

        $I->sendRaw('PUT', '/api/gednasajon/configuracoestaxasadministrativas/' . $configuracaotaxaadm['configuracaotaxaadm'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, $dadosEnvio, [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CFGTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
    }


    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeIndexConfiguracaoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

        /* inicializações */
        $configuracoes = [];

        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);

        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 100]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 200]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 400]);

        $countAtual = $I->grabNumRecords('crm.configuracoestaxasadministrativas', ['tenant' => $this->tenant_numero]);

        /* execução da funcionalidade */
        $lista = $I->sendRaw('GET', '/api/gednasajon/configuracoestaxasadministrativas/' . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CFGTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
    }

    /**
     * @param FunctionalTester $I
     * @dataProvider UsuarioProvider
     * @return void
     */
    public function verificaSeUsuarioPodeGetConfiguracaoTaxaAdministrativa(FunctionalTester $I, \Codeception\Example $usuario){
        $I->amSamlLoggedInAs($usuario['usuario'], $usuario['permissoes']);

        /* inicializações */
        $configuracoes = [];

        $empresa['empresa'] = $I->haveInDatabaseEmpresa($I, ['id_grupoempresarial' => $this->grupoempresarial_id]);
        $estabelecimento = $I->haveInDatabaseEstabelecimento($I, ['empresa' => $empresa['empresa']]);

        $configuracaotaxaadm = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 100]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 200]);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento);
        $configuracoes[] = $I->haveInDatabaseConfiguracaoTaxaAdministrativa($I, $estabelecimento, ['valor' => 400]);

        /* execução da funcionalidade */
        $configuracao = $I->sendRaw('GET', '/api/gednasajon/configuracoestaxasadministrativas/' . $configuracaotaxaadm['configuracaotaxaadm'] . '?tenant=' . $this->tenant . '&grupoempresarial=' . $this->grupoempresarial, [], [], [], null);

        /* validação do resultado */
        $httpRetorno = in_array(EnumAcao::CFGTAXASADM_GERENCIAR, $usuario['permissoes'] ) ? HttpCode::OK : HttpCode::FORBIDDEN; 
        $I->seeResponseCodeIs($httpRetorno);
    }
}