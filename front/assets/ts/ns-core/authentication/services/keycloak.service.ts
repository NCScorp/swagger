import Keycloak from 'keycloak-js'
import { ITipoAuthService } from '@nsj/core/authentication/tipo-auth-service.interface';
import { configInstance } from '@nsj/core/configuracao/nsj-config';
import { Http2ServerRequest } from 'http2';

export class KeycloakService implements ITipoAuthService {
    static keycloakAuth: Keycloak.KeycloakInstance;
    
    initOptions: Keycloak.KeycloakInitOptions = { onLoad: 'login-required', checkLoginIframe: false };
    public buscarUsuarioDoProfile: boolean = true;

    constructor(){}

    init(): Promise<void>{
        return new Promise((resolve, reject) => {
            const configOptions = configInstance.getConfig('auth');
            KeycloakService.keycloakAuth = Keycloak(configOptions);

            // @ts-ignore
            KeycloakService.keycloakAuth.init(this.initOptions)
                .success(() => {
                    resolve();
                })
                .error((errorData: any) => {
                    reject(errorData);
                });
        });
    }

    logout() {
        (<any>window).location = KeycloakService.keycloakAuth.createLogoutUrl();
    }

    onInterceptorRequest(config: Http2ServerRequest): void{
        config.headers = this.addAuthHeader(config.headers);
    }

    createLogoutUrl(params?: any): string {
        return KeycloakService.keycloakAuth.createLogoutUrl(params);
    }

    addAuthHeader(header?: any): any {
        if (!header) {
            header = {};
        }
        
        //Seto token no header de Authorization
        header['Authorization'] = this.refreshToken();

        return header;
    }

    authenticated(): boolean {
        // @ts-ignore
        return KeycloakService.keycloakAuth.authenticated;
    }

    login() {
        KeycloakService.keycloakAuth.login();
    }

    account() {
        KeycloakService.keycloakAuth.accountManagement();
    }
    
    authServerUrl(): string {
        // @ts-ignore
        return KeycloakService.keycloakAuth.authServerUrl;
    }
    
    realm(): string {
        // @ts-ignore
        return KeycloakService.keycloakAuth.realm;
    }

    refreshToken(): string {
      KeycloakService.keycloakAuth.updateToken(300);

      return KeycloakService.keycloakAuth.token;
    }

    getToken(): Promise<string> {
        return new Promise<string>((resolve, reject) => {
            if (KeycloakService.keycloakAuth.token) {
                KeycloakService.keycloakAuth
                    .updateToken(300)
                    .success(() => {
                        resolve(<string>KeycloakService.keycloakAuth.token);
                    })
                    .error((err) => {
                        reject('Falha ao carregar token');
                    });
            } else {
                reject('Não possui token');
            }
        });
    }
}

