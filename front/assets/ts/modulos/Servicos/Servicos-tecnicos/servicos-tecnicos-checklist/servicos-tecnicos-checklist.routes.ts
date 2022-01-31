import * as angular from 'angular';
import { ServicosTecnicosChecklistService } from './servicos-tecnicos-checklist.service';

export const ServicosTecnicosChecklistRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['servicosTecnicosChecklistService', '$stateParams', '$state', (servicosTecnicosChecklistService: ServicosTecnicosChecklistService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.servicotecnicochecklist) {
                    return new Promise((resolve: any, reject: any) => {
                        servicosTecnicosChecklistService.get($stateParams.servicotecnico, $stateParams.servicotecnicochecklist)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('servicostecnicos_servicostecnicoschecklist_not_found', $stateParams)) {
                                        $state.go('servicostecnicos_servicostecnicoschecklist_not_found', $stateParams);
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
        .state('servicostecnicos_servicostecnicoschecklist', {
            url: '/{servicotecnico}/servicostecnicos/servicostecnicoschecklist?q',
            resolve: resolveFilter,
            template: require('./index/servicos-tecnicos-checklist-index.html'),
            controller: 'servicosTecnicosChecklistIndexController',
            controllerAs: 'srvcstcncs_srvcstcncschcklst_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('servicostecnicos_servicostecnicoschecklist_new', {
            parent: 'servicostecnicos_servicostecnicoschecklist',
            url: '/new',
            resolve: resolve,
            template: require('./new/servicos-tecnicos-checklist-new.html'),
            controller: 'servicosTecnicosChecklistNewController',
            controllerAs: 'srvcstcncs_srvcstcncschcklst_frm_nw_cntrllr'
        });
}];

