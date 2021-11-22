import angular = require('angular');
/**
 * Simulação da API de contratos
 */
import apiContratos from './api-contratos.json';
/**
 * Simulação da API de contratos, próximo item na paginação
 */
import apiContratosNext from './api-contratos-next.json';
/**
 * Simulação da API de totais contratos
 */
import apiContratosTotais from './api-contratos-totais.json';

import { IContratoApi } from './interfaces/icontratoapi.interface';
import { RotasService } from '@shared/rotas/rotas.service';
import { ApiService } from '@shared/api/api.service';
import { IContratoTotaisApi } from './interfaces/icontratototaisapi.interface';

export class ContratosService {
    /**
     * Injeções de dependência do service
     */
    static $inject = [
        '$http', 
        'nsjRouting', 
        '$rootScope', 
        '$q',
        'rotasService',
        'apiService'
    ];
    
    constructor(
        $http: angular.IHttpService, 
        nsjRouting: any, 
        $rootScope: angular.IScope, 
        $q: angular.IQService,
        private rotasService: RotasService,
        private apiService: ApiService
    ) {}

    /**
     * Dispara requisição de buscar listagegm da entidade
     * @param entidade 
     * @param construtores 
     */
    public getAll(filters: IGetAllFilters = {}): Promise<IContratoApi> {
        return new Promise((resolve, reject) => {
            const url = this.rotasService.getRota('contratos_listar', { 
                expand: ['pessoa', 'itemContrato']
            });

            // Busco contratos
            this.apiService.get(url).then((retorno: IContratoApi) => {
                resolve(retorno);
                // resolve(<IContratoApi>(apiContratos));
            }).catch((err) => {
                // resolve(<IContratoApi>(apiContratos));
                reject(err);
            });
        });
    }

    /**
     * Dispara requisição de buscar listagem de contratos do link "Next" da lista paginada
     * @param link 
     */
     public getAllNext(link: string): Promise<IContratoApi> {
        return new Promise((resolve, reject) => {
            // Busco contratos
            this.apiService.get(link).then((retorno: IContratoApi) => {
                // resolve(retorno);
                resolve(<IContratoApi>(apiContratosNext));
            }).catch((err) => {
                // resolve(<IContratoApi>(apiContratosNext));
                reject(err);
            });
        });
    }

    /**
     * Dispara requisição de buscar totalizadores de contratos
     * @param link 
     */
     public getTotaisContratos(): Promise<IContratoTotaisApi> {
        return new Promise((resolve, reject) => {
            const url = this.rotasService.getRota('contratos_totais');

            // Busco totais
            this.apiService.get(url).then((retorno: IContratoTotaisApi) => {
                resolve(retorno);
            }).catch((err) => {
                // resolve(<IContratoTotaisApi>apiContratosTotais);
                reject(err);
            });
        });
    }
}

export interface IGetAllFilters {
    texto?: string;
    periodo?: {
        inicio?: string;
        fim?: string;
    }
}