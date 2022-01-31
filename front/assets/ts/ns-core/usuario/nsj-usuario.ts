import {deepCopy} from '../utils/utils';
import {Catch} from "../decorators/catch";
import {NsjRoutingInstance} from '../routing/nsj-rotas';
import {httpClient} from '../services/http-client';
import {ServerError} from "../services/server-error";
import {Usuario} from './models/usuario-model';
import { NsjAuthInstance } from '../authentication/nsj-autenticacao';

class UsuarioService {
// @ts-ignore
    public Usuario: Usuario;
    private static inst: UsuarioService;

    private constructor() {
    }

    public static getUsuario(): UsuarioService {
        if (!UsuarioService.inst) {
            UsuarioService.inst = new UsuarioService();
        }
        return UsuarioService.inst;
    }

    @Catch(TypeError, (error, ctx) => {
        console.error({error, ctx});
        ServerError.showErrorPage('Ocorreu um erro ao carregar dados do usuário');
    })
    public async carregarUsuario() {
        if (NsjAuthInstance.authService.buscarUsuarioDoProfile) {
            const url = NsjRoutingInstance.Routing.generate('profile', {}, true);
    
            const response = await httpClient.get<Usuario>(url)
            // @ts-ignore
            this.Usuario = deepCopy(response.parsedBody);
        } else {
            this.Usuario = NsjAuthInstance.authService.getDadosUsuario();
        }
    }
}

export const NsjUsuarioInstance: UsuarioService = UsuarioService.getUsuario();

