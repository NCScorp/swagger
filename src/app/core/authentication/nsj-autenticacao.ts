import { KeycloakService } from './services/keycloak.service';
import { ConfigService, configInstance } from '@core/configuracao/nsj-config';

export class AuthService {

    public nsjAuth: Keycloak.KeycloakInstance = KeycloakService.keycloakAuth;
    private static inst: AuthService;

    private constructor() { }

    public static getNsjAuth(): AuthService {
        if (!AuthService.inst) {
            AuthService.inst = new AuthService();
        }
        return AuthService.inst;
    }

    public async iniciarAuth() {

        let configAuth: Keycloak.KeycloakConfig = configInstance.config.auth;
        let configInitAuth: Keycloak.KeycloakInitOptions = { onLoad: 'login-required', checkLoginIframe: false };

        await KeycloakService.init(configAuth, configInitAuth).then(() => {
            this.nsjAuth = KeycloakService.keycloakAuth;
        });
    }
}

export const NsjAuthInstance: AuthService = AuthService.getNsjAuth();
