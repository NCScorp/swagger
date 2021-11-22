import { NsjUsuarioInstance } from '../core/usuario/nsj-usuario';
import { Usuario as profile  } from '../core/usuario/models/usuario-model'


export class Usuario {
  
  private profile: profile = NsjUsuarioInstance.Usuario;

  constructor() { }
  
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
}

