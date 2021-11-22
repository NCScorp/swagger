import {Router} from 'symfony-ts-router';
import {deepCopy, deepMerge} from '@utils/utils';
import {configInstance} from '@core/configuracao/nsj-config';
import {Catch} from "@core/decorators/catch";
import {httpClient} from '@core/services/http-client';
import {ServerError} from "@core/services/server-error";

class RotasService {

    public Routing: Router = new Router();
    private static inst: RotasService;

    private constructor() {
    }

    public static getRotas(): RotasService {
        if (!RotasService.inst) {
            RotasService.inst = new RotasService();
        }
        return RotasService.inst;
    }

    @Catch(TypeError, (error, ctx) => {
        console.error({error, ctx});
        ServerError.showErrorPage('Ocorreu um erro ao iniciar');
    })
    public async carregarRotas() {
        const url = configInstance.config.routing.url;
        const response = await httpClient.get<any>(url, false);
        this.Routing.setRoutingData(response.parsedBody);
    }

    public routes = () => {
        const config = {
            baseParams: {}
        };

        return {
            config: config,
            $get: () => {
                return {
                    generate: (route: string, opt_params: object, absolute: boolean) => {
                        const globals = configInstance.config.globals;

                        if (globals && globals.tenantCodigo) {
                            config.baseParams['tenant'] = globals.tenantCodigo;
                        }

                        if (globals && globals.grupoEmpresarialCodigo) {
                            config.baseParams['grupoempresarial'] = globals.grupoEmpresarialCodigo;
                        }

                        const params = deepMerge(deepCopy(config.baseParams), (opt_params || {}));

                        return this.Routing.generate(route, params, absolute);
                    }
                };
            }
        };
    }
}

export const NsjRoutingInstance: RotasService = RotasService.getRotas();
