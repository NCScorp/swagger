import angular = require('angular');
import { NsClientes } from './../../Ns/Clientes/factory';
import { CamposcustomizadosService } from '../../Commons/camposcustomizados/CamposcustomizadosService';
import { ConfiguracaoService } from '../../../inicializacao/configuracao.service';
import { CommonsUtilsService } from '../../Commons/utils.service';
import { RotasSepultamentoModalService } from './modal-rotassepultamento';

export class CrmAtcsVisualizacaocompletaShowController {

    static $inject = [
        'CrmAtcsresponsaveisfinanceirosFormService',
        'CrmAtcstiposdocumentosrequisitantesFormService',
        'CrmPropostasFormService',
        'CrmPropostasFormShowService',
        '$scope',
        'toaster',
        'CrmAtcs',
        'utilService',
        '$http',
        'nsjRouting',
        '$rootScope',
        '$uibModal',
        '$state',
        'NsClientes',
        'CamposcustomizadosService',
        'ConfiguracaoService',
        'CommonsUtilsService',
        'RotasSepultamentoModalService',
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;
    public camposcustomizados: any = {};
    public camposcustomizadosview: any = {};
    public rulesAttr: any = {
        'negocioarea': 'this.entity.area.negocioarea',
        'possuiseguradora': 'this.entity.possuiseguradora',
        'camposcustomizados': 'this.entity.camposcustomizados',
        '\\': '',
    };
    public campoCustomizadoVisibilidadeConfig: any = [];
    public camposcustomizadosVelorioSepultamento: any = [];
    public camposcustomizadosTranslado: any = [];
    public config;

    public tooltipCidadesInformacoesFunerariasSepultamento: string;
    public tooltipCidadesInformacoesFunerarias: string;

    constructor(
        public CrmAtcsresponsaveisfinanceirosFormService: any,
        public CrmAtcstiposdocumentosrequisitantesFormService: any,
        public CrmPropostasFormService: any,
        public CrmPropostasFormShowService: any,
        public $scope: any,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public $rootScope: any,
        public $uibModal: any,
        public $state: angular.ui.IStateService,
        public NsClientes: NsClientes,
        public CamposcustomizadosService: CamposcustomizadosService,
        private configuracaoService: ConfiguracaoService,
        public CommonsUtilsService: CommonsUtilsService,
        public RotasSepultamentoModalService: RotasSepultamentoModalService) {
    }

    /* Carregamento */
    $onInit() {
        this.loadDadosCidadesInformacoesFunerariasSepultamento();
        this.loadDadosCidadesInformacoesFunerarias();

        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue && this.form != null ) {
                this.form.$setDirty();
            }
        }, true);
        this.$scope.$on('crm_atcstiposdocumentosrequisitantes_loaded', () => {
            this.busy = false;
        });

        // Início campos customizados
        this.getCamposCustomizados();

        this.campoCustomizadoVisibilidadeConfig = {
          scope: this,
          rule: /possuiseguradora|negocioarea|camposcustomizados|\\/g,
          dictionary: this.rulesAttr,
          stringIndex: 'indexcampocustomizado'
        };

        // Fim campos customizados

        this.defineExibeNomeFalecido();

        const divLayout = document.querySelector('.nsj-form');
        divLayout.classList.add('nsj-form-view');



        if (this.entity && (typeof this.entity.camposcustomizados == 'string')) {
            this.entity.camposcustomizados = JSON.parse(this.entity.camposcustomizados);

        }

        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue && this.form) {
                this.form.$setDirty();
            }
        }, true);

        this.busy = true;
        
        if (this.entity && this.entity.negocio) {
          for (let i in this.entity.responsaveisfinanceiros) {
            this.busy = true;
            this.entity.respfinanceiroprincipal = angular.copy(
              this.entity.responsaveisfinanceiros[i].responsavelfinanceiro.nomefantasia
            );
            this.NsClientes.get(this.entity.responsaveisfinanceiros[i].responsavelfinanceiro.cliente);
            this.$scope.$on('ns_clientes_loaded', (event: any, args: any) => {
              this.entity.responsaveisfinanceiros.forEach((respfin) => {
                if (respfin.responsavelfinanceiro.cliente == args.cliente) {
                  respfin.responsavelfinanceiro.telefone =
                    args.contatos.length > 0 && args.contatos[0].telefones.length > 0
                      ? args.contatos[0].telefones[0].ddi +
                        args.contatos[0].telefones[0].ddd +
                        args.contatos[0].telefones[0].telefone
                      : null;
                  if (args.contatos.length > 0 && args.contatos[0].telefones.length > 0) {
                    this.entity.respfinanceiroddi = args.contatos[0].telefones[0].ddi;
                    this.entity.respfinanceiroddd = args.contatos[0].telefones[0].ddd;
                    this.entity.respfinanceirotelefone = args.contatos[0].telefones[0].telefone;
                  }
                  if (respfin.principal) {
                    this.entity.respfinanceiroprincipaltelefone =
                      args.contatos.length > 0 && args.contatos[0].telefones.length > 0
                        ? args.contatos[0].telefones[0].ddi +
                          args.contatos[0].telefones[0].ddd +
                          args.contatos[0].telefones[0].telefone
                        : null;
                    this.entity.respfinanceiroprincipaltelefoneramal =
                      args.contatos.length > 0 && args.contatos[0].telefones.length > 0
                        ? args.contatos[0].telefones[0].ramal
                        : null;
                  }
      
                  this.busy = false;
                }
              });
            });
          }
        }

        // Controle de exibição do campo "Funerária envolvida" e "Falecido liberado" na visualização dos dados do falecido no modo visualização completa
        if (this.action == 'retrieve' && this.entity.camposcustomizadosFalecidoViewCompleto.length > 0) {
            let idCampoCustomizado;

            //Se o atendimento possuir campos customizados de falecido, faço as atribuições
            if(this.entity.camposcustomizadosFalecidoViewCompleto[0].objeto.length > 0){
                idCampoCustomizado = this.entity.camposcustomizadosFalecidoViewCompleto[0].campocustomizado;

                // Controle para os campos falecido liberado e funerária envolvida serem exibidos corretamente na visualização
                if(this.entity.camposcustomizados[idCampoCustomizado] != null){
                    let objCampoCustomizadoFalecido = this.entity.camposcustomizados[idCampoCustomizado][0];
                    objCampoCustomizadoFalecido.falecidoliberado = objCampoCustomizadoFalecido.falecidoliberado == true ? "Sim" : "Não";
                    objCampoCustomizadoFalecido.funerariaenvolvida = objCampoCustomizadoFalecido.funerariaenvolvida == true ? "Sim" : "Não";

                    //Se possuir CPF, coloco máscara para visualização
                    if(objCampoCustomizadoFalecido.cpf != null && objCampoCustomizadoFalecido.cpf != ""){
                        objCampoCustomizadoFalecido.cpf = this.CommonsUtilsService.getCpfFormatado(objCampoCustomizadoFalecido.cpf);
                    }

                }

            }           

        }

    }

    definePerfilAcionamento(numeroPerfil) {
        let perfilAcionamento: string;
        switch (numeroPerfil) {
          case 1:
            perfilAcionamento = 'Concessão';
            break;
          case 2:
            perfilAcionamento = 'Municipal';
            break;
          case 3:
            perfilAcionamento = 'Regular';
            break;
          case 4:
            perfilAcionamento = 'Rodízio';
            break;
          default:
            break;
        }
        return perfilAcionamento;
      }

      /**
   * Recebe os dados funerários de uma cidade por parâmetro e retorna o tooltip com essas informações
   * @param dadosFunerarios 
   */
   carregaTooltipInfoFunerarias(dadosFunerarios: any) {

    const possuiSVO = dadosFunerarios.possuisvo === true ? 'Sim' : 'Não';
    const possuiIML = dadosFunerarios.possuiiml === true ? 'Sim' : 'Não';
    const possuiCrematorio = dadosFunerarios.possuicrematorio === true ? 'Sim' : 'Não';
    const possuiCrematorioMunicipal = dadosFunerarios.possuicemiteriomunicipal === true ? 'Sim' : 'Não';
    const possuiCapelaMunicipal = dadosFunerarios.possuicapelamunicipal === true ? 'Sim' : 'Não';
    const trabalhaComFloresNaturais = dadosFunerarios.trabalhacomfloresnaturais === true ? 'Sim' : 'Não';
    let perfilAcionamento = this.definePerfilAcionamento(dadosFunerarios.perfilfunerario) != null ? this.definePerfilAcionamento(dadosFunerarios.perfilfunerario) : 'Não Informado';

    return `INFORMAÇÕES FUNERÁRIAS<br>
            Possui SVO: ${possuiSVO}.<br>
            Possui IML: ${possuiIML}.<br>
            Possui Crematório: ${possuiCrematorio}.<br>
            Possui Cemitério Municipal: ${possuiCrematorioMunicipal}.<br>
            Possui Capela Municipal: ${possuiCapelaMunicipal}.<br>
            Trabalha com flores naturais: ${trabalhaComFloresNaturais}.<br>
            Perfil acionamento: ${perfilAcionamento}.`;

  }

  loadDadosCidadesInformacoesFunerariasSepultamento() {
    if (this.entity.localizacaomunicipiosepultamento == undefined) {
      this.tooltipCidadesInformacoesFunerariasSepultamento = 'Cidade sem dados';
      return;
    }

    if (this.entity.localizacaomunicipiosepultamento.ibge == null && this.entity.localizacaomunicipiosepultamento.codigo != null) {
      this.entity.localizacaomunicipiosepultamento.ibge = this.entity.localizacaomunicipiosepultamento.codigo;
    }

    this.$http({
      method: 'GET',
      url: this.nsjRouting.generate(
        'ns_cidadesinformacoesfunerarias_index',
        { municipio: this.entity.localizacaomunicipiosepultamento.ibge },
        true, true
      ),
    }).then((response: any) => {
      if (response.data.length <= 0) {
        this.tooltipCidadesInformacoesFunerariasSepultamento = 'Cidade sem dados';
      } else {
        const dadosCidadefunerarias = response.data[0];
        this.tooltipCidadesInformacoesFunerariasSepultamento = this.carregaTooltipInfoFunerarias(dadosCidadefunerarias);
      }
    });
  }

  loadDadosCidadesInformacoesFunerarias() {
    if (this.entity.localizacaomunicipio == null) {
      this.tooltipCidadesInformacoesFunerarias = 'Cidade sem dados';
      return;
    }

    if (this.entity.localizacaomunicipio.ibge == null && this.entity.localizacaomunicipio.codigo != null) {
      this.entity.localizacaomunicipio.ibge = this.entity.localizacaomunicipio.codigo;
    }

    this.$http({
      method: 'GET',
      url: this.nsjRouting.generate(
        'ns_cidadesinformacoesfunerarias_index',
        { municipio: this.entity.localizacaomunicipio.ibge },
        true, true
      ),
    }).then((response: any) => {
      if (response.data.length <= 0) {
        this.tooltipCidadesInformacoesFunerarias = 'Cidade sem dados';
      } else {
        const dadosCidadefunerarias = response.data[0];
        this.tooltipCidadesInformacoesFunerarias = this.carregaTooltipInfoFunerarias(dadosCidadefunerarias);
      }
    });
  }

  private abrirModalRotasGoogle() {
    let modal = this.RotasSepultamentoModalService.open({}, this.entity);
    modal.result.then((entidade: any) => { }).catch((error: any) => { });
  }

    private getCamposCustomizados() {
      this.busy = true;
      Promise.all([
        this.getCamposcustomizadosVelorioSepultamento(),
        this.getCamposcustomizadosTranslado(),
      ]).then(() => {
        this.busy = false;
        this.$scope.$applyAsync();
      });
    }

    async getCamposcustomizadosVelorioSepultamento() {
      const { data } = (await this.$http({
        method: 'GET',
        url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosdoveloriosepultamento' }, true),
      })) as any;
  
      this.camposcustomizadosVelorioSepultamento = data;
    }
  
    async getCamposcustomizadosTranslado() {
      const { data } = (await this.$http({
        method: 'GET',
        url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadostranslado' }, true),
      })) as any;
  
      this.camposcustomizadosTranslado = data;
    }

    /**
     * Seta a propriedade nomeFalecido, que fará o controle da exibição da label do campo nome. Se nomeFalecido for true, mostra label como "Nome do Falecido", se false, mostra apenas "Nome"
     */
    defineExibeNomeFalecido() {
        let config = this.configuracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');
        this.entity['nomeFalecido'] = config == '1' ? true : false;
    }

    crmAtcsresponsaveisfinanceirosForm() {
        let modal = this.CrmAtcsresponsaveisfinanceirosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.responsaveisfinanceiros === undefined) {
                this.entity.responsaveisfinanceiros = [subentity];
            } else {
                this.entity.responsaveisfinanceiros.push(subentity);
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
    
    crmAtcstiposdocumentosrequisitantesForm() {
        let modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.negociostiposdocumentosrequisitantes === undefined) {
                this.entity.negociostiposdocumentosrequisitantes = [subentity];
            } else {
                this.entity.negociostiposdocumentosrequisitantes.push(subentity);
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
    
    crmAtcstiposdocumentosrequisitantesFormEdit(subentity: any) {
        let parameter = { 'negocio': subentity.negocio, 'identifier': subentity.negociotipodocumentorequisitante };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.negociostiposdocumentosrequisitantes) {
                    if ((this.entity.negociostiposdocumentosrequisitantes[key].negociotipodocumentorequisitante !== undefined && this.entity.negociostiposdocumentosrequisitantes[key].negociotipodocumentorequisitante === subentity.negociotipodocumentorequisitante)
                        || (this.entity.negociostiposdocumentosrequisitantes[key].$id !== undefined && this.entity.negociostiposdocumentosrequisitantes[key].$id === subentity.$id)) {
                        this.entity.negociostiposdocumentosrequisitantes[key] = subentity;
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