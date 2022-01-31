import angular = require('angular');
import { CrmAtcs } from './factory';
import { CrmAtcsFormHistoricoService } from './../Atcshistorico/modal';
import { CrmOrcamentosPreencherModalService } from './orcamento-preencher-modal';
import { CamposcustomizadosService } from '../../Commons/camposcustomizados/CamposcustomizadosService';
import { CrmContratoMaisPaganteModalService } from './contrato-mais-pagante-modal';
import { ConfirmacaoFecharPedidoModalService } from './confirmacao-fechar-pedido-modal';
import { ConfiguracaoService } from '../../../inicializacao/configuracao.service';
import { find, map } from 'core-js/fn/array';
import { NsClientes } from './../../Ns/Clientes/factory';
import { EnviarAtcEmailModalService, EnumTipoEnvioEmail, IParamsTela } from './modal-enviaratcemail';
import { SolicitarOrcamentoEmailModalService } from './modal-solicitar-orcamento-email/modal-solicitar-orcamento-email';
import { EnviarAtcDocsEmailModalService, EnumTipoEnvioEmail as EnumTipoEnvioDocsEmail, IParamsTela as IParamsTelaDocs } from './modal-enviaratcdocsemail';
import { CrmFornecedoresenvolvidos } from '../Fornecedoresenvolvidos/factory';
import { ISecaoController } from './classes/isecaocontroller';
import { NsjIndiceClasses } from '../../Commons/nsjindice/components/nsjindice.classes';
import { TaxaAdministrativaModalService } from './taxa-administrativa-modal';
import { RotasSepultamentoModalService } from './modal-rotassepultamento';
import { Usuarios } from '../../../usuarios/usuarios.service';
import { FullscreenService } from '../../Commons/fullscreen.service';
import { ConfirmacaoFinalizarAtendimentoModalService } from './confirmacao-finalizar-atendimento-modal';
import { ModalVisualizarMaisContatosService } from './modal-visualizar-mais-contatos/modal-visualizar-mais-contatos';
export class CrmAtcsFullControllerSobrescrito {

  static $inject = [
      'utilService',
      '$scope',
      '$stateParams',
      '$state',
      'CrmAtcs',
      'toaster',
      'entity',
      'CrmAtcsFormHistoricoService',
      '$timeout',
      'CrmAtcsFormContratoService',
      'CrmOrcamentosPreencherModalService',
      '$http',
      'CamposcustomizadosService',
      'nsjRouting',
      '$window',
      'CrmContratoMaisPaganteModalService',
      'ConfirmacaoFecharPedidoModalService',
      'ConfiguracaoService',
      'NsClientes',
      'EnviarAtcEmailModalService',
      'EnviarAtcDocsEmailModalService',
      'SolicitarOrcamentoEmailModalService',
      'CrmFornecedoresenvolvidos',
      'TaxaAdministrativaModalService',
      'RotasSepultamentoModalService',
      'Usuarios', 
      'FullscreenService',
      'ConfirmacaoFinalizarAtendimentoModalService',
      '$rootScope',
      'ModalVisualizarMaisContatosService'
  ];
  
  // Declaro enum para utilizar na view
  public EnumTipoEnvioEmail = EnumTipoEnvioEmail;

  public action: string = 'retrieve';
  public busy: boolean = false;
  public constructors: any;
  public isEdit: boolean = false;
  public blockButtons: boolean = true;

  public rulesAttr: any = {
    negocioarea: 'this.entity.area.negocioarea',
    possuiseguradora: 'this.entity.possuiseguradora',
    camposcustomizados: 'this.entity.camposcustomizados',
    '\\': '',
  };

  public mostrarCompletoBool: boolean = false;

  public camposcustomizados: any = {};
  public camposcustomizadosview: any;
  public enderecogoogle: any;
  public submittingFinalizado: boolean = false;
  public submittingReaberto: boolean = false;
  public submittingCancelado: boolean = false;
  public submittingDescancelado: boolean = false;
  public submittingFechado: boolean = false;

  public exibeCamposSepultamento: boolean = false;
  public camposcustomizadosContatos: any = [];

  //campos para serem exibidos no resumo
  public camposcustomizadosResponsavelViewResumo: any = [];
  public camposcustomizadosFalecidoViewResumo: any = [];

  // campos para ser exibidos na visualização completa
  public camposcustomizadosResponsavelViewCompleto: any = [];
  public camposcustomizadosFalecidoViewCompleto: any = [];
  config: any;

  public linksExternos: any;

  public tooltipCidadesInformacoesFunerariasSepultamento: string;
  public tooltipCidadesInformacoesFunerarias: string;

  /**
   * Lista de objetos utilizados para controlar o carregamento das seções do atendimento comercial
   */
  public arrCtrlSecoes: ISecaoController[] = [];

  /**
   * Timeout de scroll de seções, ajuda a realizar o scroll após parar por determinado tempo
   */
  private timerScrollSecao: any = null;
  /**
   * Timeout de scroll de seções, ajuda a subir a ancora pouco antes da posição
   */
  private timerScrollIndice: any = null;

  /**
   * Lista de fornecedores envolvidos no atendimento
   */
  private arrFornecedoresEnvolvidos: any[] = [];
  public configIndice: NsjIndiceClasses.Config;
   /**
  * Informa se algum dos prestadores/fornecedores é da empresa 
  */
    public possuiPrestadorDaEmpresa: boolean = undefined;

  /**
   * Faz o controle da desativação do botão da taxa administrativa na ficha financeira, para impedir mais de um clique nele
   */
  public desativaBtnTaxaAdm: boolean = false;

  /**
   * Array com os prestadores que estão na ficha financeira
   */
  public arrPrestadoresFicha: any[] = [];

  /**
   * Variavel que indica que o cancelamento do atendimento comercial foi desfeito
   */
  public descancelouAtc: boolean = false;

  constructor(
      public utilService: any,
      public $scope: any,
      public $stateParams: any,
      public $state: angular.ui.IStateService,
      public entityService: any,
      public toaster: any,
      public entity: any,
      public CrmAtcsFormHistoricoService: any,
      public $timeout: angular.ITimeoutService,
      public CrmAtcsFormContratoService: any,
      public CrmOrcamentosPreencherModalService: any,
      public $http: angular.IHttpService,
      public CamposcustomizadosService: CamposcustomizadosService,
      public nsjRouting: any,
      public $window: angular.IWindowService,
      public CrmContratoMaisPaganteModalService: CrmContratoMaisPaganteModalService,
      public ConfirmacaoFecharPedidoModalService: ConfirmacaoFecharPedidoModalService,
      private configuracaoService: ConfiguracaoService,
      public NsClientes: NsClientes,
      public EnviarAtcEmailModalService: EnviarAtcEmailModalService,
      public EnviarAtcDocsEmailModalService: EnviarAtcDocsEmailModalService,
      public SolicitarOrcamentoEmailModalService: SolicitarOrcamentoEmailModalService,
      private CrmFornecedoresenvolvidos: CrmFornecedoresenvolvidos,
      public TaxaAdministrativaModalService: TaxaAdministrativaModalService,
      public RotasSepultamentoModalService: RotasSepultamentoModalService,
      /**
       * Service de usuários
       */
      public Usuarios: Usuarios,
      /**
       * Service de fullscreen
       */
      public fullscreenService: FullscreenService,
      public ConfirmacaoFinalizarAtendimentoModalService: ConfirmacaoFinalizarAtendimentoModalService,
      public $rootScope: angular.IRootScopeService,
      public ModalVisualizarMaisContatosService: ModalVisualizarMaisContatosService
     ) {
      entityService.constructors = {};
      for (var i in $stateParams) {
          if (i !== 'entity' && typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q' && i !== 'atc') {
              entityService.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
          }
      }

      this.constructors = entityService.constructors;

      // Configuro índice
      const arrIndiceItens: NsjIndiceClasses.IndiceItem[] = [
          new NsjIndiceClasses.IndiceItem('indice-dadosatc', 'Dados do Atendimento', '#accordion-dados-atendimento'),
          new NsjIndiceClasses.IndiceItem('indice-pendencia', 'Pendências', '#secao-pendencias'),
          new NsjIndiceClasses.IndiceItem('indice-followups', 'Acompanhamento', '#accordion-followups'),
          new NsjIndiceClasses.IndiceItem('indice-pedidos', 'Pedidos', '#secao-pedidos'),
          new NsjIndiceClasses.IndiceItem('indice-fichafinanceira', 'Ficha Financeira', '#secao-fichafinanceira')
      ];
      
      if (this.temPermissao('crm_atcscontasapagar_gerenciar')) {
          arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-contaspagar', 'Contas a pagar', '#secao-contaspagar') );
      }

      arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-prestadorasenvolvidas', 'Prestadoras de Serviços Envolvidas', '#secao-prestadorasenvolvidas') );
      arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-docsautomaticos', 'Documentos Automáticos', '#secao-docsautomaticos') );
      arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-atcsrelacionados', 'Atendimentos Relacionados', '#secao-atcsrelacionados') );
      arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-documentos', 'Documentos', '#secao-documentos') );
      arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-contasreceber', 'Contas a receber', '#secao-contasreceber') );
      arrIndiceItens.push( new NsjIndiceClasses.IndiceItem('indice-projetos', 'Projetos', '#secao-projetos') );

      this.configIndice = new NsjIndiceClasses.Config(
          arrIndiceItens,
          'fa fa-ellipsis-h'
      );
      $scope.$on('verificou_possui_prestador_empresa', (event, possuiPrestadorDaEmpresa) => {
          this.possuiPrestadorDaEmpresa = possuiPrestadorDaEmpresa;
      });

      //Escutando quando ativar o botão de acionar prestador na ficha financeira novamente
      $scope.$on('ativar_botao_acionar_prestador', () => {

        //Obtendo o endereço do prestador que terá o botão reativado
        let indexPrestClicado = this.arrPrestadoresFicha.findIndex( (prestador) => {
          return prestador.desativaBtnAcionar == true;
        });

        //Reativando botão do prestador
        if (indexPrestClicado > -1){
          this.arrPrestadoresFicha[indexPrestClicado].desativaBtnAcionar = false;
        }
        
      });

  }
  /**
   * Verifica se possui permissão
   * @param chave
   */
  temPermissao(chave: any): boolean {
    return this.Usuarios.temPermissao(chave);
  }

  /**
   * Desabilita o botão de acionar prestador na ficha financeira
   * @param dadoFicha O prestador que teve o botão clicado
   * @param arrCompleto O array com todos os prestadores da ficha
   */
  desabilitaBotaoAcionarPrestador(dadoFicha: any, arrCompleto: any){

    //Desativando botão de acionar do prestador clicado
    dadoFicha.desativaBtnAcionar = true;
    this.arrPrestadoresFicha = arrCompleto;
    
  }

  /**
   * Função que retorna o valor da taxa administrativa do atendimento comercial. Caso não possua uma, retorna zero
   * @returns valortaxaadm
   */
  getValorTaxaAdm(){
    if(this.possuiPrestadorDaEmpresa == true){
      return 0;
    }
    return this.entity.valortaxaadm != null ? parseFloat(this.entity.valortaxaadm) : 
      this.entity.configuracaoTaxaAdministrativa?.valor != null ? parseFloat(this.entity.configuracaoTaxaAdministrativa.valor) : 0;
  }

  /**
   * filtra os campos do responsavel para exibir no resumo
   */
  async filtroCamposResponsavelExibicaoResumo() {
    let camposAExibirResponsavel = ['nomeCompleto', 'telefone'];
    //para cada campo dos dados do responsavel
    await this.camposcustomizadosResponsavelViewResumo.forEach(async (campos) => {
      await campos.objeto.forEach(async (objeto) => {
        //verifico os campos permitidos, se for encontrado, assinalo.
        let campoPermitido = false;
        for (let indexB = 0; indexB < camposAExibirResponsavel.length; indexB++) {
          const campoExibir = camposAExibirResponsavel[indexB];
          if (objeto.nome === campoExibir) {
            campoPermitido = true;
          }
        }
        //se ele não foi encontrado na lista, eu retiro a exibição dele.
        if (!campoPermitido) {
          objeto.visible = false;
          objeto.tamanho = 0;
        }
      });
    });
  }

  /**
   * filtra os campos do falecido para exibir no resumo
   */
  async filtroCamposFalecidoExibicaoResumo() {
    let camposAExibir = ['causadofalecimento'];
    //para cada campo dos dados do falecido
    await this.camposcustomizadosFalecidoViewResumo.forEach(async (campos) => {
      await campos.objeto.forEach(async (objeto) => {
        //verifico os campos permitidos, se for encontrado, assinalo.
        let campoPermitido = false;
        for (let indexB = 0; indexB < camposAExibir.length; indexB++) {
          const campoExibir = camposAExibir[indexB];
          if (objeto.nome === campoExibir) {
            campoPermitido = true;
          }
        }
        //se ele não foi encontrado na lista, eu retiro a exibição dele.
        if (!campoPermitido) {
          objeto.visible = false;
          objeto.tamanho = 0;
        }
      });
    });
  }

  /**
   * Faz o eval da condição visible do camposcustomizadosFalecidoViewCompleto
   * por conta de não conseguir passar o dicionario para a permissão poder ser
   * avaliada direto no default.form.show.html.twig
   */
  definirVisualizacaoFalecidoExibicaoCompleto() {
    if (this.entity.area != null) {
      let negocioarea = this.entity.area.negocioarea;
      this.entity['camposcustomizadosFalecidoViewCompleto'].forEach((campocustomizado) => {
        let result = eval(campocustomizado.visible?.replace(/\\/g, ''));
        campocustomizado.visible = result;
      });
    }
  }

  defineCamposSepultamento() {
    let config = this.configuracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');
    this.exibeCamposSepultamento = config == '1' ? true : false;
  }

  /**
   * Define objetos de controle de carregamento das seções do atendimento comercial
   */
  private defineSecoes() {
    this.arrCtrlSecoes = [];
    // Seção Dados do Atendimento
    this.adicionarSecao('accordion-dados-atendimento');
    // Seção Pendências
    this.adicionarSecao('secao-pendencias');
    // Seção Acompanhamento
    this.adicionarSecao('accordion-followups');
    // Seção Pedidos
    this.adicionarSecao('secao-pedidos');
    // Seção Ficha Financeira
    this.adicionarSecao('secao-fichafinanceira');
    // Seção Contas a pagar
    if (this.temPermissao('crm_atcscontasapagar_gerenciar')) {
      this.adicionarSecao('secao-contaspagar');
    }
    // Seção Prestadoras de Serviços Envolvidas
    this.adicionarSecao('secao-prestadorasenvolvidas');
    // Seção Documentos Automáticos
    this.adicionarSecao('secao-docsautomaticos');
    // Seção Atendimentos Relacionados
    this.adicionarSecao('secao-atcsrelacionados');
    // Seção Documentos
    this.adicionarSecao('secao-documentos');
    // Seção Contas a receber
    this.adicionarSecao('secao-contasreceber');
    // Seção Projetos
    this.adicionarSecao('secao-projetos');
  }

  /**
   * Adiciona uma seção a lista
   * @param id
   */
  private adicionarSecao(id: string) {
    this.arrCtrlSecoes.push({ id: id, ativarAtualizacao: () => {}, pararAtualizacao: () => {} });
  }

  /**
   * Disparado quando o scroll do container de seções é realizado
   */
  private onSecaoContainerScroll() {
    if (this.timerScrollSecao != null) {
      clearTimeout(this.timerScrollSecao);
    }

    if (this.timerScrollIndice != null) {
      clearTimeout(this.timerScrollIndice);
    }
   

    // Espero 300 milisegundos para confirmar que parou o scroll
    this.timerScrollIndice = setTimeout(() => {
      // Se realizou scroll atravéz do índice, subo um pouco
      if (window.location.hash != '') {
        window.location.hash = '';
        const scrollCt = document
          .querySelector('.crm-corpo-container')
          .querySelector('#crm-atcs.secao-container-scroll');
        scrollCt.scrollTo(0, scrollCt.scrollTop - 5);
      }
    }, 200);

    // Espero 300 milisegundos para confirmar que parou o scroll
    this.timerScrollSecao = setTimeout(() => {
      this.carregarSecoes();
    }, 300);
  }

  /**
   * Retorna o id das seções apresentadas na tela no momento
   */
  private getIdSecoesAtivas(): string[] {
    // Busco container de scroll
    const scrollCt = document.querySelector('.crm-corpo-container').querySelector('#crm-atcs.secao-container-scroll');

    // Busco itens de accordion
    const arrItens = scrollCt.querySelectorAll('.secao-scroll');

    // Busco itens ativos(visíveis) na tela
    // 60 é o tamanho do bloco de título
    const arrItensAtivos = [];
    arrItens.forEach((itemForeach) => {
      const ativo =
        scrollCt.scrollTop <= (<any>itemForeach).offsetTop + (<any>itemForeach).offsetHeight - 60 &&
        (<any>itemForeach).offsetTop + 60 <= scrollCt.scrollTop + (<any>scrollCt).offsetHeight;

      if (ativo == true) {
        arrItensAtivos.push(itemForeach.id);
      }
    });

    return arrItensAtivos;
  }

  /**
   * Chama rotina de carregamento das seções apresentadas na tela.
   *  e paraliza atualização das seções que estão escondidas
   */
  carregarSecoes() {
    // Busco seções que estão sendo apresentadas na tela
    const arrIdAtivos = this.getIdSecoesAtivas();

    this.arrCtrlSecoes.forEach((secaoForeach) => {
      if (arrIdAtivos.indexOf(secaoForeach.id) > -1) {
        secaoForeach.ativarAtualizacao();
      } else {
        secaoForeach.pararAtualizacao();
      }
    });
  }

  /**
   * Retorna o controller da seção de acordo com o id
   * @param id
   */
  getSecaoCtrl(id: string): ISecaoController {
    return this.arrCtrlSecoes.find((ctrlFind) => {
      return ctrlFind.id == id;
    });
  }

    async $onInit() {
        this.linksExternos = this.$window.nsj.globals.a.links;
        this.defineExibeNomeFalecido();
        this.defineSecoes();
        this.loadDadosCidadesInformacoesFunerariasSepultamento();
        this.loadDadosCidadesInformacoesFunerarias();

        //Buscando informações do projeto associado ao atendimento comercial, caso haja projeto no atendimento
        if(this.entity.projeto != null && this.entity.projeto.projeto != null){

          //Função que retorna os dados de um projeto
          let dadosProjeto: any = await this.buscaInfoProjetoAssociado(this.entity.projeto.projeto);

          //Setando no objeto do atendimento, nos dados de projeto, as informações de liberação do projeto
          this.entity.projeto.created_at = dadosProjeto.created_at;
          this.entity.projeto.created_by = dadosProjeto.created_by;

        }

        if (!this.entity.negocio) {
            this.isEdit = true;
            this.blockButtons = true;
        } else {
            this.blockButtons = false;
            // Busco fornecedores envolvidos
            this.arrFornecedoresEnvolvidos = await this.CrmFornecedoresenvolvidos.getAll(this.entity.negocio);

            // Atualiza lista de fornecedores envolvidos ao acionar prestador
            this.$scope.$on('crm_fornecedoresenvolvidos_submitted', async (event: any, args: any) => {
                // Pego o id do fornecedor envolvido
                let fornecedorEnvolvidoID = args.response.data.fornecedorenvolvido;
                // Verifico se o item já existe
                let itemIndex = this.arrFornecedoresEnvolvidos.findIndex((itemFindIndex) => {
                    return itemFindIndex.fornecedorenvolvido == fornecedorEnvolvidoID;
                });

                // Caso o item não exista na lista, adiciono na lista
                if (itemIndex < 0) {
                    this.arrFornecedoresEnvolvidos.push(args.response.data);
                } else {
                    this.arrFornecedoresEnvolvidos[itemIndex] = args.response.data;
                }
            });
            // Atualiza lista de fornecedores envolvidos ao cancelar acionamento do prestador
            this.$scope.$on('crm_fornecedoresenvolvidos_deleted', async (event: any, args: any) => {
                // Pego o id do fornecedor envolvido
                let fornecedorEnvolvidoID = args.entity.fornecedorenvolvido;
                // Removo o item da lista
                this.arrFornecedoresEnvolvidos = this.arrFornecedoresEnvolvidos.filter((itemFilter) => {
                    return itemFilter.fornecedorenvolvido != fornecedorEnvolvidoID;
                });
            });
        }
        this.config = {
            scope: this,
            rule: /possuiseguradora|negocioarea|camposcustomizados|\\/g,
            dictionary: this.rulesAttr,
            stringIndex: 'indexcampocustomizado'
        };
        this.getCamposcustomizados();
        this.defineCamposSepultamento();

        if (this.entity.negocio) {
            // Se já estiver com o negócio definido, mando carregar seções
            this.carregarSecoes();
        }

    this.$scope.$on('crm_atcs_submitted', (event: any, args: any) => {
      if (args.response.data.erroGP) {
        console.log('ERRO GP', args.response.data.erroGP);
      }
      if (args.response.statusText !== 'Created') {
        this.isEdit = false;
      }
    });

    this.$scope.$on('crm_atcs_gera_contrato_taxa_administrativa_success_atualiza_atc', (event: any, args: any) => {
      this.entity.contratotaxaadm = args.contratotaxaadm;
      this.entity.createdcontratotaxa_at = args.createdcontratotaxa_at;
      this.entity.createdcontratotaxa_by = args.createdcontratotaxa_by;
      this.entity.valortaxaadm = args.valortaxaadm
      this.getSecaoCtrl('secao-fichafinanceira').ativarAtualizacao();
    });

    this.$scope.$on('crm_atcs_exclui_contrato_taxa_administrativa_success_atualiza_atc', (event: any, args: any) => {
      this.entity.contratotaxaadm = null;
      this.entity.valortaxaadm = null;
      this.getSecaoCtrl('secao-fichafinanceira').ativarAtualizacao();
    });

    this.$scope.$on('crm_atcs_salva_contrato_taxa_administrativa', (event: any, args: any) => {
      this.entity.valortaxaadm = args.valortaxaadm;
      this.getSecaoCtrl('secao-fichafinanceira').ativarAtualizacao();
    });

    this.$scope.$on('crm_propostasitens_submitted', (event, { response, entity }) => {
      const isNew = !(response.config.method === 'PUT');
      if (isNew) {
        this.atualizarStatusAtcNoFront(response.data.negocio.status);
      }
    });
    this.$scope.$on('crm_propostasitens_deleted', (entity, response) => {
      this.callAlterarStatus('_novo');
    });

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

    // Fica escutando quando a lista de fornecedores envolvidos é carregada
    this.$scope.$on('crm_fornecedoresenvolvidos_loaded', async (event: any, args: any) => {
      this.arrFornecedoresEnvolvidos = args;
      this.$scope.$applyAsync();
    });

    //Escuta se há algum acionamento ou cancelamento de acionamento de fornecedores envolvidos na ficha financeira.
    //Caso haja, carregará a lista de fornecedores envolvidos atualizada para atualizar botão de envio de e-mail
    this.$scope.$on('crm_fornecedoresenvolvidos_change', async (event, data) => {

      // Busco lista de fornecedores envolvidos
      this.arrFornecedoresEnvolvidos = await this.CrmFornecedoresenvolvidos.getAll(this.entity.negocio);
    });

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

  /**
   * Função que retorna os dados de um projeto
   * @param idProjeto Guid / Identificador do projeto
   * @returns 
   */
  async buscaInfoProjetoAssociado(idProjeto: any){

    return new Promise(async (resolve, reject) => {
      await this.$http({
        method: 'GET',
        url: this.nsjRouting.generate('financas_projetos_get', {'id': idProjeto}, true, true),
      })
        .then((response: any) => {
          resolve(response.data);
        })
        .catch((erro: any) => {
            reject(erro);
        });
    })

  }

  /**
   * Seta a propriedade nomeFalecido, que fará o controle da exibição da label do campo nome. Se nomeFalecido for true, mostra label como "Nome do Falecido", se false, mostra apenas "Nome"
   */
  defineExibeNomeFalecido() {
    let config = this.configuracaoService.getConfiguracao('EXIBEMUNICIPIOSEPULTAMENTO');
    this.entity['nomeFalecido'] = config == '1' ? true : false;
  }

  /**
   * Modifica o status conforme a api chamada.
   * @param funcao string iniciada com underline, que completa a identificação da api alvo e, portanto, da funcao acessada.
   */
  async callAlterarStatus(funcao) {
    let apiCompleta: string = '';
    let autorizado: boolean = false;
    const parteApi: string = 'crm_atcs_atc_status';

    //checa se o status original condiz com o desejo de mudar para o novo status
    switch (funcao) {
      case '_novo':
        if (this.entity.status === 1) {
          autorizado = true;
        }
        break;
      case '_fechado':
        if (this.entity.status === 1) {
          autorizado = true;
        }
        break;
      case '_reaberto':
        if (this.entity.status === 2) {
          autorizado = true;
        }
        break;
      case '_finalizado':
        if (this.entity.status === 2) {
          autorizado = true;
        }
        break;
      case '_cancelado':
        if (this.entity.status === 0 || this.entity.status === 1 || this.entity.status === 2) {
          autorizado = true;
          this.descancelouAtc = false;
        }
        break;
      case '_descancelado':
        if (this.entity.status === 4) {
          autorizado = true;
          this.descancelouAtc = true;
        }
        break;
      default:
        break;
    }

    //se sim, monta a api. caso não, o comando dará erro.
    if (autorizado) {
      apiCompleta = parteApi + funcao;
    }

    await this.$http({
      method: 'POST',
      data: angular.copy(this.entity),
      url: this.nsjRouting.generate(
        apiCompleta,
        angular.extend({}, { id: this.entity.negocio }, { offset: '', filter: '' }),
        true
      ),
    })
      .then((response: any) => {
        this.atualizarStatusAtcNoFront(response.data.status);
        this.stopSubmittingStatus();

        //Se o atendimento foi finalizado, cancelado ou descancelado, disparo evento para recarregamento dos cards de pedido e ficha financeira
        if (response.data.status == 3 || response.data.status == 4 || this.descancelouAtc){
          this.$rootScope.$broadcast('cancelouFinalizouAtc');
        }

        this.toaster.pop({
          type: 'success',
          title: `Status modificado para ${this.getStatusLabel(response.data.status)}`,
        });
      })
      .catch((error: any) => {
        this.stopSubmittingStatus();
        this.toaster.pop({
          type: 'error',
          title: error.data.message,
        });
      });
  }

  /**
   * Para/desliga os loaders específicos de mudança de status (nos botões)
   */
  stopSubmittingStatus() {
    this.submittingFinalizado = false;
    this.submittingReaberto = false;
    this.submittingCancelado = false;
    this.submittingFechado = false;
    this.submittingDescancelado = false;
  }

  /**
   * Abre modal de confirmação de mudança de status e, caso confirme, dispara a mudança para status finalizado
   */
  finalizarAtendimento() {

    let modal = this.ConfirmacaoFinalizarAtendimentoModalService.open({ funcao: 'finalizarAtendimento' });

    modal.result.then((response: any) => {
      this.submittingFinalizado = true;
      this.callAlterarStatus('_finalizado');
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

  /**
   * Dispara a mudança para status reaberto (novo ou em atendimento)
   */
  reabrirAtendimento() {
    this.submittingReaberto = true;
    this.callAlterarStatus('_reaberto');
  }

  /**
   * Dispara a mudança para status descancelado (novo ou em atendimento)
   */
  descancelarAtendimento() {
    this.submittingDescancelado = true;
    this.callAlterarStatus('_descancelado');
  }

  /**
   * Abre modal de confirmação de mudança de status e, caso confirme, dispara a mudança para status cancelado
   */
  abrirModalCancelarAtendimento() {
    let modal = this.ConfirmacaoFecharPedidoModalService.open({ funcao: 'cancelarAtendimento' }, '', '');
    modal.result
      .then((response: any) => {
        this.submittingCancelado = true;
        this.callAlterarStatus('_cancelado');
      })
      .catch((error: any) => {
        if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
          // this.busy = false;
          this.toaster.pop({
            type: 'error',
            title: error,
          });
        }
      });
  }

    /**
     * Recebe o atendimento comercial como parâmetro e chama a API de liberação de projeto
     * @param entity 
     * @returns 
     */
    liberarProjeto(entity: any){
   
        let objProj = {
            codigo: entity.codigo,
            nome: entity.nome,
            atc: entity.negocio,
            estabelecimento: entity.estabelecimento,
            cliente: entity.cliente
        };

        this.$http({
            method: 'POST',
            data: angular.copy(objProj),
            url: this.nsjRouting.generate('financas_projetos_create', angular.extend({}), true, true),
        })
        .then((response: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'O Projeto foi liberado com sucesso.'
            });

            //Associando projeto criado a entidade. Necessário para fazer exibição correta dos botões e também para a ação de cancelar liberação do projeto. Campos exibidos no accordion
            this.entity.projeto = {
              projeto: response.data.projeto,
              nome: response.data.nome,
              created_at: response.data.created_at,
              created_by: response.data.created_by,
              situacao: response.data.situacao
            };
            
        })
        .catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: error.data.message
            });
        });
    }

    /**
     * Chama a API de cancelamento de liberação do projeto
     * @param entity 
     */
    cancelarLiberacaoProjeto(entity: any){
        
        this.$http({
            method: 'GET',
            url: this.nsjRouting.generate('financas_projetos_cancelar', { 'id': entity.projeto.projeto}, true, true),
        })
        .then((response) => {
            this.toaster.pop({
                type: 'success',
                title: 'A liberação do Projeto foi cancelada com sucesso.'
            });

            //Não há mais projeto liberado para esse atendimento. Atribuição necessária para exibição dos botões pertinentes após o cancelamento
            this.entity.projeto = null;
            
        })
        .catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: error.data.message
            });
        });
        
    }

  /**
   * Abre modal de confirmação de mudança de status e, caso confirme, dispara a mudança para status fechado
   */
  abrirModalFecharAtendimento() {
    let modal = this.ConfirmacaoFecharPedidoModalService.open({ funcao: 'fecharPedido' }, '', '');
    modal.result
      .then((response: any) => {
        this.submittingFechado = true;
        this.callAlterarStatus('_fechado');
      })
      .catch((error: any) => {
        if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
          // this.busy = false;
          this.toaster.pop({
            type: 'error',
            title: error,
          });
        }
      });
  }

  /**
   * Abre modal da Taxa Administrativa
   */
  abrirModalTaxaAdministrativa(atc: any) {

    //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação abrir a modal da taxa administrativa
    if (this.entity.status == 3 || this.entity.status == 4) return;

    this.desativaBtnTaxaAdm = true;
    let modal = this.TaxaAdministrativaModalService.open(atc);
    modal.result
      .then((response: any) => {
        this.desativaBtnTaxaAdm = false;
      })
      .catch((error: any) => {
     
        if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
          // this.busy = false;
          this.toaster.pop({
            type: 'error',
            title: error,
          });
        }
        this.desativaBtnTaxaAdm = false;
      });
  }

  /**
   * Atualiza na no negócio this.entity o atributo status e statusLabel, para refletir na tela.
   * @param status numero que representa um status escrito.
   */
  atualizarStatusAtcNoFront(status: number) {
    const statusLabel = this.getStatusLabel(status);
    this.entity.statusLabel = statusLabel;
    this.entity.status = status;
  }

  /**
   * Converte o número do status, devolvendo uma string.
   * @param status numero que representa um status escrito.
   */
  getStatusLabel(status: number) {
    switch (status) {
      case 0:
        return 'Novo';
      case 1:
        return 'Em Atendimento';
      case 2:
        return 'Processando';
      case 3:
        return 'Finalizado';
      case 4:
        return 'Cancelado';
    }
  }
  async salvaListaDadosSeguradora(seguradora: any = [], { entity, response }: any) {
    const seguradoraAtualizada = [];
    if (Array.isArray(seguradora.negociosdadosseguradoras)) {
      for (const dadoSeguradora of seguradora.negociosdadosseguradoras) {
        const { rota, metodo, paramNegocio } = this.getRotaSeguradora(dadoSeguradora.negociodadosseguradora);
        const resposta = await this.$http({
          method: angular.copy(metodo),
          data: angular.copy(dadoSeguradora),
          url: this.nsjRouting.generate(
            rota,
            angular.extend(
              {},
              { paramNegocio: entity.negocio, id: dadoSeguradora.negociodadosseguradora },
              { offset: '', filter: '' }
            ),
            true
          ),
        });

        if (metodo == 'POST') {
          seguradoraAtualizada.push(resposta.data);
        } else {
          seguradoraAtualizada.push(resposta.config.data);
        }
      }

      this.atualizarListaSeguradora(seguradoraAtualizada);
    }
  }
  atualizarListaSeguradora(listaAtualizada) {
    listaAtualizada.forEach((item, index) => {
      this.entity.negociosdadosseguradoras[index].negociodadosseguradora = item.negociodadosseguradora;
    });
  }

  getRotaSeguradora(atcdadosseguradora) {
    if (atcdadosseguradora) {
      return { rota: 'crm_atcsdadosseguradoras_put', metodo: 'PUT', paramNegocio: 'atc' };
    }
    return { rota: 'crm_atcsdadosseguradoras_create', metodo: 'POST', paramNegocio: 'negocio' };
  }

  mostrarCompleto() {
    if (this.mostrarCompletoBool == true) {
      this.mostrarCompletoBool = false;
    } else {
      this.mostrarCompletoBool = true;
    }
  }

  criandoNovo(): boolean {
    return !this.entity.negocio ? false : true;
  }

  voltarParaLista() {
    if (window.confirm('Deseja mesmo redirecionar? Os dados da edição no formulário serão perdidos.')) {
      this.$state.go('crm_atcs');
    }
  }

  irParaAtcPai() {
    let confirma = false;
    if (this.isEdit) {
      if (window.confirm('Deseja mesmo redirecionar? Os dados da edição no formulário serão perdidos.')) {
        confirma = true;
      }
    } else {
      confirma = true;
    }
    if (confirma) {
      this.$state.go('crm_atcs_full', { atc: this.entity.negociopai.negocio });
    }
  }

  irParaNovoAtcRelacionado() {

    //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de redirecionar para criar atendimento relacionado
    if (this.entity.status == 3 || this.entity.status == 4) return;

    let confirma = false;
    if (this.isEdit) {
      if (window.confirm('Deseja mesmo redirecionar? Os dados da edição no formulário serão perdidos.')) {
        confirma = true;
      }
    } else {
      confirma = true;
    }

    if (confirma) {
      this.$state.go('crm_atcs_full_new', { area: this.entity.area.negocioarea, negociopai: this.entity.negocio });
    }
  }

  mostrarEditar() {

    //Se o atendimento estiver finalizado ou cancelado (status = 3 ou) não executo ação de abrir o modo de edição
    if (this.entity.status == 3 || this.entity.status == 4) return;

    this.isEdit = !this.isEdit;
  }

  /**
   *
   * @param secao secao de histórico => quando não informado exibe o histórico de todas as seções de negócio
   */
  abrirHistoricoModal(secao: string = '') {
    const parameters = { negocio: this.entity.negocio };
    let filters = secao ? { secao: secao } : null;
    this.CrmAtcsFormHistoricoService.open(parameters, {}, filters);
  }

  /**
   * Abre o projeto em modo de visualização em uma nova guia
   */
  abrirProjeto(){
    
    let urlProjeto = this.$state.href('projetos_show', { projeto: this.entity.projeto.projeto });
    window.open(urlProjeto, '_blank');
    
  }

  geraContrato() {
    let modal = this.CrmAtcsFormContratoService.open({
      atc: this.entity.negocio,
      cliente: this.entity.cliente.cliente,
    });
    modal.result
      .then(() => {
        this.$state.reload();
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

  excluiContrato(entity: any, force: boolean) {
    this.busy = true;
    if (force || confirm('Tem certeza que deseja excluir o contrato?')) {
      this.entityService
        .excluiContrato(entity)
        .then((response: any) => {
          this.toaster.pop({
            type: 'success',
            title: 'Sucesso ao excluir contrato!',
          });
          this.$state.reload();
        })
        .catch((response: any) => {
          if (typeof response.data.message !== 'undefined' && response.data.message) {
            this.toaster.pop({
              type: 'error',
              title: response.data.message,
            });
          } else {
            this.toaster.pop({
              type: 'error',
              title: 'Erro ao excluir contrato.',
            });
          }
        })
        .finally((response: any) => {
          this.busy = false;
        });
    }
  }

  async getCamposcustomizados() {
    this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { crmatcsvisualizacao: '1' }, true),
    }).then((response: any) => {
      response.data.forEach((campo) => {
        if (this.$scope.$eval(campo.visible?.replace(/\\/g, '')) && this.$scope.$eval(campo.crmatcsvisualizacao)) {
          campo.visible = true;
        }
      });
      this.camposcustomizadosview = response.data.filter((dado) => dado.visible == true);
    });

    // *********************
    await Promise.all([
      this.getCamposcustomizadosFalecidoViewResumo(),
      // this.getCamposcustomizadosPaciente(),
      this.getCamposcustomizadosResposavelViewResumo(),
      // this.getCamposcustomizadosPet()
    ]).then(async () => {
      this.definirVisualizacaoFalecidoExibicaoCompleto();
      this.busy = false;
      this.$scope.$applyAsync();
    });
    // *******
  }
  async getCamposcustomizadosResposavelViewResumo() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosResponsavel' }, true),
    })) as any;

    this.camposcustomizadosResponsavelViewResumo = angular.copy(data);
    this.entity['camposcustomizadosResponsavelViewCompleto'] = angular.copy(data);
    await this.filtroCamposResponsavelExibicaoResumo();
  }
  async getCamposcustomizadosFalecidoViewResumo() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosFalecido' }, true),
    })) as any;

    this.camposcustomizadosFalecidoViewResumo = angular.copy(data);
    this.entity['camposcustomizadosFalecidoViewCompleto'] = angular.copy(data);
    await this.filtroCamposFalecidoExibicaoResumo();
  }

  abrirModalContratoMaisPagante() {

    //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de abrir modal de responsabilidade financeira
    if (this.entity.status == 3 || this.entity.status == 4) return;

    let modal = this.CrmContratoMaisPaganteModalService.open({
      obj: this.entity,
    });

    modal.result
      .then((entidade: any) => {})
      .catch((error: any) => {
        if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
          this.busy = false;
          this.toaster.pop({
            type: 'error',
            title: error,
          });
        }
      });
  }

  /**
   * Recebe o created by de um projeto e faz sua conversão para JSON para exibição no accordion de projeto
   */
  parseCreatedBy(created_by: any){

    let jsonFormatado;

    if(typeof created_by == 'string'){
      jsonFormatado = JSON.parse(created_by);
      return jsonFormatado.nome;
    }

  }


    /**
     * Abre modal de solicitação de orçamento da prestadora por e-mail
     */
     private abrirModalSolicitarOrcamentoPorEmail(pessoa: string) {
      let modal = this.SolicitarOrcamentoEmailModalService.open({}, this.entity);
      // Abro modal de enviar documentos po
      modal.result
          .then((entidade: any) => {})
          .catch((error: any) => {});
  }

  /**
   * Abre modal para o envio de e-mail
   */
  private abrirModalEnviarEmail(
    tipoEnvioEmail: EnumTipoEnvioEmail = EnumTipoEnvioEmail.teeTodos,
    pessoa: string = null
  ) {
    let paramsTela: IParamsTela = {
      tipoEnvioEmail: tipoEnvioEmail,
      pessoa: pessoa,
    };
    let modal = this.EnviarAtcEmailModalService.open(paramsTela, this.entity);

    modal.result.then((entidade: any) => {}).catch((error: any) => {});
  }

  /**
   * Envia documentos por e-mail para a seguradora/cliente
   */
  private abrirModalEnviarDocsEmail(pessoa: string) {
    // Monto parametros da tela modal
    let paramsTela: IParamsTelaDocs = {
      tipoEnvioEmail: this.entity.possuiseguradora
        ? EnumTipoEnvioDocsEmail.teeSeguradora
        : EnumTipoEnvioDocsEmail.teeCliente,
      pessoa: pessoa,
    };
    let modal = this.EnviarAtcDocsEmailModalService.open(paramsTela, this.entity);
    // Abro modal de enviar documentos po
    modal.result.then((entidade: any) => {}).catch((error: any) => {});
  }
  
  /**
   * Função que abre a modal para visualizar mais contatos, no accordion de prestadoras de serviços envolvidas
   * @param contatos Os contatos a serem carregados na modal
   */
  private abrirModalVisualizarMaisContatos(contatos: any) {

    let modal = this.ModalVisualizarMaisContatosService.open(contatos);
    modal.result.then((entidade: any) => {}).catch((error: any) => {});
  }

}


export const CrmAtcsFullModule = angular.module('CrmAtcsFull',[])
    .directive('nsEnter', function () {
        return function (scope, element, attrs) {
            element.unbind('keypress').bind('keypress', function (event) {
                if (event.which === 13) {
                    event.stopImmediatePropagation();
                    scope.$apply(function () {
                        scope.$eval(attrs.nsEnter);
                    });

                    event.preventDefault();
                }


            });
        };
    }).name;
