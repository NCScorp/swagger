import { IEscopo } from './escopo.interface';

export interface IEscopoNode extends IEscopo {
  nodeID: string;
  creatingNode: boolean;
  addingMode: boolean;
  editingMode: boolean;
  editedField: string;
  editedFields: string[];
  datahorainicioFormatada?: string;
  datahorafimFormatada?: string;
  datahorainicioMax?: string;
  datahorainicioMin?: string;
  datahorafimMax?: string;
  datahorafimMin?: string;
  __editingFields: string[];
  __saving: boolean;
  __children__: IEscopoNode[];
};

