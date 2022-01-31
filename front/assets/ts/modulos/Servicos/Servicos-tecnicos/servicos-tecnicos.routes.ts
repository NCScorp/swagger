import * as angular from 'angular';
import { ServicosTecnicosService } from './servicos-tecnicos.service';

export const ServicosTecnicosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['servicosTecnicosService', '$stateParams', '$state', (servicosTecnicosService: ServicosTecnicosService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.servicotecnico) {
                    return new Promise((resolve: any, reject: any) => {
                        servicosTecnicosService.get($stateParams.servicotecnico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('servicostecnicos_not_found', $stateParams)) {
                                        $state.go('servicostecnicos_not_found', $stateParams);
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
        .state('servicostecnicos', {
            url: '/servicostecnicos?q',
            resolve: resolveFilter,
            template: require('./index/servicos-tecnicos-index.html'),
            controller: 'servicosTecnicosIndexController',
            controllerAs: 'srvcstcncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('servicostecnicos_new', {
            parent: 'servicostecnicos',
            url: '/new',
            resolve: resolve,
            template: require('./new/servicos-tecnicos-new.html'),
            controller: 'servicosTecnicosNewController',
            controllerAs: 'srvcstcncs_frm_nw_cntrllr'
        }).state('servicostecnicos_show', {
            parent: 'servicostecnicos',
            url: '/:servicotecnico',
            resolve: resolve,
            template: require('./show/servicos-tecnicos-show.html'),
            controller: 'servicosTecnicosShowController',
            controllerAs: 'srvcstcncs_frm_shw_cntrllr'
        }).state('servicostecnicos_edit', {
            parent: 'servicostecnicos',
            url: '/:servicotecnico/edit',
            resolve: resolve,
            template: require('./edit/servicos-tecnicos-edit.html'),
            controller: 'servicosTecnicosEditController',
            controllerAs: 'srvcstcncs_frm_cntrllr'
        });
}];

