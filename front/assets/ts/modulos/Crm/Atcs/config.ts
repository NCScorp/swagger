import { CrmAtcsareas } from "./../Atcsareas/factory";
import angular = require('angular')

export const atcsRoutes = ['$stateProvider', '$stateRegistryProvider', ($stateProvider: angular.ui.IStateProvider, $stateRegistryProvider: any) => {

    let resolve = {
        'entity': ['CrmAtcs', '$stateParams', '$state', 'CrmAtcsareas', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService, crmAtcsareas: CrmAtcsareas) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.atc) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.atc)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_atcs_not_found', $stateParams)) {
                                        $state.go('crm_atcs_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    if (!$stateParams.area && !$stateParams.negociopai) return {}
                    else {
                        if ($stateParams.area && $stateParams.negociopai) {
                            return new Promise((resolve: any, reject: any) => {
                                entityService.get($stateParams.negociopai)
                                    .then((res: any) => {
                                        let negociopai: any = { negociopai: res };
                                        negociopai.area =
                                        {
                                            "negocioarea": negociopai.negociopai.area.negocioarea,
                                            "nome": negociopai.negociopai.area.nome,
                                            "tenant": negociopai.negociopai.tenant
                                        };
                                        resolve(negociopai)

                                    })
                            });
                        }
                        if ($stateParams.area) {
                            return new Promise((resolve: any, reject: any) => {
                                crmAtcsareas.get($stateParams.area)
                                    .then((res: any) => {
                                        let atcArea: any = { area: res }
                                        resolve(atcArea)
                                    })
                            });
                        }
                    }
                }
            }
        }]
    };
    let resolveFilter = {
        'clienteFilter': ['NsClientes', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.cliente) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.cliente)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            } else {
                return {};
            }
        }]
        ,
        'areaFilter': ['CrmAtcsareas', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.area) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.area)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            }
            if ($stateParams.negociopai) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.negociopai)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            } else {
                return {};
            }
        }]
    };


    // $stateRegistryProvider.deregister('crm_atcs_new');

    $stateProvider
        .state('crm_atcs_full', {
            url: '/crm/atcs/?:atc',
            resolve: resolve,
            template: require('./atcs.full.html'),
            controller: 'CrmAtcsController',
            controllerAs: 'crm_tcs_cntrllr',

        })
        .state('crm_atcs_full_new', {
            url: '/crm/atcs/new?area&negociopai',
            resolve: resolve,
            template: require('./atcs.full.html'),
            controller: 'CrmAtcsController',
            controllerAs: 'crm_tcs_cntrllr',
        })
        .state('crm_atcs', {
            url: '/crm/atcs?q?negociopai?cliente?localizacaopaisnome?localizacaoestadonome?localizacaomunicipionome?status?possuiseguradora?area?datacriacao_inicio?datacriacao_fim?dataedicao_inicio?dataedicao_fim',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\AtcsListController',
            controllerAs: 'crm_tcs_lst_cntrllr',
            reloadOnSearch: false
        })
        // .state('crm_atcs_new', {
        //     url: '/crm/atcs/?area/new',
        //     resolve: resolve,
        //     template: require('./../../Crm/Atcs/new.html'),
        //     controller: 'CrmAtcsFormNewController',
        //     controllerAs: 'crm_tcs_frm_nw_cntrllr'
        // })
        // .state('crm_atcs_show', {
        //     url: '/crm/atcs/:atc',
        //     resolve: resolve,
        //     template: require('./show.html'),
        //     controller: 'CrmAtcsFormShowController',
        //     controllerAs: 'crm_tcs_frm_shw_cntrllr'
        // })
        // .state('crm_atcs_edit', {
        //     url: '/crm/atcs/:atc/edit',
        //     resolve: resolve,
        //     template: require('./form.html'),
        //     controller: 'Crm\AtcsFormController',
        //     controllerAs: 'crm_tcs_frm_cntrllr'
        // })
        ;

}];
