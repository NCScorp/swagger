export interface ISecaoController {
    /**
     * Identificador de seção
     */
    id: string;
    /**
     * Deve ser sobrescrito em cada tela para realizar o carregamento da seção
     */
    ativarAtualizacao: () => void;
    /**
     * Deve ser sobrescrito em cada tela para parar a rotina de atualização, caso exista
     */
    pararAtualizacao: () => void;
}