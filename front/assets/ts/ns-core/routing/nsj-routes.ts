
import { deepCopy, deepMerge } from '../utils/utils';
import { configInstance } from '../configuracao/nsj-config';
import { NsjRouting } from '../index';

export class NsjRoutesService {
    /**
    * Configurações
    */
    public config: any = {
        baseParams: {}
    }

    getBaseParams(opt_params) {
        const globals = configInstance.getConfig('globals');

        if (globals && globals.tenantCodigo) {
            this.config.baseParams['tenant'] = globals.tenantCodigo;
        }

        if (globals && globals.grupoEmpresarialCodigo) {
            this.config.baseParams['grupoempresarial'] = globals.grupoEmpresarialCodigo;
        }

        const params = deepMerge(deepCopy(this.config.baseParams), (opt_params || {}));
        return params;
    }

    generate(route: string, opt_params: object, absolute: boolean) {
        const params = this.getBaseParams(opt_params);
        return NsjRouting.generate(route, params, absolute);
    }
}