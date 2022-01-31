/**
 * Classe de entidades retornadas da api 
 */
export class Entity<IAPI>{
    constructor(
        protected dados: IAPI
    ){}

    /**
     * Retorna os dados da api
     */
    getDados(): IAPI {
        return this.dados;
    }

    /**
     * Retorna o valor de uma propriedade
     * @param nome 
     */
    get(nome: string): any {
        return this.dados[nome];
    }

    /**
     * Seta o valor de um determinado campo
     * @param nome 
     * @param valor 
     */
    set(nome: string, valor: any): any {
        this.dados[nome] = valor;
    }

    /**
     * Converte um texto para um number, com determinado numero de casas decimais.
     * @param valor 
     * @param casas 
     */
    protected textToNumber(valor: string, casas = 2): number{
        try {
            const valorFloat = parseFloat(valor);
            // Se não for um número, retorno 0
            if (isNaN(valorFloat)) {
                return 0;
            }
            const valorTextoCasa = valorFloat.toFixed(casas);
            return parseFloat(valorTextoCasa);
        } catch (error) {
            return 0;
        }
    }

    /**
     * Converte uma propriedade texto para um number, com determinado numero de casas decimais.
     * @param nome 
     * @param casas 
     */
    protected getPropertyToNumber(nome: string, casas = 2): number {
        return this.textToNumber(this.dados[nome], casas);
    }

    /**
     * Converte um valor para texto, com determinado numero de casas decimais.
     * @param valor 
     * @param casas 
     */
    protected numberToText(valor: number, casas = 2): string{
        const valorTextoCasa = valor.toFixed(casas);
        return valorTextoCasa;
    }

    /**
     * Converte uma propriedade number para texto, com determinado numero de casas decimais.
     * @param nome 
     * @param casas 
     */
    protected setPropertyNumberToText(nome: string, valor: number, casas = 2) {
        this.dados[nome] = this.numberToText(valor, casas);
    }
}