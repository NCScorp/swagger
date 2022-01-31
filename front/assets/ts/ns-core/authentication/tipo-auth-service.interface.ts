import { Http2ServerRequest } from "http2";
import { Usuario } from "@nsj/core/usuario/models/usuario-model";

export interface ITipoAuthService {
    /**
     * Inicializa a autenticação
     */
    init(): Promise<void>;
    /**
     * Realiza logout da aplicação
     */
    logout(): void;
    /**
     * Evento chamado quando a requisição passar pelo Interceptor
     */
    onInterceptorRequest(config: Http2ServerRequest): void;
    /**
     * Define se a autenticação deve buscar dados do usuário da api de Profile ou montar dados
     */
    buscarUsuarioDoProfile: boolean;
    /**
     * Monta e retorna estrutura do usuário
     */
    getDadosUsuario?(): Usuario;
    /**
     * Cria url de logout
     */
    createLogoutUrl(params?: any): string;
    /**
     * Adiciona dados de autenticação no header
     * @param header 
     */
    addAuthHeader(header?: any): any;
}