export const TemplatesemailadvertirprestadorRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['CrmTemplatesemailadvertirprestador', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.templateemailadvertirprestador) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.templateemailadvertirprestador)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('crm_templatesemailadvertirprestador_not_found', $stateParams)) {
                                        $state.go('crm_templatesemailadvertirprestador_not_found', $stateParams);
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
        .state('crm_templatesemailadvertirprestador', {
            url: '/crm/templatesemailadvertirprestador?q?estabelecimento',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Crm\TemplatesemailadvertirprestadorListController',
            controllerAs: 'crm_tmpltsmldvrtrprstdr_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('crm_templatesemailadvertirprestador_new', {
            parent: 'crm_templatesemailadvertirprestador',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'CrmTemplatesemailadvertirprestadorFormNewController',
            controllerAs: 'crm_tmpltsmldvrtrprstdr_frm_nw_cntrllr'
        }).state('crm_templatesemailadvertirprestador_show', {
            parent: 'crm_templatesemailadvertirprestador',
            url: '/:templateemailadvertirprestador',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'CrmTemplatesemailadvertirprestadorFormShowController',
            controllerAs: 'crm_tmpltsmldvrtrprstdr_frm_shw_cntrllr'
        }).state('crm_templatesemailadvertirprestador_edit', {
            parent: 'crm_templatesemailadvertirprestador',
            url: '/:templateemailadvertirprestador/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Crm\TemplatesemailadvertirprestadorFormController',
            controllerAs: 'crm_tmpltsmldvrtrprstdr_frm_cntrllr'
        });
}];

