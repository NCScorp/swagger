import angular = require('angular');
import { Object } from 'core-js';
import { INegocioListaApi, EnumTipoQualificacao } from './classes';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';
import { EnumSituacaoPreNegocioCor } from '../Situacoesprenegocios/default.form';


export class CrmNegociosListController {
    static $inject = [
        'utilService', 
        '$scope', 
        '$stateParams', 
        '$state', 
        'CrmNegocios', 
        'toaster',
        'clientecaptadorFilter',
        'vendedorFilter',
        '$rootScope', 
        '$location', 
        'nsjRouting',
        'CommonsUtilsService'
    ];

    /**
     * Título da tela. Muda caso seja tela de pré negócios ou negócios qualificados
     */
    private tituloTela: string = 'Negócios para Qualificação';
    private EnumTipoTela = EnumTipoTela;

    /**
     * Define se, na listagem de negócios para qualificação, deve buscar os negócios desqualificados
     */
    private buscarDesqualificados: boolean = false;

    /**
     * Define se é a tela de negócios para qualificação, ou negócios qualificados
     */
    public tipoTela: EnumTipoTela = EnumTipoTela.ttNegociosParaQualificacao;

    public busy: boolean = false;
    public entities: any;
    public fields: any;
    public service: any;
    public filters: any;
    public selected: any = [];
    public selectPage: boolean = false;
    public selectAll: boolean = false;
    
    constructor (
        public utilService: any, 
        public $scope: any, 
        public $stateParams: any, 
        public $state: any, 
        public entityService: any, 
        public toaster: any,
        public clientecaptadorFilter: any,
        public vendedorFilter: any,
        public $rootScope: any, 
        public $location: any, 
        public nsjRouting: any,
        public CommonsUtilsService: CommonsUtilsService
    ) {
        entityService.filter = $stateParams.q ? $stateParams.q : '';
        entityService.filters = {};
        entityService.constructors = {};
        for (let i in $stateParams) {
            if (['prenegocio', 'tipoqualificacaopn', 'clientecaptador', 'vendedor',].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                entityService.filters[i] = $stateParams[i];
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'documento') {
                entityService.constructors[i] = $stateParams[i];
            }
        }
        this.service = entityService;
        this.filters = entityService.filters;
        this.entities = entityService.reload();
        this.fields = $rootScope.FIELDS_CrmNegocios;
        $scope.$on('crm_negocios_deleted', (event: any) => {
            this.entityService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.entityService.loading_deferred) {
                this.entityService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('crm_negocios_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.entityService.reload();
            }
        });
    }


    $onInit(){
        // Formato as datas de created_at e updated_at para o formato padrão
        this.$rootScope.$on('crm_negocios_list_finished', (event: any, args: any) => {
            (<INegocioListaApi[]>this.entities).forEach(negocio => {
                negocio.created_at_formatado = this.CommonsUtilsService.getDataFormatoPadrao(negocio.created_at);

                if (negocio.datahoraultimofollow != null){
                    negocio.datahoraultimofollow_formatado = this.CommonsUtilsService.getDataFormatoPadrao(negocio.datahoraultimofollow);
                } else {
                    negocio.datahoraultimofollow_formatado = "";
                }
            });
        });

        let alterouFiltro = false;

        //Caso filtro de pré negócio não tenha sido passado, configuro para negócios para qualificação
        if (this.service.filters['prenegocio'] == undefined){
            this.service.filters['prenegocio'] = '1';
            alterouFiltro = true;
        }

        // Defino tipo de tela
        if (this.service.filters['prenegocio'] == '1'){
            this.tipoTela = EnumTipoTela.ttNegociosParaQualificacao;
        } else {
            this.tipoTela = EnumTipoTela.ttNegociosQualificados;
            this.tituloTela = 'Negócios Qualificados';
        }

        // Só chamo a configuração de campos da lista, após decidir o tipo de tela
        this.configurarCamposLista();

        if (this.service.filters['tipoqualificacaopn'] == undefined && this.service.filters['tipoqualificacaopn[0]'] == undefined){
            // Se for tela de negócios para qualificação, me certifico que filtre por negócios sem qualificação
            // Senão, filtro somente negócios qualificados
            this.service.filters['tipoqualificacaopn'] = (this.tipoTela == EnumTipoTela.ttNegociosParaQualificacao) ?
                EnumTipoQualificacao.tqSemQualificacao : EnumTipoQualificacao.tqQualificado;
            alterouFiltro = true;
        }

        if (alterouFiltro) {
            this.filters = this.entityService.filters;
            this.entities = this.entityService.reload();
        }

        this.$scope.$applyAsync();
    }

    /**
     * Sobrescrita para garantir a busca filtrando por pré negócio
     * @param filter 
     */
    search(filter: any) {
        // Adiciono filtro de pré negócio, de acordo com o tipo da tela.
        let filters = filter.filters ? filter.filters : {};
        filters.prenegocio = this.tipoTela == EnumTipoTela.ttNegociosParaQualificacao ? '1' : '0';

        // Caso seja a tela de negócios para qualificaçao, só busco negócios desqualificados caso seja setado para isso
        if (this.tipoTela == EnumTipoTela.ttNegociosParaQualificacao) {

            filters['tipoqualificacaopn[0]'] = EnumTipoQualificacao.tqSemQualificacao;

            if (this.buscarDesqualificados) {
                filters['tipoqualificacaopn[1]'] = EnumTipoQualificacao.tqDesqualificado
            }
        }

        filter.filters = filters;

        // Chamo busca da classe pai
        let entities = this.entityService.search(filter);
        let filterURL = angular.copy(this.entityService.filters);

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.entityService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    /**
     * Altera as configurações da listagem original
     */
    private configurarCamposLista(){
        // Adiciono configuração de meus campos de data na listagem, caso ainda não estejam lá
        if (this.fields.findIndex(field => {
            return field.value == 'created_at_formatado'
        }) < 0) {
            this.fields.push({
                value: 'created_at_formatado',
                label: 'Primeiro Contato',
                type: 'string',
                style: 'default',
                copy: ''
            });
        }

        if (this.fields.findIndex(field => {
            return field.value == 'datahoraultimofollow_formatado'
        }) < 0) {
            this.fields.push({
                value: 'datahoraultimofollow_formatado',
                label: 'Última atualização',
                type: 'string',
                style: 'default',
                copy: ''
            });
        }

        // Busco configuração do campo de faturamento
        let configFaturamentoIndex = this.fields.findIndex((fieldIndex) => {
            return fieldIndex.value == 'clientereceitaanual';
        });

        //Faço comparar valor em vez de texto, pois o float do banco vem string com zeros no final
        this.fields[configFaturamentoIndex].options = {
            "Até R$ 5 mi": "entity.clientereceitaanual == 5000000",​​​​​
            "Até R$ 300 mi": "entity.clientereceitaanual == 300000000",
            "Até R$ 100 mi": "entity.clientereceitaanual ==  100000000",
            "Até R$ 500 mi": "entity.clientereceitaanual == 500000000",
            "Até R$ 30 mi": "entity.clientereceitaanual == 30000000",
            "Até R$ 1 bi": "entity.clientereceitaanual == 1000000000",
            "Mais de R$ 1 bi": "entity.clientereceitaanual == 2000000000",
        }

        // Busco configuração de situação para adicionar estilo
        let configSituacaoIndex = this.fields.findIndex((fieldIndex) => {
            return fieldIndex.value == 'situacaoprenegocio.nome';
        });
        
        this.fields[configSituacaoIndex].style = "label";

        this.fields[configSituacaoIndex].label_class = `{
            'label-situacaoprenegocio-vermelho': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnVermelho},
            'label-situacaoprenegocio-laranja': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnLaranja},
            'label-situacaoprenegocio-abobora': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnAbobora},
            'label-situacaoprenegocio-amarelo': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnAmarelo},
            'label-situacaoprenegocio-verdeclaro': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnVerdeClaro},

            'label-situacaoprenegocio-verde': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnVerde},
            'label-situacaoprenegocio-azulclaro': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnAzulClaro},
            'label-situacaoprenegocio-azul': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnAzul},
            'label-situacaoprenegocio-roxo': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnRoxo},
            'label-situacaoprenegocio-rosa': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnRosa},

            'label-situacaoprenegocio-preto': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnPreto},
            'label-situacaoprenegocio-cinza1': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnCinza1},
            'label-situacaoprenegocio-cinza2': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnCinza2},
            'label-situacaoprenegocio-cinza3': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnCinza3},
            'label-situacaoprenegocio-cinza4': entity.situacaoprenegocio.cor === ${EnumSituacaoPreNegocioCor.spnCinza4},
        }`;
        
        // Configuro a atribuição, de acordo com o tipo de tela.
        // Negócios para qualificar: clientecaptador; Negócios qualificados: vendedor;
        if (this.tipoTela == EnumTipoTela.ttNegociosQualificados) {
            let indiceAtribuicao = this.fields.findIndex(field => {
                return field.value == 'clientecaptador.vendedor_nome';
            });

            if (indiceAtribuicao > -1){
                this.fields[indiceAtribuicao].value = 'vendedor.vendedor_nome';
            }
        } else {
            let indiceAtribuicao = this.fields.findIndex(field => {
                return field.value == 'vendedor.vendedor_nome';
            });

            if (indiceAtribuicao > -1){
                this.fields[indiceAtribuicao].value = 'clientecaptador.vendedor_nome';
            }
        }
    }

    loadMore() {
        this.entityService.loadMore();
    }

    generateRoute(route: any, params: any) {
        return this.nsjRouting.generate(route, params);
    }

    isBusy() {
        return this.entityService.loadParams.busy;
    }
}
enum EnumTipoTela {
    ttNegociosParaQualificacao,
    ttNegociosQualificados
}
;