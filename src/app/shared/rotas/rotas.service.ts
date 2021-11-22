import configJson from '@config/config.json';
import { ConfigService } from '@core/configuracao/nsj-config';

export class RotasService {
    private rotas: IRotaApi[] = [];

    constructor(){
        // Contratos
        this.rotas.push({
            nome: 'contratos_totais',
            url: 'contratos-estatisticas',
            urlParams: []
        });
        this.rotas.push({
            nome: 'contratos_listar',
            url: 'contratos',
            urlParams: []
        });
    }

    /**
     * Monta uma rota baseado no nome e parametros
     * @param nome 
     * @param params 
     * @returns 
     */
    getRota(nome, params: any = {}): string {
        const rota = this.rotas.find((rotaFind) => {
            return rotaFind.nome == nome;
        });

        if (!rota) {
            throw new Error("Rota não encontrada");
        }

        let urlRota = rota.url;
        
        // Converto query parametros
        let queryParams = '';
        for (const param in params) {
            if (rota.urlParams.indexOf(param) <= -1) {
                let queryParam = '';

                if (!Array.isArray(params[param])) {
                    queryParam = `${param}=${params[param]}`;
                } else {
                    const queryParamValues = params[param].join(`&${param}=`);
                    queryParam = `${param}=${queryParamValues}`;
                }

                if (queryParams != '') {
                    queryParams += '&';
                }

                queryParams += queryParam;
            }
        }
        // Converto params de url
        rota.urlParams.forEach((urlParam) => {
            urlRota = urlRota.replace(`:${urlParam}`, params[urlParam]);
        })

        // Defino o tenant atual na url @TODO: Remover mock de tenant e buscar do usuário
        // const tenantId = '11045';
        const tenantId = ConfigService.getConfig().config.globals.tenant_id;
        let urlBase = configJson.api.url.replace(':tenant', tenantId);

        // Monto url final
        let urlFinal = urlBase + urlRota + '?' + queryParams;

        return urlFinal;
    }
}

export interface IRotaApi {
    nome: string;
    url: string;
    urlParams: string[];
}