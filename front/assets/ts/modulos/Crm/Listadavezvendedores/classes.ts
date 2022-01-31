export interface IListadavezVendedor {
    listadavezvendedor?: string;
    nome?: string;
    itens?: IListadavezvendedoritem[];
}

export interface IListadavezvendedoritem {
    listadavezvendedoritem?: string;
    listadavezvendedor?: IListadavezVendedor;
    vendedor?: IVendedor;
    posicao?: number;

    // Utilizado na tela de criação/edição da lista
    checked?: boolean;
}

export interface IVendedor {
    vendedor_id?: string;
    vendedor_nome?: string;

    // Utilizado na tela de criação/edição da lista
    checked?: boolean;
}