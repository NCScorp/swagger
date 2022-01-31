import * as angular from 'angular';
import { EquipesTecnicosService } from './equipes-tecnicos.service';

export const EquipesTecnicosRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['equipesTecnicosService', '$stateParams', '$state', (equipesTecnicosService: EquipesTecnicosService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.equipetecnicotecnico) {
                    return new Promise((resolve: any, reject: any) => {
                        equipesTecnicosService.get($stateParams.equipetecnico, $stateParams.equipetecnicotecnico)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('equipes_equipestecnicos_not_found', $stateParams)) {
                                        $state.go('equipes_equipestecnicos_not_found', $stateParams);
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
        .state('equipes_equipestecnicos', {
            url: '/{equipetecnico}/equipes/equipestecnicos?q',
            resolve: resolveFilter,
            template: require('./index/equipes-tecnicos-index.html'),
            controller: 'equipesTecnicosIndexController',
            controllerAs: 'qps_qpstcncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('equipes_equipestecnicos_new', {
            parent: 'equipes_equipestecnicos',
            url: '/new',
            resolve: resolve,
            template: require('./new/equipes-tecnicos-new.html'),
            controller: 'equipesTecnicosNewController',
            controllerAs: 'qps_qpstcncs_frm_nw_cntrllr'
        }).state('equipes_equipestecnicos_edit', {
            parent: 'equipes_equipestecnicos',
            url: '/:equipetecnicotecnico/edit',
            resolve: resolve,
            template: require('./edit/equipes-tecnicos-edit.html'),
            controller: 'equipesTecnicosEditController',
            controllerAs: 'qps_qpstcncs_frm_cntrllr'
        });
}];

