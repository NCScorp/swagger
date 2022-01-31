import * as angular from 'angular';
import { EtapasService } from './etapas.service';

export const EtapasRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['etapasService', '$stateParams', '$state', (etapasService: EtapasService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.projetoetapa) {
                    return new Promise((resolve: any, reject: any) => {
                        etapasService.get($stateParams.projetoetapa)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('etapas_not_found', $stateParams)) {
                                        $state.go('etapas_not_found', $stateParams);
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
        .state('etapas', {
            url: '/etapas?q',
            resolve: resolveFilter,
            template: require('./index/etapas-index.html'),
            controller: 'etapasIndexController',
            controllerAs: 'tps_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('etapas_new', {
            parent: 'etapas',
            url: '/new',
            resolve: resolve,
            template: require('./new/etapas-new.html'),
            controller: 'etapasNewController',
            controllerAs: 'tps_frm_nw_cntrllr'
        }).state('etapas_show', {
            parent: 'etapas',
            url: '/:projetoetapa',
            resolve: resolve,
            template: require('./show/etapas-show.html'),
            controller: 'etapasShowController',
            controllerAs: 'tps_frm_shw_cntrllr'
        }).state('etapas_edit', {
            parent: 'etapas',
            url: '/:projetoetapa/edit',
            resolve: resolve,
            template: require('./edit/etapas-edit.html'),
            controller: 'etapasEditController',
            controllerAs: 'tps_frm_cntrllr'
        });
}];

