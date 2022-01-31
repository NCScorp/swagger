import angular = require('angular');
import { ISecaoController } from '../Atcs/classes/isecaocontroller';
export class CrmAtcsRelacionadosController {

    static $inject = [
        '$scope', 
        'toaster', 
        'CrmAtcs', 
        'utilService', 
        '$http', 
        'nsjRouting', 
        '$rootScope', 
        'entity',
        'secaoctrl'
    ];

    public form: any;
    public $stateParams: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public atcsfilhos: any = [];
    public busyNegRel: boolean = false;
    private busyInicializacao: boolean = false;
    public inicializado: boolean = false;
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    /**
     * Faz o controle se o accordion de atcs relacionados já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    constructor(
        public $scope: angular.IScope, 
        public toaster: any, 
        public entityService: any, 
        public utilService: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public $rootScope: any, 
        public entity: any,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController
    ) {
        entityService.constructors = {};

        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
            }

            //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar dados do accordion
            if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                this.busyInicializacao = true;
                // Chamo função que carrega dados da tela;
                this.getAtcsFilhos();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }
    }

    Init(){
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;
        }
    }

  getAtcsFilhos() {
    // Descomentar quando houverem dados para que mostre o loading
    this.busyNegRel = true;
    this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('crm_atcs_index', { 'negociopai': [{ 'condition': 'eq', 'value': (<any>this).entity.negocio },] }, false)
    }).then((response: any) => {
      this.atcsfilhos = [...response.data];
      // Descomentar caso necessite pegar outros campos do atcfilho;
      // this.atcsfilhos = this.getCamposAtcsFilhos(this.atcsfilhos);
      this.busyNegRel = false;

      //Uma vez que o accordion foi carregado, seto a variável de controle para true
      this.accordionCarregado = true;

      this.busyInicializacao = false;
    });
  }

  getCamposAtcsFilhos(atc) {
    this.atcsfilhos.forEach((atc) => {
      this.$http({
        method: 'GET',
        url: this.nsjRouting.generate('crm_atcs_get', { 'id': atc.negocio }, true)
      }).then((response: any) => {
        atc.dados = response.data;
        // Descomentar quando houverem dados para que mostre o loading
        this.busyNegRel = false;
      });
    });
    return atc;
  }

  isBusyNegRel() {
      return this.busyNegRel;
  }

}
