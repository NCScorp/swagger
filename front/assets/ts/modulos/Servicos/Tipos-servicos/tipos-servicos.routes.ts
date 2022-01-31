import * as angular from 'angular';
import { TiposServicosService } from './tipos-servicos.service';

export const TiposServicosRouting = ['$stateProvider', function ($stateProvider: angular.ui.IStateProvider) {

    let resolve = {
        'entity': ['tiposServicosService', '$stateParams', '$state', (tiposServicosService: TiposServicosService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tiposervico) {
                    return new Promise((resolve: any, reject: any) => {
                        tiposServicosService.get($stateParams.tiposervico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('tiposservicos_not_found', $stateParams)) {
                                        $state.go('tiposservicos_not_found', $stateParams);
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
        .state('tiposservicos', {
            url: '/tiposservicos?q',
            resolve: resolveFilter,
            template: require('./index/tipos-servicos-index.html'),
            controller: 'tiposServicosIndexController',
            controllerAs: 'tpssrvcs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('tiposservicos_new', {
            parent: 'tiposservicos',
            url: '/new',
            resolve: resolve,
            template: require('./new/tipos-servicos-new.html'),
            controller: 'tiposServicosNewController',
            controllerAs: 'tpssrvcs_frm_nw_cntrllr'
        })
        .state('tiposservicos_show', {
            parent: 'tiposservicos',
            url: '/:tiposervico',
            resolve: resolve,
            template: require('./show/tipos-servicos-show.html'),
            controller: 'tiposServicosShowController',
            controllerAs: 'tpssrvcs_frm_shw_cntrllr'
        })
        .state('tiposservicos_edit', {
            parent: 'tiposservicos',
            url: '/:tiposervico/edit',
            resolve: resolve,
            template: require('./edit/tipos-servicos-edit.html'),
            controller: 'tiposServicosEditController',
            controllerAs: 'tpssrvcs_frm_cntrllr'
        });
}];
