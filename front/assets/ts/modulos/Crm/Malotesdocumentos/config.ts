export const malotesDocumentosRoutes  = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

            let resolve = {
                    'entity' : ['CrmMalotesdocumentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                            if ($stateParams.entity) {
                                return $stateParams.entity;
                            } else {
                                if ($stateParams.malotedocumento) {
                                    return new Promise((resolve: any, reject: any) => {
                                        entityService.get($stateParams.malote, $stateParams.malotedocumento)
                                        .then((data: any) => {
                                            resolve(data);
                                        })
                                        .catch( (error: any) => {
                                           if (error.status === 404) {
                                               if ($state.href('crm_malotesdocumentos_not_found', $stateParams)) {
                                                   $state.go('crm_malotesdocumentos_not_found', $stateParams);
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
                                        .state('crm_malotesdocumentos', {
                        url: '/{malote}/crm/malotesdocumentos?q',
                        resolve : resolveFilter,
                        template: require('./index.html'),
                        controller: 'Crm\MalotesdocumentosListController',
                        controllerAs: 'crm_mltsdcmnts_lst_cntrllr',
                        reloadOnSearch: false
                    })
                                        .state('crm_malotesdocumentos_new', {
                                                    url: '/{malote}/crm/malotesdocumentos/new',
                                                resolve : resolve,
                        template: require('./new.html'),
                        controller: 'CrmMalotesdocumentosFormNewController',
                        controllerAs: 'crm_mltsdcmnts_frm_nw_cntrllr'
                    })                    .state('crm_malotesdocumentos_show', {
                                                    url: '/{malote}/crm/malotesdocumentos/:malotedocumento',
                                                resolve : resolve,
                        template: require('./show.html'),
                        controller: 'CrmMalotesdocumentosFormShowController',
                        controllerAs: 'crm_mltsdcmnts_frm_shw_cntrllr'
                    })                    .state('crm_malotesdocumentos_edit', {
                                                    url: '/{malote}/crm/malotesdocumentos/:malotedocumento/edit',
                                                                        resolve : resolve,
                        template: require('./form.html'),
                        controller: 'Crm\MalotesdocumentosFormController',
                        controllerAs: 'crm_mltsdcmnts_frm_cntrllr'
                    })                    ; }]