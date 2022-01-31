import angular = require('angular');
import { ServiceBase } from '../../../../../shared/services/service-base/service-base.service'; 
import { IOrdensServicosTemplates } from '../../interfaces/ordensservicostemplates.interface';

export class OrdensServicosTemplatesService extends ServiceBase<IOrdensServicosTemplates>{
    /**
     * Injeções de dependência do service
     */
    static $inject = [
        '$http', 
        'nsjRouting', 
        '$rootScope', 
        '$q',
        '$state',
    ];
    
    constructor(
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public $rootScope: angular.IScope, 
        public $q: angular.IQService,
        public $state: angular.ui.IStateService,
    ) {
        super(
            $http,
            nsjRouting,
            $rootScope,
            $q
        );
    }

    protected setCampoid(): void {
        this.campoid = '';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'ordensservicos_templates';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'servicos';
    }
}

