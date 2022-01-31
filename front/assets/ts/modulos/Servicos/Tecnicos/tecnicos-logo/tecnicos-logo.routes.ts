import { TecnicosLogoService } from "./tecnicos-logo.service";

export const TecnicosLogoRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {
    let resolve = {
        'entity': ['tecnicosLogoService', '$stateParams', '$state', (tecnicosLogoService: TecnicosLogoService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tecnicologo) {
                    return new Promise((resolve: any, reject: any) => {
                        tecnicosLogoService.get($stateParams.tecnico, $stateParams.tecnicologo)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('tecnicos_tecnicoslogos_not_found', $stateParams)) {
                                        $state.go('tecnicos_tecnicoslogos_not_found', $stateParams);
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
        .state('tecnicos_tecnicoslogos', {
            url: '/{tecnico}/tecnicos/tecnicoslogos?q?ativo',
            resolve: resolveFilter,
            template: require('./index/tecnicos-logo-index.html'),
            controller: 'tecnicosLogoIndexController',
            controllerAs: 'tcncs_tcncslgs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('tecnicos_tecnicoslogos_new', {
            parent: 'tecnicos_tecnicoslogos',
            url: '/new',
            resolve: resolve,
            template: require('./new/tecnicos-logo-new.html'),
            controller: 'tecnicosLogoNewController',
            controllerAs: 'tcncs_tcncslgs_frm_nw_cntrllr'
        });
}];
