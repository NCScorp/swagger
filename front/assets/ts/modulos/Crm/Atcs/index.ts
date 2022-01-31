import angular = require('angular');
import { Object } from 'core-js';
// import { CrmAtcsListController } from './../../Crm/Atcs/index';
import { CamposcustomizadosService } from '../../Commons/camposcustomizados/CamposcustomizadosService';
import { inArray } from '../../Commons/utils';
import { ConfiguracaoService } from '../../../inicializacao/configuracao.service';

export class CrmAtcsListController {

    static $inject = [
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'CrmAtcs',
        'toaster',
        'clienteFilter',
        'areaFilter',
        '$rootScope',
        '$location',
        'nsjRouting',
        'FIELDS_CrmAtcs',
        '$http',
        'moment',
        '$parse',
        'CamposcustomizadosService',
        '$window',
        'ConfiguracaoService'
    ];

    public datahorainicio: any;
    public datahorafim: any;
    public datacriacao: any;
    public dataedicao: any;
    public busy: boolean = false;
    public filtroExibido: any;
    public entities: any;
    public fields: any;
    public service: any;
    public filters: any;
    public selected: any = [];
    public selectPage: boolean = false;
    public selectAll: boolean = false;
    public datacriacaoStartDate: string;
    public datacriacaoEndDate: string;
    public dataedicaoStartDate: string;
    public dataedicaoEndDate: string;

    public rulesAttr: any = {
        negocioarea: 'this.areaFilter.atcarea',
    };
    
    public linksExternos: any;

    constructor(
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public entityService: any,
        public toaster: any,
        public clienteFilter: any,
        public areaFilter: any,
        public $rootScope: any,
        public $location: any,
        public nsjRouting: any,
        public FIELDS_CrmAtcs: any,
        public $http : any,
        public moment: any,
        public $parse: any,
        public camposCustomizadoService: CamposcustomizadosService,
        public $window: angular.IWindowService,
        private configuracaoService: ConfiguracaoService
       
        
    ) {
        entityService.filter = $stateParams.q ? $stateParams.q : '';
        entityService.filters = {};
        entityService.constructors = {};
        for (let i in $stateParams) {
            if (['negociopai', 'cliente', 'localizacaopaisnome', 'localizacaoestadonome', 'localizacaomunicipionome', 'status', 'possuiseguradora', 'area',].indexOf(i) > -1 && $stateParams[i] !== undefined) {
                entityService.filters[i] = $stateParams[i];
            } else if (i === 'datacriacao_inicio' && $stateParams[i] !== undefined) {
                if (!entityService.filters.datacriacao) {
                    entityService.filters.datacriacao = {};
                }
                entityService.filters.datacriacao.inicio = { 'condition': 'gte', 'value': $stateParams[i] };
            } else if (i === 'datacriacao_fim' && $stateParams[i] !== undefined) {
                if (!entityService.filters.datacriacao) {
                    entityService.filters.datacriacao = {};
                }
                entityService.filtersdatacriacao.fim = { 'condition': 'lte', 'value': $stateParams[i] };
            } else if (i === 'dataedicao_inicio' && $stateParams[i] !== undefined) {
                if (!entityService.filters.dataedicao) {
                    entityService.filters.dataedicao = {};
                }
                entityService.filters.dataedicao.inicio = { 'condition': 'gte', 'value': $stateParams[i] };
            } else if (i === 'dataedicao_fim' && $stateParams[i] !== undefined) {
                if (!entityService.filters.dataedicao) {
                    entityService.filters.dataedicao = {};
                }
                entityService.filtersdataedicao.fim = { 'condition': 'lte', 'value': $stateParams[i] };
            } else if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negocio') {
                entityService.constructors[i] = $stateParams[i];
            }
        }
        this.service = entityService;
        this.filters = entityService.filters;
        if (typeof (entityService.filters.datacriacao) !== 'undefined') {
            this.datacriacaoStartDate = entityService.filters.datacriacao.inicio.value.split(' ')[0];
            this.datacriacaoEndDate = entityService.filters.datacriacao.fim.value.split(' ')[0];
        }
        if (typeof (entityService.filters.dataedicao) !== 'undefined') {
            this.dataedicaoStartDate = entityService.filters.dataedicao.inicio.value.split(' ')[0];
            this.dataedicaoEndDate = entityService.filters.dataedicao.fim.value.split(' ')[0];
        }
        this.entities = entityService.reload();
        this.fields = $rootScope.FIELDS_CrmAtcs;
        $scope.$on('crm_atcs_deleted', (event: any) => {
            this.entityService.reload();
        });
        $scope.$on('$destroy', () => {
            if (this.entityService.loading_deferred) {
                this.entityService.loading_deferred.resolve();
            }
        });
        $rootScope.$on('crm_atcs_submitted', (event: any, args: any) => {
            if (!args.autosave) {
                this.entityService.reload();
            }
        });


        this.busy = true;

        /* Sobrescrito para carregar as colunas vindas dos campos customizados 
        *  Obs.:
        *    O bloco abaixo estava originalmente no constant.ts customizado e foi movido
        *    para o controller por conta da ordem de inicialização do keycloak
        */

        /* limpar colunas de campos customizados */
        this.removeCamposcustomizados();

        $http({
            method: 'GET',
            url: nsjRouting.generate('ns_camposcustomizados_index', { 'crmatcexibenalistagem': '1' }, true)
        }).then((response: any) => {
            let camposcustomizados = this.camposCustomizadoService.formatavisibilidade(response.data, {escopo: this, regra: /negocioarea/, dicionario: this.rulesAttr });
            for (let campo in camposcustomizados) {
                if (camposcustomizados[campo]['tipo'] == 'OB') {
                    for (let atributo in camposcustomizados[campo].objeto) {
                        if (camposcustomizados[campo].objeto[atributo].crmatcexibenalistagem == 'true' && camposcustomizados[campo].visible && camposcustomizados[campo].objeto[atributo].visible && !inArray(FIELDS_CrmAtcs, 'label', camposcustomizados[campo].objeto[atributo].label)) {
                            FIELDS_CrmAtcs.push(
                                {
                                    value: this.obterValorCampo(camposcustomizados[campo],atributo),
                                    label: camposcustomizados[campo].objeto[atributo].label,
                                    type: 'string',
                                    style: 'title',
                                    copy: '',
                                    campocustomizado: true
                                })
                        }
                    }
                } else {
                    if (camposcustomizados[campo].visible && !inArray(FIELDS_CrmAtcs, 'label', camposcustomizados[campo].label)) {
                        FIELDS_CrmAtcs.push(
                            {
                                value: camposcustomizados[campo].nome,
                                label: camposcustomizados[campo].label,
                                type: 'string',
                                style: 'title',
                                copy: '',
                                campocustomizado: true
                            });
                    }
                }

            }
            $rootScope.FIELDS_CrmAtcs = FIELDS_CrmAtcs;

            //Modificando a label "Nome" para "Nome do Falecido", caso tenha a configuração
            let config = configuracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');

            //Tem a configuração
            if(config == '1'){
                for(let i = 0; i < $rootScope.FIELDS_CrmAtcs.length; i++){
                    let coluna =  $rootScope.FIELDS_CrmAtcs[i];
    
                    //Se a label da coluna for nome, mudo ela
                    if(coluna.label == 'Nome'){
                        coluna.label = 'Nome do Falecido';
                        break;
                    }
                }
            }
            this.busy = false;
        });

        /*** 
        * Sobreescrito para passar a data do filtro na tela de negócios *
        ***/
        this.$scope.$watch("crm_tcs_lst_cntrllr.datacriacao", (newValue: any, oldValue: any) => {
            if (newValue != oldValue) {
                if (this.filters.datacriacao === undefined)
                    this.filters.datacriacao = [];

                if (this.datacriacao.datahorainicio) {
                    this.filters.datacriacao[0] = ({ 'condition': 'gte', 'value': this.datacriacao.datahorainicio + ' 00:00:00' });
                }
                else
                    this.filters.datacriacao[0] = [];

                if (this.datacriacao.datahorafim) {
                    this.filters.datacriacao[1] = ({ 'condition': 'lte', 'value': this.datacriacao.datahorafim + ' 23:59:59' });
                }
                else
                    this.filters.datacriacao[1] = [];

                this.entities = entityService.reload();
            }
        }, true);

        /*** 
        * Sobreescrito para passar a data do filtro na tela de negócios *
        * Filtro pela data de atualização do negócio
        ***/
        this.$scope.$watch("crm_tcs_lst_cntrllr.dataedicao", (newValue: any, oldValue: any) => {
            if (newValue != oldValue) {
                if (this.filters.dataedicao === undefined)
                    this.filters.dataedicao = [];

                if (this.dataedicao.datahorainicio) {
                    this.filters.dataedicao[0] = ({ 'condition': 'gte', 'value': this.dataedicao.datahorainicio + ' 00:00:00' });
                }
                else
                    this.filters.dataedicao[0] = [];

                if (this.dataedicao.datahorafim) {
                    this.filters.dataedicao[1] = ({ 'condition': 'lte', 'value': this.dataedicao.datahorafim + ' 23:59:59' });
                }
                else
                    this.filters.dataedicao[1] = [];

                this.entities = entityService.reload();
            }
        }, true);
       

    }

    // Função que controla qual filtro será exibido na listagem de negócios (Filtro por data de criação ou por data de atualização)
    exibeFiltro(campo){

        //Controle para ao trocar qual filtro será exibido, o filtro anterior ser limpado. Isso evita que o conteúdo seja duplamente filtrado
        if (campo === 'criacao'){
            delete this.filters.dataedicao;
        }
        else if (campo === 'edicao'){
            delete this.filters.datacriacao;
        }
        
        this.filtroExibido = campo;
    }

    $onInit() {
        this.linksExternos = this.$window.nsj.globals.a.links;
        //No momento que a página foi carregada, o filtro que tem que ser exibido é o da data de criação
        this.filtroExibido = 'criacao';
    }

    /* @todo verificar porque o filter não  funcionou */
    removeCamposcustomizados() {
        for (let i = 0; i < this.FIELDS_CrmAtcs.length; i++) {
            if (this.FIELDS_CrmAtcs[i].campocustomizado) {
                this.FIELDS_CrmAtcs.splice(i, 1);
                i--;
            }
        }
    }

    // search(filter: any) {
    //     let entities = this.entityService.search(filter);
    //     let filterURL = angular.copy(this.entityService.filters);

    //     if (filterURL.datacriacao) {
    //         filterURL.datacriacao_inicio = filterURL.datacriacao.inicio.value;
    //         filterURL.datacriacao_fim = filterURL.datacriacao.fim.value;
    //         delete filterURL.datacriacao;
    //     }
    //     if (filterURL.dataedicao) {
    //         filterURL.dataedicao_inicio = filterURL.dataedicao.inicio.value;
    //         filterURL.dataedicao_fim = filterURL.dataedicao.fim.value;
    //         delete filterURL.dataedicao;
    //     }
    //     if (filter.search !== '') {
    //         this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.entityService.filter }));
    //     } else {
    //         this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
    //     }

    //     return entities;
    // }

    search(filter: any) {
        this.entityService.constructors = this.filters;

        //Verificação para colocar letras em maiúsculo no caso de país, estado e município
        if(filter.filters.hasOwnProperty('localizacaopaisnome')){
            filter.filters.localizacaopaisnome[0] = filter.filters.localizacaopaisnome[0].toUpperCase();
        }

        if(filter.filters.hasOwnProperty('localizacaoestadonome')){
            filter.filters.localizacaoestadonome[0] = filter.filters.localizacaoestadonome[0].toUpperCase();
        }

        if(filter.filters.hasOwnProperty('localizacaomunicipionome')){
            filter.filters.localizacaomunicipionome[0] = filter.filters.localizacaomunicipionome[0].toUpperCase();
        }

        let entities = this.entityService.search(filter);
        let filterURL = angular.copy(this.entityService.filters);

        // if (filterURL.datacriacao) {
        //     filterURL.datacriacao_inicio = filterURL.datacriacao.inicio.value;
        //     filterURL.datacriacao_fim = filterURL.datacriacao.fim.value;
        //     delete filterURL.datacriacao;
        // }

        if (filter.search !== '') {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL, { 'q': this.entityService.filter }));
        } else {
            this.$location.path(this.$location.path()).search(angular.extend({}, filterURL));
        }

        return entities;
    }

    obterValorCampo(campo,atributo){
        return `camposcustomizados.${campo.campocustomizado}.0.${campo.objeto[atributo].nome}`
        
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

    /**
     * Abre o projeto em modo de visualização em uma nova guia
     * @param entity 
     */
    abrirProjeto(entity: any){
        
        let urlProjeto = this.$state.href('projetos_show', { projeto: entity.projeto.projeto });
        window.open(urlProjeto, '_blank');

    }

}
