import { NsjAuth } from '@nsj/core';
import angular = require('angular');
export class NsClientesDefaultController {

    static $inject = [
        'NsEnderecosFormService',
        'NsEnderecosFormShowService',
        'NsContatosFormService',
        'NsContatosFormShowService',
        'CrmTemplatespropostasgruposFormService',
        'NsClientesdocumentosFormService',
        'NsClientesdocumentosFormShowService',
        '$scope',
        'toaster',
        'NsClientes',
        'utilService',
        '$state',
        'nsjRouting',
        '$http'
    ];

    private addFile: (file: any) => void;
    //private removeFile: (file: any) => void;
    // private successFile: (response: any) => void;
    private errorFile: (response: any) => void;
    private listFiles: any[] = [];
    private optionsDropzone: any;
    private modelDropzone = [];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public newEntity: any;
    public tiposatividadeAux: any;
    public tipoatividade: any = {};
    public verificacao: number = 0;

    public contatosConfig = {
        nome: 'Nome',
        primeironome: 'Primeiro nome',
        sobrenome: 'Sobrenome',
        cargo: 'Cargo',
        email: 'Email',
        principal: 'Principal',
        actions: null
    }
    public tiposatividadeConfig = {
        nome: 'Nome',
        descricao: 'Descrição',
        actions: null
    }

    public enderecosConfig = {
        nome: 'Nome',
        tipoendereco: 'Tipo de Endereço',
        rua: 'Nome do Logradouro',
        numero: 'Número',
        actions: null
    }

    public documentosConfig = {
        nome: 'Nome',
        copiasimples: 'Cópia simples',
        copiaautenticada: 'Cópia autenticada',
        original: 'Original',
        enviarporemail: 'Enviar por e-mail',
        pedirinformacoesadicionais: 'Pedir informações adicionais',
        naoexibiremrelatorios: 'Não exibir nos relatórios de documentos automáticos',
        actions: null
    }

    constructor(
        public NsEnderecosFormService: any,
        public NsEnderecosFormShowService: any,
        public NsContatosFormService: any,
        public NsContatosFormShowService: any,
        public CrmTemplatespropostasgruposFormService: any,
        public NsClientesdocumentosFormService: any,
        public NsClientesdocumentosFormShowService: any,
        public $scope: angular.IScope,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        public $state: angular.ui.IStateService,
        public nsjRouting: any,
        public $http: angular.IHttpService,
    ) {
        this.montaListaContatos = this.montaListaContatos.bind(this);
        this.montaListaTiposatividades = this.montaListaTiposatividades.bind(this);
        this.montaListaEnderecos = this.montaListaEnderecos.bind(this);
        this.montaListaDocumentos = this.montaListaDocumentos.bind(this);
        this.$onInit();
    }
    // $onInit() {
    //     this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
    //         if (newValue !== oldValue) {
    //             this.form.$setDirty();
    //         }
    //     }, true);
    //     this.$scope.$on('ns_enderecos_loaded', () => {
    //         this.busy = false;
    //     });
    //     this.$scope.$on('ns_contatos_loaded', () => {
    //         this.busy = false;
    //     });
    //     this.$scope.$on('crm_templatespropostasgrupos_loaded', () => {
    //         this.busy = false;
    //     });
    // }

    async $onInit() {
        if (this.entity) {
          
            this.entity.tiposatividades = this.entity.tiposatividades || [];

            if(this.entity.clientesanexo != null){
                this.entity.clientesanexo.forEach(element => {
                    let makefile = {
                        id: element.clienteanexo,
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
                    'ns_clientesanexos_create',
                    angular.extend({}, { 'cliente': this.entity.cliente }, { 'offset': '', 'filter': '' }),
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
        }

        this.onClientesdocumentosFormShow();
    }

    /** MÉTODOS DROPZONE  */

    loadAnexos() {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate(
                    'ns_clientesanexos_index',
                    angular.extend({}, { 'cliente': this.entity.cliente }, { 'offset': '', 'filter': '' }),
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
                    'ns_clientesanexos_delete',
                    angular.extend({}, { 'cliente': this.entity.cliente, 'id': id }, { 'offset': '', 'filter': '' }),
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
        let obj = this.entity.clientesanexo?.filter((item) => {
            return item.clienteanexo == fileId
        }); // item.id = id do anexo

        // MONTA A REQUEST E DELETA
        if (obj?.length) {
            //deve ser um só, dado que guids são unicos.
            this.deletaAnexo(obj[0].clienteanexo)
                .then((result) => {
                    this.entity.clientesanexo.pop(obj[0]);
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
            cliente: this.entity.cliente,
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


    // nsContatosForm() {

    //     let modal = this.NsContatosFormService.open({}, {});
    //     modal.result.then((subentity: any) => {
    //         subentity.$id = this.idCount++;
    //         if (this.entity.contatos === undefined) {
    //             this.entity.contatos = [subentity];
    //         } else {
    //             this.entity.contatos.push(subentity);
    //         }
    //     })
    //         .catch((error: any) => {
    //             if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
    //                 this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //             }
    //         });
    // }

    NsContatosForm() {

        let modal = this.NsContatosFormService.open({}, {});
        modal.result.then((subentity: any) => {

            if (this.entity.contatos === undefined) {

                this.entity.contatos = [subentity];

            } else {

                //Se for um novo contato principal, mudo no array de contatos qualquer outro contato que esteja como principal
                if (subentity.principal == true) {
                    this.mudaPropriedadePrincipalParaFalso();
                }

                this.entity.contatos = [...this.entity.contatos, subentity];

            }

            //Após a adição do novo contato, garantir que pelo menos um dos contatos é o contato principal
            if (subentity.principal != true) {
                this.garanteAoMenosUmContatoPrincipal();
            }

            this.$scope.$applyAsync();

        }).catch((error: any) => {
            this.busy = false;
            if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: error
                });
            }
        });
    }

    montaListaContatos(subentity: any) {

        const config = {
            entity: {
                principal: subentity.principal ? 'Sim' : 'Não'
            },
            actions: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.NsContatosFormShow(subentity)
                    }
                },
                {
                    label: 'Editar',
                    icon: 'fas fa-edit',
                    method: (subentity: any, index: any) => {
                        this.NsContatosFormEdit(subentity)
                    }
                },
                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (subentity: any, index: any) => {
                        this.crmremoveContato(subentity, index)
                    }
                }
            ]
        }

        return config;
    }

    montaListaTiposatividades(tipoatividade: any) {

        const config = {
            entity: {
                nome: tipoatividade.tipoatividade.nome,
                descricao: tipoatividade.tipoatividade.descricao
            },
            actions: [

                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (subentity: any, index: any) => {
                        this.crmremoveTiposatividade(subentity, index)
                    }
                }
            ]
        }

        return config;

    }

    montaListaEnderecos(subentity: any) {

        let tipoEnderecoTexto;

        switch (subentity.tipoendereco) {
            case 0:
                tipoEnderecoTexto = 'Local'
                break;
            case 1:
                tipoEnderecoTexto = 'Entrega'
                break;
            case 2:
                tipoEnderecoTexto = 'Cobrança'
                break;
            default:
                tipoEnderecoTexto = 'Comercial'
                break;
        }

        const config = {

            entity: {
                endereco: subentity.endereco,
                nome: subentity.nome,
                tipoendereco: tipoEnderecoTexto
            },

            actions: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.nsEnderecosFormShow(subentity)
                    }
                },
                {
                    label: 'Editar',
                    icon: 'fas fa-edit',
                    method: (subentity: any, index: any) => {
                        this.nsEnderecosFormEdit(subentity)
                    }
                },
                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (endereco: any) => {
                        this.nsEnderecoRemover(endereco)
                    }
                }
            ]
        }

        return config;
    }


    montaListaDocumentos(subentity: any) {

        const config = {

            entity: {
                clientedocumento: subentity.clientedocumento,
                nome: subentity.tipodocumento.nome,
                copiasimples: subentity.copiasimples ? 'Sim' : 'Não',
                copiaautenticada: subentity.copiaautenticada ? 'Sim' : 'Não',
                original: subentity.original ? 'Sim' : 'Não',
                enviarporemail: subentity.permiteenvioemail ? 'Sim' : 'Não',
                pedirinformacoesadicionais: subentity.pedirinformacoesadicionais ? 'Sim' : 'Não',
                naoexibiremrelatorios: subentity.naoexibiremrelatorios ? 'Sim' : 'Não',
            },

            actions: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.nsClientesdocumentosFormShow(subentity)
                    }
                },
                {
                    label: 'Excluir',
                    icon: 'far fa-trash-alt',
                    method: (documento: any) => {
                        this.nsDocumentoRemover(documento)
                    }
                }
            ]

        }

        return config;
    }

    add() {

        if (Object.keys(this.tipoatividade).length == 0) {
            return;
        }

        this.entity.tiposatividades = [...this.entity.tiposatividades, angular.copy(this.tipoatividade)];
        this.tipoatividade = {};
        this.$scope.$applyAsync();
    }

    atualizaTiposatividades(tiposatividades: any) {

        this.entity.tiposatividades = tiposatividades;
    }

    crmremoveTiposatividade(tipoatividade: any, index: number) {
        this.entity.tiposatividades.splice(index, 1);
        this.entity.tiposatividades = [...this.entity.tiposatividades];
        this.$scope.$applyAsync();
    }

    atualizaContatos(contatos) {
        this.entity.contatos = contatos;
    }

    atualizaEnderecos(enderecos) {
        this.entity.endereco = enderecos;
        this.$scope.$applyAsync();
    }

    atualizaDocumentos(documentos) {
        this.entity.clientesdocumentos = documentos;
        this.$scope.$applyAsync();
    }

    // nsContatosFormEdit(subentity: any) {
    //     let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
    //     if (parameter.identifier) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsContatosFormService.open(parameter, subentity);
    //     modal.result.then(
    //         (subentity: any) => {
    //             let key;
    //             for (key in this.entity.contatos) {
    //                 if ((this.entity.contatos[key].contato !== undefined && this.entity.contatos[key].contato === subentity.contato)
    //                     || (this.entity.contatos[key].$id !== undefined && this.entity.contatos[key].$id === subentity.$id)) {
    //                     this.entity.contatos[key] = subentity;
    //                 }
    //             }
    //         })
    //         .catch((error: any) => {
    //             this.busy = false;
    //             if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
    //                 this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //             }
    //         });
    // }

    NsContatosFormEdit(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if (!subentity) {
            this.busy = true;
        }
        var modal = this.NsContatosFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                //Se o contato editado for o novo contato principal, mudo um possível contato principal existente para falso
                if (subentity.principal == true) {
                    this.mudaPropriedadePrincipalParaFalso();
                }

                // Atualizo contato na lista
                const contatoNovo = subentity.contato == null;
                const contatoIndex = this.entity.contatos.findIndex((contatoIndex) => {
                    return (contatoNovo && contatoIndex.$$id == subentity.$$id) ||
                        (!contatoNovo && contatoIndex.contato == subentity.contato);
                });

                if (contatoIndex > -1) {
                    this.entity.contatos[contatoIndex] = subentity;
                }

                this.entity.contatos = angular.copy(this.entity.contatos);

                //Após a edição do contato, garantir que pelo menos um dos contatos é o contato principal
                if (subentity.principal != true) {
                    this.garanteAoMenosUmContatoPrincipal();
                }

                this.$scope.$applyAsync();
            }).catch((error: any) => {
                this.busy = false;
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

    NsContatosFormShow(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };

        if (!subentity) {
            this.busy = true;
        }
        var modal = this.NsContatosFormShowService.open(parameter, subentity);
    }

    // Sobrescrito para tirar o loading infinito ao editar endereço
    nsEnderecosFormEdit(subentity: any) {
        let parameter = { 'identifier': subentity.endereco };
        if (parameter.identifier) {
            this.busy = true;
        }

        var modal = this.NsEnderecosFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.endereco) {
                    if ((this.entity.endereco[key].endereco !== undefined && this.entity.endereco[key].endereco === subentity.endereco)
                        || (this.entity.endereco[key].$id !== undefined && this.entity.endereco[key].$id === subentity.$id)) {
                        this.entity.endereco[key] = subentity;
                    }
                }
                this.entity.endereco = angular.copy(this.entity.endereco);
                this.$scope.$applyAsync();
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
        this.busy = false;
    }

    // // Sobrescrito para tirar o loading infinito ao visualizar endereço
    nsEnderecosFormShow(subentity: any) {
        let parameter = { 'identifier': subentity.endereco };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsEnderecosFormShowService.open(parameter, subentity);
        this.busy = false;
    }

    /**
     * Recebe o elemento a ser removido e o array de elementos. Retorna a posição a ser removida desse array
     * @param elemento O elemento a ser removido do array
     * @param arrayRemover Array de onde o elemento será removido
     */
    retornaIndexRemocao(elemento, arrayRemover){

        //Criando um array com todos os identificadores dos elementos do array, baseado na propriedade $$id e a partir desse array, retornando o index da posição a ser removida
        return arrayRemover.map(function(elementoMap){

            return elementoMap.$$id;

        }).indexOf(elemento.$$id);
    }

    nsEnderecoRemover(endereco: any){

        let enderecoIndex = this.retornaIndexRemocao(endereco, this.entity.endereco);

        //Removendo o elemento e atualizando o array de endereços
        this.entity.endereco.splice(enderecoIndex, 1);
        this.entity.endereco = angular.copy(this.entity.endereco);
        this.$scope.$applyAsync();
    }

    nsDocumentoRemover(documento: any) {

        let docIndex = this.retornaIndexRemocao(documento, this.entity.clientesdocumentos);
        
        //Removendo o elemento e atualizando o array de documentos
        this.entity.clientesdocumentos.splice(docIndex, 1);
        this.entity.clientesdocumentos = angular.copy(this.entity.clientesdocumentos);
        this.$scope.$applyAsync();
    }

    crmremoveContato(contato: any, index: number) {

        let contatoIndex = this.retornaIndexRemocao(contato, this.entity.contatos);
        
        //Removendo o elemento e atualizando o array de contatos
        this.entity.contatos.splice(contatoIndex, 1);
        this.entity.contatos = angular.copy(this.entity.contatos);

        this.$scope.$applyAsync();
    }

    onClientesdocumentosFormShow() {
        this.$scope.$on('ns_clientesdocumentos_loaded', (event, { response, entity }) => {
            this.busy = false;
        })
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

    nsEnderecosForm() {
        let modal = this.NsEnderecosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.endereco === undefined) {
                this.entity.endereco = [subentity];
            } else {
                this.entity.endereco.push(subentity);
            }
            this.entity.endereco = angular.copy(this.entity.endereco);
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



    crmTemplatespropostasgruposForm() {
        let modal = this.CrmTemplatespropostasgruposFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.templates === undefined) {
                this.entity.templates = [subentity];
            } else {
                this.entity.templates.push(subentity);
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

    nsClientesdocumentosForm() {
        let modal = this.NsClientesdocumentosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.clientesdocumentos === undefined) {
                this.entity.clientesdocumentos = [subentity];
            } else {
                this.entity.clientesdocumentos.push(subentity);
            }
            this.entity.clientesdocumentos = angular.copy(this.entity.clientesdocumentos);
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



    crmTemplatespropostasgruposFormEdit(subentity: any) {
        let parameter = { 'cliente': subentity.cliente, 'identifier': subentity.templatepropostagrupo };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.CrmTemplatespropostasgruposFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.templates) {
                    if ((this.entity.templates[key].templatepropostagrupo !== undefined && this.entity.templates[key].templatepropostagrupo === subentity.templatepropostagrupo)
                        || (this.entity.templates[key].$id !== undefined && this.entity.templates[key].$id === subentity.$id)) {
                        this.entity.templates[key] = subentity;
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




    nsClientesdocumentosFormShow(subentity: any) {
        let parameter = { 'cliente': subentity.cliente, 'identifier': subentity.clientedocumento };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsClientesdocumentosFormShowService.open(parameter, subentity);
    } submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.cliente = response.data.cliente;
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