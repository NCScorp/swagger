import angular = require('angular');
import { CrmAtcspendencias } from '../Atcspendencias/factory';
import { 
    EnumStatusPendencia, 
    EnumPendenciaPrazoStatus,
    EnumImpeditivoPendencia, 
    CrmAtcspendenciasUtilsService } from '../Atcspendencias/utils';

import { CrmAtcsFormHistoricoService } from './../../Crm/Atcshistorico/modal';
import { ISecaoController } from './classes/isecaocontroller';
interface IScopeCrmPendencias extends angular.IScope{
    valorDigitado : string;
}
interface IAtcPendencia { 
    pendencia: [];
    action: string; 
}

export class CrmAtcsPendenciasController {
    EStatusPendencia = EnumStatusPendencia;
    EPendenciaPrazoStatus = EnumPendenciaPrazoStatus;
    static $inject = [
        '$scope',
        'toaster',
        'entity',
        'utilService',
        '$http',
        'nsjRouting',
        'CrmAtcspendenciaslistasFormService',
        'CrmAtcspendenciaslistas',
        'CrmAtcspendenciasFormService',
        'CrmAtcspendencias',
        'moment',
        'layout',
        'carregarDados',
        'telaorigem',
        'CrmAtcspendenciasUtilsService',
        'CrmAtcsFormHistoricoService',
        'ctrlReqChild',
        'secaoctrl',
        '$state'
    ];

    public camposcustomizados: any = {};
    public atcsfilhos: any = [];
    public propostas: any = {};
    public pendenciaslistas: any = [];
    private idCount: any = 0;
    public busy: boolean = true;
    public busyPendencias: boolean = false;
    public atribuicoesUnicas: Array<{atribuidoa: string}> = [];
    public busyInicializacao: boolean = false;
    public pedencialista: { lista: {}; action: string; };
    public _index: any;
    public atcPendencia: IAtcPendencia  =  { pendencia: [], action: ''};
    public valorDigitado: string;
    //Intervalo responsavel por atualizar o campo aberta_ha das pendências.
    public itvAtualizaAbertaHa: any = null;
    public busyHistorico: boolean =  false;
    public filtroAtcPendenciaLista: string = '';
    public filtroPrioridade = undefined;
    public lastAtribuido;
    public lastFilter = null;
    public filteringWithOrder: boolean;
    public filtro: any = {};
    public finishedLoading: boolean = false;
    public paginate: { count: number, full_count: number };
    public service;
    public entities: any = [];

    /**
     * O tamanho do array é a quantidade de skeletons que vai aparecer na tela
     */
    public arrQtdSkeletons: any[] = [1,2,3];
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    /**
     * O tipo de ordenação escolhida para a listagem de pendências
     */
    public ordenacaoListaPendencias: string = 'crescente';

    /**
     * Faz o controle se o accordion de pendências já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
    public accordionCarregado: boolean = false;

    constructor(
        public $scope: IScopeCrmPendencias,
        public toaster: any,
        public entity: any,
        public utilService: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public CrmAtcspendenciaslistasFormService: any,
        public CrmAtcspendenciaslistas: any,
        public CrmAtcspendenciasFormService: any,
        public CrmAtcspendencias: CrmAtcspendencias,
        public moment: any,
        public layout: 'table' | 'block',
        public carregarDados: boolean,
        public telaorigem: 'atc' | 'dashboard-pendencias',
        public CrmAtcspendenciasUtilsService: CrmAtcspendenciasUtilsService,
        public CrmAtcsFormHistoricoService : CrmAtcsFormHistoricoService,
        public ctrlReqChild: any,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController,
        public $state: any
    ) {
      // esvazio o array de pendẽncias toda vez que uma nova rota é aberta
      this.CrmAtcspendencias.entities = [];

      if(this.carregarDados){
          this.$onInit();
      }

      if ($state.current.name !== 'crm_atcs_full_new') {
        // só busco as pendências se não estiver na tela de criação
        if (this.ctrlReqChild) {
          this.ctrlReqChild.fnLoadPendencias = async () => {
            return await this.carregarListaPendenciasPromise(true);
          }
        }
      } else {
        this.busy = false;
      }
        
        $scope.$on('crm_atcspendencias_list_loaded', (event, args) => {
          this.paginate = args.paginate;
          this.busyPendencias = false;
          this.finishedLoading = false;
          this.atualizaCamposPendencias(this.entities);
        });

        $scope.$on('crm_atcspendencias_list_finished', (event, args) => {
          this.paginate = args.paginate;
          this.busyPendencias = false;
          this.finishedLoading = true;
          this.atualizaCamposPendencias(this.entities);
        });

        if (secaoCtrl) {
            // Implemento função de ativação da atualização chamada pelo atendimento
            this.secaoCtrl.ativarAtualizacao = () => {

                //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar lista de pendências
                if (!this.accordionCarregado && !this.atualizacaoAtivada && this.carregarDados) {
                    // Informo que a atualização está ativada
                    this.atualizacaoAtivada = true;
                    this.busyInicializacao = true;
                    // Chamo função que carrega dados da tela;
                    this.carregarListaPendencias();
                }
            }
            // Implemento função de desativação da atualização chamada pelo atendimento
            this.secaoCtrl.pararAtualizacao = () =>  {
                this.atualizacaoAtivada = false;
            }
        }

        $scope.$on('$destroy', () => {
          clearInterval(this.itvAtualizaAbertaHa);
        });
    }

    /* Carregamento */
    $onInit() {
        this.onAtcsPendeciasForm();
    }

  /**
     * 
     * @param secao secao de histórico => quando não informado exibe o histórico de todas as seções de negócio
     */
    abrirHistoricoModal(secao: string = '') {
        this.busyHistorico = true;
        const parameters = { 'negocio': this.entity.negocio }
        let filters = secao ? { 'secao': secao } : null;
        this.CrmAtcsFormHistoricoService.open(parameters, {}, filters).result
            .then(()=>{
                this.busyHistorico = false;
            })
            .catch(()=>{
                this.busyHistorico = false;
            });
    }

    /**
     * Atualiza os campos aberta_ha e prazoStatus de todas as pendências no array.
     */
    atualizaCamposPendencias = (arrPendencias) => {
        arrPendencias.forEach((_p) => {
            //Atualizo campo que informa há quanto tempo a Pendência foi aberta em minutos
            _p.aberta_ha = this.CrmAtcspendenciasUtilsService.getAbertaHa(_p.created_at);

            //Atualizo campo de status do prazo.
            _p.prazoStatus = EnumPendenciaPrazoStatus.ppsValido;

            if (_p.aberta_ha > _p.prazo) {
                _p.prazoStatus = EnumPendenciaPrazoStatus.ppsExpirado;
            }
            else if (_p.aberta_ha >= (_p.prazo - _p.temponotificaexpiracao)) {
                _p.prazoStatus = EnumPendenciaPrazoStatus.ppsProximoExpiracao;
            }
        });
        this.$scope.$applyAsync();
    };

    /**
     * Filtro a partir do que foi ordenado no arrPendenciasOrdered
     * @param searchObj 
     */
     filtrar(searchObj){
      this.busyPendencias = true;

      let filtro: any = {};
      if(
          searchObj != null && 
          searchObj.filterLookup != null && 
          searchObj.filterLookup.filtroatcpendencialista !== undefined){
          filtro.negociopendencialista = searchObj.filterLookup.filtroatcpendencialista.negociopendencialista;
      }
      if (searchObj != null && searchObj.filterLookup != null && searchObj.filterLookup.filtroprioridade !== undefined) {
        filtro.prioridade = searchObj.filterLookup.filtroprioridade.prioridade;
      }
      if(
          searchObj != null && 
          searchObj.search !== undefined){
          filtro.filter = searchObj.search;
      }

      this.filtro.filters = filtro;
      this.entities = this.CrmAtcspendencias.search(this.filtro);
  }
  

    /**
     * Organiza as pendências em ordem crescente ou decrescente. A prioridade sempre será crescente, independente do método de ordenação escolhido, pois quanto menor a ordem, maior a prioridade.
     * O método de ordenação passado por parâmetro levará em consideração o atributo aberto_ha de cada pendência para ordená-las dentro de uma prioridade
     * @param ordenacao 
     */
    organizaListaPendencias(ordenacao){
        this.busyPendencias = true;
        this.CrmAtcspendencias.order = { created_at: ordenacao };

        //Na tela de negócio, só retorno as pendências relacionadas ao negócio
        if (this.entity && this.entity.negocio) {
            this.filtro.search = { 'negocio': this.entity.negocio }
        }

        //No dashboard de pendência, só retorno as pendências abertas
        if (this.telaorigem == 'dashboard-pendencias') {
            this.filtro.search = { 'status': EnumStatusPendencia.sAberta };
        }

        this.entities = this.CrmAtcspendencias.search(this.filtro);

        this.$scope.$applyAsync();
    }

    /**
     * Carrega a lista de pendências
     */
    carregarListaPendencias(filtro = null, reload = false) {
        this.busyPendencias = true;
        
        let filter: any = {};
        if(filtro !== null) {
            filter = filtro;
        }

        //Na tela de negócio, só retorno as pendências relacionadas ao negócio
        if (this.entity && this.entity.negocio) {
          this.CrmAtcspendencias.negocio = this.entity.negocio;
        } else {
          this.CrmAtcspendencias.negocio = null;
        }

        //No dashboard de pendência, só retorno as pendências abertas
        if (this.telaorigem == 'dashboard-pendencias') {
            this.CrmAtcspendencias.status = EnumStatusPendencia.sAberta;            
        } else {
          filter = '';
          this.CrmAtcspendencias.filters = {};
          this.CrmAtcspendencias.status = null;
        }

        this.CrmAtcspendencias.order = { created_at: 'asc' };
        this.CrmAtcspendencias.filter = filter;
        this.CrmAtcspendencias.loadParams.to_load = 1;
        this.CrmAtcspendencias.loadParams.finished = false;
        this.CrmAtcspendencias.after = this.paginate ? { paginate: this.paginate?.count } : {};

        if (reload) {
          this.entities = this.CrmAtcspendencias.reload();
        } else {
          this.entities = this.CrmAtcspendencias.load();
        }

        if (this.itvAtualizaAbertaHa != null) {
            clearInterval(this.itvAtualizaAbertaHa);
        }
        
        //Atualizo o tempo de abertura da pendência a cada 5 minutos.
        this.itvAtualizaAbertaHa = setInterval(() => {
            this.busyPendencias = true;
            this.entities = this.CrmAtcspendencias.reload();
            this.atualizaCamposPendencias(this.entities);
        }, 300000);
        this.busy = false;

        this.busyInicializacao = false;

        //Uma vez que o accordion foi carregado, seto a variável de controle para true
        this.accordionCarregado = true;
    }

    carregarListaPendenciasPromise(reload = false): Promise<void> {
      return new Promise(async (resolve, reject) => {
        try {
          this.busyPendencias = true;
        
          let filter: any = {};
  
          //Na tela de negócio, só retorno as pendências relacionadas ao negócio
          if (this.entity && this.entity.negocio) {
            this.CrmAtcspendencias.negocio = this.entity.negocio;
          } else {
            this.CrmAtcspendencias.negocio = null;
          }
  
          //No dashboard de pendência, só retorno as pendências abertas
          if (this.telaorigem == 'dashboard-pendencias') {
            this.CrmAtcspendencias.status = EnumStatusPendencia.sAberta;              
          } else {
            filter = '';
            this.CrmAtcspendencias.filters = {};
            this.CrmAtcspendencias.status = null;
          }
  
          this.CrmAtcspendencias.order = { created_at: 'asc' };
          this.CrmAtcspendencias.filter = filter;
          this.CrmAtcspendencias.loadParams.to_load = 1;
          this.CrmAtcspendencias.loadParams.finished = false;
          this.CrmAtcspendencias.after = this.paginate ? { paginate: this.paginate?.count } : {};
  
          if (reload) {
            this.entities = this.CrmAtcspendencias.reload();
          } else {
            this.entities = this.CrmAtcspendencias.load();
          }

          if (this.itvAtualizaAbertaHa != null) {
              clearInterval(this.itvAtualizaAbertaHa);
          }
          
          //Atualizo o tempo de abertura da pendência a cada 5 minutos.
          this.itvAtualizaAbertaHa = setInterval(() => {
              this.busyPendencias = true;
              this.entities = this.CrmAtcspendencias.reload();
              this.atualizaCamposPendencias(this.entities);
          }, 300000);
          this.busy = false;
  
          this.busyInicializacao = false;
  
          //Uma vez que o accordion foi carregado, seto a variável de controle para true
          this.accordionCarregado = true;

          resolve();
        } catch (error) {
          reject(error);
        }
      });
    }
    
    /**
     * Método usado no botão de fechar pendência exibido na listagem de pendências
     */
    fecharPendencia(pendencia){
        this.busy = true;
        this.busyPendencias = true;
        let confirmaFechamento = confirm('Tem certeza que deseja fechar a Pendência?');

        if(confirmaFechamento){
            this.CrmAtcspendencias.marcar(pendencia).then(() => {
                this.toaster.pop({
                    type: 'success',
                    title: 'A Pendência foi fechada com sucesso!'
                });
            }).catch(() => {
                this.toaster.pop({
                    type: 'error',
                    title: 'Ocorreu um erro ao tentar marcar a Pendência como realizada!'
                });
            }).finally(() => {
                this.carregarListaPendencias(null, true);
                this.busy = false;
            });
        }
        this.busy = false;
        this.busyPendencias = false;
    }

    /**
     * 
     * @param text
     * Função que transforma códigos unicode da string em caracteres 
     */
    unicodeToChar(text) {
        return text.replace(/\\u[\dA-F]{4}/gi, function (match) {
            return String.fromCharCode(parseInt(match.replace(/\\u/g, ''), 16));
        });
    }

    /**
     * Abre o modal para cadastro de pendência
     */
    crmAtcspendenciasForm(atc, pendencia: any = {}) {
        let parameters: any = {
            atc: atc,
            telaorigem: this.telaorigem
        };

        // this.busyPendencias =  true;
        // this.busy = true;
        let modal = this.CrmAtcspendenciasFormService.open(parameters, pendencia);
        modal.result.then((retornoModal: any) => {
            let action = null;
            let subentity: any = null;
            if (retornoModal.action != undefined) {
                action = retornoModal.action;
                subentity = retornoModal.entity;
            }
            else {
                subentity = retornoModal;
                action = subentity.atcpendencia ? 'update' : 'insert';
            }
            
            if (['insert', 'update'].indexOf(action) > -1) {
                subentity.$id = this.idCount++;
                subentity.negocio = {negocio: atc};

                this.busyPendencias = true;
                this.busy = true;
                this.CrmAtcspendencias.save(subentity).then((response) => {
                    this.busyPendencias = false;                    
                    let msgToasterSucesso = this.atcPendencia['pendencia']['negociopendencia'] == undefined ? 'A Pendência foi criada com sucesso!' : 'A Pendência foi editada com sucesso!';
                    this.toaster.pop({
                        type: 'success',
                        title: msgToasterSucesso
                    });
                    this.carregarListaPendencias(null, true);
                }).catch((response) => {
                    this.busyPendencias = false;
                    let msgToasterErro = response.config.method == 'POST' ? 'Ocorreu um erro ao criar a Pendência!' : 'Ocorreu um erro ao editar a Pendência!';
                    this.toaster.pop({
                        type: 'error',
                        title: msgToasterErro
                    });
                    if (response.status === 409) {
                        if (confirm(response.data.message)) {
                            this.entity[''] = response.data.entity[''];
                            this.CrmAtcspendencias.save(this.entity);
                        }
                    } else {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let distinct = (value: any, index: any, self: any) => {
                                    return self.indexOf(value) === index;
                                };
                                response.data.errors.errors = response.data.errors.errors.filter(distinct);
                                let message = this.utilService.parseValidationMessage(response.data);
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: 'Erro de Validação',
                                        body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                        bodyOutputType: 'trustedHtml'
                                    });
                            } else {
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop({
                                type: 'error',
                                title: msgToasterErro
                            });
                        }
                    }

                }).finally(() => {
                    this.busy = false;
                });

                this.atcPendencia = { pendencia, action }
            }
            else if (action == 'fechar-pendencia') {
                this.busyPendencias = true;
                this.CrmAtcspendencias.save(subentity).then(() => {
                    this.CrmAtcspendencias.marcar(subentity).then(() => {
                        this.busyPendencias= true;
                        this.toaster.pop({
                            type: 'success',
                            title: 'A Pendência foi fechada com sucesso!'
                        });
                    }).catch((error: any) => {
                        this.toaster.pop({
                            type: 'error',
                            title: 'Ocorreu um erro ao fechar a Pendência!'
                        });
                    }).finally(() => {
                        this.carregarListaPendencias(null, true);
                    })

                }).catch((response) => {

                    this.busyPendencias = false;
                    let msgToasterErro = response.config.method == 'POST' ? 'Ocorreu um erro ao criar a Pendência!' : 'Ocorreu um erro ao editar a Pendência!';
                    this.toaster.pop({
                        type: 'error',
                        title: msgToasterErro
                    });
                    if (response.status === 409) {
                        if (confirm(response.data.message)) {
                            this.entity[''] = response.data.entity[''];
                            this.CrmAtcspendencias.save(this.entity);
                        }
                    } else {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let distinct = (value: any, index: any, self: any) => {
                                    return self.indexOf(value) === index;
                                };
                                response.data.errors.errors = response.data.errors.errors.filter(distinct);
                                let message = this.utilService.parseValidationMessage(response.data);
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: 'Erro de Validação',
                                        body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                        bodyOutputType: 'trustedHtml'
                                    });
                            } else {
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop({
                                type: 'error',
                                title: msgToasterErro
                            });
                        }
                    }

                });

            }
            
        }).catch((error: any) => {
            this.busyPendencias = false;
            this.busy = false;
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }

    onAtcsPendeciasForm() {
        this.$scope.$on('crm_atcspendencias_submitted', (event: any, args: any) => {
            this.busyPendencias = false;
            let msgToasterSucesso = args.response.config.method == 'POST' ? 'A Pendência foi criada com sucesso!' : 'A Pendência foi editada com sucesso!';
            this.toaster.pop({
                type: 'success',
                title: msgToasterSucesso
            });
            this.carregarListaPendencias(null, true);
        });

        this.$scope.$on('crm_atcspendencias_submit_error', (event: any, args: any) => {
            this.busyPendencias = false;
            let msgToasterErro = args.response.config.method == 'POST' ? 'Ocorreu um erro ao criar a Pendência!' : 'Ocorreu um erro ao editar a Pendência!';
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.CrmAtcspendencias.save(this.entity);
                }
            } else {
                if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    if (args.response.data.message === 'Validation Failed') {
                        let distinct = (value: any, index: any, self: any) => {
                            return self.indexOf(value) === index;
                        };
                        args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                        let message = this.utilService.parseValidationMessage(args.response.data);
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: 'Erro de Validação',
                                body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                bodyOutputType: 'trustedHtml'
                            });
                    } else {
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: args.response.data.message
                            });
                    }
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: msgToasterErro
                    });
                }
            }
        });
    }

    isBusyPendencias() {
        return this.busyPendencias;
    }

    loadMore() {
      this.busyPendencias = true;
      this.CrmAtcspendencias.loadParams.to_load = 1;
      this.CrmAtcspendencias.loadParams.finished = false;
      this.CrmAtcspendencias.after = this.paginate ? { paginate: this.paginate?.count } : {};

      this.CrmAtcspendencias.loadMore();
    }
}

export const CrmAtcsPendenciasModule = angular.module('CrmAtcsPendencias', ['ui.router'])
.directive('nsEnter', function () {
    return function (scope, element, attrs) {
        element.unbind('keypress').bind('keypress', function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.nsEnter);
                });

                event.preventDefault();
            }
        });
    };
}).name;