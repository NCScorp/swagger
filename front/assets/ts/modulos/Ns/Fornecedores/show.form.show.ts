import angular = require('angular');
import { for } from 'core-js/fn/symbol';
import { NsAdvertenciasFormArquivarService } from './../../Ns/Advertencias/arquivar.modal';
import { NsAdvertenciasFormExcluirService } from './../../Ns/Advertencias/excluir.modal';

export class NsFornecedoresShowShowController {

    static $inject = [
        'NsEnderecosFormService',
        'NsEnderecosFormShowService',
        'NsContatosFormService',
        'NsContatosFormShowService',
        'FinancasContasfornecedoresFormService',
        'FinancasContasfornecedoresFormShowService',
        'NsFornecedoresdocumentosFormService',
        'NsFornecedoresdocumentosFormShowService',
        '$scope',
        'toaster',
        'NsFornecedores',
        'utilService',
        'NsAdvertenciasFormArquivarService',
        'NsAdvertenciasFormExcluirService',
        '$state'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public exibeAcoesAdvertencias: boolean = false; 

    public anexosConfig = {
        nome: 'Nome do arquivo',
        // actions: null
    }

    constructor(public NsEnderecosFormService: any,
        public NsEnderecosFormShowService: any,
        public NsContatosFormService: any,
        public NsContatosFormShowService: any,
        public FinancasContasfornecedoresFormService: any,
        public FinancasContasfornecedoresFormShowService: any,
        public NsFornecedoresdocumentosFormService: any,
        public NsFornecedoresdocumentosFormShowService: any,
        public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any,
        public NsAdvertenciasFormArquivarService: NsAdvertenciasFormArquivarService,
        public NsAdvertenciasFormExcluirService: NsAdvertenciasFormExcluirService,
        public $state,
    ) {
        this.montaListaAnexos = this.montaListaAnexos.bind(this);
    }

    montaListaAnexos(subentity: any) {

        const config = {

            entity:{
                clientesanexo: subentity.clientesanexo,
                nome: subentity.nomearquivo,
            },
            actions: []
        }

        return config;
    }

    /* Sobrescrito para realizar as ações nas advertencias na tela de fornecedores */
    arquivar(advertencia: any) {
        let modal = this.NsAdvertenciasFormArquivarService.open({ 'advertencia': advertencia });
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

    excluir(advertencia: any) {
        let modal = this.NsAdvertenciasFormExcluirService.open({ 'advertencia': advertencia });
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

    // nsContatosFormShow(subentity: any) {
    //     let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
    //     if (parameter.identifier) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsContatosFormShowService.open(parameter, subentity);
    // }

    /* Sobrescritos para corrigir o problema do loading infinito */
    nsContatosFormShow(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsContatosFormShowService.open(parameter, subentity);
        this.busy = false;
    }

    // nsFornecedoresdocumentosFormShow(subentity: any) {
    //     let parameter = { 'fornecedor': subentity.fornecedor, 'identifier': subentity.fornecedordocumento };
    //     if (parameter.identifier) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsFornecedoresdocumentosFormShowService.open(parameter, subentity);
    // }


    /* Sobreescrito para corrigir o problema de requisição de tiposdocumentos na tela de exibição de fornecedores */
    nsFornecedoresdocumentosFormShow(subentity: any) {
        let parameter = { 'fornecedor': this.entity.fornecedor, 'identifier': subentity.fornecedordocumento };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsFornecedoresdocumentosFormShowService.open(parameter, subentity);
        this.busy = false;
    }

    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
        this.$scope.$on('ns_enderecos_loaded', () => {
            this.busy = false;
        });
        this.$scope.$on('ns_contatos_loaded', () => {
            this.busy = false;
        });
        this.$scope.$on('financas_contasfornecedores_loaded', () => {
            this.busy = false;
        });

        this.verificaExibicaoAcoesAdv(this.entity.advertencia);

    }

    /**
     * Verifica se há advertência em vigor. Se houver, a propriedade exibeAcoesAdvertencias recebe true e a coluna das ações é exibida
     * @param advertencias 
     */
    verificaExibicaoAcoesAdv(advertencias: Array<any>){
        
        for(let i = 0; i < advertencias.length; i++){
            if(advertencias[i].status == 0){
                this.exibeAcoesAdvertencias = true;
                break;
            }
        }

    }

    nsEnderecosForm() {
        let modal = this.NsEnderecosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.endereco === undefined) {
                this.entity.endereco = [subentity];
            } else {
                this.entity.endereco.push(subentity);
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
    } nsContatosForm() {
        let modal = this.NsContatosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.contatos === undefined) {
                this.entity.contatos = [subentity];
            } else {
                this.entity.contatos.push(subentity);
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
    } financasContasfornecedoresForm() {
        let modal = this.FinancasContasfornecedoresFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.dadosbancarios === undefined) {
                this.entity.dadosbancarios = [subentity];
            } else {
                this.entity.dadosbancarios.push(subentity);
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
    } nsFornecedoresdocumentosForm() {
        let modal = this.NsFornecedoresdocumentosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.fornecedoresdocumentos === undefined) {
                this.entity.fornecedoresdocumentos = [subentity];
            } else {
                this.entity.fornecedoresdocumentos.push(subentity);
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
    } 
    
    nsEnderecosFormEdit(subentity: any) {
        let parameter = { 'identifier': subentity.endereco };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsEnderecosFormService.open(parameter, subentity);
        this.busy = false;
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.endereco) {
                    if ((this.entity.endereco[key].endereco !== undefined && this.entity.endereco[key].endereco === subentity.endereco)
                        || (this.entity.endereco[key].$id !== undefined && this.entity.endereco[key].$id === subentity.$id)) {
                        this.entity.endereco[key] = subentity;
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
    } 
    
    nsContatosFormEdit(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsContatosFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.contatos) {
                    if ((this.entity.contatos[key].contato !== undefined && this.entity.contatos[key].contato === subentity.contato)
                        || (this.entity.contatos[key].$id !== undefined && this.entity.contatos[key].$id === subentity.$id)) {
                        this.entity.contatos[key] = subentity;
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
    } financasContasfornecedoresFormEdit(subentity: any) {
        let parameter = { 'fornecedor': subentity.fornecedor, 'identifier': subentity.contafornecedor };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.FinancasContasfornecedoresFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.dadosbancarios) {
                    if ((this.entity.dadosbancarios[key].contafornecedor !== undefined && this.entity.dadosbancarios[key].contafornecedor === subentity.contafornecedor)
                        || (this.entity.dadosbancarios[key].$id !== undefined && this.entity.dadosbancarios[key].$id === subentity.$id)) {
                        this.entity.dadosbancarios[key] = subentity;
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
    } 
    
    nsEnderecosFormShow(subentity: any) {
        let parameter = { 'identifier': subentity.endereco };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsEnderecosFormShowService.open(parameter, subentity);
        this.busy = false;
    } 
    
    financasContasfornecedoresFormShow(subentity: any) {
        let parameter = { 'fornecedor': subentity.fornecedor, 'identifier': subentity.contafornecedor };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.FinancasContasfornecedoresFormShowService.open(parameter, subentity);
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