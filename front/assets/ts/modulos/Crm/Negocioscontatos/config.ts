export const NegocioscontatosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

            let resolve = {
                    'entity' : ['CrmNegocioscontatos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                            if ($stateParams.entity) {
                                return $stateParams.entity;
                            } else {
                                if ($stateParams.id) {
                                    return new Promise((resolve: any, reject: any) => {
                                        entityService.get($stateParams.documento, $stateParams.id)
                                        .then((data: any) => {
                                            resolve(data);
                                        })
                                        .catch( (error: any) => {
                                           if (error.status === 404) {
                                               if ($state.href('crm_negocioscontatos_not_found', $stateParams)) {
                                                   $state.go('crm_negocioscontatos_not_found', $stateParams);
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
                                        .state('crm_negocioscontatos', {
                        url: '/{documento}/crm/negocioscontatos?q',
                        resolve : resolveFilter,
                        template: require('./index.html'),
                        controller: 'Crm\NegocioscontatosListController',
                        controllerAs: 'crm_ngcscntts_lst_cntrllr',
                        reloadOnSearch: false
                    })
                                        .state('crm_negocioscontatos_new', {
                                                    url: '/{documento}/crm/negocioscontatos/new',
                                                resolve : resolve,
                        template: require('./new.html'),
                        controller: 'CrmNegocioscontatosFormNewController',
                        controllerAs: 'crm_ngcscntts_frm_nw_cntrllr'
                    })                    .state('crm_negocioscontatos_edit', {
                                                    url: '/{documento}/crm/negocioscontatos/:id/edit',
                                                                        resolve : resolve,
                        template: require('./form.html'),
                        controller: 'Crm\NegocioscontatosFormController',
                        controllerAs: 'crm_ngcscntts_frm_cntrllr'
                    })                    ; }]