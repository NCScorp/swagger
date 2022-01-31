import { Usuarios } from "../usuarios/usuarios.service";

export class ConfiguracaoService {

  static $inject = ['Usuarios'];

  constructor(protected usuarios: Usuarios) {   }

  public getConfiguracao(chave: string, grupoempresarial = null): string | null {

    const tenant = (<any>window).nsj.globals.a.tenant;

    const organizacao = this.usuarios.getOrganizacao(tenant);

    const retorno = organizacao.configuracoes.find((config) => {
        let retorno: boolean = config.chave == chave;

        retorno = retorno && (grupoempresarial == null || config.id_grupoempresarial == grupoempresarial);

        return retorno;
    });

    return retorno ? retorno.valor : null;

  }

}
