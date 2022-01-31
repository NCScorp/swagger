import { ETipoItemFormulario } from "./formulariomodal/enum/tipoItemFormulario.enum";

export interface IFormulario {
  formulario: string;
  formulariomodulo: string;
  modulo: string;
  formularionome: string;
  tipomodulo: tipoModuloEnum;
  countQuestoes: number;
  countRespostas: number;
  formularioitem: IFormularioItem[];
}

export interface IFormularioRespostas { 
  formulariomoduloresposta: string;
  formulariomodulo: string;
  resposta: string;
  formularioitem: IFormularioItem
}

export interface IFormularioItem {
  obrigatorio: string, 
  titulo: string;
  nome: string;
  formulario: string;
  formularioitem: string;
  formularioitempai: string;
  formularioresposta: IFormularioRespostas;
  tipo: ETipoItemFormulario
  perguntas?: IFormularioItem[]
}

export interface IFormularioModulo {
  formulario: IFormulario;
  finalizado_at: string;
  formulariomodulo: string;
  modulo: string;
}

enum tipoModuloEnum {
  PROJETO = 0,
  ATCS = 1,
  OS = 2
}
