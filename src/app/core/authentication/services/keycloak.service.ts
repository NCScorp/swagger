import Keycloak from 'keycloak-js'

export class KeycloakService {

    static keycloakAuth: Keycloak.KeycloakInstance;

    /**
     * Configura inicialização do Keycloak.
     *
     * @param configOptions Dados do Json de configuração
     * 
     * @param adapterOptions Configurações de inicialização
     * 
     * @returns {Promise<T>}
     */
    static init(configOptions?: Keycloak.KeycloakConfig, initOptions?: Keycloak.KeycloakInitOptions): Promise<any> {
        KeycloakService.keycloakAuth = Keycloak(configOptions);

        return new Promise((resolve, reject) => {
            KeycloakService.keycloakAuth.init(initOptions)
                .success(() => {
                    resolve();
                })
                .error((errorData: any) => {
                    reject(errorData);
                });
        });
    }

    authenticated(): boolean {
        return KeycloakService.keycloakAuth.authenticated;
    }

    login() {
        KeycloakService.keycloakAuth.login();
    }

    logout() {
        KeycloakService.keycloakAuth.logout();
    }

    account() {
        KeycloakService.keycloakAuth.accountManagement();
    }
    
    authServerUrl(): string {
        return KeycloakService.keycloakAuth.authServerUrl;
    }
    
    realm(): string {
        return KeycloakService.keycloakAuth.realm;
    }

    getToken(): Promise<string> {
        return new Promise<string>((resolve, reject) => {
            if (KeycloakService.keycloakAuth.token) {
                KeycloakService.keycloakAuth
                    .updateToken(5)
                    .success(() => {
                        resolve(<string>KeycloakService.keycloakAuth.token);
                    })
                    .error(() => {
                        reject('Falha ao carregar token');
                    });
            } else {
                reject('Não possui token');
            }
        });
    }
}

