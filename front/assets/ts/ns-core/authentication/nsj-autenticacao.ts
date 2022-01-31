import { KeycloakService } from './services/keycloak.service';
import { ConfigService, configInstance } from '../configuracao/nsj-config';
import { ITipoAuthService } from '@nsj/core/authentication/tipo-auth-service.interface';
import { HashService } from '@nsj/core/authentication/services/hash/hash.service';
import { AuthServiceTipo } from '@nsj/core/authentication/auth-service-tipo.enum';

export class AuthService {

    public nsjAuth: Keycloak.KeycloakInstance = KeycloakService.keycloakAuth;
    private static inst: AuthService;
    public tipoAuth: AuthServiceTipo = AuthServiceTipo.astKeycloak;

    /**
     * Servico de autenticação utilizado no sistema.
     */
    public authService: ITipoAuthService = null;

    private constructor() {
        this.definirTipoAuth()
    }

    public static getNsjAuth(): AuthService {
        if (!AuthService.inst) {
            AuthService.inst = new AuthService();
        }
        return AuthService.inst;
    }

    /**
     * Define o tipo de autenticação a ser utilizada na aplicação
     */
    private definirTipoAuth(){
        let formaAutenticacaoDefinida: boolean = false;

        // Busco parametros da URL para definir tipo de autenticação
        let locationSearch = window.location.search;

        if (locationSearch[0] == '?') {
            locationSearch = locationSearch.substr(1, locationSearch.length -1);

            const searchObj: any = {};

            // Crio objeto de chave:valor com itens do search da URL
            locationSearch.split('&').forEach((itemSearch) => {
                const itemSearchArray = itemSearch.split('=');
                if (itemSearchArray.length > 0) {
                    searchObj[itemSearchArray[0]] = itemSearchArray[1];
                }
            });

            // Se possuir hash, defino serviço de hash
            if (searchObj.hash != null) {
                HashService.hash = searchObj.hash;
                this.authService = new HashService();
                this.tipoAuth = AuthServiceTipo.astHash;
                formaAutenticacaoDefinida = true;
            }
        }

        // Caso nenhuma forma de autenticação tenha sido definida, verifico se existe um hash no local storage
        // Só descomentar quando estudar melhor o fluxo da autenticação com hash e local storage(login e logout)
        // if (!formaAutenticacaoDefinida) {
        //     let storageHash = window.localStorage.getItem('hash');

        //     // Se possuir hash, defino serviço de hash
        //     if (storageHash != null) {
        //         HashService.hash = storageHash;
        //         this.authService = new HashService();
        //         this.tipoAuth = AuthServiceTipo.astHash;
        //         formaAutenticacaoDefinida = true;
        //     }
        // }

        // Caso nenhuma forma de autenticação tenha sido definida, deixo a padrão que é o Keycloak
        if (!formaAutenticacaoDefinida) {
            this.authService = new KeycloakService();
        }
    }

    /**
     * Realiza inicialização da autenticação
     */
    public async iniciarAuth() {
        await this.authService.init()
            .then(() => {})
            .catch((err) => {
                this.authService.logout();
            });
    }
}

export const NsjAuthInstance: AuthService = AuthService.getNsjAuth();