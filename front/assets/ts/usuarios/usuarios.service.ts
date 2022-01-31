import { NsjUsuario } from "@nsj/core";

export class Usuarios {
  static $inject = ['$rootScope', '$window'];

  public entities: any = [];
  private profile = NsjUsuario.Usuario;

  constructor(
    protected $rootScope: angular.IRootScopeService,
    public $window: angular.IWindowService,
  ) {
  }

  /**
   * Retorna dados de profile do usuário
   */
  public getProfile() {
    return new Promise((resolve) => {
      resolve({
        data: this.profile
      });
    });
  }

  public getProfileNoPromise() {
    return this.profile;
  }

  //mock com chaves do profile
  obterChaves(): any[] {

    const index = this.profile.organizacoes.findIndex(x => x.codigo === this.$window.nsj.globals.a.tenant);
    return (this.profile.organizacoes[index] as any).permissoes || [];
  }

  temPermissao(chave: any[]) {
    return this.obterChaves().indexOf(chave) >= 0;
  }


  /**
   * Retorna organização do tenant 
   */
  public getOrganizacao(tenant: string) {
    return this.profile.organizacoes.find(x => x.codigo == tenant);
  }
}
