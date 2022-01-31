import * as angular from 'angular';
import { OperacoesOrdensServicosService } from './operacoes-ordens-servicos.service';

export const OperacoesOrdensServicosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['operacoesOrdensServicosService', '$stateParams', '$state', (operacoesOrdensServicosService: OperacoesOrdensServicosService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.operacaoordemservico) {
                    return new Promise((resolve: any, reject: any) => {
                        operacoesOrdensServicosService.get($stateParams.operacaoordemservico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('operacoesordensservicos_not_found', $stateParams)) {
                                        $state.go('operacoesordensservicos_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    return {};
                }
            }
        }]
    };
    let resolveFilter = {
    };
    $stateProvider
        .state('operacoesordensservicos', {
            url: '/operacoesordensservicos?q',
            resolve: resolveFilter,
            template: require('./index/operacoes-ordens-servicos-index.html'),
            controller: 'operacoesOrdensServicosIndexController',
            controllerAs: 'prcsrdnssrvcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('operacoesordensservicos_new', {
            parent: 'operacoesordensservicos',
            url: '/new',
            resolve: resolve,
            template: require('./new/operacoes-ordens-servicos-new.html'),
            controller: 'operacoesOrdensServicosNewController',
            controllerAs: 'prcsrdnssrvcs_frm_nw_cntrllr'
        }).state('operacoesordensservicos_show', {
            parent: 'operacoesordensservicos',
            url: '/:operacaoordemservico',
            resolve: resolve,
            template: require('./show/operacoes-ordens-servicos-show.html'),
            controller: 'operacoesOrdensServicosShowController',
            controllerAs: 'prcsrdnssrvcs_frm_shw_cntrllr'
        }).state('operacoesordensservicos_edit', {
            parent: 'operacoesordensservicos',
            url: '/:operacaoordemservico/edit',
            resolve: resolve,
            template: require('./edit/operacoes-ordens-servicos-edit.html'),
            controller: 'operacoesOrdensServicosEditController',
            controllerAs: 'prcsrdnssrvcs_frm_cntrllr'
        });
}];

