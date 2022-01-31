import {OperacoesDocumentosTemplatesService} from "./operacoes-documentos-templates.service";

export const OperacoesDocumentosTemplatesRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['operacoesDocumentosTemplatesService', '$stateParams', '$state', (operacoesDocumentosTemplatesService: OperacoesDocumentosTemplatesService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.operacaodocumentotemplate) {
                    return new Promise((resolve: any, reject: any) => {
                        operacoesDocumentosTemplatesService.get($stateParams.operacaoordemservico, $stateParams.operacaodocumentotemplate)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('operacoesdocumentostemplates_not_found', $stateParams)) {
                                        $state.go('operacoesdocumentostemplates_not_found', $stateParams);
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
    let resolveFilter = {};
    $stateProvider
        .state('operacoesdocumentostemplates', {
            url: '/{operacaoordemservico}/operacoesdocumentostemplates?q',
            resolve: resolveFilter,
            template: require('./index/operacoes-documentos-templates-index.html'),
            controller: 'operacoesDocumentosTemplatesIndexController',
            controllerAs: 'prcsdcmntstmplts_lst_cntrllr',
            reloadOnSearch: false
        })
        .state('operacoesdocumentostemplates_new', {
            url: '/{operacaoordemservico}/operacoesdocumentostemplates/new',
            resolve: resolve,
            template: require('./new/operacoes-documentos-templates-new.html'),
            controller: 'operacoesDocumentosTemplatesNewController',
            controllerAs: 'prcsdcmntstmplts_frm_nw_cntrllr'
        })
        .state('operacoesdocumentostemplates_show', {
        url: '/{operacaoordemservico}/operacoesdocumentostemplates/:operacaodocumentotemplate',
        resolve: resolve,
        template: require('./show/operacoes-documentos-templates-show.html'),
        controller: 'operacoesDocumentosTemplatesShowFormController',
        controllerAs: 'prcsdcmntstmplts_frm_shw_cntrllr'
    });
}];
