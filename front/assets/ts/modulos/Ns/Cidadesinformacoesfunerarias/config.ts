export const CidadesinformacoesfunerariasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['NsCidadesinformacoesfunerarias', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.cidadeinformacaofuneraria) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.cidadeinformacaofuneraria)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_cidadesinformacoesfunerarias_not_found', $stateParams)) {
                                        $state.go('ns_cidadesinformacoesfunerarias_not_found', $stateParams);
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
        .state('ns_cidadesinformacoesfunerarias', {
            url: '/ns/cidadesinformacoesfunerarias?q?municipio',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Ns\CidadesinformacoesfunerariasListController',
            controllerAs: 'ns_cddsnfrmcsfnrrs_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('ns_cidadesinformacoesfunerarias_new', {
            parent: 'ns_cidadesinformacoesfunerarias',
            url: '/new',
            resolve: resolve,
            template: require('./new.html'),
            controller: 'NsCidadesinformacoesfunerariasFormNewController',
            controllerAs: 'ns_cddsnfrmcsfnrrs_frm_nw_cntrllr'
        }).state('ns_cidadesinformacoesfunerarias_show', {
            parent: 'ns_cidadesinformacoesfunerarias',
            url: '/:cidadeinformacaofuneraria',
            resolve: resolve,
            template: require('./show.html'),
            controller: 'NsCidadesinformacoesfunerariasFormShowController',
            controllerAs: 'ns_cddsnfrmcsfnrrs_frm_shw_cntrllr'
        }).state('ns_cidadesinformacoesfunerarias_edit', {
            parent: 'ns_cidadesinformacoesfunerarias',
            url: '/:cidadeinformacaofuneraria/edit',
            resolve: resolve,
            template: require('./form.html'),
            controller: 'Ns\CidadesinformacoesfunerariasFormController',
            controllerAs: 'ns_cddsnfrmcsfnrrs_frm_cntrllr'
        });
}]