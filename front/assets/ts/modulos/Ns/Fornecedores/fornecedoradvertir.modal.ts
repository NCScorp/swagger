import angular = require('angular');

export class NsFornecedoresFormFornecedoradvertirController {
    static $inject = ['NsFornecedores', 'toaster', 'utilService',  '$uibModalInstance', 'entity'];

    public action: string = 'fornecedoradvertir';
    public busy: boolean = false;
    public form: any;

    constructor(public entityService: any, public toaster: any, public utilService: any,  public $uibModalInstance: any, public entity: any) {
    }

    submit() {
        if (this.form.$valid && !this.entity.$$__submitting) {
            this.busy = true;
            this.entityService.fornecedoradvertir(this.entity)
                .then((response: any) => {
                    this.toaster.pop({
                        type: 'success',
                        title: 'A Prestadora de Serviços foi advertida com sucesso!'
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
                                    title: 'Ocorreu um erro ao advertir a Prestadora de Serviços!'
                                });
                        }
                    }

                }).finally((response: any) => {
                    this.busy = false;
                });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }
    }

export class NsFornecedoresFormFornecedoradvertirService {
    static $inject = ['$uibModal'];

    constructor(public $uibModal: any) {
    }

    open(ctrlEntity: any) {
            return this.$uibModal.open({
                template: require('./fornecedoradvertir.modal.html'),
                controller: 'NsFornecedoresFormFornecedoradvertirController',
                controllerAs: 'ns_frncdrs_frm_frncdrdvrtr_cntrllr',
                windowClass: '',
                resolve: {
                    entity: () => {
                        return ctrlEntity;
                    }
                }
            });
    }

}

