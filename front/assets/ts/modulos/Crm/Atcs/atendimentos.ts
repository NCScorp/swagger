import angular = require('angular');

export class CrmAtcsDashboardController {
    EStatusAtendimento = EnumStatusAtendimento;
    static $inject = [
        '$scope',
        'toaster',
        '$http',
        'nsjRouting',
        'moment',
        'carregarDados',
        'ctrlReqChild'
    ];

    public busy: boolean = true;
    public busyAtendimentos: boolean = false;
    public arrAtcs: any[] = [];
    public isReloading: boolean = false;

    constructor(
        public $scope: angular.IScope,
        public toaster: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public moment: any,
        public carregarDados: boolean,
        public ctrlReqChild: any
    ) {
        if (this.carregarDados) {
            this.$onInit();
        }

        this.ctrlReqChild.fnLoadAtendimentos = () => {
            return this.carregarListaAtcsPromise();
        }
    }

    /* Carregamento */
    $onInit() {
        this.carregarListaAtcs();
    }

    carregarListaAtcsPromise() {
        return new Promise((resolve, reject) => {
            if (this.isBusyAtendimentos() || this.isReloadingAtendimento()) {
                reject();
            }

            try {
                this.setBusyAtendimento(true);
                
                //Trazer somente atendimentos que estejam em uma dessas situações
                let filter: any = { 'status': [
                    { 'condition': 'eq', 'value': EnumStatusAtendimento.saNovo },
                    { 'condition': 'eq', 'value': EnumStatusAtendimento.saEmAtendimento },
                ]};

                this.buscarAtcs(filter).then((arrAtcs: any[]) => {
                    this.arrAtcs = arrAtcs;
                    this.setBusyAtendimento(false);
                    this.setIsReloading(false);
                    this.$scope.$applyAsync();
                    resolve();
                }).catch((error) => {
                    this.setBusyAtendimento(false);
                    this.setIsReloading(false);
                    this.toaster.pop({
                        type: 'error',
                        title: 'Ocorreu um erro ao atualizar a lista de atendimentos.'
                    });
                    reject(error);
                });
            } catch (error) {
                reject(error);
            }
        })
    }

    /**
     * Carrega a lista de negócios(Atendimentos)
     */
    carregarListaAtcs(reloading = false) {
        this.setBusyAtendimento(!reloading);
        this.setIsReloading(reloading);
        
        //Trazer somente atendimentos que estejam em uma dessas situações
        let filter: any = { 'status': [
            { 'condition': 'eq', 'value': EnumStatusAtendimento.saNovo },
            { 'condition': 'eq', 'value': EnumStatusAtendimento.saEmAtendimento },
        ]};

        this.buscarAtcs(filter).then((arrAtcs: any[]) => {
            this.arrAtcs = arrAtcs;
            this.setBusyAtendimento(false);
            this.setIsReloading(false);
            this.$scope.$applyAsync();
        }).catch((err) => {
            this.setBusyAtendimento(false);
            this.setIsReloading(false);
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao atualizar a lista de atendimentos.'
            });
        });
    }

    setBusyAtendimento(busy: boolean) {
        this.busyAtendimentos = busy;
    }

    setIsReloading(reloading) {
        this.isReloading = reloading;
    }
    /**
     * Faz requisição para buscar lista de negócios(Atendimentos)
     * @param filter 
     */
    buscarAtcs(filter: any = {}) {

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_atcs_index', filter)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    getStatusDescricao(pStatus: EnumStatusAtendimento) {
        switch (pStatus) {
            case EnumStatusAtendimento.saNovo: return 'ABERTO';
            case EnumStatusAtendimento.saEmAtendimento: return 'EM ATENDIMENTO';
            case EnumStatusAtendimento.saProcessando: return 'PROCESSANDO';
            case EnumStatusAtendimento.saFinalizado: return 'FINALIZADO';
            case EnumStatusAtendimento.saCancelado: return 'CANCELADO';
        };
    }

    getDataFormatada(data: string) {
        return this.moment(data).format('DD/MM/YYYY');
    }

    getHoraFormatada(data: string) {
        return this.moment(data).format('HH:mm:ss');
    }

    isBusyAtendimentos() {
        return this.busyAtendimentos;
    }
    isReloadingAtendimento() {
        return this.isReloading;
    }

    onCriarClick(ev) {
        console.log("Evento criar", ev.stopPropagation());
    }
}

export const CrmAtcsDashboardModule = angular
    .module('CrmAtcsDashboard', ['ui.router'])
    .directive('nsEnter', function () {
        return function (scope, element, attrs) {
            element.unbind('keypress').bind('keypress', function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.nsEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    }).name;

/**
 * Enum que define todas as situações possíveis para um atendimento comercial
 */
export enum EnumStatusAtendimento {
    saNovo = 0,
    saEmAtendimento = 1,
    saProcessando = 2,
    saFinalizado = 3,
    saCancelado = 4
}
