import angular = require('angular');
import { CrmCidadesInfoFunerariasPretadoresModalService } from './modal-prestadores';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';
export class NsCidadesinformacoesfunerariasCadastroController {

    static $inject = [
        'NsCidadesinfofunerariasfornecedoresFormService',
        'NsCidadesinfofunerariasfornecedoresFormShowService',
        '$scope', 
        'toaster', 
        'NsCidadesinformacoesfunerarias', 
        'utilService',
        'CrmCidadesInfoFunerariasPretadoresModalService',
        'CommonsUtilsService'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    private EnumPrestadoraStatus = EnumPrestadoraStatus;
    
    /**
     * Define se apresenta o alerta informativo do ranking de fornecedores
     */
    private mostrarInfoFornecedores: boolean = true;
    
    constructor(
        public NsCidadesinfofunerariasfornecedoresFormService: any,
        public NsCidadesinfofunerariasfornecedoresFormShowService: any,
        public $scope: angular.IScope,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        private CrmCidadesInfoFunerariasPretadoresModalService: CrmCidadesInfoFunerariasPretadoresModalService,
        private CommonsUtilsService: CommonsUtilsService
    ) {
    }
    
    /**
     * Sobrescrevo para fazer modificações antes de chamar a função pai
     */
    $onInit() {

        //Lookup de país já inicia com Brasil, caso seja uma criação
        if (this.action == 'insert') {
            this.entity["pais"] = {
                'full_count': 1,
                'nome': 'BRASIL',
                'pais': '1058'
            };
        }

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

    /**
     * Abre modal para adição de fornecedores ao ranking
     */
    private adicionarNovoFornecedor(){
        let modal = this.CrmCidadesInfoFunerariasPretadoresModalService.open({});

        modal.result.then((retorno: any) => {
            if (retorno.fornecedores && retorno.fornecedores.length > 0) {
                retorno.fornecedores.forEach(fornecedorForeach => {
                    let jaTemFornecedor = this.entity.fornecedores.findIndex((fornecedorFind) => {
                        return fornecedorFind.fornecedor.fornecedor == fornecedorForeach.fornecedor;
                    }) > -1;

                    if (!jaTemFornecedor) {
                        this.entity.fornecedores.push({
                            fornecedor: fornecedorForeach,
                            cidadeinformacaofuneraria: this.entity.cidadeinformacaofuneraria
                        });
                    }
                });

                this.$scope.$applyAsync();
            }
        }).catch((err) => {});
    }

    /**
     * Retorna o nome do status da prestadora
     */
    private getStatusNome(status: EnumPrestadoraStatus): string {
        let descricao = '';

        switch (status) {
            case EnumPrestadoraStatus.psAtiva: {
                descricao = 'ATIVA';
                break;
            }
            case EnumPrestadoraStatus.psBanida: {
                descricao = 'BANIDA';
                break;
            }
            case EnumPrestadoraStatus.psSuspensa: {
                descricao = 'SUSPENSA';
                break;
            }
        
            default:
                break;
        }

        return descricao;
    }

    /**
     * Retorna a data e hora formatada
     * @param dataStr 
     */
    private getData(dataStr: string): string {
        let data = '';

        if (dataStr != null) {
            data = this.CommonsUtilsService.getDataFormatada(dataStr, 'DD/MM/YYYY [às] HH:mm');
        }
        
        return data;
    }

    /**
     * Retorna a data de ranqueamento da prestadora. No caso de uma nova prestadora, retorna vazio
     * @param fornecedor 
     */
    private getDataRanqueamento(fornecedor: any): string {
        if (!fornecedor.cidadeinfofunerariafornecedor) {
            return '';
        } else {
            return this.getData(this.entity.updated_at);
        }
    }

    /**
     * Fecha alerta informativo do ranking de fornecedores
     */
    private fecharInfoFornecedores(){
        this.mostrarInfoFornecedores = false;
    }

    /**
     * Remove fornecedor da lista de ranqueados
     * @param fornecedor 
     */
    private removerFornecedor(fornecedor){
        let index = this.entity.fornecedores.findIndex((fornecedorFind) => {
            return fornecedorFind.fornecedor.fornecedor == fornecedor.fornecedor.fornecedor;
        });

        this.entity.fornecedores.splice(index, 1);
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
    } submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.cidadeinformacaofuneraria = response.data.cidadeinformacaofuneraria;
                    resolve(this.entity);
                })
                    .catch((response: any) => {
                        if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                            if (response.data.message === 'Validation Failed') {
                                let message = this.utilService.parseValidationMessage(response.data.errors.children);
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
                                    title: 'Erro ao adicionar.'
                                });
                        }
                    });
            });
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    uniqueValidation(params: any): any {
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

enum EnumPrestadoraStatus {
    psAtiva = "0",
    psSuspensa = "1",
    psBanida = "2"
}
