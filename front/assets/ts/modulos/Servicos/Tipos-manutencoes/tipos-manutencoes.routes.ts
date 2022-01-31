import * as angular from 'angular';
import { TiposManutencoesService } from './tipos-manutencoes.service';

export const TiposManutencoesRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['tiposManutencoesService', '$stateParams', '$state', (tiposManutencoesService: TiposManutencoesService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tipomanutencao) {
                    return new Promise((resolve: any, reject: any) => {
                        tiposManutencoesService.get($stateParams.tipomanutencao)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('tiposmanutencoes_not_found', $stateParams)) {
                                        $state.go('tiposmanutencoes_not_found', $stateParams);
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
        .state('tiposmanutencoes', {
            url: '/tiposmanutencoes?q',
            resolve: resolveFilter,
            template: require('./index/tipos-manutencoes-index.html'),
            controller: 'tiposManutencoesIndexController',
            controllerAs: 'tpsmntncs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('tiposmanutencoes_new', {
            parent: 'tiposmanutencoes',
            url: '/new',
            resolve: resolve,
            template: require('./new/tipos-manutencoes-new.html'),
            controller: 'tiposManutencoesNewController',
            controllerAs: 'tpsmntncs_frm_nw_cntrllr'
        })
        .state('tiposmanutencoes_show', {
            parent: 'tiposmanutencoes',
            url: '/:tipomanutencao',
            resolve: resolve,
            template: require('./show/tipos-manutencoes-show.html'),
            controller: 'tiposManutencoesShowController',
            controllerAs: 'tpsmntncs_frm_shw_cntrllr'
        })
        .state('tiposmanutencoes_edit', {
            parent: 'tiposmanutencoes',
            url: '/:tipomanutencao/edit',
            resolve: resolve,
            template: require('./edit/tipos-manutencoes-edit.html'),
            controller: 'tiposManutencoesEditController',
            controllerAs: 'tpsmntncs_frm_cntrllr'
        });
}];
