import {NsDocumentosFopTemplatesService} from "./documentos-fop-templates.service";

export const NsDocumentosFopTemplatesRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['nsDocumentosFopTemplatesService', '$stateParams', '$state', (nsDocumentosFopTemplatesService: NsDocumentosFopTemplatesService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.documentotemplate) {
                    return new Promise((resolve: any, reject: any) => {
                        nsDocumentosFopTemplatesService.get($stateParams.documentotemplate)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ns_documentosfoptemplates_not_found', $stateParams)) {
                                        $state.go('ns_documentosfoptemplates_not_found', $stateParams);
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
        .state('ns_documentosfoptemplates', {
            url: '/ns/documentosfoptemplates?q?empresa?estabelecimento',
            resolve: resolveFilter,
            template: require('./index/documentos-fop-templates-index.html'),
            controller: 'nsDocumentosFopTemplatesIndexController',
            controllerAs: 'ns_dcmntsfptmplts_lst_cntrllr',
            reloadOnSearch: false
        })
    ;
}];

