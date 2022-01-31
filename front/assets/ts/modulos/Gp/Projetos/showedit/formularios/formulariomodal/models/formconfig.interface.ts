export interface IFormEntity {
  visualizacao: boolean;
  arrSecoes: IFormSecao[];
}

export interface IFormSecao {
  titulo: string;
  arrItens: IFormItem[];
}

export interface IFormItem {
  nome: string;
  titulo: string;
  formularioresposta: IFormResposta;
  formularioitemopcao: IFormItemConfigOpcao[];
  tiporesposta: EFormItemTipo;
  config?: TFormItemConfig;
}

export interface IFormResposta {
  resposta: string;
  formulariomoduloresposta: string;
}

export enum EFormItemTipo {
  fitTextoCurto = 0,
  fitTextoLongo = 1,
  fitData = 2,
  fitOpcoes = 3
}

export interface IFormItemConfigTextoCurto {
  placeholder: string;
}

export interface IFormItemConfigTextoLongo {
  placeholder: string;
}


export interface IFormItemConfigOpcao {
  descricao: string;
  formularioitemopcao: string;
}

export type TFormItemConfig = IFormItemConfigOpcao[]
  | IFormItemConfigTextoCurto
  | IFormItemConfigTextoLongo;  