import * as angular from 'angular';
import { TiposOrdensServicosService } from './tipos-ordens-servicos.service';

export const TiposOrdensServicosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['tiposOrdensServicosService', '$stateParams', '$state', (tiposOrdensServicosService: TiposOrdensServicosService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tipoordemservico) {
                    return new Promise((resolve: any, reject: any) => {
                        tiposOrdensServicosService.get($stateParams.tipoordemservico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('tiposordensservicos_not_found', $stateParams)) {
                                        $state.go('tiposordensservicos_not_found', $stateParams);
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
        .state('tiposordensservicos', {
            url: '/tiposordensservicos?q',
            resolve: resolveFilter,
            template: require('./index/tipos-ordens-servicos-index.html'),
            controller: 'tiposOrdensServicosIndexController',
            controllerAs: 'tpsrdnssrvcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('tiposordensservicos_new', {
            parent: 'tiposordensservicos',
            url: '/new',
            resolve: resolve,
            template: require('./new/tipos-ordens-servicos-new.html'),
            controller: 'tiposOrdensServicosNewController',
            controllerAs: 'tpsrdnssrvcs_frm_nw_cntrllr'
        })
        .state('tiposordensservicos_show', {
            parent: 'tiposordensservicos',
            url: '/:tipoordemservico',
            resolve: resolve,
            template: require('./show/tipos-ordens-servicos-show.html'),
            controller: 'tiposOrdensServicosShowController',
            controllerAs: 'tpsrdnssrvcs_frm_shw_cntrllr'
        })
        .state('tiposordensservicos_edit', {
            parent: 'tiposordensservicos',
            url: '/:tipoordemservico/edit',
            resolve: resolve,
            template: require('./edit/tipos-ordens-servicos-edit.html'),
            controller: 'tiposOrdensServicosEditController',
            controllerAs: 'tpsrdnssrvcs_frm_cntrllr'
        });
}];

