import angular = require('angular');

import { ServiceBase } from '../../../shared/services/service-base/service-base.service';
import { IProjeto } from '../Projetos/interfaces/projetos.interface';

export class FormulariosModulosService extends ServiceBase<IProjeto>{
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
        this.nomeEntidade = 'formularios_modulos';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'crm';
    }

    finalizar(formulariomodulo: string): Promise<unknown> {
      return new Promise((resolve, reject) => {
        this.$http({
          method: "POST",
          url: this.nsjRouting.generate(
            'crm_formularios_modulos_finalizar',
            { 
              id: formulariomodulo
            },
            true,
            true
          ),
        })
          .then((response: any) => {
            resolve(response.data);
          })
          .catch((erro: any) => {
            reject(erro);
          });
      });
    }
}

