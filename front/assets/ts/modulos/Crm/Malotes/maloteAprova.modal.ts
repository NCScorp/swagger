import angular = require('angular');

export class CrmMalotesFormAprovarController {
    static $inject = ['CrmMalotes', 'toaster', 'utilService', '$uibModalInstance', 'entity'];

    public action: string = 'maloteAprova';
    public busy: boolean = false;
    public form: any;

    constructor(public entityService: any, public toaster: any, public utilService: any,  public $uibModalInstance: any, public entity: any) {
    }

    /**
     * Sobrescrito para corrigir a mensagem do Toaster de salvar a resposta do requisitante
     */
    
    submit() {
        if (this.form.$valid) {
            this.busy = true;
            this.entityService.maloteAprova(this.entity)
                .then((response: any) => {
                    this.toaster.pop({
                        type: 'success',
                        title: 'A Resposta do Requisitante foi salva com sucesso!'
                    });
                    this.$uibModalInstance.close();
                }).catch((response: any) => {
                    if (response.status === 409) {
                        if (confirm(response.data.message)) {
                            this.entity[''] = response.data.entity[''];
                            this.entityService.save(this.entity);
                        }
                    } else {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let distinct = (value: any, index: any, self: any) => {
                                    return self.indexOf(value) === index;
                                };
                                response.data.errors.errors = response.data.errors.errors.filter(distinct);
                                let message = this.utilService.parseValidationMessage(response.data);
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: 'Erro de Validação',
                                        body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                                        bodyOutputType: 'trustedHtml'
                                    });
                            } else {
                                this.toaster.pop(
                                    {
                                        type: 'error',
                                        title: response.data.message
                                    });
                            }
                        } else {
                            this.toaster.pop(
                                {
                                    type: 'error',
                                    title: 'Ocorreu um erro ao salvar a Resposta do Requisitante!'
                                });
                        }
                    }

                }).finally((response: any) => {
                    this.busy = false;
                });

        }
    }

        close() {
            this.$uibModalInstance.dismiss('fechar');
        }
    }

export class CrmMalotesFormAprovarService {
    static $inject = ['$uibModal'];

    constructor(public $uibModal: any) {
    }

    open(ctrlEntity: any) {
            return this.$uibModal.open({
                template: require('./maloteAprova.modal.html'),
                controller: 'CrmMalotesFormAprovarController',
                controllerAs: 'crm_mlts_frm_prvr_cntrllr',
                windowClass: '',
                resolve: {
                    entity: () => {
                        return ctrlEntity;
                    }
                }
            });
    }

}
