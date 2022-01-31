import angular = require('angular');
import { ServiceBase } from '../../../../../shared/services/service-base/service-base.service'; 
import { IEscopo } from '../../interfaces/escopo.interface';
import { IEscopoNode } from '../../interfaces/escoponode.interface';

export class EscopoService extends ServiceBase<IEscopo>{
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
        this.campoid = 'projetoitemescopo';
    }

    setNomeEntidade(): void {
        this.nomeEntidade = 'projetositensescopos';
    }

    setModuloEntidade(): void {
        this.moduloEntidade = 'gp';
    }

    getVeiculosOcupados (node: IEscopoNode): any {
      return this.$q((resolve, reject) => {
        this.$http
        .get(
          this.nsjRouting.generate('gp_projetositensescopos_listar_veiculos_ocupados', { 
            id: node.projetoitemescopo, 
            projeto: node.projeto.projeto,
            veiculo: node.veiculo.veiculo,
            datahorainicio: node.datahorainicio,
            datahorafim: node.datahorafim,
          }, true, true),
          {
            timeout: this.loading_deferred.promise,
          }
          )
          .then((response) => {
            this.dispararEvento('gp_projetositensescopos_listar_veiculos_ocupados_success', response.data);
            resolve(response.data);
          })
          .catch((response) => {
            reject(response);
          });
        });
    }
}

