import angular = require('angular');

export class CrmPropostasitensFormModalController {
    static $inject = ['toaster', '$uibModalInstance', 'entity', 'constructors', 'moment'];
    public action: string;
    public form: any;
    public submitted: boolean = false;


    constructor(
        public toaster: any,
        public $uibModalInstance: any,
        public entity: any,
        public constructors: any,
        private moment: any
    ) {
        this.action = entity.propostaitem ? (constructors.showBool ? 'show' : 'update') : 'insert';

        if (this.action == 'insert') {
            this.entity.especificarendereco = false;
        }
        else {

        }
    }

    datasPrevisaoValida(){
        if(this.entity.previsaodatainicio > this.entity.previsaodatafim) {
            return false;
        }
        return true;
    }

    /**
     * Verifica se o serviço adicionado permite informar descrição
     */
    verificaSeHaNomeManual(composicao: any){

        //Baseado na composicao recebida, verifico se o serviço permite informar a descrição
        return composicao.servicocoringa ? true : false;

    }

    submit() {

        // Se estiver inserindo nova proposta item, preciso consultar a sua composição para saber se permite informar a descrição. Na edição a entity já tem essa propriedade, que é imutável
        if(this.action == 'insert'){
            this.entity.nomeservicoalterado = this.verificaSeHaNomeManual(this.entity.composicao);
        }

        if(!this.datasPrevisaoValida()){
            this.toaster.pop({
                type: 'error',
                title: 'A data de fim não pode ser menor que a data de início.'
            });
            return;
        }

        this.submitted = true;

        if (this.form.$valid) {

            if (this.entity.especificarendereco == false) {
                this.entity.propostasitensenderecos = [];
            }
            this.entity.especificarendereco = `${this.entity.especificarendereco}`;

            //Deixo datas inicio e fim no formato data/hora, caso seja edição
            if (this.action == 'update' &&
                (this.entity.previsaodatainicio != null && this.entity.previsaodatainicio != undefined) &&
                (this.entity.previsaohorainicio != null && this.entity.previsaohorainicio != undefined) &&
                (this.entity.previsaodatafim != null && this.entity.previsaodatafim != undefined) &&
                (this.entity.previsaohorafim != null && this.entity.previsaohorafim != undefined)
            ) {
                //cria objetos do momentjs
                let horaPrevInicio = this.moment(this.entity.previsaohorainicio);
                let horaPrevFim = this.moment(this.entity.previsaohorafim);
                //se não for válida a data informada, tento fazer considerando só hora minuto segundo.
                if (!horaPrevInicio.isValid()) {
                    horaPrevInicio = this.moment(this.entity.previsaohorainicio, 'HH:mm:ss');
                }
                if (!horaPrevFim.isValid()) {
                    horaPrevFim = this.moment(this.entity.previsaohorafim, 'HH:mm:ss');
                }

                this.entity.previsaodatahorainicio = this.moment(this.entity.previsaodatainicio).format('YYYY-MM-DD') + ' ' + horaPrevInicio.format('HH:mm:ss');
                this.entity.previsaodatahorafim = this.moment(this.entity.previsaodatafim).format('YYYY-MM-DD') + ' ' + horaPrevFim.format('HH:mm:ss');
            }
            //Se estiver atualizando e algum dos campos forem vazios, salvo nulo
            else if (this.action == 'update' &&
                this.entity.previsaodatainicio == null || this.entity.previsaodatainicio == undefined ||
                this.entity.previsaohorainicio == null || this.entity.previsaohorainicio == undefined ||
                this.entity.previsaodatafim == null || this.entity.previsaodatafim == undefined ||
                this.entity.previsaohorafim == null || this.entity.previsaohorafim == undefined
            ) {
                this.entity.previsaodatahorainicio = null;
                this.entity.previsaodatahorafim = null;
            }
            else if (this.action == 'insert') {

                //Se na inserção não estiver marcado para especificar data, passo as previsões como nulas
                if (this.entity.especificadata != true) {
                    this.entity.previsaodatahorainicio = null;
                    this.entity.previsaodatahorafim = null;
                }
                else {
                    this.entity.previsaodatahorainicio = this.moment(this.entity.previsaodatainicio).format('YYYY-MM-DD') + ' ' + this.moment(this.entity.previsaohorainicio).format('HH:mm:ss');
                    this.entity.previsaodatahorafim = this.moment(this.entity.previsaodatafim).format('YYYY-MM-DD') + ' ' + this.moment(this.entity.previsaohorafim).format('HH:mm:ss');
                }
            }

            this.$uibModalInstance.close(this.entity);
        } else {
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

export class CrmPropostasitensFormService {
    static $inject = ['CrmPropostasitens', '$uibModal', 'CrmPropostascapitulos', '$http', 'nsjRouting'];

    constructor(public entityService: any, public $uibModal: any, public CrmPropostasCapitulosService: any, public $http: any, public nsjRouting: any) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./../../Crm/Propostasitens/modal.html'),
            controller: 'CrmPropostasitensFormModalController',
            controllerAs: 'crm_prpststns_frm_dflt_cntrllr',
            windowClass: '',
            resolve: {
                entity: async () => {
                    if (parameters.propostaitem) {
                        let entity = this.entityService.get(parameters.atc, subentity.proposta.proposta, parameters.propostaitem);
                        return entity;
                    } else {
                        let entity = angular.copy(subentity);
                        entity.atc = parameters.atc;

                        if (subentity.propostacapitulo) {
                            entity.propostacapitulo = subentity.propostacapitulo;
                        } else {
                            await this.$http.get(this.nsjRouting.generate('crm_propostascapitulos_get', { 'proposta': subentity.proposta.proposta, 'id': parameters.propostacapitulo }, true, true), {})
                                .then((response: any) => {
                                    entity.propostacapitulo = response.data;
                                });
                        }

                        return entity;
                    }
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }

}
