export const AtcsconfiguracoestemplatesemailsRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

            let resolve = {
                    'entity' : ['CrmAtcsconfiguracoestemplatesemails', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                            if ($stateParams.entity) {
                                return $stateParams.entity;
                            } else {
                                if ($stateParams.atcconfiguracaotemplateemail) {
                                    return new Promise((resolve: any, reject: any) => {
                                        entityService.get($stateParams.atcconfiguracaodocumento, $stateParams.atcconfiguracaotemplateemail)
                                        .then((data: any) => {
                                            resolve(data);
                                        })
                                        .catch( (error: any) => {
                                           if (error.status === 404) {
                                               if ($state.href('crm_atcsconfiguracoestemplatesemails_not_found', $stateParams)) {
                                                   $state.go('crm_atcsconfiguracoestemplatesemails_not_found', $stateParams);
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
                                        .state('crm_atcsconfiguracoestemplatesemails', {
                        url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoestemplatesemails?q',
                        resolve : resolveFilter,
                        template: require('./index.html'),
                        controller: 'Crm\AtcsconfiguracoestemplatesemailsListController',
                        controllerAs: 'crm_tcscnfgrcstmpltsmls_lst_cntrllr',
                        reloadOnSearch: false
                    })
                                        .state('crm_atcsconfiguracoestemplatesemails_new', {
                                                    url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoestemplatesemails/new',
                                                resolve : resolve,
                        template: require('./new.html'),
                        controller: 'CrmAtcsconfiguracoestemplatesemailsFormNewController',
                        controllerAs: 'crm_tcscnfgrcstmpltsmls_frm_nw_cntrllr'
                    })                    .state('crm_atcsconfiguracoestemplatesemails_show', {
                                                    url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoestemplatesemails/:atcconfiguracaotemplateemail',
                                                resolve : resolve,
                        template: require('./show.html'),
                        controller: 'CrmAtcsconfiguracoestemplatesemailsFormShowController',
                        controllerAs: 'crm_tcscnfgrcstmpltsmls_frm_shw_cntrllr'
                    })                    .state('crm_atcsconfiguracoestemplatesemails_edit', {
                                                    url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoestemplatesemails/:atcconfiguracaotemplateemail/edit',
                                                                        resolve : resolve,
                        template: require('./form.html'),
                        controller: 'Crm\AtcsconfiguracoestemplatesemailsFormController',
                        controllerAs: 'crm_tcscnfgrcstmpltsmls_frm_cntrllr'
                    })                    ; }];

