export abstract class TFormView<I>{
    constructor(dados: I){
        this.carregarDadosFromInterface(dados)
    }

    /**
     * Converte os dados da classe para uma interface de API
     */
    public toInterface(): I {
        return <any>Object.assign({}, this);
    }

    /**
     * Converte os dados da classe para uma interface de API de criar
     */
    public toCriarInterface(): I {
        return this.toInterface();
    }

    /**
     * Converte os dados da classe para uma interface de API de atualizar
     */
    public toAtualizarInterface(): I {
        return this.toInterface();
    }

    /**
     * Carrega dados da entidade a partir da interface
     * @param dados 
     */
    protected carregarDadosFromInterface(dados: I): void {
        Object.assign(this, dados);
    }
}
