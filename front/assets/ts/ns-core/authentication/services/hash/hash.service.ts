import { ITipoAuthService } from "@nsj/core/authentication/tipo-auth-service.interface";
import { Http2ServerRequest } from "http2";
import jwt_decode from "jwt-decode";
import { Usuario, Organizacao, Grupoempresarial } from "@nsj/core/usuario/models/usuario-model";
import { IDadosHash } from "./dados-hash.interface";

export class HashService implements ITipoAuthService {
    static hash: string = '';
    static dados: IDadosHash;
    public buscarUsuarioDoProfile: boolean = false;

    init(): Promise<void>{
        return new Promise((resolve, reject) => {
            // Decodifico informaçõs do hash
            try {
                // Decodifico token em dados
                HashService.dados = jwt_decode(HashService.hash);

                // Busco código do tenant e do grupo empresarial
                const path = window.location.pathname;
                const pathDados = path.split('/');

                if (pathDados.length > 0) {
                    HashService.dados.tenant.codigo = pathDados[1];
                }

                if (pathDados.length > 1) {
                    HashService.dados.grupoempresarial.codigo = pathDados[2];
                }

                // Se não houver ao menos código do tenant preenchido, saio
                if (pathDados.length < 2) {
                    reject();
                } else {
                    // Seto hash no local storage.
                    localStorage.setItem('hash', HashService.hash);

                    resolve();
                }
            } catch (error) {
                reject(error);
            }
        })
    }

    logout() {
        let hash = HashService.hash;
        // Limpar hash
        HashService.hash = null;
        // Limpar hash do local storage
        localStorage.removeItem('hash');
        // Atualizar página, sem hash de autenticação
        window.location.href = window.location.origin;
    }

    onInterceptorRequest(config: Http2ServerRequest): void{
        config.headers = this.addAuthHeader(config.headers);
    }

    addAuthHeader(header?: any): any {
        if (!header) {
            header = {};
        }

        //Seto hash no header da requisição
        header['hash'] = HashService.hash;
        return header;
    }

    getDadosUsuario(): Usuario {
        // Monto dados iniciais do usuário
        const usuario: Usuario = {
            links: [],
            conta_url: '',
            logout_url: window.location.origin,
            username: HashService.dados.nome,
            email: HashService.dados.email,
            nome: HashService.dados.nome,
            organizacoes: [],
            roles: []
        }

        // Monto dados da organização(tenant)
        let organizacao: Organizacao = {
            nome: HashService.dados.tenant.codigo,
            codigo: HashService.dados.tenant.codigo,
            id: HashService.dados.tenant.tenant,
            logo: null,
            funcao: null,
            url: '',
            estabelecimento: [],
            gruposempresariais: [],
            configuracoes: [],
            permissoes: HashService.dados.permissoes

        };

        // Monto dados do grupo
        let grupo: Grupoempresarial = {
            grupoempresarial: HashService.dados.grupoempresarial.grupoempresarial,
            codigo: HashService.dados.grupoempresarial.codigo,
            descricao: HashService.dados.grupoempresarial.codigo,
            empresas: []
        };


        // Adiciono grupo a organização
        organizacao.gruposempresariais.push(grupo);

        // Adiciono organização aos dados do usuário
        usuario.organizacoes.push(organizacao);

        // Retorno dados
        return usuario;
    }

    createLogoutUrl(params?: any): string {
        return '';
    }
}

