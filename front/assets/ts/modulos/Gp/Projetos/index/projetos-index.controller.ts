import angular = require('angular');

import { TransitionService } from '@uirouter/core';
import { ProjetosService } from '../projetos.service';
import { IProjeto } from '../interfaces/projetos.interface';
import { NsjTelaListaClasses } from '../../../../shared/components/nsjtelalista/components/nsjtelalista.classes';
import { ControllerListaBase } from '../../../../shared/controllers/controller-lista-base/controller-lista-base';
import { SituacaoEnum } from '../enums/situacaoEnum';

export class ProjetosIndexController extends ControllerListaBase<IProjeto> {
    static $inject = [
        '$scope',
        '$stateParams',
        '$state',
        'projetosService',
        '$rootScope',
        '$location',
        '$transitions',
        'moment'
    ];

    /**
     * Filtro de cliente da tela
     */
    filtrocliente: any = null;

    /**
     * Filtro de prazo final da tela
     */
    filtroPrazoFinal: any = {
        inicio: {
            value: null
        },
        fim: {
            value: null
        }
    }

    public filters: any;

    constructor(
        $scope: angular.IScope,
        $stateParams: angular.ui.IStateParamsService,
        $state: angular.ui.IStateService,
        projetosService: ProjetosService,
        $rootScope: any,
        $location: angular.ILocationService,
        $transitions: TransitionService,
        public moment: any
    ) {
        // Chamo constructor pai
        super(
            $scope,
            $rootScope,
            $state,
            $stateParams,
            $location,
            $transitions,
            projetosService
        );

        // observa quando as entities foram carregadas
        this.onEntitiesLoaded();

        //Filtro de situação do projeto, baseado no filtro de status do atendimento comercial
        projetosService.filter = $stateParams.q ? $stateParams.q : '';
        projetosService.filters = {};
        projetosService.constructors = {};

        for (let i in $stateParams) {

            if (['situacao'].indexOf(i) > -1 && $stateParams[i] !== undefined) {

                projetosService.filters[i] = $stateParams[i];

            } else if (i === 'situacao' && $stateParams[i] !== undefined) {

                if (!projetosService.filters.situacao) {
                    projetosService.filters.situacao = {};
                }

            }
        }

        this.service = projetosService;
        this.filters = projetosService.filters;

    }

    /**
     * observa quando as entities foram carregadas e chama o parse do responsavel_conta_nasajon
     */
    onEntitiesLoaded(): void {
      // uma solução melhor seria fazer um $watch na variável entities herdada do controller base,
      // mas devido a dificuldades de implementação, mantive a solução abaixo por questões de tempo
      this.$scope.$on(`${this.getService().getModuloEntidade()}_${this.getService().getNomeEntidade()}_partially_listed`, () => this.parseResponsavelContaNasajon());
      this.$scope.$on(`${this.getService().getNomeEntidade()}_list_finished`, () => this.parseResponsavelContaNasajon());
    }

    /**
     * faz o parse da string responsavel_conta_nasajon para JSON
     */
    parseResponsavelContaNasajon() {
      this.entities.forEach((entity: IProjeto) => {
        if (typeof entity.responsavel_conta_nasajon === 'string') {
          entity.responsavel_conta_nasajon = JSON.parse(entity.responsavel_conta_nasajon);
        }
      });
    }

    // protected configurarEventosFiltros(): void {}
    protected configurarEventosFiltros(): void {
        this.$scope.$watch("pjt_lst_cntrllr.filtroPrazoFinal", (newValue: any, oldValue: any) => {
            if (newValue != oldValue) {
                //Faço uma réplica do filtro atual
                let filtro = {
                    search: this.getService().filter,
                    filters: angular.copy(this.getService().filters)
                };

                //Atualizo a busca
                this.search(filtro);
            }
        }, true);
    }

    protected criarConfiguracaoTela(): void {
        this.configLista = new NsjTelaListaClasses.Config(

            //Tipo Tela
            NsjTelaListaClasses.ETipoTela.ttTabela,

            //Titulo Tela
            {
                singular: 'Projeto',
                artigoIndefinidoSingular: 'um',
                plural: 'Projetos'
            },

            //Campo Id
            'projeto',

            //Nome Entidade
            'projetos',

            //Módulo da entidade
            'financas'  
        );

      this.configLista.acoesTabela.push(
        {
          nome: 'Criar Projeto',
          icone: 'fas fa-bullhorn',
          onClick: (entity: IProjeto) => {
            this.$state.go('projetos_edit', { projeto: entity.projeto });
          },
          target: '_self',
          isVisible: (entity: IProjeto) => entity.situacao === SituacaoEnum.AGUARDANDO_INICIALIZACAO
        },
        {
          nome: 'Visualizar Projeto',
          icone: 'fas fa-eye',
          onClick: (entity: IProjeto) => {
            this.$state.go('projetos_show', { projeto: entity.projeto });
          },
          target: '_self',
          isVisible: (entity: IProjeto) => entity.situacao !== SituacaoEnum.AGUARDANDO_INICIALIZACAO
        });
    }

    protected configurarCamposListagem(): void {
        this.configLista.configCamposListagemTabela = (<any>this.$rootScope).FIELDS_Projetos;
    }

    protected getService(): ProjetosService {
        return (<ProjetosService>this.service);
    }

    /**
     * Chamado na inicialização do controller
     */
    $onInit() {}

    protected getParamStringLookup(): {nome: string, chave: string}[] {
        return [{nome: 'cliente', chave: 'cliente'}];
    }

    preSearch(filter){
        filter.filters.datafim = [];

        //Controle do filtro de prazo final
        if (this.filtroPrazoFinal.inicio.value) {
            filter.filters.datafim.push({
                condition: 'gte',
                value: this.moment(this.filtroPrazoFinal.inicio.value).format('YYYY-MM-DD HH:mm:ss')
            });
        }

        //Controle do filtro de prazo final
        if (this.filtroPrazoFinal.fim.value) {
            filter.filters.datafim.push({
                condition: 'lte',
                value: this.moment(this.filtroPrazoFinal.fim.value).format('YYYY-MM-DD HH:mm:ss')
            });
        }
    }

    /**
     * Sobrescrevo função para não criar ações de visualizar/editar que aparecem por padrão
     */
    configurarAcoesTabela(){}
    
    /**
     * Sobrescrevo para remover remover visualização da ação de criar, que está visível por padrão
     */
    //configurarCallbacks(){
        // Chamo função pai
    //    super.configurarCallbacks();
        // Callback chamado para verificar se exibe botão de criação
    //    this.configLista.callbacks.exibirBtnCriacao = () => {
    //        return false;
    //    }
    //}
}