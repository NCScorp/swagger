import angular = require('angular');
export class NsContatosDefaultController {

    static $inject = [
        'NsTelefonesFormService',
        'NsTelefonesFormShowService',
        '$scope', 
        'toaster', 
        'NsContatos', 
        'utilService'
    ];

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

    constructor(public NsTelefonesFormService: any,
        public NsTelefonesFormShowService: any,
        public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any
    ) {
        this.montaListaTelefones = this.montaListaTelefones.bind(this);
    }
    $onInit() {
        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
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
                },
                {
                    label: 'Editar',
                    icon: 'fas fa-edit',
                    method: (subentity: any, index: any) => {
                        this.nsTelefonesFormEdit(subentity)
                    }
                },
                {
                    label: 'Excluir',
                    icon: 'fas fa-trash-alt',
                    method: (subentity: any, index: any) => {
                        this.nsTelefoneRemover(subentity)
                    }
                }
            ]
        }

        return config;
    }

    atualizaTelefones(telefones) {
        this.entity.telefones = telefones;
        this.$scope.$applyAsync();
    }

    // nsTelefonesForm() {
    //     let modal = this.NsTelefonesFormService.open({}, {});
    //     modal.result.then((subentity: any) => {
    //         subentity.$id = this.idCount++;
    //         if (this.entity.telefones === undefined) {
    //             this.entity.telefones = [subentity];
    //         } else {
    //             this.entity.telefones.push(subentity);
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
    nsTelefonesForm() {
        let modal = this.NsTelefonesFormService.open({}, {});
        
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;

            if (this.entity.telefones === undefined) {

                this.entity.telefones = [subentity];

            }
            else {

                //Se for um novo telefone principal, mudo no array de telefones qualquer outro telefone que esteja como principal
                if(subentity.principal == true){
                    this.mudaPropriedadePrincipalParaFalso();
                }

                this.entity.telefones.push(subentity);
            }

            //Após a adição do novo telefone, garantir que pelo menos um dos telefones é o telefone principal
            this.garanteAoMenosUmTelefonePrincipal();
            this.entity.telefones = angular.copy(this.entity.telefones);

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
    
    // nsTelefonesFormEdit(subentity: any) {
    //     let parameter = { 'contato': subentity.contato, 'identifier': subentity.telefone_id };
    //     if (parameter.identifier) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsTelefonesFormService.open(parameter, subentity);
    //     modal.result.then(
    //         (subentity: any) => {
    //             let key;
    //             for (key in this.entity.telefones) {
    //                 if ((this.entity.telefones[key].telefone_id !== undefined && this.entity.telefones[key].telefone_id === subentity.telefone_id)
    //                     || (this.entity.telefones[key].$id !== undefined && this.entity.telefones[key].$id === subentity.$id)) {
    //                     this.entity.telefones[key] = subentity;
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

    nsTelefonesFormEdit(subentity: any) {
        let parameter = { 'contato': subentity.contato, 'identifier': subentity.telefone_id };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsTelefonesFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.telefones) {
                    if ((this.entity.telefones[key].telefone_id !== undefined && this.entity.telefones[key].telefone_id === subentity.telefone_id)
                        || (this.entity.telefones[key].$id !== undefined && this.entity.telefones[key].$id === subentity.$id)) {

                        //Se o telefone editado for o novo telefone principal, mudo um possível telefone principal existente para falso
                        if(subentity.principal == true){
                            this.mudaPropriedadePrincipalParaFalso();
                        }

                        this.entity.telefones[key] = subentity;

                        //Após a adição do novo telefone, garantir que pelo menos um dos telefones é o telefone principal
                        this.garanteAoMenosUmTelefonePrincipal();
                    }
                }
                this.entity.telefones = angular.copy(this.entity.telefones);
                this.busy = false;
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
    
    /**
     * Garantir que após a remoção do telefone, ainda exista um telefone principal
     * @param oblistctrl Objectlist de telefones
     * @param telefone O telefone que está sendo removido da lista
     */
    nsTelefoneRemover(telefone: any){
        
        let telefoneIndex = this.retornaIndexRemocao(telefone, this.entity.telefones);

        this.entity.telefones.splice(telefoneIndex, 1);
        
        //Após a remoção, verificar se pelo menos um telefone continua como principal, se não tiver, atribuir o primeiro da lista como principal
        if(this.entity.telefones.length > 0){
            this.garanteAoMenosUmTelefonePrincipal();
        }

        this.atualizaTelefones(this.entity.telefones);
        this.entity.telefones = angular.copy(this.entity.telefones);
    }

    /**
     * Modifica um possível telefone principal da listagem de telefones para falso
     */
    mudaPropriedadePrincipalParaFalso(){

        for(let i = 0; i < this.entity.telefones.length; i++){

            if(this.entity.telefones[i].principal == true){
                this.entity.telefones[i].principal = false;
            }
        }

    }

    /**
     * Verifica se, na listagem de telefones, ao menos um telefone seja o principal. Caso não tenha nenhum principal, coloca o primeiro da lista como principal
     */
    garanteAoMenosUmTelefonePrincipal(){

        let temPrincipal = this.entity.telefones.some(function(telefone){
            return telefone.principal == true;
        });

        //Não tem nenhum principal. Defino o primeiro telefone da lista como o principal
        if(temPrincipal != true){
            this.entity.telefones[0].principal = true;
        }

    }

    nsTelefonesFormShow(subentity: any) {
        let parameter = { 'contato': subentity.contato, 'identifier': subentity.telefone_id };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsTelefonesFormShowService.open(parameter, subentity);
        this.busy = false;
    } 
    
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.contato = response.data.contato;
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