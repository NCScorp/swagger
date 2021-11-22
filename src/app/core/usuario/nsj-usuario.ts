import {deepCopy} from '@utils/utils';
import {Catch} from "@core/decorators/catch";
import {NsjRoutingInstance} from '@core/routing/nsj-rotas';
import {httpClient} from '@core/services/http-client';
import {ServerError} from "@core/services/server-error";
import {Usuario} from './models/usuario-model';

class UsuarioService {

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

        const url = NsjRoutingInstance.Routing.generate('profile', {}, true);

        const response = await httpClient.get<Usuario>(url)
        this.Usuario = deepCopy(response.parsedBody);
    }
}

export const NsjUsuarioInstance: UsuarioService = UsuarioService.getUsuario();

