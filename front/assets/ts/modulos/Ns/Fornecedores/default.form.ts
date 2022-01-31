import angular = require('angular');
import { NsjAuth } from '@nsj/core';
import { FinancasBancos } from '../../Financas/Bancos/factory';
export class NsFornecedoresDefaultController {

    static $inject = ['NsEnderecosFormService',
        'NsEnderecosFormShowService',
        'NsContatosFormService',
        'NsContatosFormShowService',
        'FinancasContasfornecedoresFormService',
        'FinancasContasfornecedoresFormShowService',
        'NsFornecedoresdocumentosFormService',
        'NsFornecedoresdocumentosFormShowService',
        '$scope', 'toaster', 'NsFornecedores', 'utilService', 'FinancasBancos', 'nsjRouting', '$http'];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;


    private addFile: (file: any) => void;
    private errorFile: (response: any) => void;
    private listFiles: any[] = [];
    private optionsDropzone: any;
    private modelDropzone = [];

    constructor(
        public NsEnderecosFormService: any,
        public NsEnderecosFormShowService: any,
        public NsContatosFormService: any,
        public NsContatosFormShowService: any,
        public FinancasContasfornecedoresFormService: any,
        public FinancasContasfornecedoresFormShowService: any,
        public NsFornecedoresdocumentosFormService: any,
        public NsFornecedoresdocumentosFormShowService: any,
        public $scope: angular.IScope,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        public FinancasBancos: FinancasBancos,
        public nsjRouting: any,
        public $http: angular.IHttpService
    ) {
    }
    $onInit() {

        //anexos
        if(this.entity.fornecedoresanexos != null){
            this.entity.fornecedoresanexos.forEach(element => {
                let makefile = {
                    id: element.fornecedoranexo,
                    name: element.nomearquivo,
                    type: element.tipo,
                    size: element.tamanho,
                    created: element.createdat,
                    url: element.url
                };
                this.modelDropzone.push(makefile);
            });
        }
        
        this.optionsDropzone = {
            url: this.nsjRouting.generate(
                'ns_fornecedoresanexos_create',
                angular.extend({}, { 'fornecedor': this.entity.fornecedor }, { 'offset': '', 'filter': '' }),
                true
            ),
            paramName: "anexo",
            maxFilesize: 5,
            maxFiles: 100,
            addRemoveLinks: true,
            dictDefaultMessage: 'Solte o arquivo aqui ou clique para adicionar',
            dictRemoveFile: 'Remover',
            dictCancelUpload: 'Aguarde',
            dictMaxFilesExceeded: 'Você não pode enviar mais arquivos',
            dictCancelUploadConfirmation: 'Tem certeza que deseja cancelar o upload?',
            dictRemoveFileConfirmation: 'Tem certeza que deseja deletar o anexo?',
            autoProcessQueue: true,
            uploadMultiple: false,
            parallelUploads: 3,
            thumbnailMethod: 'crop',
            thumbnailHeight: 144,
            thumbnailWidth: 240
        };


        this.optionsDropzone.headers = NsjAuth.authService.addAuthHeader(this.optionsDropzone.headers);

        // fim anexos

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

        this.verificaCpfOuCnpj();
        this.verificaesperapagamentoseguradora();
    }

    /** MÉTODOS DROPZONE  */

    loadAnexos() {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate(
                    'ns_fornecedoresanexos_index',
                    angular.extend({}, { 'fornecedor': this.entity.fornecedor }, { 'offset': '', 'filter': '' }),
                    true
                ),
            })
                .then((response: any) => {
                    resolve(response.data);
                })
                .catch((error: any) => {
                    reject(error);
                });
        });
    }

    deletaAnexo(id) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'DELETE',
                url: this.nsjRouting.generate(
                    'ns_fornecedoresanexos_delete',
                    angular.extend({}, { 'fornecedor': this.entity.fornecedor, 'id': id }, { 'offset': '', 'filter': '' }),
                    true
                ),
            })
                .then((response: any) => {
                    resolve(response.data);
                })
                .catch((error: any) => {
                    reject(error);
                });
        });
    }

    /**
     * Callback chamado quando um arquivo é adicionado ao componente, antes do upload ser realizado
     * @param file 
     */
    AddFile(file: any) {
        //Se arquivo já estiver criado no backend, adiciono botão de download aqui
        if (file.uuid != undefined && file.uuid != null) {
            const img = file.previewTemplate.querySelector('.dz-image').querySelector('.container-imagem').querySelector('img');
            //Adiciono o evento caso tenha dado erro para verificar se é um arquivo ou imagem com erro de carregamento
            img.addEventListener("error", () => {
                //Se foi uma imagem que não carregou direito
                if (file.type.indexOf('image') > -1) {
                    img.setAttribute('class', img.className + ' erro-carregamento');
                }
                else {
                    img.setAttribute('class', img.className + ' file');
                }

            });
        }
        //Me certifico de sempre ser um image preview em vez de file preview
        file.previewTemplate.setAttribute('class', file.previewTemplate.className.replace('dz-file-preview', 'dz-image-preview'));
        this.addFile({ file: file });
    }

    /**
     * Callback chamado quando um arquivo é removido
     * @param file 
     */
    removeFile(file: any) {
        let fileId = file.upload ? file.upload.uuid : file.uuid;

        // ENCONTRA O CARA NO PAI
        let obj = this.entity.fornecedoresanexos?.filter((item) => {
            return item.fornecedoranexo == fileId
        }); // item.id = id do anexo

        // MONTA A REQUEST E DELETA
        if (obj?.length) {
            //deve ser um só, dado que guids são unicos.
            this.deletaAnexo(obj[0].fornecedoranexo)
                .then((result) => {
                    this.entity.fornecedoresanexos.pop(obj[0]);
                    this.toaster.pop({
                        type: 'success',
                        title: 'Anexo excluído.'
                    });
                })
                .catch((error) => {
                    console.error(error);
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao apagar anexo.'
                    });
                });
        }

    }
    /**
     * Callback chamado quando o upload do arquivo é realizado com sucesso
     * @param response 
     */
    successFile(response: any) {
        let entity = {
            uploadid: response.file.upload.uuid,
            nomearquivo: response.file.name,
            fornecedor: this.entity.fornecedor,
            url: response.url,
            tipo: response.mimetype,
            tamanho: response.file.size,
            createdat: response.createdat,
        }
        //Busco elemento de link de download do anexo e deixo invisível até buscar a url correta.
        const divAcaoDownload: Element = response.file.previewTemplate
            .querySelector('.acoes-anexo').querySelector('.acao-download');

        divAcaoDownload.setAttribute('class', divAcaoDownload.getAttribute('class') + ' hide');

        entity.url = response.url;

        let makefile = {
            id: entity.uploadid,
            name: entity.nomearquivo,
            type: entity.tipo,
            size: entity.tamanho,
            created: entity.createdat,
            url: entity.url
        };

        this.modelDropzone.push(makefile);

        // Atualizo a URL do link de download
        const linkDownload: Element = divAcaoDownload.querySelector('a');

        linkDownload.setAttribute('href', entity.url);

        // Mostro botão de download
        divAcaoDownload.setAttribute('class', divAcaoDownload.getAttribute('class').replace('hide', ''));
    }

    /**
     * Callback chamado quando acontece um erro no upload do arquivo
     * @param response
     */
    ErrorFile(response: any) {
        this.busy = false;
        this.toaster.pop({
            type: 'error',
            title: 'Erro ao atualizar logo.'
        });
    }

    /** FIM DOS MÉTODOS DO DROPZONE */

    //Função que no carregamento da página de edição, faz o controle da exibição do campo CPF, criando essa propriedade na entity
    verificaCpfOuCnpj() {
        if (this.action === 'update') {

            //Se o campo não for vazio e o campo cnpj tiver 11 digitos, na verdade ele é um CPF
            if ((this.entity.cnpj != null && this.entity.cnpj != undefined) && this.entity.cnpj.length == 11) {

                //Criando a propriedade cpf na entity
                this.entity.cpf = this.entity.cnpj;

                //Atribuindo null a propriedade cnpj da entity, para não exibir os 2 campos na edição da prestadora
                this.entity.cnpj = null;
            }

        }

    }

    verificaesperapagamentoseguradora() {

        if (this.entity.esperapagamentoseguradora) {
            this.entity.esperapagamentoseguradora = 1
        } else {
            this.entity.esperapagamentoseguradora = 0
        }
    }
    // nsContatosFormShow(subentity: any) {
    //     let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
    //     if ( parameter.identifier ) {
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

    // nsContatosForm() {
    //     let modal = this.NsContatosFormService.open({}, {});
    //     modal.result.then( (subentity: any) => {
    //         subentity.$id = this.idCount++;
    //         if ( this.entity.contatos === undefined ) {
    //             this.entity.contatos = [subentity];
    //         } else {
    //             this.entity.contatos.push(subentity);
    //         }
    //     })
    //     .catch( (error: any) => {
    //         if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
    //             this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //         }
    //     });
    // }

    nsContatosForm() {
        let modal = this.NsContatosFormService.open({}, {});

        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;

            if (this.entity.contatos === undefined) {

                this.entity.contatos = [subentity];

            } else {

                //Se for um novo contato principal, mudo no array de contatos qualquer outro contato que esteja como principal
                if (subentity.principal == true) {
                    this.mudaPropriedadePrincipalParaFalso();
                }

                this.entity.contatos.push(subentity);
            }

            //Após a adição do novo contato, garantir que pelo menos um dos contatos é o contato principal
            this.garanteAoMenosUmContatoPrincipal();

            this.entity.contatos = [].concat(this.entity.contatos);
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

    // nsContatosFormEdit(subentity: any) {
    //     let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
    //     if ( parameter.identifier ) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsContatosFormService.open(parameter, subentity);
    //     modal.result.then(
    //     (subentity: any) => {
    //         let key;
    //         for ( key in this.entity.contatos ) {
    //             if ( (this.entity.contatos[key].contato !== undefined && this.entity.contatos[key].contato === subentity.contato)
    //                 || (this.entity.contatos[key].$id !== undefined && this.entity.contatos[key].$id === subentity.$id)) {
    //                 this.entity.contatos[key] = subentity;
    //             }
    //         }
    //     })
    //     .catch( (error: any) => {
    //         this.busy = false;
    //         if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
    //             this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //         }
    //     });
    // }

    nsContatosFormEdit(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsContatosFormService.open(parameter, subentity);
        this.busy = false;
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.contatos) {
                    if ((this.entity.contatos[key].contato !== undefined && this.entity.contatos[key].contato === subentity.contato)
                        || (this.entity.contatos[key].$id !== undefined && this.entity.contatos[key].$id === subentity.$id)) {

                        //Se o contato editado for o novo contato principal, mudo um possível contato principal existente para falso
                        if (subentity.principal == true) {
                            this.mudaPropriedadePrincipalParaFalso();
                        }

                        this.entity.contatos[key] = subentity;

                        //Após a edição do contato, garantir que pelo menos um dos contatos é o contato principal
                        this.garanteAoMenosUmContatoPrincipal();

                        this.entity.contatos = [].concat(this.entity.contatos);
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

    /**
     * Garantir que após a remoção do contato, ainda exista um contato principal
     * @param oblistctrl Objectlist de contatos
     * @param contato O contato que está sendo removido da lista
     */
    nsContatoRemover(oblistctrl: any, contato: any) {
        oblistctrl.remove(contato);

        //Após a remoção, verificar se pelo menos um contato continua como principal, se não tiver, atribuir o primeiro da lista como principal
        if (this.entity.contatos.length > 0) {
            this.garanteAoMenosUmContatoPrincipal();
        }

        this.entity.contatos = [].concat(this.entity.contatos);
    }

    // financasContasfornecedoresForm() {
    //     let modal = this.FinancasContasfornecedoresFormService.open({}, {});
    //     modal.result.then( (subentity: any) => {
    //         subentity.$id = this.idCount++;
    //         if ( this.entity.dadosbancarios === undefined ) {
    //             this.entity.dadosbancarios = [subentity];
    //         } else {
    //             this.entity.dadosbancarios.push(subentity);
    //         }
    //     })
    //     .catch( (error: any) => {
    //         if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
    //             this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //         }
    //     });
    // } 

    /* Sobreescrito para passar de dados bancarios o número do banco para o campo "banco" na tela de fornecedores*/
    financasContasfornecedoresForm() {
        let modal = this.FinancasContasfornecedoresFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.dadosbancarios === undefined) {
                subentity.banco = subentity.id_banco.numero;
                this.entity.dadosbancarios = [subentity];
            } else {
                subentity.banco = subentity.id_banco.numero;
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
    }

    // financasContasfornecedoresFormEdit(subentity: any) {
    //     let parameter = { 'fornecedor': subentity.fornecedor, 'identifier': subentity.contafornecedor };
    //     if ( parameter.identifier ) {
    //         this.busy = true;
    //     }
    //     var modal = this.FinancasContasfornecedoresFormService.open(parameter, subentity);
    //     modal.result.then(
    //     (subentity: any) => {
    //         let key;
    //         for ( key in this.entity.dadosbancarios ) {
    //             if ( (this.entity.dadosbancarios[key].contafornecedor !== undefined && this.entity.dadosbancarios[key].contafornecedor === subentity.contafornecedor)
    //                 || (this.entity.dadosbancarios[key].$id !== undefined && this.entity.dadosbancarios[key].$id === subentity.$id)) {
    //                 this.entity.dadosbancarios[key] = subentity;
    //             }
    //         }
    //     })
    //     .catch( (error: any) => {
    //         this.busy = false;
    //         if ( error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press' ) {
    //             this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //         }
    //     });
    // }

    /*Sobreescrito para passar de dados bancarios o número do banco para o campo "banco" na tela de fornecedores e carregar valor certo de banco no lookup*/
    financasContasfornecedoresFormEdit(subentity: any) {
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
                        subentity.banco = subentity.id_banco.numero;
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

    /**
     * Modifica um possível contato principal da listagem de contatos para falso
     */
    mudaPropriedadePrincipalParaFalso() {

        for (let i = 0; i < this.entity.contatos.length; i++) {

            if (this.entity.contatos[i].principal == true) {
                this.entity.contatos[i].principal = false;
            }
        }

    }

    /**
     * Verifica se, na listagem de contatos, ao menos um contato seja o principal. Caso não tenha nenhum principal, coloca o primeiro da lista como principal
     */
    garanteAoMenosUmContatoPrincipal() {

        let temPrincipal = this.entity.contatos.some(function (contato) {
            return contato.principal == true;
        });

        //Não tem nenhum principal. Defino o primeiro contato da lista como o principal
        if (temPrincipal != true && this.entity.contatos.length > 0) {
            this.entity.contatos[0].principal = true;
        }

    }

    // nsFornecedoresdocumentosFormShow(subentity: any) {
    //     let parameter = { 'fornecedor': subentity.fornecedor, 'identifier': subentity.fornecedordocumento };
    //     if ( parameter.identifier ) {
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
    } submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.fornecedor = response.data.fornecedor;
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
