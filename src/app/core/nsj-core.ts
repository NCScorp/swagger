import { NsjInicializacao } from './inicializacao/nsj-inicializacao';
import { NsjRoutingInstance } from './routing/nsj-rotas';

export { NsjUsuarioInstance as NsjUsuario } from './usuario/nsj-usuario';
export { configInstance as NsjConfig } from './configuracao/nsj-config';
export { NsjAuthInstance as NsjAuth } from './authentication/nsj-autenticacao';
export { HttpInterceptorService as NsjHttpInterceptorService } from './interceptors/http.interceptors.service';
export { httpClient as NsjHttpClient } from './services/http-client';

export const NsjRouting = NsjRoutingInstance.Routing;
export const NsjRoutes = NsjRoutingInstance.routes;

export class NsjCore {
    static async bootstrap(config: any, bootstrap: Function, usaGrupoEmpresarial: boolean = true) {
        const bootstrapApp = new NsjInicializacao(config, bootstrap, usaGrupoEmpresarial);
        await bootstrapApp.run();
    }
}

