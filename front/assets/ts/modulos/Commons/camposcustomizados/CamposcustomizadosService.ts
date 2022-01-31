import angular = require('angular');
import { readRule, evalString } from '../utils';

export class CamposcustomizadosService {

  formatavisibilidade(campos = [], config: Config) {
    const { regra, dicionario, escopo } = config;

    campos.forEach((campo, index) => {
      if (typeof campo.visible === 'string') {
        campo.visible = campo.visible.replace("indexcampocustomizado",'0');
        const regraVisibilidade = readRule(campo.visible, regra, dicionario);
        campo.visible = evalString(escopo, regraVisibilidade);
      }

      if (campo.objeto && campo.objeto.length > 0 && campo.visible) {
        this.formatavisibilidade(campo.objeto, config);
      }
    });

    return campos;
  }

}

type Config = {
  regra: any,
  dicionario: object,
  escopo: any
};
