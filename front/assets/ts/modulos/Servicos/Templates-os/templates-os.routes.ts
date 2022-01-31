import * as angular from 'angular';
import { TemplatesOrdemServicoService } from './templates-os.service';
import { TemplateOrdemServicoFormView } from './interfaces/template-os.form';

export const TemplatesOrdemServicoRouting = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {
    let resolve = {
        'entity': ['templatesOrdemServicoService', '$stateParams', '$state', (templatesOrdemServicoService: TemplatesOrdemServicoService, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.ordemservicotemplate) {
                    return new Promise((resolve: any, reject: any) => {
                        templatesOrdemServicoService.get($stateParams.ordemservicotemplate)
                            .then((data) => {
                                resolve(new TemplateOrdemServicoFormView(data));
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('ordensservicos_templates_not_found', $stateParams)) {
                                        $state.go('ordensservicos_templates_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    $state.go('ordensservicos_templates_not_found', $stateParams);
                }
            }
        }]
    };

    let resolveFilter = {};

    $stateProvider
        .state('ordensservicos_templates', {
            url: '/ordens-servicos-templates?q',
            resolve: resolveFilter,
            template: require('./index/templates-os-index.html'),
            controller: 'templatesOrdemServicoIndexController',
            controllerAs: 'tpt_os_lst_cntrllr',
            reloadOnSearch: false
        }).state('ordensservicos_templates_new', {
            parent: 'ordensservicos_templates',
            url: '/new',
            resolve: resolveFilter,
            template: require('./new/templates-os-new.html'),
            controller: 'templatesOrdemServicoNewController',
            controllerAs: 'tpt_os_frm_nw_cntrllr'
        })
        .state('ordensservicos_templates_show', {
            parent: 'ordensservicos_templates',
            url: '/:ordemservicotemplate',
            resolve: resolve,
            template: require('./showedit/templates-os-showedit.html'),
            controller: 'templatesOrdemServicoShowEditController',
            controllerAs: 'tpt_os_frm_shw_dt_cntrllr'
        });
}];

