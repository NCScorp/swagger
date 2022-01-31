import * as angular from 'angular';
import { TecnicosService } from './tecnicos.service';

export const TecnicosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['tecnicosService', '$stateParams', '$state', (tecnicosService: TecnicosService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tecnico) {
                    return new Promise((resolve: any, reject: any) => {
                        tecnicosService.get($stateParams.tecnico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('tecnicos_not_found', $stateParams)) {
                                        $state.go('tecnicos_not_found', $stateParams);
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
        .state('tecnicos', {
            url: '/tecnicos?q',
            resolve: resolveFilter,
            template: require('./index/tecnicos-index.html'),
            controller: 'tecnicosIndexController',
            controllerAs: 'tcncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('tecnicos_new', {
            parent: 'tecnicos',
            url: '/new',
            resolve: resolve,
            template: require('./new/tecnicos-new.html'),
            controller: 'tecnicosNewController',
            controllerAs: 'tcncs_frm_nw_cntrllr'
        }).state('tecnicos_show', {
            parent: 'tecnicos',
            url: '/:tecnico',
            resolve: resolve,
            template: require('./show/tecnicos-show.html'),
            controller: 'tecnicosShowController',
            controllerAs: 'tcncs_frm_shw_cntrllr'
        }).state('tecnicos_edit', {
            parent: 'tecnicos',
            url: '/:tecnico/edit',
            resolve: resolve,
            template: require('./edit/tecnicos-edit.html'),
            controller: 'tecnicosEditController',
            controllerAs: 'tcncs_frm_cntrllr'
        });
}];

