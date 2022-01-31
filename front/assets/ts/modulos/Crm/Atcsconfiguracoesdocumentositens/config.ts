export const atcsConfiguracoesDocumentosRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

            let resolve = {
                    'entity' : ['CrmAtcsconfiguracoesdocumentositens', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                            if ($stateParams.entity) {
                                return $stateParams.entity;
                            } else {
                                if ($stateParams.atcconfiguracaodocumentoitem) {
                                    return new Promise((resolve: any, reject: any) => {
                                        entityService.get($stateParams.atcconfiguracaodocumento, $stateParams.atcconfiguracaodocumentoitem)
                                        .then((data: any) => {
                                            resolve(data);
                                        })
                                        .catch( (error: any) => {
                                           if (error.status === 404) {
                                               if ($state.href('crm_atcsconfiguracoesdocumentositens_not_found', $stateParams)) {
                                                   $state.go('crm_atcsconfiguracoesdocumentositens_not_found', $stateParams);
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
                                        .state('crm_atcsconfiguracoesdocumentositens', {
                        url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoesdocumentositens?q',
                        resolve : resolveFilter,
                        template: require('./index.html'),
                        controller: 'Crm\AtcsconfiguracoesdocumentositensListController',
                        controllerAs: 'crm_tcscnfgrcsdcmntstns_lst_cntrllr',
                        reloadOnSearch: false
                    })
                                        .state('crm_atcsconfiguracoesdocumentositens_new', {
                                                    url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoesdocumentositens/new',
                                                resolve : resolve,
                        template: require('./new.html'),
                        controller: 'CrmAtcsconfiguracoesdocumentositensFormNewController',
                        controllerAs: 'crm_tcscnfgrcsdcmntstns_frm_nw_cntrllr'
                    })                    .state('crm_atcsconfiguracoesdocumentositens_show', {
                                                    url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoesdocumentositens/:atcconfiguracaodocumentoitem',
                                                resolve : resolve,
                        template: require('./show.html'),
                        controller: 'CrmAtcsconfiguracoesdocumentositensFormShowController',
                        controllerAs: 'crm_tcscnfgrcsdcmntstns_frm_shw_cntrllr'
                    })                    .state('crm_atcsconfiguracoesdocumentositens_edit', {
                                                    url: '/{atcconfiguracaodocumento}/crm/atcsconfiguracoesdocumentositens/:atcconfiguracaodocumentoitem/edit',
                                                                        resolve : resolve,
                        template: require('./form.html'),
                        controller: 'Crm\AtcsconfiguracoesdocumentositensFormController',
                        controllerAs: 'crm_tcscnfgrcsdcmntstns_frm_cntrllr'
                    })                    ; }];

