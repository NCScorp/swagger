import angular = require('angular');
import { ServiceBase } from '../../../../shared/services/service-base/service-base.service';
import { ITemplateOrdemServicoMaterial } from './interfaces/templates-os-material.interface';

export class TemplatesOrdemServicoMaterialService extends ServiceBase<ITemplateOrdemServicoMaterial>{
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
        this.campoid = 'ordemservicotemplatematerial';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'ordensservicos_templates_materiais';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'servicos';
    }
}

