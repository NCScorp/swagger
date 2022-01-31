export interface IConfigItemObjectListAction {
    /**
     * Texto a ser apresentado na ação
     */
    label?: string;
    /**
     * Classes do ícone a ser apresentado na ação
     */
    icon?: string;
    /**
     * Função a ser chamada quando ação for clicada
     */
    method?: any;
}