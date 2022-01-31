import angular = require('angular');

export class NsAdvertenciasFormShowController {
    static $inject = [                                                                        'NsAdvertenciasFormArquivarService',
                                                                                'NsAdvertenciasFormExcluirService',
                                                        'utilService', '$scope', '$stateParams', '$state', 'NsAdvertencias', 'toaster'
    , 'entity', ];
    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(                                                                        public NsAdvertenciasFormArquivarService: any,
                                                                                public NsAdvertenciasFormExcluirService: any,
                                                        public utilService: any, public $scope: any, public $stateParams: any, public $state: any, public entityService: any, public toaster: any
    , public entity: any ) {
        this.constructors = entityService.constructors;
    }    arquivar(entity: any) {
                    let modal = this.NsAdvertenciasFormArquivarService.open({ 'advertencia': entity.advertencia });
            modal.result.then( () => {
                this.$state.reload();            })
            .catch( (error: any) => {
                if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                    this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                }
            });
            }
        excluir(entity: any) {
                    let modal = this.NsAdvertenciasFormExcluirService.open({ 'advertencia': entity.advertencia });
            modal.result.then( () => {
                this.$state.reload();            })
            .catch( (error: any) => {
                if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                    this.toaster.pop({
                            type: 'error',
                            title: error
                        });
                }
            });
            }
    }
