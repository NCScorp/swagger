export const MotivosdesqualificacoesprenegociosRoutes  = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

            let resolve = {
                    'entity' : ['CrmMotivosdesqualificacoesprenegocios', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                            if ($stateParams.entity) {
                                return $stateParams.entity;
                            } else {
                                if ($stateParams.motivodesqualificacaoprenegocio) {
                                    return new Promise((resolve: any, reject: any) => {
                                        entityService.get($stateParams.motivodesqualificacaoprenegocio)
                                        .then((data: any) => {
                                            resolve(data);
                                        })
                                        .catch( (error: any) => {
                                           if (error.status === 404) {
                                               if ($state.href('crm_motivosdesqualificacoesprenegocios_not_found', $stateParams)) {
                                                   $state.go('crm_motivosdesqualificacoesprenegocios_not_found', $stateParams);
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
                                        .state('crm_motivosdesqualificacoesprenegocios', {
                        url: '/crm/motivosdesqualificacoesprenegocios?q',
                        resolve : resolveFilter,
                        template: require('./index.html'),
                        controller: 'Crm\MotivosdesqualificacoesprenegociosListController',
                        controllerAs: 'crm_mtvsdsqlfccsprngcs_lst_cntrllr',
                        reloadOnSearch: false
                    })
                                        .state('crm_motivosdesqualificacoesprenegocios_show', {
                                                    url: '/crm/motivosdesqualificacoesprenegocios/:motivodesqualificacaoprenegocio',
                                                resolve : resolve,
                        template: require('./show.html'),
                        controller: 'CrmMotivosdesqualificacoesprenegociosFormShowController',
                        controllerAs: 'crm_mtvsdsqlfccsprngcs_frm_shw_cntrllr'
                    })                    ; }];

