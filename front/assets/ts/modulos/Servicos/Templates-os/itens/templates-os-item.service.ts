import angular = require('angular');

import { ServiceBase } from '../../../../shared/services/service-base/service-base.service';
import { ITemplateOrdemServicoItem } from './interfaces/templates-os-item.interface';

export class TemplatesOrdemServicoItemService extends ServiceBase<ITemplateOrdemServicoItem>{
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
        this.campoid = 'ordemservicotemplateitem';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'ordensservicos_templates_itens';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'servicos';
    }
}

