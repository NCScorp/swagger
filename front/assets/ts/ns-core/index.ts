import { NsjInicializacao } from './inicializacao/nsj-inicializacao';
import { NsjRoutingInstance } from './routing/nsj-rotas';
import { NsjRoutesService } from './routing/nsj-routes';
import { RouterFactoryType } from './routing/routes';

export { NsjUsuarioInstance as NsjUsuario } from './usuario/nsj-usuario';
export { configInstance as NsjConfig } from './configuracao/nsj-config';
export { NsjAuthInstance as NsjAuth } from './authentication/nsj-autenticacao';
export { HttpInterceptorService as NsjHttpInterceptorService } from './interceptors/http.interceptors.service';
export { httpClient as NsjHttpClient } from './services/http-client';

export const NsjRouting = NsjRoutingInstance.Routing;
export const NsjRoutes = NsjRoutesService;

export class NsjCore {
    static async bootstrap(options: IBootstrapOptions) {
        const optionsBootstrap = Object.assign({ usaGrupoEmpresarial: true, routesFactory: null }, options) as IBootstrapOptions
        const bootstrapApp = new NsjInicializacao(optionsBootstrap);
        await bootstrapApp.run();
    }
}

interface IAuth {
    url: string;
    realm: string;
    clientId: string;
}

interface IConfigOptions {
    auth: IAuth;
    routing: Record<string, string>
}

export interface IBootstrapOptions {
    config: IConfigOptions,
    bootstrapFn: Function,
    routesFactory?: RouterFactoryType,
    usaGrupoEmpresarial?: boolean
}
