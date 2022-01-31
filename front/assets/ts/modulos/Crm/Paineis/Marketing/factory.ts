import angular = require('angular');
import { IPainelMarketingApi } from './interfaces.api';

export class CrmPainelMarketing {
    /**
     * Lista de injeções de dependência da tela
     */
    static $inject = [
        '$http', 
        'nsjRouting',
    ];

    constructor(
        public $http: angular.IHttpService, 
        public nsjRouting: any
    ) {}

    /**
     * Retorna os dados do painel de marketing
     * @param filtros 
     */
    get(filtros: any = {}): Promise<IPainelMarketingApi>{
        return new Promise((resolve, reject) => {
            // Monto parâmetros
            const params = filtros;
            // Monto a url
            const url = this.nsjRouting.generate('relatorios_paineis_marketing_get', params, true);

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
