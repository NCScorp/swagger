import angular = require('angular');
import { IListadavezconfiguracoesApi } from './classes';

export class CrmListadavezconfiguracoes {
    static $inject = [
        '$http', 
        'nsjRouting', 
        '$rootScope', 
        '$q'
    ];

    public constructors: any = {};

    constructor(
        public $http: angular.IHttpService, 
        public nsjRouting: any,
        public $rootScope: angular.IScope, 
        public $q: angular.IQService
    ) {}

    /**
     * Salva lista de configurações da lista da vez em lote
     */
    salvarEmLote(configuracaoEmLote: IConfiguracaoEmLoteForm): Promise<any> {
        return new Promise((resolve, reject) => {
            // Monto a url
            const url = this.nsjRouting.generate('crm_listadavezconfiguracoes_salvar_lote', angular.extend(this.constructors), true, true);
            // Monto os dados a serem enviados
            const data = angular.copy(configuracaoEmLote);

            // Chamo requisição
            this.$http({
                method: 'POST',
                url: url,
                data: data
            }).then((response: any) => {
                resolve(response);
            })
            .catch((err: any) => {
                reject(err);
            });
        });
    }

    /**
     * Retorna a lista das configurações da lista da vez
     */
    getAll(): Promise<IListadavezconfiguracoesApi[]> {
        return new Promise((resolve, reject) => {
            // Monto a url
            const url = this.nsjRouting.generate('crm_listadavezconfiguracoes_index', angular.extend(this.constructors), true, true);

            // Chamo requisição
            this.$http({
                method: 'GET',
                url: url
            }).then((response: any) => {
                resolve(response.data);
            })
            .catch((err: any) => {
                reject(err);
            });
        });
    }
}

export interface IConfiguracaoEmLoteForm {
    listadavezconfiguracoes: IListadavezconfiguracoesApi[]
}
