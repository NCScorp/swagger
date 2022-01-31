import angular = require('angular');

export class NsAdvertenciasFormExcluirController {
    static $inject = ['NsAdvertencias', 'toaster', 'utilService',  '$uibModalInstance', 'entity'];

    public action: string = 'excluir';
    public busy: boolean = false;
    public form: any;

    constructor(public entityService: any, public toaster: any, public utilService: any,  public $uibModalInstance: any, public entity: any) {
    }

    submit() {
        if (this.form.$valid) {
                          this.busy = true;
               this.entityService.excluir(this.entity)
               .then((response: any) => {
                                                 this.toaster.pop({
                           type: 'success',
                           title : 'Advertência excluída com sucesso.'
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
            if ( response.data.message === 'Validation Failed' ) {
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
                title: 'Erro ao tentar excluir advertência.'            });
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

export class NsAdvertenciasFormExcluirService {
    static $inject = ['$uibModal'];

    constructor(public $uibModal: any) {
    }

    open(ctrlEntity: any) {
            return this.$uibModal.open({
                template: require('./excluir.modal.html'),
                controller: 'NsAdvertenciasFormExcluirController',
                controllerAs: 'ns_dvrtncs_frm_xclr_cntrllr',
                windowClass: '',
                resolve: {
                    entity: () => {
                        return ctrlEntity;
                    }
                }
            });
    }

}
