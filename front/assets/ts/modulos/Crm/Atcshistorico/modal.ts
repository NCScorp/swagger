import angular = require('angular');
export class CrmHistoricoAtcFormModalController {
    static $inject = [ 'toaster', '$uibModalInstance', 'entities', 'constructors', '$http', 'nsjRouting', 'moment'];
    public action: string;
    public form: any;
    public submitted: boolean = false;

    /**
     * Filtros utilizados para carregar o painel
     */
    private filtros: any = {
        dataInicial: null,
        dataFinal: null,
        filter: null,
    };

    constructor(
        public toaster: any,
        public $uibModalInstance: any, 
        public entities: any,
        public constructors: any,
        public $http: any,
        public nsjRouting: any,
        public moment: any,
    ) {}

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    carregarPainel (evento = null) {
        if(evento != null && evento.search != null){
            this.filtros.filter = evento.search;
        }
        this.getLista(this.constructors, this.filtros);
    }

    getLista(parameters, filters = null){

        delete parameters.created_at;

        let filtersObject = {
            'created_at': {
            }
        };

        if(filters == null || filters.filter == null) {
            filters.filter = '';
        }

        if ((filters.dataInicial != null && filters.dataInicial != '') &&
            (filters.dataFinal == null || filters.dataFinal == '')
        ){
            //se data ini preenchido mas data fim não preenchido
            this.toaster.pop({
                type: 'error',
                title: 'Favor preencher a data final para filtrar.'
            });
            return;
        } else if ((filters.dataFinal != null && filters.dataFinal != '') &&
            (filters.dataInicial == null || filters.dataInicial == '')
        ){
            //se data fim preenchido mas data ini não preenchido
            this.toaster.pop({
                type: 'error',
                title: 'Favor preencher a data inicial para filtrar.'
            });
            return;
        }

        if(filters != null && (
            (filters.dataInicial != null && filters.dataInicial != '') &&
            (filters.dataFinal != null && filters.dataFinal != ''))
        ){
            let dataInicialCompare = this.moment(filters.dataInicial);
            let dataFinalCompare = this.moment(filters.dataFinal);
            
            if (dataInicialCompare.diff(dataFinalCompare) > 0) {
                this.toaster.pop({
                    type: 'error',
                    title: 'A data inicial não pode ser maior que a data final.'
                });
                return;
            } else if (dataFinalCompare.diff(dataInicialCompare) < 0) {
                this.toaster.pop({
                    type: 'error',
                    title: 'A data final não pode ser menor que a data inicial.'
                });
                return;
            }
            
            filtersObject.created_at[0] = ({ 'condition': 'gte', 'value': filters.dataInicial + ' 00:00:00' });
            filtersObject.created_at[1] = ({ 'condition': 'lte', 'value': filters.dataFinal + ' 23:59:59' });
            parameters = angular.extend(parameters, filtersObject);
        }

        return this.$http({
            method: 'GET',
            url: this.nsjRouting.generate(
                'crm_atcs_historicosindex', 
                angular.extend({}, parameters, {'offset': '', 'filter': filters.filter}, {}), 
                true,
                true
            )
        })
        .then((response: any) => {
            this.entities = response.data;
         })
        .catch((response: any) => {
            this.toaster.pop({
                type: 'error',
                title: response
            });
        });
    }

    /**
     * Seta a data inicial e final que serão carregadas nos filtros na inicialização da página
     */
     filtroDatasIniciais(){

        /* 
            Irá carregar inicialmente com filtro semestral. A data inicial será o primeiro dia do mês atual e 
            data final será daqui a 6 meses no último dia do mês
        */

        let dataAtual = new Date;
        let anoAtual = dataAtual.getFullYear();
        let mesAtual = dataAtual.getMonth() + 1;
        
        let mesFinal = mesAtual + 5;

        let diaFinal = this.diasNoMes(mesFinal, anoAtual);
        
        this.filtros.dataInicial = anoAtual + '-' + mesAtual + '-' + '01';
        this.filtros.dataFinal = anoAtual + '-' + mesFinal + '-' + diaFinal;
    }

    /**
     * Retorna o último dia do mês que recebemos por parâmetro. Recebemos também o ano porque o ano pode ser bissexto e com isso o número de dias de fevereiro varia
     * @param mes: o número do mês que se deseja saber o último dia (1 = janeiro, 2 = fevereiro...)
     * @param ano: o número do ano da data que queremos saber o último dia do mês
     */
    diasNoMes (mes, ano) {
        // Função Date em js usa os meses começando em zero. O parâmetro zero na função significa que desejo saber o último dia do mês anterior, 
        // como a função diasNoMes usa os meses começando em 1, funciona corretamente
        return new Date(ano, mes, 0).getDate();
    }

}
export class CrmAtcsFormHistoricoService {
    static $inject = ['CrmHistoricoatcs', '$uibModal', '$http', 'nsjRouting', 'toaster'];
 
    constructor(
        public entityService: any,
        public $uibModal: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any
    ) {}
    
    getLista(parameters){
        return this.$http({
            method: 'GET',
            // url: this.nsjRouting.generate('crm_historicoatcs_index', angular.extend({}, parameters, {'offset': '', 'filter': ''}, {}), true)
            url: this.nsjRouting.generate(
                'crm_atcs_historicosindex', 
                angular.extend({}, parameters, {'offset': '', 'filter': ''}, {}), 
                true,
                true
            )
        })
        .then((response: any) => {
            return response.data;
         })
        .catch((response: any) => {
            this.toaster.pop({
                type: 'error',
                title: response
              });
              return ;
        });
    }
 
    open(parameters: any, subentity: any, filters : any | null) {

            return  this.$uibModal.open({
                template: require('../../Crm/Atcshistorico/default.form.html'),
                controller: 'CrmHistoricoAtcFormModalController',
                controllerAs: 'crm_hstrc_ngc_frm_mdl_cntrllr',
                windowClass: '',
                size: 'lg',
                resolve: {
                    entities: async () => {
                        let entities = [];
                        if(filters){
                            parameters = angular.extend(parameters, filters);
                        }
                        
                        entities = await this.getLista(parameters);
                        return entities;
                    },
                    constructors: () => {
                        return parameters;
                    }
                }
            });
    }
 
}

