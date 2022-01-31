import angular = require('angular');
export class NsContatosShowShowController {

    static $inject = [                         'NsTelefonesFormService',
                                                'NsTelefonesFormShowService',
                         '$scope', 'toaster', 'NsContatos', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    public telefonesConfig = {
        ddi: 'DDI',
        ddd: 'DDD',
        telefone: 'Telefone',
        ramal: 'Ramal',
        descricao: 'Descrição',
        principal: 'Principal',
        actions: null
    }

    constructor(
        public NsTelefonesFormService: any,
        public NsTelefonesFormShowService: any,
        public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any
    ) {
        this.montaListaTelefones = this.montaListaTelefones.bind(this);
    }
    $onInit() {
                        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }}, true);
                 this.$scope.$on('ns_telefones_loaded', () => {
            this.busy = false;
        });
            }

        montaListaTelefones(subentity: any) {

            const config = {
                entity: {
                    ddi: subentity.ddi,
                    ddd: subentity.ddd,
                    telefone: subentity.telefone,
                    ramal: subentity.ramal,
                    descricao: subentity.descricao,
                    principal: subentity.principal ? 'Sim' : 'Não'
                },
                actions: [
                    {
                        label: 'Visualizar',
                        icon: 'fas fa-eye',
                        method: (subentity: any, index: any) => {
                            this.nsTelefonesFormShow(subentity)
                        }
                    }
                ]
            }

            return config;
        }

       nsTelefonesForm() {
        let modal = this.NsTelefonesFormService.open({}, {});
        modal.result.then( (subentity: any) => {
            subentity.$id = this.idCount++;
            if ( this.entity.telefones === undefined ) {
                this.entity.telefones = [subentity];
            } else {
                this.entity.telefones.push(subentity);
            }
        })
        .catch( (error: any) => {
            if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                this.toaster.pop({
                        type: 'error',
                        title: error
                    });
            }
        });
    }         nsTelefonesFormEdit(subentity: any) {
        let parameter = { 'contato': subentity.contato, 'identifier': subentity.telefone_id };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.NsTelefonesFormService.open(parameter, subentity);
        modal.result.then(
        (subentity: any) => {
            let key;
            for ( key in this.entity.telefones ) {
                if ( (this.entity.telefones[key].telefone_id !== undefined && this.entity.telefones[key].telefone_id === subentity.telefone_id)
                    || (this.entity.telefones[key].$id !== undefined && this.entity.telefones[key].$id === subentity.$id)) {
                    this.entity.telefones[key] = subentity;
                }
            }
        })
        .catch( (error: any) => {
            this.busy = false;
            if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
                this.toaster.pop({
                        type: 'error',
                        title: error
                    });
            }
        });
    }         nsTelefonesFormShow(subentity: any) {
        let parameter = { 'contato': subentity.contato, 'identifier': subentity.telefone_id };
        if ( parameter.identifier ) {
            this.busy = true;
        }
        var modal = this.NsTelefonesFormShowService.open(parameter, subentity);
    }        uniqueValidation(params: any): any {
        if ( this.collection ) {
            let validation = { 'unique' : true };
            for ( var item in this.collection ) {
                if ( this.collection[item][params.field] === params.value ) {
                    validation.unique = false;
                    break;
                }
            }
            return validation;
        } else {
            return null;
        }
    }
}