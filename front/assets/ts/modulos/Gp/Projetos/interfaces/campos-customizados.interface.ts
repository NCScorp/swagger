export interface ICamposCustomizados {
  campocustomizado?: string;
  descricao?: string;
  label: string;
  nome: string;
  secao: string;
  tipo?: string;
  tamanho: number;
  visible: string;
  objeto: Campo[];
}

interface Campo {
  descricao: string;
  label: string;
  nome: string;
  visible: boolean;
  tamanho: number;
  tipo?: string;
}
