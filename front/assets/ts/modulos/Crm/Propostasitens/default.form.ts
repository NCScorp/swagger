import angular = require('angular');
import { GooglePlacesService } from './../../Commons/googlemaps/places.factory';
import { readRule, evalString } from '../../Commons/utils';
export class CrmPropostasitensDefaultController {
    static $inject = [
        '$scope',
        'toaster',
        'CrmPropostasitens',
        'utilService',
        'nsjRouting',
        '$http',
        'GooglePlacesService',
        'moment'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public camposcustomizadosCache = [{}];
    public endereco: boolean;
    public dataorigem: boolean;

    public rulesAttr: any = {
        'negocioarea': 'this.entity.area.negocioarea',
        'possuiseguradora': 'this.entity.possuiseguradora',
    };


    public enderecogoogle: any;

    constructor(
        public $scope: any,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        public nsjRouting: any,
        public $http: any,
        public googlePlacesService: GooglePlacesService,
        public moment: any
    ) {
        // this.getCampoCustomizado();
    }

    $onInit() {
        if(this.entity.camposcustomizados === undefined){
            this.entity.camposcustomizados = {};
        }


        if(this.entity.propostasitensenderecos && this.entity.propostasitensenderecos.length>0 ) {
            this.entity.propostasitensenderecos.forEach(element => {
                this.associaendereco(element);            
            });
        } else {
            this.entity.propostasitensenderecos = [];
            this.entity.propostasitensenderecos[0] = { localcadastrado : false}
        }
    }

    checkswitch(item){
        if (item.localcadastrado==true){
            item.enderecomanual = false;

        } else {
            item.enderecoid = undefined;
            item.enderecomanual = true;
        }
    }

    associaendereco(endereco){
        if (endereco.enderecoid != null && endereco.enderecoid != undefined) {

            endereco.tipologradouro = endereco.enderecoid.tipologradouro;
            endereco.logradouro = endereco.enderecoid.rua;
            endereco.numeroendereco = endereco.enderecoid.numero;
            endereco.complemento = endereco.enderecoid.complemento;
            endereco.bairro = endereco.enderecoid.bairro;
            endereco.pais = endereco.enderecoid.pais;
            endereco.ibge = endereco.enderecoid.municipio;
            endereco.cep = endereco.enderecoid.cep;
            endereco.uf = endereco.enderecoid.estado;
            endereco.localcadastrado = true;
        } else {
            endereco.localcadastrado = false;
            endereco.enderecomanual = true;
        }

        if(this.entity.previsaohorainicio != undefined){
            this.entity.previsaohorainicio = new Date("1970-01-01" + " " + this.moment(new Date("1970-01-01" + " " + this.entity.previsaohorainicio)).format('H:mm'));
        }

        if(this.entity.previsaohorafim != undefined){
            this.entity.previsaohorafim = new Date("1970-01-01" + " " + this.moment(new Date("1970-01-01" + " " + this.entity.previsaohorafim)).format('H:mm'));
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

    getChecklist(composicao: any) {
        this.entity.itemdefaturamentovalor = this.entity.composicao.servicotecnico.valor;
        if (composicao.servicotecnico !== undefined) {
            this.entity.propostasitensenderecos = [];
            for (var i = 0; i < composicao.servicotecnico.tipo + 1; i++) {
                let obj = {
                    'ordem': i,
                    'enderecomanual': true
                }
                this.entity.propostasitensenderecos.push(obj);
            }
        } else {
            this.entity.propostasitensenderecos = [];
        }
    };

    especificarEndereco({checked}){
        this.endereco = checked;
    }

    dataOrigem({checked}){
        this.dataorigem = checked;
    }

    change({ detail }) {
        const campos = detail.data;

        Object.keys(campos).forEach(key => {
            this.entity.camposcustomizados = {
                 [key] :campos[key]
            };
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
    
    submit() {
        this.form.$submitted = true;
        if (this.form.$valid && !this.entity.$$__submitting) {
            return new Promise((resolve) => {
                this.entityService._save(this.entity).then((response: any) => {
                    this.entity.propostaitem = response.data.propostaitem;
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