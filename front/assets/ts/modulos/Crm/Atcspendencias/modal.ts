import angular = require('angular');
import {CrmAtcspendenciasFormModalController as ParentController } from './../../Crm/Atcspendencias/modal';
import {CrmAtcspendenciasFormService as ParentService } from './../../Crm/Atcspendencias/modal';
import { CrmAtcspendenciasUtilsService, EnumStatusPendencia } from './utils';

export class CrmAtcspendenciasFormModalController {
    EStatusPendencia = EnumStatusPendencia;
    static $inject = [ 
        'toaster', 
        '$uibModalInstance', 
        'entity', 
        'constructors', 
        'CrmAtcspendencias', 
        'CrmAtcspendenciasUtilsService', 
        'telaorigem',
        'moment'
    ];
    public tituloTela: string = '';
    public action: string;
    public form: any;
    public submitted: boolean = false;
    public busy: boolean;


    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        public CrmAtcspendencias: any,
        public CrmAtcspendenciasUtilsService: CrmAtcspendenciasUtilsService,
        public telaorigem: 'atc' | 'dashboard-pendencias',
        public moment: any,
    ) {
        this.action = entity.negociopendencia ? 'update' : 'insert';

        if (this.entity.atribuidoa != null) { 
            this.entity.atribuidoa = this.unicodeToChar(this.entity.atribuidoa);
        }
            
        this.entity.aberta_ha = this.CrmAtcspendenciasUtilsService.getAbertaHa(this.entity.created_at);

        if (this.action == 'insert'){
            this.tituloTela = 'Abrir Pendência';
        }
        else {
            this.tituloTela = 'Consultar Pendência';
        }
    }

    //Função que transforma códigos unicode da string em caracteres
    unicodeToChar(text) {
        return text.replace(/\\u[\dA-F]{4}/gi, function (match) {
            return String.fromCharCode(parseInt(match.replace(/\\u/g, ''), 16));
        });
    }

    fecharPendencia(){

        this.submit('fechar-pendencia');

    }

    validarCampos(action: string = null): boolean {
        let valido: boolean = true;

        //Obtendo a data e hora que será enviada a notificação. Para isso, uso a data de expiração menos os minutos informados no campo Notificar expiração faltando
        let notificaData = this.moment(this.entity.dataprazopendencia).subtract(this.entity.temponotificaexpiracao, "minutes").toDate();
        let dataAtual = new Date();

        //O fechamento da pendência é o único cenário onde permito que o prazo para expiração da pendência e "notificar expiração faltando" sejam menores que a data atual
        //Caso não esteja fechando uma pendência, faço verificação de datas
        if(action != 'fechar-pendencia'){
            
            //Não permitir que a data de notificação seja menor que a data atual
            if (notificaData < dataAtual) {
                valido = false;
                this.toaster.pop({
                    type: 'error',
                    title: 'A data de notificação não pode ser menor que a data atual.'
                });
            }

            //Atributo usado para verificação no backend
            this.entity.fecharpendencia = false;
            
        }else{
            this.entity.fecharpendencia = true;
        }

        if (this.entity.temponotificaexpiracao >= 2147483648) {
            valido = false;
            this.toaster.pop({
                type: 'error',
                title: 'A notificação de expiração do prazo não pode ser maior que 2147483648 minutos.'
            });
        } 

        if (this.entity.temponotificaexpiracao < 1){
            valido = false;
            this.toaster.pop({
                type: 'error',
                title: 'A notificação de expiração do prazo deve ser maior que 0.'
            });
        }

        return valido;
    }

    submit(action){

        // Variável que armazena retorno da função que valida os campos relacionados aos campos de minutos da pendencia
        let validacaoMinutos: boolean = true;

        if (this.form.$valid) {
            validacaoMinutos = this.validarCampos(action);

            if(validacaoMinutos){

                //converter a data e hora para o formato aceito no backend yyyy-mm-dd HH:mm:ss
                this.entity.dataprazopendencia = this.entity.dataprazopendencia.getFullYear() + '-' +
                                                 (this.entity.dataprazopendencia.getMonth() + 1) + '-' +
                                                 this.entity.dataprazopendencia.getDate() + ' ' +
                                                 this.entity.dataprazopendencia.getHours() + ':'  +
                                                 this.entity.dataprazopendencia.getMinutes() + ':' +
                                                 '00'

            }

        }

        if (validacaoMinutos && !this.entity.$$__submitting) {
            this.submitted = true;

            if (this.form.$valid) {
                if(action == undefined){
                    this.$uibModalInstance.close(this.entity);
                }
                else{
                    this.$uibModalInstance.close({action: action, entity: this.entity});
                }
            } else {
                this.toaster.pop({
                    type: 'error',
                    title: 'Alguns campos do formulário apresentam erros.'
                });
            } 

        } 
        //Condição para em caso da validação de minutos retornar verdadeiro, mostrar mensagem genérica. Do contrário toaster da função validarCampos será mostrado
        else if(!this.form.$valid && validacaoMinutos){ 
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

export class CrmAtcspendenciasFormService {
    static $inject = ['CrmAtcspendencias', '$uibModal', 'CrmAtcs'];

    constructor(
        public entityService: any, 
        public $uibModal: any,
        public CrmAtcs: any
    ) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            // template: require('./modal.html'),
            template: require('./../../Crm/Atcspendencias/modal.html'),
            controller: 'CrmAtcspendenciasFormModalController',
            controllerAs: 'crm_tcspndncs_frm_dflt_cntrllr',
            windowClass: '',
            size: 'lg',
            resolve: {
                entity: () => {
                    if (parameters.identifier) {
                        let entity = this.entityService.get(parameters.identifier);
                        return entity;
                    } else {
                        return angular.copy(subentity);
                    }
                },
                constructors: () => {
                    return parameters;
                },
                telaorigem: () => {
                    return parameters.telaorigem;
                }
            }
        });
    }

}
