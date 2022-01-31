import * as angular from 'angular';
import { EquipesService } from './equipes.service';

export const EquipesRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['equipesService', '$stateParams', '$state', (equipesService: EquipesService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.equipetecnico) {
                    return new Promise((resolve: any, reject: any) => {
                        equipesService.get($stateParams.equipetecnico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('equipes_not_found', $stateParams)) {
                                        $state.go('equipes_not_found', $stateParams);
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
        .state('equipes', {
            url: '/equipes?q',
            resolve: resolveFilter,
            template: require('./index/equipes-index.html'),
            controller: 'equipesIndexController',
            controllerAs: 'qps_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('equipes_new', {
            parent: 'equipes',
            url: '/new',
            resolve: resolve,
            template: require('./new/equipes-new.html'),
            controller: 'equipesNewController',
            controllerAs: 'qps_frm_nw_cntrllr'
        }).state('equipes_show', {
            parent: 'equipes',
            url: '/:equipetecnico',
            resolve: resolve,
            template: require('./show/equipes-show.html'),
            controller: 'equipesShowController',
            controllerAs: 'qps_frm_shw_cntrllr'
        }).state('equipes_edit', {
            parent: 'equipes',
            url: '/:equipetecnico/edit',
            resolve: resolve,
            template: require('./edit/equipes-edit.html'),
            controller: 'equipesEditController',
            controllerAs: 'qps_frm_cntrllr'
        });
}];

