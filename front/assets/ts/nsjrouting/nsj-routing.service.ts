import { NsjConfig, NsjRoutes, NsjRouting } from '@nsj/core';
import angular = require('angular');

export class NsjRoutingService extends NsjRoutes {
    /**
     * Injeções de dependencias
     */
    static $inject = [];


    constructor() {
        super()
    }

    /**
     * Gera uma determinada rota
     * @param route 
     * @param opt_params 
     * @param absolute 
     * @param erpapi 
     */
    generate(route, opt_params = {}, absolute = true, erpapi: boolean = false): string {

        var params = this.getBaseParams(opt_params)

        // Se for rota do erp_api, busco rota com prefixo
        if (erpapi) {
            const prefixoErp = 'erpapi_';
            route = prefixoErp + route;
        }

        var ret: string = NsjRouting.generate(route, params, true);

        // Se for uma rota do ERP Api, ajusto rota pra substituir schema e host.
        if (erpapi) {
            const dadosRota = NsjConfig.getConfig('dadosRota');
            // Substituo schema
            ret = ret.replace(dadosRota.crmScheme, dadosRota.erpapiScheme);
            // Substituo host
            ret = ret.replace(dadosRota.crmHost, dadosRota.erpapiHost);
        }

        return ret;
    }
}
