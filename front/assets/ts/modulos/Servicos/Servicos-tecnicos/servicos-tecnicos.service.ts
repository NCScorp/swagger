import angular = require('angular');
import { ServiceBase } from '../../../shared/services/service-base/service-base.service';
import { IServicoTecnico } from '../../Servicos/Servicos-tecnicos/interfaces/servicos-tecnicos.interface';

export class ServicosTecnicosService extends ServiceBase<IServicoTecnico> {
  /**
   * Injeções de dependência do service
   */
  static $inject = ['$http', 'nsjRouting', '$rootScope', '$q'];

  constructor(
    $http: angular.IHttpService,
    nsjRouting: any,
    $rootScope: angular.IScope,
    $q: angular.IQService
  ) {
    super($http, nsjRouting, $rootScope, $q);
  }

  protected setCampoid(): void {
    this.campoid = 'servicotecnico';
  }

  setNomeEntidade(): void {
    this.nomeEntidade = 'servicostecnicos';
  }

  setModuloEntidade(): void {
    this.moduloEntidade = 'servicos';
  }
}
