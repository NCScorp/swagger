import { NsjIndiceClasses } from "./nsjindice.classes";

export class NsjIndiceController {

    /**
     * Injeção de dependências
     */
    static $inject = [
        '$scope',
        '$element'
    ];

    /**
     * Representação do html do componente
     */
    private htmlComponent: any;
    /**
     * Define se já carregou os itens do menu horizontal
     */
    private menuHorizontalCarregado: boolean = false;

    //Funções de callback passadas para o componente
    public config: NsjIndiceClasses.Config;

    private arrMenuItemHorizontalMaisItens: string[] = [];

    constructor (
        public $scope: any,
        private $element: any
    ) {}

    $onInit() {
        //Elemento que representa o html do componente.
        this.htmlComponent = this.$element[0];
        
        // Seto timeout para rodar somente após o html ser renderizado
        setTimeout(() => {
            // Carrego os itens de menu horizontal
            this.carregarItensMenuHorizontal();
            // Defino os itens apresentados e os itens e os itens do mais itens
            this.definirApresentacaoMenuHorizontal();

            this.reloadScope();
        });

        // Evento disparado quando a tela redimensionar
        window.addEventListener('resize', () => {
            // Carrego os itens de menu horizontal, caso não tenham sido carregados
            if (!this.menuHorizontalCarregado) {
                this.carregarItensMenuHorizontal();
            }

            // Defino os itens apresentados e os itens e os itens do mais itens
            this.definirApresentacaoMenuHorizontal();

            this.reloadScope();
        })
    }

    private reloadScope() {
        this.$scope.$applyAsync();
    }

    /**
     * Define os itens do menu horizontal
     */
    private carregarItensMenuHorizontal(){
        // Busco menu
        const menuHorizontal = this.htmlComponent.querySelector('.menus .menu-horizontal');
            
        // Se o menu horizontal está visível
        if (menuHorizontal != null && menuHorizontal.offsetWidth > 0) {
            // Busco itens de menu para definir seu tamanho na tela
            const arrMenuItens = menuHorizontal.querySelectorAll('.menu-item');
            arrMenuItens.forEach((itemForeach) => {
                let menuItem = this.config.arrItens.find((menuItemFind) => {
                    return menuItemFind.id == itemForeach.id;
                })

                if (menuItem) {
                    menuItem.tamanho = itemForeach.offsetWidth
                }
            });

            if (arrMenuItens.length > 0) {
                this.menuHorizontalCarregado = true;
            }
        }
    }

    /**
     * Defino os itens apresentados e os itens e os itens do mais itens
     */
    private definirApresentacaoMenuHorizontal(){
        // Busco menu
        const menuHorizontal = this.htmlComponent.querySelector('.menus .menu-horizontal');
                
        // Inicializo itens 
        this.arrMenuItemHorizontalMaisItens = [];

        // Se o menu horizontal está visível
        if (menuHorizontal != null && menuHorizontal.offsetWidth > 0 && this.menuHorizontalCarregado) {
            // Inicio o espaço ocupado com 50px referente ao botão de mais itens.
            let espacoOcupado = 60;
            let espacoTotalJaOcupado: boolean = false;

            this.config.arrItens.forEach((menuItemForeach) => {
                const maisItens =  espacoTotalJaOcupado || (espacoOcupado + menuItemForeach.tamanho) > menuHorizontal.offsetWidth;

                if (maisItens) {
                    this.arrMenuItemHorizontalMaisItens.push(menuItemForeach.id);
                    espacoTotalJaOcupado = true;
                } else {
                    espacoOcupado += menuItemForeach.tamanho;
                }
            });
        }
    }

    /**
     * Verifica se o item do menu horizontal esta no 'Mais itens'
     * @param id 
     */
    private estaNoMenuHorizontalPrincipal(id: string): boolean {
        return this.arrMenuItemHorizontalMaisItens.indexOf(id) < 0;
    }
}