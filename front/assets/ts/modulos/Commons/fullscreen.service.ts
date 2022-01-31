import angular = require('angular');

export class FullscreenService{
    static $inject = [
        '$rootScope',
        '$state',
        'toaster'
    ];

    /**
     * Define se o sistema está em modo fullscreen
     */
    private ativado: boolean = false;
    /**
     * Define se está ouvindo o evento de sair do fullscreen
     */
    private escutandoExitFS: boolean = false;

    constructor(
        public $rootScope: any,
        public $state: any,
        public toaster: any
    ) {
        // Fico escutando mudança de rotas para controle de fullscreen
        this.$rootScope.$on('$locationChangeSuccess', (event, current, previous) => {
            // Se está com o fullscreen ativado, verifico se rota atual suporta fullscreen.
            if (this.isAtivado()) {
                const rotaAtual = this.$state.current.name;
                const arrRotasComFullscreen = this.getRotasComFullscreen();

                // Caso a rota atual não suporte fullscreen, desativo funcionalidade
                if (arrRotasComFullscreen.indexOf(rotaAtual) == -1) {
                    this.ativaDesativaFullscreen(false);
                }
            }
        });
    }

    /**
     * Retorna lista de rotas que possuem funcionalidade de fullscreen
     */
    private getRotasComFullscreen(): string[] {
        const arrRotasFullscreen: string[] = [];

        arrRotasFullscreen.push(
            'crm_composicoes_show',
            'crm_composicoes_edit',
        );

        return arrRotasFullscreen;
    }

    /**
     * Controla ativação do fullscreen
     * @param ativado 
     */
    public ativaDesativaFullscreen(ativado: boolean = false){
        if (this.ativado != ativado) {
            // Defino que o fullscreen está ativado
            this.ativado = ativado;

            // Se está ativando
            if (this.ativado){
                // Se não está escutando evento de mudar o estado de fullscreen
                if (!this.escutandoExitFS) {
                    document.addEventListener('webkitfullscreenchange', () => this.onExitFullScreen(), false);
                    document.addEventListener('mozfullscreenchange', () => this.onExitFullScreen(), false);
                    document.addEventListener('fullscreenchange', () => this.onExitFullScreen(), false);
                    document.addEventListener('MSFullscreenChange', () => this.onExitFullScreen(), false);

                    this.escutandoExitFS = true;
                }

                // Ativo modo F11
                this.ativarDesativarF11(true);

                // Apresento mensagem de ativação
                this.apresentarToasterAtivacao();
            } else {
                // Desativo modo F11
                this.ativarDesativarF11(false);
            }


            this.$rootScope.$broadcast('fullscreen_modificado', ativado);
        }
    }

    /**
     * Apresenta um toaster indicando que o fullscreen foi ativado
     */
    public apresentarToasterAtivacao(){
        this.toaster.pop({
            type: 'info',
            title: 'Pressione "ESC" para desativar o modo tela cheia.'
        });
    }

    /**
     * Retorna se o fullscreen está ativado
     */
    public isAtivado(): boolean {
        return this.ativado;
    }

    /**
     * Função para ativar o modo do F11 dos navegadores
     */
    ativarDesativarF11(ativar: boolean) {
        if (ativar) {
            // Busco elemento do documento da tela
            const el = document.documentElement;

            if (el.requestFullscreen) {
                el.requestFullscreen();
            } else if (el.webkitRequestFullscreen) { /* Safari */
                el.webkitRequestFullscreen();
            } else if ((<any>el).msRequestFullscreen) { /* IE11 */
                (<any>el).msRequestFullscreen();
            }
        } else {
            // Se fullscreen estiver ativado, chamo requisições de desativar
            if (document.webkitIsFullScreen || (<any>document).mozFullScreen || (<any>document).msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                } else if ((<any>document).msExitFullscreen) { /* IE11 */
                    (<any>document).msExitFullscreen();
                }
            }
        }
    }

    /**
     * Chamado ao sair do evento de fullscreen
     */
    onExitFullScreen(){
        if (!document.webkitIsFullScreen && !(<any>document).mozFullScreen && !(<any>document).msFullscreenElement) {
            // Desativo modo fullscreen da tela
            this.ativaDesativaFullscreen(false);
        }
    }
}