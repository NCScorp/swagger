import { IBootstrapOptions, NsjConfig, NsjUsuario } from '../index';
import { NsjAuthInstance } from '../authentication/nsj-autenticacao';
import { ErrorPageRoutes } from '../components/error-page.routes';
import { configInstance } from '../configuracao/nsj-config';
import { Catch } from "../decorators/catch";

import { NsjRoutingInstance } from '../routing/nsj-rotas';
import { ServerError } from "../services/server-error";

import { Grupoempresarial, Organizacao, Usuario } from '../usuario/models/usuario-model';
import { NsjUsuarioInstance } from '../usuario/nsj-usuario';
import { Routes } from '../routing/routes';

declare var nsj: any;

class InicializacaoService {

    constructor(private options: IBootstrapOptions) {

    }

    async run() {
        ErrorPageRoutes.initRouter();
        configInstance.iniciarConfig(this.options.config);
        NsjAuthInstance.iniciarAuth()
            .then(() => NsjRoutingInstance.carregarRotas(new Routes(this.options.routesFactory)))
            .then(() => NsjUsuarioInstance.carregarUsuario())
            .then(() => this.bootstrapApp())
    }

    @Catch('object', (error, ctx) => {
        console.error({ error, ctx });
        ServerError.showErrorPage(error.message);
    })

    async bootstrapApp() {

        const usuario: Usuario = NsjUsuarioInstance.Usuario;
        let tenantCodigo: string = '';
        let grupoEmpresarialCodigo: string = '';

        let tenant: Organizacao
        let grupoempresarial: Grupoempresarial;

        if (!usuario) {
            throw new Error('Erro ao carregar usuário.')
        }

        //Busco se usuário possui algum tenant
        // @ts-ignore
        tenant = usuario.organizacoes.find((tenant) => {
            return tenant;
        });

        //Caso o usuário não possua tenant, retorno erro.
        if (!tenant) {
            throw new Error('Usuário não possui tenant');
        }

        //Caso utilize Grupo Empresarial na URL base, busco o primeiro grupo do tenant selecionado 
        if (this.options.usaGrupoEmpresarial) {
            // @ts-ignore
            grupoempresarial = tenant.gruposempresariais.find((grupoempresarial) => {
                return grupoempresarial;
            });

            //Caso o usuário não possua Grupo Empresarial, retorno erro.
            if (!grupoempresarial) {
                throw new Error('Usuário não possui Grupo Empresarial');
            }

            tenantCodigo = tenant.codigo;
            grupoEmpresarialCodigo = grupoempresarial.codigo;
        }

        //   Busco dados de tenant e grupo empresarial presentes na url
        const indexUrl = window.location.href.indexOf('://') + 3;
        const indexUrlParams = window.location.href.indexOf('?');
        let urlSemProtocolo = (indexUrlParams > -1) ? window.location.href.substring(indexUrl, indexUrlParams) : window.location.href.substring(indexUrl);

        //Se o último caractere for uma barra, eu removo
        if (urlSemProtocolo[urlSemProtocolo.length - 1] == '/') {
            urlSemProtocolo = urlSemProtocolo.substr(0, urlSemProtocolo.length - 1);
        }

        const dadosUrl = urlSemProtocolo.split('/');

        //Verifico se existe tenant e grupo empresarial na url, considerando que dadosUrl[0] é o host.
        let tenantUrl = (dadosUrl.length > 1) ? dadosUrl[1] : null;
        let grupoEmpresarialUrl = (dadosUrl.length > 2) ? dadosUrl[2] : null;

        //Caso tenant/grupo empresarial não existam no corpo da url e existam parametros de url, busco nos parametros
        if ((tenantUrl == null && grupoEmpresarialUrl == null) && indexUrlParams > -1) {
            const params = window.location.href.substring(indexUrlParams + 1).split('&');
            const arrParams = params.map((paramText) => {
                const arrChaveValor = paramText.split('=');

                return {
                    nome: arrChaveValor[0],
                    valor: arrChaveValor.length > 1 ? arrChaveValor[1] : ''
                };
            });

            const paramTenant = arrParams.find((param) => {
                return (param.nome == 'tenant');
            });

            if (paramTenant) {
                tenantUrl = paramTenant.valor;

                const paramGrupoEmpresarial = arrParams.find((param) => {
                    return (param.nome == 'grupoempresarial');
                });

                if (paramGrupoEmpresarial) {
                    grupoEmpresarialUrl = paramGrupoEmpresarial.valor;
                }
            }
        }

        //Caso encontro tenant e grupo empresarial da url no perfil do usuário, seto esses valores para colocar na base da url
        const tenantCompleto = usuario.organizacoes.find((tenant) => {
            return tenant.codigo == tenantUrl
        });

        if (tenantCompleto) {
            const grupoEmpresarialCompleto = tenantCompleto.gruposempresariais.find((grupo) => {
                return grupo.codigo == grupoEmpresarialUrl;
            });

            if (grupoEmpresarialCompleto) {
                tenantCodigo = tenantCompleto.codigo;
                grupoEmpresarialCodigo = grupoEmpresarialCompleto.codigo;
            }
        }

        //Busco a tag Base
        let baseTag = document.getElementsByTagName('base')[0];

        //Seto a base da url do sistema
        baseTag.href = baseTag.href + (this.options.usaGrupoEmpresarial ? tenantCodigo + '/' + grupoEmpresarialCodigo + '/' : tenantCodigo + '/');

        //Adiciono Url de redirecionamento no logout do sistema. Quando deslogar, vai pra página inicial
        usuario.logout_url = NsjAuthInstance.authService.createLogoutUrl({
            redirectUri: baseTag.href
        });

        configInstance.setConfig('globals', { tenantCodigo, grupoEmpresarialCodigo });
        nsj.globals.getInstance().setGlobals({
            tenant: NsjConfig.getConfig('globals').tenantCodigo,
            grupoempresarial: NsjConfig.getConfig('globals').grupoEmpresarialCodigo,
            links: NsjUsuario.Usuario.links
        });

        this.options.bootstrapFn();
    }
}

export const NsjInicializacao = InicializacaoService;
