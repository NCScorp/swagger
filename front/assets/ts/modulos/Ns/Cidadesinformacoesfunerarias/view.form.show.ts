import angular = require('angular');
export class NsCidadesinformacoesfunerariasViewShowController {

    static $inject = ['NsCidadesinfofunerariasfornecedoresFormService',
        'NsCidadesinfofunerariasfornecedoresFormShowService',
        '$scope', 'toaster', 'NsCidadesinformacoesfunerarias', 'utilService'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    constructor(public NsCidadesinfofunerariasfornecedoresFormService: any,
        public NsCidadesinfofunerariasfornecedoresFormShowService: any,
        public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) {
    }

    /**
     * Sobrescrevo para fazer modificações antes de chamar a função pai
     */
    $onInit() {
        if (!this.entity.fornecedores) {
            this.entity.fornecedores = [];
        }

        //Ordeno fornecedores de acordo com campo ordem
        this.entity.fornecedores = (this.entity.fornecedores as any[]).sort((itemA, itemB) => {
            if (itemA.ordem < itemB.ordem) {
                return -1;
            } else {
                return 1;
            }
        });

        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
        this.$scope.$on('ns_cidadesinfofunerariasfornecedores_loaded', () => {
            this.busy = false;
        });
    }

    // $onInit() {
    //     this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
    //         if (newValue !== oldValue) {
    //             this.form.$setDirty();
    //         }
    //     }, true);
    //     this.$scope.$on('ns_cidadesinfofunerariasfornecedores_loaded', () => {
    //         this.busy = false;
    //     });
    // }
    nsCidadesinfofunerariasfornecedoresForm() {
        let modal = this.NsCidadesinfofunerariasfornecedoresFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.fornecedores === undefined) {
                this.entity.fornecedores = [subentity];
            } else {
                this.entity.fornecedores.push(subentity);
            }
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsCidadesinfofunerariasfornecedoresFormEdit(subentity: any) {
        let parameter = { 'cidadeinformacaofuneraria': subentity.cidadeinformacaofuneraria, 'identifier': subentity.cidadeinfofunerariafornecedor };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsCidadesinfofunerariasfornecedoresFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.fornecedores) {
                    if ((this.entity.fornecedores[key].cidadeinfofunerariafornecedor !== undefined && this.entity.fornecedores[key].cidadeinfofunerariafornecedor === subentity.cidadeinfofunerariafornecedor)
                        || (this.entity.fornecedores[key].$id !== undefined && this.entity.fornecedores[key].$id === subentity.$id)) {
                        this.entity.fornecedores[key] = subentity;
                    }
                }
            })
            .catch((error: any) => {
                this.busy = false;
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsCidadesinfofunerariasfornecedoresFormShow(subentity: any) {
        let parameter = { 'cidadeinformacaofuneraria': subentity.cidadeinformacaofuneraria, 'identifier': subentity.cidadeinfofunerariafornecedor };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsCidadesinfofunerariasfornecedoresFormShowService.open(parameter, subentity);
    } uniqueValidation(params: any): any {
        if (this.collection) {
            let validation = { 'unique': true };
            for (var item in this.collection) {
                if (this.collection[item][params.field] === params.value) {
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