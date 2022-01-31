/**
 * Módulo contendo as classes utilizadas pelo componente NsjIndice
 */
export module NsjIndiceClasses {
    export class Config {
        constructor(
            /**
             * Links do indice
             */
            public arrItens: IndiceItem[] = [],
    
            /**
             * Icone de mais ações.
             */
            public iconeMaisAcoes: string = 'fas fa-plus'
        ){}
    }

    export class IndiceItem {
        public tamanho: number = 0;

        constructor(
            public id: string,
            public nome: string,
            public url: string
        ){}
    }
}