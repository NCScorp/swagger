import rotasInvalidas from '../rotas-invalidas.json'
import configRotas from './../../../config/rotasSistema.json';
import { NsjConfig, NsjHttpClient } from "@nsj/core";

/**
 * Interface dos arquivos de rotas
 */
export interface IRotasSistema {
    base_url: string;
    routes: any;
    host: string;
    scheme: string;
}

/**
 * Busca as rotas do sistema, de acordo com a url passada
 */
function carregarRotasFromUrl(url: string): Promise<IRotasSistema> {
    return new Promise(async (resolve, reject) => {
        try {
            const response = await NsjHttpClient.get<any>(url, false)
            resolve(response.parsedBody)
        }
        catch {
            reject()
        }
    });
}

/**
    * Carrega as rotas do sistema
    */
export async function carregarRotasSistema() {
    const dadosRota = {
        crmHost: '',
        crmScheme: '',
        erpapiHost: '',
        erpapiScheme: ''
    };

    return new Promise<IRotasSistema>(async (resolve, reject) => {
        try {

            // Busco rotas do sistema, tanto da api do CRM Web quanto do ERP Api
            const [rotasCrm, rotasErp] = await Promise.all([
                carregarRotasFromUrl(configRotas.routing.URL_CRM),
                carregarRotasFromUrl(configRotas.routing.URL_ERP_API)
            ]);

            dadosRota.crmHost = rotasCrm.host;
            dadosRota.crmScheme = rotasCrm.scheme;
            dadosRota.erpapiHost = rotasErp.host;
            dadosRota.erpapiScheme = rotasErp.scheme;
            
            NsjConfig.setConfig('dadosRota', dadosRota)

            const prefixoErp = 'erpapi_';

            const arrRotasCrmWebInvalidas = rotasInvalidas;
            arrRotasCrmWebInvalidas.forEach((rotaInvalidaForeach) => {
                if (rotasCrm.routes[rotaInvalidaForeach] != undefined) {
                    delete rotasCrm.routes[rotaInvalidaForeach];
                }
            });

            // Adiciono as rotas do ERP Api às rotas do crm, com prefixo identificando rotas do ERP Api
            Object.keys(rotasErp.routes).forEach((rotaForeach) => {
                rotasCrm.routes[prefixoErp + rotaForeach] = rotasErp.routes[rotaForeach];
            });

            resolve(rotasCrm)

        } catch (error) {
            alert('Não foi possível carregar rotas do sistema');
            reject()
        }
    })
}
