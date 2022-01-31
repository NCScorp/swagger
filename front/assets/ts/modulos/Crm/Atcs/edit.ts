import { NsTiposdocumentos } from './../../Ns/Tiposdocumentos/factory';
import angular = require('angular');
// import { CrmAtcsFormController } from './../../Crm/Atcs/edit';
import { CrmAtcsdocumentos } from './../../Crm/Atcsdocumentos/factory';
import { ValidacoesCRM } from "./ValidacoesCRM";

export class CrmAtcsFormController {

    static $inject = [
        'CrmAtcsFormBaixardocumentoService',
        'CrmAtcsFormEnviaratendimentoemailService',
        'CrmAtcsFormRotasgoogledirectionsService',
        'utilService',
        '$scope',
        '$stateParams',
        '$state',
        'CrmAtcs',
        'toaster',
        'entity',
        'nsjRouting',
        '$http',
        'CrmAtcsdocumentos',
        'NsTiposdocumentos',
        '$rootScope',
        'CrmAtcsFormContratoService',
        'CrmAtcsFormContratoexcluirService',
        'moment'
      ];

    public busySubmit: boolean = false;
    public event : Event;
    public formDom: any;
    public action: string = 'update';
    public busy: boolean = false;
    public form: any;
    public constructors: any;

    constructor(
        public CrmAtcsFormBaixardocumentoService: any,
        public CrmAtcsFormEnviaratendimentoemailService: any,
        public CrmAtcsFormRotasgoogledirectionsService: any,
        public utilService: any,
        public $scope: any,
        public $stateParams: any,
        public $state: any,
        public entityService: any,
        public toaster: any,
        public entity: any,
        public nsjRouting: any,
        public $http: any,
        public crmAtcsdocumentos: CrmAtcsdocumentos,
        public NsTiposdocumentos: NsTiposdocumentos,
        public $rootScope: any,
        public CrmAtcsFormContratoService: any,
        public CrmAtcsFormContratoexcluirService,
        public moment: any,
    ) {

        this.action = entity.negocio ? 'update' : 'insert'; $scope.$watch('crm_tcs_frm_cntrllr.entity', (newValue: any, oldValue: any) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);

        entityService.constructors = {};

        for (var i in $stateParams) {
            if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'negocio') {
                entityService.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
            }
        }

        this.constructors = entityService.constructors;

        $scope.$on('crm_atcs_submitted', (event: any, args: any) => {
            let insertSuccess = 'O Atendimento Comercial foi criado com sucesso!';
            let updateSuccess = 'O Atendimento Comercial foi editado com sucesso!';
            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.$state.go('crm_atcs_show', angular.extend({}, this.entityService.constructors, { 'negocio': args.entity.negocio }));
        });

        $scope.$on('crm_atcs_submit_error', (event: any, args: any) => {
            if (args.response.status === 409) {
                if (confirm(args.response.data.message)) {
                    this.entity[''] = args.response.data.entity[''];
                    this.entityService.save(this.entity);
                }
            } else {
                if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    if (args.response.data.message === 'Validation Failed') {
                        let distinct = (value: any, index: any, self: any) => {
                            return self.indexOf(value) === index;
                        };
                        args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
                        let message = this.utilService.parseValidationMessage(args.response.data);
                        this.toaster.pop({
                            type: 'error',
                            title: 'Erro de Validação',
                            body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                            bodyOutputType: 'trustedHtml'
                        });
                    } else {
                        this.toaster.pop({
                            type: 'error',
                            title: args.response.data.message
                        });
                    }
                } else {
                    this.toaster.pop({
                        type: 'error',
                        title: args.response.config.method === 'PUT' ? 'Ocorreu um erro ao atualizar Crm\Atcs!' : 'Ocorreu um erro ao inserir Crm\Atcs!'
                    });
                }
            }
        });

        this.event = document.createEvent('Event');
        this.event.initEvent('invalid', true, true);

        //Caso dê algum erro ao salvar o formulário, reativar o botão salvar
        this.$scope.$on('crm_atcs_submit_error', (event: any, args: any) => {
        this.busySubmit = false;
        });
        this.$scope.$on('crm_atcs_submitted', (event: any, args: any) => {
            let insertSuccess = 'O Atendimento Comercial foi criado com sucesso!';
            let updateSuccess = 'O Atendimento Comercial foi editado com sucesso!';

            this.toaster.pop({
                type: 'success',
                title: args.response.config.method === 'PUT' ? updateSuccess : insertSuccess
            });
            this.busySubmit=false;
            this.$state.go('crm_atcs_full', angular.extend({}, this.entityService.constructors, { 'atc': args.entity.negocio }));
        });
    }

    $onInit(){
    }
  

    calculaIdadeFalecido(){
        if(this.entity.camposcustomizadosFalecidoViewCompleto === undefined ) return;
        if(this.entity.camposcustomizadosFalecidoViewCompleto === null ) return;
        if(this.entity.camposcustomizadosFalecidoViewCompleto[0] === undefined ) return;
        if(this.entity.camposcustomizadosFalecidoViewCompleto[0] === null ) return;
        let guidCampoCustomizadoFalecido = this.entity.camposcustomizadosFalecidoViewCompleto[0].campocustomizado;
        if(this.entity.camposcustomizados[guidCampoCustomizadoFalecido] === undefined ) return;
        if(this.entity.camposcustomizados[guidCampoCustomizadoFalecido] === null ) return;
    
        // Se a data de nascimento não estiver preenchida, verifico se a idade já está preenchida. Se não estiver, apenas retorno, se estiver atribuo vazio para a idade e retorno
        if(this.entity.camposcustomizados[guidCampoCustomizadoFalecido][0].datanascimento == ''){
    
          if(isNaN(this.entity.camposcustomizados[guidCampoCustomizadoFalecido][0].idade)){
            return;
          }
          else{
            this.entity.camposcustomizados[guidCampoCustomizadoFalecido][0].idade = '';
            return;
          }
    
        }
    
        let dataNascimento = this.entity.camposcustomizados[guidCampoCustomizadoFalecido][0].datanascimento;
    
        let momentDataNascimento = this.moment(dataNascimento,'DD/MM/YYYY').format('YYYY MM DD'); 
        let hoje = this.moment();
        let diff = hoje.diff(momentDataNascimento, 'years');
    
        this.entity.camposcustomizados[guidCampoCustomizadoFalecido][0].idade = `${diff}`;
    }

    /**
     * Verifica se o atendimento comercial possui campos customizados relacionados ao falecido.
     * Caso possua, definirá o campo Falecido dos campos customizados com o mesmo nome do atendimento comercial.
     */
    atualizaNomeFalecido() {

        //Se nesse array não tiver nenhum elemento, significa que não há campos customizados de falecido configurado
        if (this.entity.camposcustomizadosFalecidoViewCompleto.length < 1) return;

        let idCampoCustFalecido = this.entity.camposcustomizadosFalecidoViewCompleto[0].campocustomizado;
        
        //Se atendimento não tiver campos customizados de falecido, retorno
        if (this.entity.camposcustomizados[idCampoCustFalecido] == null) return;

        //Se tiver, uso o nome do atendimento para atribuir ao campo Falecido dos campos customizados
        this.entity.camposcustomizados[idCampoCustFalecido][0].nome = this.entity.nome;
    }

    submit() {
        this.calculaIdadeFalecido();
        this.atualizaNomeFalecido();
        this.busySubmit=true;
        this.formDom =  document.forms['crm_tcs_frm_cntrllr.form'];
        (<any>this).form.$submitted = true;
        /* sobrescrito para tratamento de possiveis erros dos responsaveis financeiros */
        let mensagem = ValidacoesCRM.responsaveisFinanceiros(this.entity);
        if ((<any>this).form.$valid && !this.entity.$$__submitting && !mensagem && this.entity.camposcustomizadosvalidos) {
            /* sobrescrito por causa dos campos customizados */

            /* sobrescrito por causa do combo para salvar localização */
            // this.entity.localizacaosalvar = (this.entity.localizacaoexistentesalvar != null) ? this.entity.localizacaoexistentesalvar : (this.entity.localizacaonovasalvar != null ? this.entity.localizacaonovasalvar : null);
            
            // Verificando se há dados da seguradora e se se há dados da seguradora em que o tipo da apólice é física e o campo beneficiário está preenchido. Se houver, limpar o campo beneficiário
            if(this.entity.negociosdadosseguradoras != null){
                for (let i = 0; i < this.entity.negociosdadosseguradoras.length; i++) {
                    const dadoSeg = this.entity.negociosdadosseguradoras[i];
    
                    // Se o tipo da apólice for física e houver valor digitado no campo beneficiario, limpo o campo beneficiario
                    if(dadoSeg.apolicetipo == 0 && dadoSeg.beneficiario != null){
                        dadoSeg.beneficiario = null;
                    }
                    
                }
            }

            // Verificando se os objetos dos municípios estão no formato correto
            this.verificaMunicipio('localizacaomunicipio');
            this.verificaMunicipio('localizacaomunicipiosepultamento');
            
            let data = angular.copy(this.entity);
      
            if (this.entity.possuiseguradoraint == 0) {
                this.entity.negociosdadosseguradoras = [];
            }

            data.camposcustomizados = JSON.stringify(angular.copy({...data.camposcustomizados }));
            this.entityService.save(data);
        } else {
            this.busySubmit=false;
            this.formDom.dispatchEvent(this.event);

            
            mensagem = (mensagem === null) ? 'Alguns campos do formulário apresentam erros' : mensagem;

            this.toaster.pop({
                type: 'error',
                title: mensagem
            });
            this.busySubmit = false;
        }
    }

    /**
     * Faz a verificação se o Municipio ou Municipio de Sepultamento está com a propriedade "codigo" preenchida.
     * Caso não esteja, preenchê-la usando a propriedade "ibge"
     */
    verificaMunicipio(campo: string){

        if(this.entity[campo] != null && (this.entity[campo].codigo == null && this.entity[campo].ibge != null)){
            this.entity[campo].codigo = this.entity[campo].ibge;
        }

    }

    geraContrato(entity: any) {
        let modal = this.CrmAtcsFormContratoService.open({ 'negocio': entity.negocio });
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

    excluiContrato(entity: any) {
        let modal = this.CrmAtcsFormContratoexcluirService.open({ 'negocio': entity.negocio });
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

    atcStatusNovo(entity: any) {
        this.busy = true;
        this.entityService.atcStatusNovo(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatusnovo!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatusnovo.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    atcStatusEmAtendimento(entity: any) {
        this.busy = true;
        this.entityService.atcStatusEmAtendimento(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatusematendimento!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatusematendimento.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    atcStatusFechado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusFechado(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatusfechado!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatusfechado.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    atcStatusReaberto(entity: any) {
        this.busy = true;
        this.entityService.atcStatusReaberto(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatusreaberto!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatusreaberto.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    atcStatusFinalizado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusFinalizado(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatusfinalizado!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatusfinalizado.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    atcStatusCancelado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusCancelado(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatuscancelado!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatuscancelado.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    atcStatusDescancelado(entity: any) {
        this.busy = true;
        this.entityService.atcStatusDescancelado(entity)
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'Sucesso ao atcstatusdescancelado!'
            });
            this.$state.reload();
        }).catch((response: any) => {
            if (typeof (response.data.message) !== 'undefined' && response.data.message) {
                this.toaster.pop(
                    {
                        type: 'error',
                        title: response.data.message
                    });
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao atcstatusdescancelado.'
                });
            }
        }).finally((response: any) => {
            this.busy = false;
        });
    }

    baixardocumento(entity: any) {
        let modal = this.CrmAtcsFormBaixardocumentoService.open({ 'negocio': entity.negocio });
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

    enviaratendimentoemail(entity: any) {
        let modal = this.CrmAtcsFormEnviaratendimentoemailService.open({ 'negocio': entity.negocio });
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

    indexRotasGoogleDirections(entity: any) {
        let modal = this.CrmAtcsFormRotasgoogledirectionsService.open({ 'negocio': entity.negocio });
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
}
