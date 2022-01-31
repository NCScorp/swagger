declare var nsj;

export class InicializacaoService {
    /**
     * Injeções de dependencias
     */
    static $inject = [
        'nsjRouting'
    ];

    private keycloakCarregado: boolean = false;
    private profileCarregado: boolean = false;

    constructor(
        public nsjRouting: any
    ) {}
  
    setDadosRotas(dados: {
        crmHost: string,
        crmScheme: string,
        erpapiHost: string,
        erpapiScheme: string
    }) {
        this.nsjRouting.config.dadosRota = dados;
    }

  /**
   * Retorna se os dados necessarios à aplicação foram carregados
   */
  private dadosCarregados(): boolean {
    return this.keycloakCarregado && this.profileCarregado;
  }

  /**
   * Promise que aguarda os dados necessários à aplicação 
   * serem carregados para ser resolvida
   */
  public aguardarCarregamentoInicial(): Promise<any>{
    return new Promise((resolve, reject) => {
      if (this.dadosCarregados()){
        resolve();
      }
      else{
        //Aguardo o keycloak estar iniciado para retorná-lo.
        const intervalCarregamento = setInterval(() => {
          if (this.dadosCarregados()){
            resolve();
            clearInterval(intervalCarregamento);
          }
        }, 1000);
      }
    });
  }

  /**
   * Seta se a autenticação keycloak já foi carregada
   * @param carregado
   */
  public setKeycloakCarregado(carregado: boolean){
    this.keycloakCarregado = carregado;
  }

  /**
   * Seta se o profile do usuário já foi carregado
   * @param carregado 
   */
  public setProfileCarregado(carregado: boolean){
    this.profileCarregado = carregado;
  }

  public setGlobals(globals){
    nsj.globals.getInstance().setGlobals(globals);
  }

  /**
   * Retorna o valor de uma variavel global, pela chave passada
   * @param key 
   */
  public getGlobalsKeyValue(key: string): string {
      return nsj.globals.getInstance().get(key);
  }
}
