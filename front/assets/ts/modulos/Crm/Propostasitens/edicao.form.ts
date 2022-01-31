import angular = require('angular');
import { GooglePlacesService } from './../../Commons/googlemaps/places.factory';
import { readRule, evalString } from '../../Commons/utils';
export class CrmPropostasitensEdicaoController {

    static $inject = [ 
        '$scope', 
        'toaster', 
        'CrmPropostasitens', 
        'utilService',
        '$http',
        'nsjRouting',
        'GooglePlacesService',
        'moment',
        'CrmComposicoes'
    ];

    public camposcustomizadosCache: any[];
    public rulesAttr: any = {
        'negocioarea': 'this.entity.area.negocioarea',
        'possuiseguradora': 'this.entity.possuiseguradora',
    };

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    public enderecogoogle: any;
    private previsaohorainicio = null;
    private previsaohorafim = null;

    constructor( 
        public $scope: angular.IScope, 
        public toaster: any, 
        public entityService: any, 
        public utilService: any, 
        public $http:angular.IHttpService,
        public nsjRouting: any,
        public googlePlacesService: GooglePlacesService,
        public moment: any,
        public CrmComposicoes: any
        ) {
        // this.getCampoCustomizado();
    }

    async $onInit() {
        this.entity.propostasitensenderecos.forEach((element, index) => {
            if(element.enderecoid.endereco === null) {
                element.enderecomanual = true;
            }
            element.ordem = (element.ordem != null ? element.ordem : index);
        });

        if(this.entity.propostasitensenderecos && this.entity.propostasitensenderecos.length>0 ) {
            this.entity.especificarendereco = true;
        } else {
            this.entity.especificarendereco = false;
            this.entity.propostasitensenderecos = [];
            this.entity.propostasitensenderecos[0] = { localcadastrado : false, ordem: 0}

            //Caso não tenha endereço cadastrado, verifico se a composição é do tipo transporte
            // para saber se apresento um endereço para cadastrar ou dois
            this.busy = true;
            try {
                const composicao = await this.CrmComposicoes.get(this.entity.composicao.composicao);
                if (composicao.servicotecnico.tipo == 1) {
                    this.entity.propostasitensenderecos.push({ localcadastrado : false, ordem: 1 });
                }
                this.busy = false;
            } catch (error) {
                this.busy = false;
            }
        }

        this.entity.propostasitensenderecos = this.entity.propostasitensenderecos.sort((itemA, itemB) => {
            if (itemA != null && itemB!=null){
                return (itemA < itemB ? -1 : 1);
            } else {
                return 0;
            }
        });

        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
        }}, true);

        this.$scope.$watch('$ctrl.previsaohorainicio', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.entity.previsaohorainicio = this.moment(this.previsaohorainicio).format('YYYY-MM-DD H:mm:ss');
                
        }}, true);
        this.$scope.$watch('$ctrl.previsaohorafim', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.entity.previsaohorafim = this.moment(this.previsaohorafim).format('YYYY-MM-DD H:mm:ss');
        }}, true);

        if(typeof this.entity.camposcustomizados === 'string') {
            this.entity.camposcustomizados =  JSON.parse(this.entity.camposcustomizados);
        }
        
        if(this.entity.previsaohorainicio != undefined){
            this.previsaohorainicio = this.moment(this.entity.previsaodatahorainicio).toDate();
        }

        if(this.entity.previsaohorafim != undefined){
            this.previsaohorafim = this.moment(this.entity.previsaodatahorafim).toDate();
        }
    }

    preencherEnderecoGoogle(item:any){

        this.googlePlacesService.get(this.enderecogoogle.place_id).then((result)=>{
          /* 
          * itens do endereço digitados a mão
          */
          item.bairro = result.bairro;
          item.logradouro = result.logradouro;
          item.numeroendereco = result.number;
          item.cep = result.cep;
    
          /*
          * itens do endereço escolhidos por lookup
          */
          if(result.pais.pais!=null && result.pais.pais!=undefined){
            item.pais = result.pais;
          } 
    
          if(result.estado.uf!=null && result.estado.uf!=undefined){
            item.estado = result.estado;
          } 
          
          if(result.municipio.codigo!=null && result.municipio.codigo!=undefined){
            item.ibge = result.municipio;
          }
           
          if(result.tipologradouro.tipologradouro!=null && result.tipologradouro.tipologradouro!=undefined){
            item.tipologradouro = result.tipologradouro;
          } 
    
          this.$scope.$applyAsync();
        });
    }

    change({ detail }) {
        const campos = detail.data;

        Object.keys(campos).forEach(key => {
            this.entity.camposcustomizados = {
                [key] : campos[key]
            }
        })
    }
    
     /**
     * @todo
     * Definir quais campos customizados devem aparecer 
     */
    async getCampoCustomizado() {
        const { data } = await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosResponsavel' }, true)
        }) as any;
       
        this.camposcustomizadosCache = this.tratarVisibilidade(data);

    }

    tratarVisibilidade(campos = []) {
        campos.forEach(campo => {
            if (typeof campo.visible === 'string') {
                const regraVisibilidade = readRule(campo.visible, /^area|^possuiseguradora|^negocioarea/, this.rulesAttr);
                campo.visible = evalString(this, regraVisibilidade);
            }

            if (campo.objeto && campo.objeto.length > 0) {
                this.tratarVisibilidade(campo.objeto);
            }
        })

        return campos;
    }
    // $onInit() {
    //     this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
    //         if (newValue !== oldValue) {
    //             this.form.$setDirty();
    //         }
    //     }, true);
    // }
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