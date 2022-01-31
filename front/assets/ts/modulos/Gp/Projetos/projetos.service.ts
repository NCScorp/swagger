import angular = require('angular');

import { ServiceBase } from '../../../shared/services/service-base/service-base.service';
import { IProjeto } from '../Projetos/interfaces/projetos.interface';

export class ProjetosService extends ServiceBase<IProjeto>{
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
        this.campoid = 'projeto';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'projetos';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'financas';
    }
}

