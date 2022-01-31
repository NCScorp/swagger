import angular = require('angular');
import { ServiceBase } from '../../../shared/services/service-base/service-base.service';
import { ITemplateOrdemServico } from './interfaces/templates-os.interface';

export class TemplatesOrdemServicoService extends ServiceBase<ITemplateOrdemServico>{
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
        this.campoid = 'ordemservicotemplate';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'ordensservicos_templates';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'servicos';
    }
}

