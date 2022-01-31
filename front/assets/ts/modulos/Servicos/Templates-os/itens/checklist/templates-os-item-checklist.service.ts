import angular = require('angular');

import { ServiceBase } from '../../../../../shared/services/service-base/service-base.service';
import { ITemplateOrdemServicoItemChecklist } from './interfaces/templates-os-item-checklist.interface';

export class TemplatesOrdemServicoItemChecklistService extends ServiceBase<ITemplateOrdemServicoItemChecklist>{
    /**
     * Injeções de dependência do service
     */
    static $inject = [
        '$http', 
        'nsjRouting', 
        '$rootScope', 
        '$q'
    ];
    
    constructor(
        $http: angular.IHttpService, 
        nsjRouting: any, 
        $rootScope: angular.IScope, 
        $q: angular.IQService
    ) {
        super(
            $http,
            nsjRouting,
            $rootScope,
            $q
        );
    }

    protected setCampoid(): void {
        this.campoid = 'ordemservicotemplatechecklist';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'ordensservicos_templates_itens_checklist';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'servicos';
    }
}

