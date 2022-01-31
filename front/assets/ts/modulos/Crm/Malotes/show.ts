import angular = require('angular');

export class CrmMalotesFormShowController {

    static $inject = [
        'CrmMalotesFormAprovarService',
        'CrmMalotesFormEnviarService',
        'CrmMalotesConfirmMalotecancelaenvioService',
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'CrmMalotes',
        'toaster',
        'entity',
        'CrmMalotesFormEditarenvioService'
    ];

    public action: string = 'retrieve';
    public busy: boolean = false;
    public constructors: any;

    constructor(public CrmMalotesFormAprovarService: any,
        public CrmMalotesFormEnviarService: any,
        public CrmMalotesConfirmMalotecancelaenvioService: any,
        public utilService: any, 
        public $scope: any, 
        public $stateParams: any, 
        public $state: any, 
        public entityService: any, 
        public toaster: any, 
        public entity: any,
        public CrmMalotesFormEditarenvioService: any
    ){
        this.constructors = entityService.constructors;
    }
    
    maloteAprova(entity: any) {
        let modal = this.CrmMalotesFormAprovarService.open({ 'malote': entity.malote });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }
    maloteEnviar(entity: any) {
        let modal = this.CrmMalotesFormEnviarService.open({ 'malote': entity.malote });
        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }
    
    /**
     * 
     * @param entity
     * Sobrescrito para corrigir mensagem do Toaster de cancelamento do envio do malote
     */
    malotecancelaenvio(entity: any) {
        let confirm = this.CrmMalotesConfirmMalotecancelaenvioService.open(entity);
        confirm.result.then((entity: any) => {
            this.busy = true;
            this.entityService.malotecancelaenvio(entity)
                .then((response: any) => {
                    this.toaster.pop({
                        type: 'success',
                        title: 'O envio do Malote foi cancelado com sucesso!'
                    });
                    this.$state.reload();
                }).catch((response: any) => {
                    if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                        this.toaster.pop(
                            {
                                type: 'error',
                                title: response.data.message
                            });
                    } else {
                        this.toaster.pop({
                            type: 'error',
                            title: 'Ocorreu um erro ao cancelar o envio do Malote!'
                        });
                    }
                }).finally((response: any) => {
                    this.busy = false;
                });
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    }

    /**
     * 
     * @param entity 
     * Sobrescrito para passar a entidade corretamente na modal de edição de dados do envio do malote
     */
    maloteEditarEnvio(entity: any) {
        let dadosMalote = entity;
        let modal = null;

        //Fazendo o controle de quais dados serão passados para a abertura da modal. Cada tipo de envio tem propriedades diferentes, por isso esse controle
        dadosMalote['enviomodal'] = entity.enviomodal;
        dadosMalote['enviodata'] = entity.enviomodal == 1 || entity.enviomodal == 2 ? entity.enviodata : null;
        dadosMalote['enviocodigorastreio'] = entity.enviomodal == 1 ? entity.enviocodigorastreio : null;
        dadosMalote['enviorecebimentodata'] = entity.enviomodal == 0 ? entity.enviorecebimentodata : null;
        dadosMalote['enviorecebidopornome'] = entity.enviomodal == 0 ? entity.enviorecebidopornome : null;
        dadosMalote['enviorecebidoporcargo'] = entity.enviomodal == 0 ? entity.enviorecebidoporcargo : null;
        
        //Condicional para fazer o controle de quais campos serão passados para a abertura da modal e exibir somente os campos pertinentes ao modo de envio
        //Em mãos
        if (entity.enviomodal == 0){
            modal = this.CrmMalotesFormEditarenvioService.open({ malote : dadosMalote.malote, enviomodal : dadosMalote.enviomodal, enviorecebimentodata : dadosMalote.enviorecebimentodata,
                                                                     enviorecebidopornome : dadosMalote.enviorecebidopornome, enviorecebidoporcargo : dadosMalote.enviorecebidoporcargo});    
        }
        //Correio
        else if (entity.enviomodal == 1){
            modal = this.CrmMalotesFormEditarenvioService.open({ malote : dadosMalote.malote, enviomodal : dadosMalote.enviomodal, enviocodigorastreio : dadosMalote.enviocodigorastreio,
                                                                     enviodata : dadosMalote.enviodata});
        }
        //Se não for em mãos e nem correio, só pode ser Email
        else{
            modal = this.CrmMalotesFormEditarenvioService.open({ malote : dadosMalote.malote, enviomodal : dadosMalote.enviomodal, enviodata : dadosMalote.enviodata});
        }

        modal.result.then(() => {
            this.$state.reload();
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });

    }
    
}
