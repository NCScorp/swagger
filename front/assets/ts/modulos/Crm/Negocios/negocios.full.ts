import angular = require('angular');
import { INegocioApi, EnumTipoNegocio, EnumClienteQualificacao, EnumNegocioContatoCargo, EnumTipoQualificacao } from "./classes";
import { ConfiguracaoService } from '../../../inicializacao/configuracao.service';
// import { InicializacaoService } from '../../../inicializacao/inicializacao.service';
import { NsjConfig } from '@nsj/core';

export class CrmNegociosFullController {
    static $inject = [
        'entity',
        '$state',
        'ConfiguracaoService',
        // 'InicializacaoService',
        '$scope',
        'CrmSituacoesprenegocios',
        'CrmNegociosFormQualificacaoService',
        'CrmNegociosFormDesqualificacaoService',
        'Usuarios',
        '$http',
        'nsjRouting'
    ];

    private EnumSituacaoTela = EnumSituacaoTela;
    private EnumAcoesTela = EnumAcoesTela;
    public bloqueiaBotaoAdicionarFollowup: boolean = false;
    
    /**
     * Define se o negócio está sendo criado, editado ou visualizado
     */
    private situacaoTela: EnumSituacaoTela = EnumSituacaoTela.stNovo;

    private vendedorUsuarioLogado;

    constructor(
        private entity: INegocioApi = null,
        private $state: any,
        private ConfiguracaoService: ConfiguracaoService,
        // private InicializacaoService: InicializacaoService,
        private $scope: any,
        private CrmSituacoesprenegocios: any,
        private CrmNegociosFormQualificacaoService: any,
        private CrmNegociosFormDesqualificacaoService: any,
        public Usuarios: any,
        public $http: angular.IHttpService,
        public nsjRouting: any
    ){}

    /**
     * Função chamada ao inicializar tela
     */
    async $onInit(){
        // Se a entidade não estiver preenchida, inicializo dados
        if (this.entity == null) {
            
            const grupoempresarial_id = NsjConfig.getConfig('globals').grupoEmpresarialCodigo;
            const situacaoNovoID = this.ConfiguracaoService.getConfiguracao('SITUACAOPRENEGOCIOPARACRIAR', grupoempresarial_id);

            // Botão de adicionar followup fica desativado na criação de um negócio
            this.bloqueiaBotaoAdicionarFollowup = true;

            this.entity = {
                prenegocio: 1,
                tiponegocio: EnumTipoNegocio.tnNegocio,
                cliente_qualificacao: EnumClienteQualificacao.cqCNPJ,
                buscoucliente: false,
                ehcliente: 0,
                clientereceitaanual: 5000000,
                observacao: '',
                negocioscontatos: [
                    {
                        cargo: EnumNegocioContatoCargo.nccSocioProprietarioCEO
                    }
                ],
            };

            //Feito para o campo Atribuição já vir preenchido, caso o usuário logado seja um vendedor/captador
            this.vendedorUsuarioLogado = await this.verificaVendedor(this.Usuarios.profile.email).then(function(vendedor: Array<any>){
                return vendedor.length > 0 ? vendedor[0] : null;
            });
            this.entity.clientecaptador = this.vendedorUsuarioLogado;

            if (situacaoNovoID != null) {
                const situacao = await this.CrmSituacoesprenegocios.get(situacaoNovoID);
                this.entity.situacaoprenegocio = situacao;
            }
        } else {
            this.situacaoTela = EnumSituacaoTela.stVisualizacao;
            this.entity.ehcliente = this.entity.ehcliente ? 1 : 0;
            this.entity.prenegocio = this.entity.prenegocio ? 1 : 0;
            
            if (typeof this.entity.clientereceitaanual == 'string'){
                this.entity.clientereceitaanual = parseInt(this.entity.clientereceitaanual);
                
            }
        }

        this.$scope.$on('crm_negocios_submitted', (event: any, args: any) => {
            if (this.situacaoTela == EnumSituacaoTela.stEdicao) {
                this.situacaoTela = EnumSituacaoTela.stVisualizacao;
            }
        });
    }

    /**
     * Retorna se a tela está em modo de criação ou visualização
     */
    private ehCriacaoEditao(): boolean {
        return [EnumSituacaoTela.stNovo, EnumSituacaoTela.stEdicao].indexOf(this.situacaoTela) > -1;
    }

    /**
     * Abre tela de edição
     */
    private mostrarEditar(): void {
        // Altero a situação da tela para edição
        this.situacaoTela = EnumSituacaoTela.stEdicao;
    }

    /**
     * Sai do formulário de criação/edição.
     *  - Se estiver na ediçao: Abre a visualização.
     *  - Se estiver na criação: Volta a listagem.
     */
    private cancelarCriacaoEdicao(): void {
        if (this.situacaoTela == EnumSituacaoTela.stEdicao) {
            this.situacaoTela = EnumSituacaoTela.stVisualizacao;
        } else {
            this.$state.go("crm_negocios({prenegocio: '1'})");
        }
    }

    /**
     * Abre modal para qualificar o negócio
     */
    private abrirModalQualificar(){
        let modal = this.CrmNegociosFormQualificacaoService.open(this.entity);

        modal.result.then((retorno: any) => {
            this.$state.reload();
        }).catch((err) => {});
    }

    /**
     * Abre modal para desqualificar o negócio
     */
    private abrirModalDesqualificar(){
        let modal = this.CrmNegociosFormDesqualificacaoService.open(this.entity);

        modal.result.then((retorno: any) => {
            this.$state.reload();
        }).catch((err) => {});
    }

    /**
     * Verifica se uma ação pode ser apresentada
     * @param acao 
     */
    private mostrarAcao(acao: EnumAcoesTela): boolean {
        let mostrar = true;

        switch (acao) {
            // Qualificar negócio
            case EnumAcoesTela.atQualificar: {
                mostrar = this.situacaoTela != EnumSituacaoTela.stNovo &&
                    this.entity.tipoqualificacaopn == EnumTipoQualificacao.tqSemQualificacao;
                break;
            }

            // Desqualificar negócio
            case EnumAcoesTela.atDesqualificar: {
                mostrar = this.situacaoTela != EnumSituacaoTela.stNovo &&
                    this.entity.tipoqualificacaopn == EnumTipoQualificacao.tqSemQualificacao;
                break;
            }

            // Editar negócio
            case EnumAcoesTela.atEditar: {
                mostrar = this.situacaoTela == EnumSituacaoTela.stVisualizacao && this.entity.documento != null
                    && this.entity.tipoqualificacaopn == EnumTipoQualificacao.tqSemQualificacao;
                break;
            }

            default:
                break;
        }

        return mostrar;
    }

    public temPermissao(chave: any){
        return this.Usuarios.temPermissao(chave);
    }

    /**
    * Faz requisição para verificar se o usuário logado é um vendedor através do email, se for, retorno o vendedor encontrado
    */
    private verificaVendedor(email){
        return new Promise(async(resolve, reject) => {
            await this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('ns_vendedores_index', {vendedoremail: email}, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            });
        });
    }

}

/**
 * Representação do estado da tela
 */
enum EnumSituacaoTela {
    stNovo,
    stEdicao,
    stVisualizacao
}

/**
 * Representação das ações da tela
 */
enum EnumAcoesTela {
    atEditar,
    atQualificar,
    atDesqualificar
}
