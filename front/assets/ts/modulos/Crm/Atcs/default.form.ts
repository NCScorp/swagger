import angular = require('angular');

import { GooglePlacesService } from './../../Commons/googlemaps/places.factory';
// import { NsCamposcustomizados } from '@MDA/Ns/Camposcustomizados/factory';
// import { CrmAtcsDefaultController } from '@MDA/Crm/Atcs/default.form';
import { NsCamposcustomizados } from './../../Ns/Camposcustomizados/factory';
import { CamposcustomizadosService } from '../../Commons/camposcustomizados/CamposcustomizadosService';
import { CrmAtcsDadosSeguradorasFormService } from '../Atcsdadosseguradoras/modal';
// import { CrmAtcsdadosseguradoras } from '@MDA/Crm/Atcsdadosseguradoras/factory';
import { CrmAtcsdadosseguradoras } from './../Atcsdadosseguradoras/factory';
import { ConfiguracaoService } from '../../../inicializacao/configuracao.service';
import { RotasSepultamentoModalService } from './modal-rotassepultamento';
interface myIScope extends angular.IScope {
  getModel: any;
}

export class CrmAtcsDefaultController {
  static $inject = [
    '$scope',
    'toaster',
    'CrmAtcs',
    'utilService',
    '$http',
    'nsjRouting',
    'CrmAtcsresponsaveisfinanceirosFormService',
    'CrmAtcstiposdocumentosrequisitantesFormService',
    '$parse',
    'CamposcustomizadosService',
    '$element',
    'NsCamposcustomizados',
    'GooglePlacesService',
    'CrmAtcsDadosSeguradorasFormService',
    'CrmAtcsdadosseguradoras',
    'ConfiguracaoService',
    'RotasSepultamentoModalService',
    'moment',
  ];

  public camposcustomizados: object = {};
  //--- campo customizado --
  public evento: any;
  public data: any;
  public decisao: any;
  public controle: any;
  public entity: any;
  public form: any;
  public constructors: any;
  public collection: any;
  public action: string;
  public idCount: number = 0;
  public camposcustomizadosContatos: any[] = [];

  public camposcustomizadosResponsavel: any[] = [];

  public camposcustomizadosFalecido: any[] = [];
  public camposcustomizadosPaciente: any[] = [];
  public camposcustomizadosVelorioSepultamento: any[] = [];
  public camposcustomizadosTranslado: any[] = [];
  public validos: any = {};
  public camposcustomizadosPet: any;
  public busy: boolean;
  public rulesAttr: any = {
    negocioarea: 'this.entity.area.negocioarea',
    possuiseguradora: 'this.entity.possuiseguradora',
    camposcustomizados: 'this.entity.camposcustomizados',
    '\\': '',
  };

  public enderecogoogle: any;
  public camposcustomizadosCache: Map<string, Object> = new Map();
  config: any;
  public localcadastrado: boolean;
  public possuiseguradoraint: any;
  public tooltipCidadesInformacoesFunerarias: string;
  public tooltipCidadesInformacoesFunerariasSepultamento: string;
  /**
   * Preenchido com o cliente atual, ao clicar no combo de clientes/Seguradora
   */
  private clienteatual: any = null;

  constructor(
    public $scope: myIScope,
    public toaster: any,
    public entityService: any,
    public utilService: any,
    public $http: angular.IHttpService,
    public nsjRouting: any,
    public CrmAtcsresponsaveisfinanceirosFormService: any,
    public CrmAtcstiposdocumentosrequisitantesFormService: any,
    public $parse: any,
    public CamposcustomizadosService: CamposcustomizadosService,
    public $element: angular.IAugmentedJQuery,
    public nsCamposcustomizados: NsCamposcustomizados,
    public googlePlacesService: GooglePlacesService,
    private crmAtcsDadosSeguradorasFormService: CrmAtcsDadosSeguradorasFormService,
    private crmAtcsdadosseguradoras: CrmAtcsdadosseguradoras,
    private configuracaoService: ConfiguracaoService,
    public RotasSepultamentoModalService: RotasSepultamentoModalService,
    private moment: any
  ) {
    this.evento = new CustomEvent('camposcustomizadoload', { detail: new Date() });
    // transformar string em ng-model:
    this.$scope.getModel = (model) => {
      var p = this.$parse(model);
      var s = p.assign;
      return (newVal) => {
        if (newVal) {
          s(this.$scope, newVal);
        }
        return p(this.$scope);
      };
    };
    /* --------------------- */
  }

  $onInit() {
    this.$scope.$watch(
      '$ctrl.entity',
      (newValue, oldValue) => {
        if (newValue !== oldValue) {
          this.form.$setDirty();
        }
      },
      true
    );
    this.$scope.$on('crm_atcstiposdocumentosrequisitantes_loaded', () => {
      this.busy = false;
    });

    // Início campos customizados
    this.getCamposCustomizados();


    this.config = {
      scope: this,
      rule: /possuiseguradora|negocioarea|camposcustomizados|\\/g,
      dictionary: this.rulesAttr,
      stringIndex: 'indexcampocustomizado',
    };
    // Fim campos customizados

    this.defineExibeMunicipioSepultamento();
    this.defineExibeNomeFalecido();

    // Se for edição chamo as funções para carregar informações dos tooltips de município e município de sepultamento
    if (this.action == 'update') {
      this.loadDadosCidadesInformacoesFunerarias();
      this.loadDadosCidadesInformacoesFunerariasSepultamento();
    }
    this.localcadastrado = true;
    this.entity.camposcustomizadosvalidos = true;
    this.entity.possuiseguradora = this.temSeguradora(this.entity);
    this.entity.possuiseguradoraint = this.entity.possuiseguradora ? 1 : 0;
    this.entity.clienteresponsavelfinanceiro =
      this.entity.cliente != null &&
      this.entity.cliente.cliente != null &&
      this.entity.responsaveisfinanceiros != null &&
      this.entity.responsaveisfinanceiros.findIndex((respFinFind) => {
        return respFinFind.responsavelfinanceiro.cliente == this.entity.cliente.cliente;
      }) > -1;
    this.clienteatual = this.entity.cliente;

    if (this.entity.camposcustomizados === undefined) {
      this.entity.camposcustomizados = {};
    }

    if (this.entity && typeof this.entity.camposcustomizados == 'string') {
      this.entity.camposcustomizados = angular.copy(angular.fromJson(this.entity.camposcustomizados));
    }

    // Se for edição de um atendimento comercial, não carrego informações da área de atendimento comercial
    if (this.action != 'update') {
      this.carregaInfoAreaAtc(this.entity.area);
    }

    this.$scope.$watch('$ctrl.entity.localizacaocarregar', (newValue: any, oldValue: any) => {
      if (newValue != null) {
        this.entity.localizacaocidade = {};
        this.entity.localizacaocidade = angular.extend(this.entity.localizacaocidade, newValue.cidade);
        this.entity.localizacaocep = newValue.cep;
        this.entity.localizacaobairro = newValue.bairro;
        this.entity.localizacaorua = newValue.rua;
        this.entity.localizacaonumero = newValue.numero;
        this.entity.localizacaocomplemento = newValue.complemento;
        this.entity.localizacaoreferencia = newValue.referencia;
        this.entity.localizacaoestado = newValue.estado;
        this.entity.localizacaomunicipio = angular.extend(newValue.municipio, { uf: this.entity.localizacaoestado });
        this.entity.localizacaotipologradouro = newValue.tipologradouro;
        this.entity.localizacaonome = newValue.nome;
        this.localcadastrado = true;
      }
    });

    this.$scope.$on('crm_atcsdadosseguradoras_delete_error', (factoryEntity: any, data: any) => {
      this.toaster.pop({
        type: 'error',
        title: data.response.data.message,
      });
    });

    this.$scope.$on('crm_atcsdadosseguradoras_deleted', (factoryEntity: any, data: any) => {
      if (data.response.data[1] !== undefined) {
        let index = this.entity.negociosdadosseguradoras.findIndex((element) => {
          if (element.negociodadosseguradora == data.response.data[1]) {
            return true;
          }
        });

        this.entity.negociosdadosseguradoras.splice(index, 1);
      }

      this.toaster.pop({
        type: 'success',
        title: 'Seguro removido.',
      });
    });

    //seta pais padrão brasil
    if (this.entity.localizacaopais == undefined) {
      this.entity['localizacaopais'] = {
        full_count: 1,
        nome: 'BRASIL',
        pais: '1058',
      };
    }

    if (!this.entity.localizacaosalvar) {
      this.entity.localizacaosalvar = '1';
    }

    if (!this.entity.localizacaonovasalvar) {
      this.entity.localizacaonovasalvar = '1';
    }

    if (!this.entity.localizacaoexistentesalvar) {
      this.entity.localizacaoexistentesalvar = '1';
    }

    // Controle para fazer os campos funerariaenvolvida e falecidoliberado voltar com os valores booleanos.
    // Controle necessário porque na visualização eles são exibidos como texto com "Sim" ou "Não"
    if (this.action == 'update' && this.entity.camposcustomizadosFalecidoViewCompleto.length > 0) {
      let idCampoCustomizado = this.entity.camposcustomizadosFalecidoViewCompleto[0].campocustomizado;
      let campofunerariaenvolvida = this.entity.camposcustomizados[idCampoCustomizado][0].funerariaenvolvida;
      let campofalecidoliberado = this.entity.camposcustomizados[idCampoCustomizado][0].falecidoliberado;

      if (campofunerariaenvolvida == 'Sim') {
        this.entity.camposcustomizados[idCampoCustomizado][0].funerariaenvolvida = true;
      } else if (campofunerariaenvolvida == 'Não') {
        this.entity.camposcustomizados[idCampoCustomizado][0].funerariaenvolvida = false;
      }

      if (campofalecidoliberado == 'Sim') {
        this.entity.camposcustomizados[idCampoCustomizado][0].falecidoliberado = true;
      } else if (campofalecidoliberado == 'Não') {
        this.entity.camposcustomizados[idCampoCustomizado][0].falecidoliberado = false;
      }

      //Mantendo somente dígitos no campo CPF do falecido, caso possua um
      if (this.entity.camposcustomizados[idCampoCustomizado][0].cpf != null && this.entity.camposcustomizados[idCampoCustomizado][0].cpf != "") {
        this.entity.camposcustomizados[idCampoCustomizado][0].cpf = this.entity.camposcustomizados[idCampoCustomizado][0].cpf.replace(/[^\d]/g, "");
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

  private abrirModalRotasGoogle() {
    let modal = this.RotasSepultamentoModalService.open({}, this.entity);
    modal.result.then((entidade: any) => { }).catch((error: any) => { });
  }

  defineExibeMunicipioSepultamento() {
    let config = this.configuracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');
    this.entity['exibeMunicipioSepultamento'] = config == '1' ? true : false;
    this.entity['complementoCaption'] = config == '1' ? 'do Falecido' : '';
  }

  /**
   * Seta a propriedade nomeFalecido, que fará o controle da exibição da label do campo nome. Se nomeFalecido for true, mostra label como "Nome do Falecido", se false, mostra apenas "Nome"
   */
  defineExibeNomeFalecido() {
    let config = this.configuracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');
    this.entity['nomeFalecido'] = config == '1' ? true : false;
  }

  /**
   * Evento chamado quando o botão possui seguradora é clicado, seja para marcar SIM ou NÃO
   */
  private onBtnPossuiSeguradoraClick(pPossuiSeguradora: boolean) {
    // Se estiver submetendo formulário, ou clicar no valor já selecionado, saio da função
    if (this.entity.$$__submitting || this.entity.possuiseguradora === pPossuiSeguradora) {
      return;
    }

    let mudancaConfirmada = true;
    let clienteNasResponsabilidadesFinanceiras =
      this.entity.cliente != null &&
      this.entity.cliente.cliente != null &&
      this.entity.responsaveisfinanceiros.findIndex((respFinFind) => {
        return respFinFind.responsavelfinanceiro.cliente == this.entity.cliente.cliente;
      }) > -1;

    if (!this.entity.possuiseguradora) {
      // Mudou de cliente para seguradora
      if (clienteNasResponsabilidadesFinanceiras) {
        mudancaConfirmada = confirm('Mudar o cliente irá apagá-lo dos responsáveis financeiros. Deseja continuar?');
      }
    } else {
      // Mudou de seguradora para cliente
      if (this.temProdutoSeguradora(this.entity.negociosdadosseguradoras)) {
        mudancaConfirmada = confirm('Essa ação removerá os dados da seguradora. Deseja Confirmar?');
      }
    }

    // Se usuário confirmar mudança, atualizo FLAG de "possuiseguradora" e removo cliente atual
    if (mudancaConfirmada) {
      if (this.entity.cliente && this.entity.cliente.cliente) {
        this.removerClienteDosResponsaveisFinanceiros(this.entity.cliente.cliente);
      }

      // Se tinha seguradora, removo dados da seguradora
      if (this.entity.possuiseguradora) {
        this.entity.negociosdadosseguradoras = [];
      }

      this.entity.possuiseguradora = !this.entity.possuiseguradora;
      this.entity.cliente = null;
      this.clienteatual = null;
      this.entity.clienteresponsavelfinanceiro = false;
    }

    this.entity.possuiseguradoraint = this.entity.possuiseguradora ? 1 : 0;
  }

  /**
   * Remove cliente dos responsáveis financeiros
   * @param cliente
   */
  private removerClienteDosResponsaveisFinanceiros(cliente: string) {
    // Busco antigo cliente nos responsaveis financeiros
    const clienteRespFinIndex = this.entity.responsaveisfinanceiros.findIndex((respFinFind) => {
      return respFinFind.responsavelfinanceiro.cliente == cliente;
    });

    this.entity.responsaveisfinanceiros.splice(clienteRespFinIndex, 1);
  }

  temProdutoSeguradora(seguradora = []) {
    if (seguradora.length && seguradora[0].produtoseguradora) {
      return true;
    }
    return false;
  }

  temSeguradora(entity) {
    return Array.isArray(entity.negociosdadosseguradoras) && entity.negociosdadosseguradoras.length > 0;
  }

  /**
   * Evento disparado ao alterar o cliente(Tanto Cliente como Seguradora), pelo combobox.
   */
  alterarResponsavelFinanceiro() {
    // Se tinha um cliente preenchido, verifico se pode mudar
    if (
      this.clienteatual != null &&
      (this.entity.cliente == null ||
        this.entity.cliente.cliente == null ||
        this.clienteatual.cliente != this.entity.cliente.cliente)
    ) {
      let mudancaConfirmada = true;
      let clienteNasResponsabilidadesFinanceiras =
        this.clienteatual.cliente != null &&
        this.entity.responsaveisfinanceiros.findIndex((respFinFind) => {
          return respFinFind.responsavelfinanceiro.cliente == this.clienteatual.cliente;
        }) > -1;

      // Se é seguradora
      if (this.entity.possuiseguradora) {
        if (this.temProdutoSeguradora(this.entity.negociosdadosseguradoras)) {
          mudancaConfirmada = confirm('Essa ação removerá os dados da seguradora. Deseja Confirmar?');
        }
      }
      // Se é cliente
      else {
        if (clienteNasResponsabilidadesFinanceiras) {
          mudancaConfirmada = confirm('Mudar o cliente irá apagá-lo dos responsáveis financeiros. Deseja continuar?');
        }
      }

      if (mudancaConfirmada) {
        if (this.clienteatual.cliente && this.clienteatual.cliente) {
          this.removerClienteDosResponsaveisFinanceiros(this.clienteatual.cliente);
        }
        this.entity.negociosdadosseguradoras = [];
      } else {
        this.entity.cliente = this.clienteatual;
      }
    }

    if (this.entity.cliente != null && this.entity.cliente.cliente != null) {
      this.entity.clienteresponsavelfinanceiro = true;
    } else {
      this.entity.clienteresponsavelfinanceiro = false;
    }

    this.clienteatual = this.entity.cliente;
    this.clientecomoresponsavelfinanceiro();
  }

  preencherEnderecoGoogle() {
    this.googlePlacesService.get(this.enderecogoogle.place_id).then((result) => {
      /*
       * itens do endereço digitados a mão
       */
      this.entity.localizacaobairro = result.bairro;
      this.entity.localizacaorua = result.logradouro;
      this.entity.localizacaonumero = result.number;
      this.entity.localizacaocep = result.cep;

      /*
       * itens do endereço escolhidos por lookup
       */
      if (result.pais.pais != null && result.pais.pais != undefined) {
        this.entity.localizacaopais = result.pais;
      }

      if (result.estado.uf != null && result.estado.uf != undefined) {
        this.entity.localizacaoestado = result.estado;
      }

      if (result.municipio.codigo != null && result.municipio.codigo != undefined) {
        this.entity.localizacaomunicipio = result.municipio;
      }

      if (result.tipologradouro.tipologradouro != null && result.tipologradouro.tipologradouro != undefined) {
        this.entity.localizacaotipologradouro = result.tipologradouro;
      }

      if (result.cidadeestrangeira != null && result.cidadeestrangeira != undefined) {
        this.entity.localizacaocidadeestrangeira = result.cidadeestrangeira;
      }

      this.$scope.$applyAsync();
    });
  }

  private getCamposCustomizados() {
    this.busy = true;
    Promise.all([
      this.getCamposcustomizadosFalecido(),
      this.getCamposcustomizadosPaciente(),
      this.getCamposcustomizadosResposavel(),
      this.getCamposcustomizadosPet(),
      this.getCamposcustomizadosVelorioSepultamento(),
      this.getCamposcustomizadosTranslado(),
    ]).then(() => {
      this.busy = false;
      this.$scope.$applyAsync();
    });
  }

  recarregarCamposCustomizados() {
    this.validos = {};
    this.getCamposCustomizados();
  }

  change({ detail }) {
    const campos = this.fromJson(detail.data);
    this.setDataHoraFalecimento(campos);
    Object.keys(campos).forEach((key) => {
      // Verifica se é do tipo string pois ao salvar camposcustomizados vem como 'string'
      if (this.entity && typeof this.entity.camposcustomizados !== 'string') {
        this.entity.camposcustomizados[key] = angular.copy(campos[key]);
      }
    });
    this.entity.camposcustomizados = angular.copy(this.entity.camposcustomizados);
    this.validos[detail.validity.key] = detail.validity.valid;

    this.entity.camposcustomizadosvalidos = this.temTodosValidos(this.validos);
    this.$scope.$evalAsync();
  }

  setDataHoraFalecimento(campos = {}) {
    const campoFalecido = campos[this.camposcustomizadosFalecido[0].campocustomizado];
    if (campoFalecido) {
      campoFalecido.forEach((campo) => {
        campo.datafalecimento = campo.datafalecimento || this.moment().format('DD/MM/YYYY');
        campo.horafalecimento = campo.horafalecimento || this.moment().format('HH:mm:ss');
      });
      return (campos[this.camposcustomizadosFalecido[0].campocustomizado] = campoFalecido);
    }
  }

  temTodosValidos(objeto: object) {
    return Object.keys(objeto).every((key) => objeto[key] === true);
  }

  fromJson(param) {
    return typeof param === 'string' ? JSON.parse(param) : param;
  }

  toArray(content) {
    return [[content]];
  }

  crmAtcsresponsaveisfinanceirosForm() {
    // let modal = this.CrmAtcsresponsaveisfinanceirosFormService.open({}, {});
    const parameters = { responsaveisfinanceiros: this.entity.responsaveisfinanceiros };
    let modal = this.CrmAtcsresponsaveisfinanceirosFormService.open(parameters, {});
    modal.result
      .then((subentity: any) => {
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
            title: error,
          });
        }
      });
  }

  mudouSeguradora() {
    const condicao = this.entity.possuiseguradora && this.entity.seguradoraapolice === undefined;
    this.camposcustomizadosContatos = this.tratarDisabled(this.camposcustomizadosContatos, condicao);
  }

  async getCamposcustomizadosResposavel() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosResponsavel' }, true),
    })) as any;

    this.camposcustomizadosResponsavel = data;
  }

  async getCamposcustomizadosFalecido() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosFalecido' }, true),
    })) as any;

    this.camposcustomizadosFalecido = data;
  }

  async getCamposcustomizadosPaciente() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosPaciente' }, true),
    })) as any;

    this.camposcustomizadosPaciente = data;
  }

  async getCamposcustomizadosPet() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosPet' }, true),
    })) as any;

    this.camposcustomizadosPet = data;
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

  tratarDisabled(campos = [], valor = false) {
    campos.forEach((campo) => {
      campo.disabled = valor;

      if (campo.objeto && campo.objeto.length > 0) {
        this.tratarDisabled(campo.objeto, valor);
      }
    });

    return campos;
  }

  /**
    * Carrega dados da área de atendimento comercial para setar os campos estabelecimento e possui seguro do atendimento, baseado nos dados carregados da área
    * @param areaAtc 
    */
  carregaInfoAreaAtc(areaAtc) {

    //Se tiver área de atendimento selecionada, faço alterações nos campos
    if (areaAtc) {

      //Ao selecionar a área de atendimento, mudo o campo possui seguro do atendimento para o mesmo valor da área selecionada
      this.onBtnPossuiSeguradoraClick(areaAtc.possuiseguradora);

      //Se a área de atendimento possuir estabelecimento e não tiver um estabelecimento selecionado no atendimento, seleciono o estabelecimento da área no atendimento
      if (areaAtc.estabelecimento != null && areaAtc.estabelecimento.estabelecimento && (!this.entity.estabelecimento || !this.entity.estabelecimento.estabelecimento)) {
        this.entity.estabelecimento = areaAtc.estabelecimento;
      }

      //Se a área de atendimento possuir origem e não tiver uma origem selecionada no atendimento, uso a origem da área de atendimento
      if (areaAtc.origem != null && areaAtc.origem.midia && (!this.entity.origem || !this.entity.origem.midia)) {
        this.entity.origem = areaAtc.origem;
      }
    }
  }

  /* Sobrescrito para inserção do cliente como responsável financeiro, abaixo as diversas verificações para evitar redundância */
  clientecomoresponsavelfinanceiro() {
    if (
      this.entity.responsaveisfinanceiros == null &&
      this.entity.cliente != null &&
      this.entity.cliente.cliente != null
    ) {
      this.entity.responsaveisfinanceiros = [];
    }

    if (this.entity.clienteresponsavelfinanceiro == true && this.entity.cliente != null) {
      let confirm = 0;
      let principal = 0;
      let arr;
      this.entity.responsaveisfinanceiros.forEach((responsavel) => {

        if (responsavel.principal == true) {
          principal++;
        }

        if (responsavel.responsavelfinanceiro.nomefantasia == this.entity.cliente.nomefantasia) {
          confirm++;
        }
      });
      if (confirm < 1) {
        if (principal > 0) {
          arr = { principal: false, responsavelfinanceiro: this.entity.cliente, clienteflag: true }; //clienteflag é o que mostra que o foi criado pela caixa de responsavelfinanceiro e permite sua deleção fácil.
        } else {
          arr = { principal: true, responsavelfinanceiro: this.entity.cliente, clienteflag: true };
        }

        let push = this.entity.responsaveisfinanceiros.push(arr);
      }

    } else if (!this.entity.clienteresponsavelfinanceiro &&
      this.entity.cliente != null &&
      this.entity.cliente.cliente != null) {

      this.removerClienteDosResponsaveisFinanceiros(this.entity.cliente.cliente);

      if (this.entity.responsaveisfinanceiros != null &&
        this.entity.responsaveisfinanceiros.length > 0 &&
        this.entity.responsaveisfinanceiros.findIndex((respFindIndex) => {
          return respFindIndex.principal == true;
        }) > -1) {

        this.entity.responsaveisfinanceiros[0]['principal'] = true;

      }
    }
  }

  crmAtcstiposdocumentosrequisitantesForm() {
    let modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open({}, {});
    modal.result
      .then((subentity: any) => {
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
            title: error,
          });
        }
      });
  }

  crmAtcstiposdocumentosrequisitantesFormEdit(subentity: any) {
    let parameter = { negocio: subentity.negocio, identifier: subentity.negociotipodocumentorequisitante };
    if (parameter.identifier) {
      this.busy = true;
    }
    var modal = this.CrmAtcstiposdocumentosrequisitantesFormService.open(parameter, subentity);
    modal.result
      .then((subentity: any) => {
        let key;
        for (key in this.entity.negociostiposdocumentosrequisitantes) {
          if (
            (this.entity.negociostiposdocumentosrequisitantes[key].negociotipodocumentorequisitante !== undefined &&
              this.entity.negociostiposdocumentosrequisitantes[key].negociotipodocumentorequisitante ===
              subentity.negociotipodocumentorequisitante) ||
            (this.entity.negociostiposdocumentosrequisitantes[key].$id !== undefined &&
              this.entity.negociostiposdocumentosrequisitantes[key].$id === subentity.$id)
          ) {
            this.entity.negociostiposdocumentosrequisitantes[key] = subentity;
          }
        }
      })
      .catch((error: any) => {
        this.busy = false;
        if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
          this.toaster.pop({
            type: 'error',
            title: error,
          });
        }
      });
  }
  submit() {
    this.form.$submitted = true;
    if (this.form.$valid && !this.entity.$$__submitting) {
      return new Promise((resolve) => {
        this.entityService
          ._save(this.entity)
          .then((response: any) => {
            this.entity.negocio = response.data.negocio;
            resolve(this.entity);
          })
          .catch((response: any) => {
            if (typeof response.data.message !== 'undefined' && response.data.message) {
              if (response.data.message === 'Validation Failed') {
                let message = this.utilService.parseValidationMessage(response.data.errors.children);
                this.toaster.pop({
                  type: 'error',
                  title: 'Erro de Validação',
                  body: 'Os seguintes itens precisam ser alterados: <ul>' + message + '</ul>',
                  bodyOutputType: 'trustedHtml',
                });
              } else {
                this.toaster.pop({
                  type: 'error',
                  title: response.data.message,
                });
              }
            } else {
              this.toaster.pop({
                type: 'error',
                title: 'Erro ao adicionar.',
              });
            }
          });
      });
    } else {
      this.toaster.pop({
        type: 'error',
        title: 'Alguns campos do formulário apresentam erros.',
      });
    }
  }
  uniqueValidation(params: any): any {
    if (this.collection) {
      let validation = { unique: true };
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
