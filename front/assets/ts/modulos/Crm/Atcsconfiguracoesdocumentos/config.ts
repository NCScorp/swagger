export const atcsConfiguracoesDocumentosRoutes = ['$stateRegistryProvider', '$stateProvider', ($stateRegistryProvider: any, $stateProvider: angular.ui.IStateProvider) => {

        let resolve = {
            'entity': ['CrmAtcsconfiguracoesdocumentos', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
                if ($stateParams.entity) {
                    return $stateParams.entity;
                } else {
                    if ($stateParams.atcconfiguracaodocumento) {
                        return new Promise((resolve: any, reject: any) => {
                            entityService.get($stateParams.atcconfiguracaodocumento)
                                .then((data: any) => {
                                    resolve(data);
                                })
                                .catch((error: any) => {
                                    if (error.status === 404) {
                                        if ($state.href('crm_atcsconfiguracoesdocumentos_not_found', $stateParams)) {
                                            $state.go('crm_atcsconfiguracoesdocumentos_not_found', $stateParams);
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
            .state('crm_atcsconfiguracoesdocumentos', {
                // url: '/crm/atcsconfiguracoesdocumentos?q',
                // resolve: resolveFilter,
                // template: require('./index.html'),
                // controller: 'Crm\AtcsconfiguracoesdocumentosListController',
                // controllerAs: 'crm_tcscnfgrcsdcmnts_lst_cntrllr',
                // reloadOnSearch: false
                url: '/crm/atcsconfiguracoesdocumentos',
                resolve: {},
                template: require('./configuracao-documentos.html'),
                controller: 'CrmConfiguracaoDocumentosController',
                controllerAs: 'crm_tcscnfgrcsdcmnts_lst_cntrllr',
            })
            .state('crm_atcsconfiguracoesdocumentos_new', {
                url: '/crm/atcsconfiguracoesdocumentos/new',
                resolve: resolve,
                template: require('./new.html'),
                controller: 'CrmAtcsconfiguracoesdocumentosFormNewController',
                controllerAs: 'crm_tcscnfgrcsdcmnts_frm_nw_cntrllr'
            }).state('crm_atcsconfiguracoesdocumentos_show', {
                url: '/crm/atcsconfiguracoesdocumentos/:atcconfiguracaodocumento',
                resolve: resolve,
                template: require('./show.html'),
                controller: 'CrmAtcsconfiguracoesdocumentosFormShowController',
                controllerAs: 'crm_tcscnfgrcsdcmnts_frm_shw_cntrllr'
            }).state('crm_atcsconfiguracoesdocumentos_edit', {
                url: '/crm/atcsconfiguracoesdocumentos/:atcconfiguracaodocumento/edit',
                resolve: resolve,
                template: require('./form.html'),
                controller: 'Crm\AtcsconfiguracoesdocumentosFormController',
                controllerAs: 'crm_tcscnfgrcsdcmnts_frm_cntrllr'
            });
    }];

