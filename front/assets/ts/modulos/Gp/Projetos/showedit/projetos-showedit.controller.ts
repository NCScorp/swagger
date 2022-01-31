import angular from 'angular';
import { CrmAtcs } from 'assets/ts/modulos/Crm/Atcs/factory';
import { NsClientes } from 'assets/ts/modulos/Ns/Clientes/factory';
import { NsjIndiceClasses } from '../../../Commons/nsjindice/components/nsjindice.classes';
import { SituacaoEnum } from '../enums/situacaoEnum';
import { FormulariosModulosRespostasService } from '../formularios-modulos-respostas.service';
import { FormulariosModulosService } from '../formularios-modulos.service';
import { ICamposCustomizados } from '../interfaces/campos-customizados.interface';
import { IEscopo } from '../interfaces/escopo.interface';
import { IProjetoTemplate } from '../interfaces/projetos-templates.interface';
import { IProjeto } from '../interfaces/projetos.interface';
import { ProjetosService } from '../projetos.service';
import { EscopoService } from './escopo/escopo.service';
import { IFormulario, IFormularioRespostas } from './formularios/formularios.interface';
import { ModalTemplateService } from './template/modal/modal-template';
import { ProjetosTemplatesService } from './template/projetos-templates.service';

export class ProjetosShowEditController implements angular.IController {
  static $inject = [
    'entity',
    'projetosService',
    'escopoService',
    '$stateParams',
    'CrmAtcs',
    '$http',
    'nsjRouting',
    '$state',
    '$scope',
    'NsClientes',
    'modalTemplateService',
    'toaster',
    'projetosTemplatesService',
    'utilService',
    'formulariosModulosService',
    'formulariosModulosRespostasService'
  ];
  public arrQtdSkeletonsEscopoCol: number[] = [1, 2, 3, 4, 5, 6, 7, 8];
  public arrQtdSekeletonsEscopoLin: number[] = [1, 2, 3, 4, 5];
  public busyEscopo: boolean = true;
  public configMenuHorizontal: NsjIndiceClasses.Config;
  public editMode: boolean = false;
  public escopoEntities: IEscopo[];
  public formulariosEntities: IFormulario[];
  public formulariosRespostas: IFormularioRespostas[];
  public acumuladorFormulariosRespostas: IFormularioRespostas[] = [];
  public busyFormulario: boolean = false;
  public mostrarMais: boolean = false;
  public timerScrollSecao;
  public timerScrollIndice: NodeJS.Timeout;
  public arrCtrlSecoes;

  public camposCustomizados: Partial<{
    dadosResponsavelFull: ICamposCustomizados[],
    dadosResponsavelResumido: ICamposCustomizados[],
    dadosFalecidoResumido: ICamposCustomizados[]
  }> = {};
  /**
   * Configurações dos camposCustomizados
   */
  public camposCustomizadosConfig = {
    scope: this,
    rule: /possuiseguradora|negocioarea|camposcustomizados|\\/g,
    dictionary: {
      negocioarea: 'this.entity.dadosAtc.area.negocioarea',
      possuiseguradora: 'this.entity.dadosAtc.possuiseguradora',
      camposcustomizados: 'this.entity.dadosAtc.camposcustomizados',
      '\\': '',
    },
    stringIndex: 'indexcampocustomizado'
  };

  public form: angular.IFormController;
  public desativaBtnTemplate: boolean = false;

  constructor(
    public entity: IProjeto,
    public projetosService: ProjetosService,
    public escopoService: EscopoService,
    public $stateParams: ng.ui.IStateParamsService,
    public CrmAtcs: CrmAtcs,
    public $http: ng.IHttpService,
    public nsjRouting,
    public $state: ng.ui.IStateService,
    public $scope: ng.IScope,
    public NsClientes: NsClientes,
    public modalTemplateService: ModalTemplateService,
    public toaster: any,
    public projetosTemplatesService: ProjetosTemplatesService,
    public utilService: any,
    public formulariosModulosService: FormulariosModulosService,
    public formulariosModulosRespostasService: FormulariosModulosRespostasService
  ) {
    // não permite ficar na tela de show quando for situação "Aguardando Inicialização"
    if (entity.situacao === SituacaoEnum.AGUARDANDO_INICIALIZACAO) {
      $state.go('projetos');
    }

    //Se salvou template com sucesso
    $scope.$on('financas_projetos_templates_submitted', (event: any, args: any) => {

      this.toaster.pop({
        type: 'success',
        title: 'O Template foi salvo com sucesso!'
      });

      //Se conseguiu salvar template, seto a propriedade da entidade para fazer controle dos botões
      this.entity.template = args.entity.template;

    });

    //Se ocorreu um erro ao salvar template
    $scope.$on('financas_projetos_templates_submit_error', (event: any, args: any) => {
      if (args.response.status === 409) {
        if (confirm(args.response.data.message)) {
          this.entity[''] = args.response.data.entity[''];
          this.projetosTemplatesService.save(this.entity, false);
        }
      } else {
        if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
          if (args.response.data.message === 'Validation Failed') {
            let distinct = (value: any, index: any, self: any) => {
              return self.indexOf(value) === index;
            };
            args.response.data.errors.errors = args.response.data.errors.errors.filter(distinct);
            let message = this.utilService.parseValidationMessage(args.response.data);
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
                title: args.response.data.message
              });
          }
        } else {
          this.toaster.pop(
            {
              type: 'error',
              title: 'Ocorreu um erro ao salvar Template!'
            });
        }
      }

    });

    //Se desfez vínculo do template com sucesso
    $scope.$on('financas_projetos_templates_deleted', (event: any, args: any) => {

      this.toaster.pop({
        type: 'success',
        title: 'O vínculo do Template foi desfeito com sucesso!'
      });

      //Se conseguiu desfazer vínculo, seto a propriedade para falso para exibir o botão apropriado
      this.entity.template = false;

    });

    //Se ocorreu um erro ao desfazer vínculo do template
    this.$scope.$on('financas_projetos_templates_delete_error', (event: any, args: any) => {
      if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
        this.toaster.pop(
          {
            type: 'error',
            title: args.response.data.message
          });
      } else {
        this.toaster.pop(
          {
            type: 'error',
            title: 'Ocorreu um erro ao desfazer vínculo do Template!'
          });
      }
    });

    //Ouvindo evento para reativar botão de template
    $scope.$on('ativar_botao_template', () => {

      this.desativaBtnTemplate = false;

    });

  }

  $onInit() {
    this.initMenuHorizontal();

    if (this.entity.atc) {
      this.getDadosAtc(),
        this.montaCamposCustomizados();
    }

    this.loadEscopoList();
    this.onEscopoListLoad();
    this.loadFormulariosList();
    this.onFormulariosListLoad();
    this.onFormularioUpdated();
  }

  /**
 * Disparado quando o scroll do container de seções é realizado
 */
  onSecaoContainerScroll() {
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
          .querySelector('.gp-projetos-container')
          .querySelector('.secao-container-scroll')
        scrollCt.scrollTo(0, scrollCt.scrollTop - 5);
      }
    }, 200);

    // Espero 300 milisegundos para confirmar que parou o scroll
    this.timerScrollSecao = setTimeout(() => {
      this.carregarSecoes();
    }, 300);
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
   * Retorna o id das seções apresentadas na tela no momento
   */
  getIdSecoesAtivas(): string[] {
    // Busco container de scroll
    const scrollCt = document.querySelector('.gp-projetos-container').querySelector('.secao-container-scroll');

    // Busco itens de accordion
    const arrItens = scrollCt.querySelectorAll('.secao-scroll');

    // Busco itens ativos(visíveis) na tela
    // 60 é o tamanho do bloco de título
    const arrItensAtivos = [];
    arrItens.forEach((itemForeach) => {
      const ativo =
        scrollCt.scrollTop <= (<HTMLElement>itemForeach).offsetTop + (<HTMLElement>itemForeach).offsetHeight - 60 &&
        (<HTMLElement>itemForeach).offsetTop + 60 <= scrollCt.scrollTop + (<HTMLElement>scrollCt).offsetHeight;

      if (ativo == true) {
        arrItensAtivos.push(itemForeach.id);
      }
    });

    return arrItensAtivos;
  }

  /**
   * Inicializa o menu horizontal passando as opções do menu para o arquivo de configurações do menu
   */
  initMenuHorizontal(): void {
    // Defino as opções do menu horizontal
    const arrOpcoesMenuHorizontal: NsjIndiceClasses.IndiceItem[] = [
      new NsjIndiceClasses.IndiceItem('indice-infos-gerais', 'Informações Gerais', '#projetos-secao-infos-gerais'),
      new NsjIndiceClasses.IndiceItem('indice-escopo', 'Escopo', '#projetos-secao-escopo'),
      // new NsjIndiceClasses.IndiceItem('indice-anexos', 'Anexos', '#secao-anexos'),                 // A IMPLEMENTAR
      new NsjIndiceClasses.IndiceItem('indice-formularios', 'Formulários', '#projetos-secao-formularios')
    ];

    // adiciono as opções ao menu horizontal
    this.configMenuHorizontal = new NsjIndiceClasses.Config(arrOpcoesMenuHorizontal, 'fa fa-ellipsis-h');
  }

  /**
   * Busco os dados do Atendimento Comercial
   */
  async getDadosAtc(): Promise<void> {
    await this.CrmAtcs.get(this.entity.atc).then((response) => {
      this.entity.dadosAtc = response;
      this.getDadosClientes();
    });
  }

  /**
   * Inicio a montagem dos campos customizados
   */
  async montaCamposCustomizados(): Promise<void> {
    // Busco os campos customizados no backend
    const dadosResponsavel = await this.getCamposcustomizadosResposavel();
    const dadosFalecido = await this.getCamposcustomizadosFalecidoViewResumo();

    // Monto os campos customizados
    this.montaCamposResponsavelFull(dadosResponsavel);
    this.montaCamposResponsavelResumido(dadosResponsavel);
    this.montaCamposDadosFalecido(dadosFalecido);
  }

  /**
   * Monto os dados do responsável do "Mostrar mais"
   */
  montaCamposResponsavelFull(dadosResponsavel) {
    let dadosResponsavelFull = angular.copy(dadosResponsavel);

    // Escolho os campos que desejo excluir
    const camposDadosResponsavelFullToExclude = [
      'tipoPessoa',
      'tipoPessoa2'
    ];

    // Filtro com os campos que desejo remover
    dadosResponsavelFull.forEach((dadosResponsavel) => {
      dadosResponsavel.objeto = this.camposCustomizadosParaExcluir(
        camposDadosResponsavelFullToExclude,
        dadosResponsavel);
    });

    // adiciono ao objeto de campos customizados
    this.camposCustomizados = {
      ...this.camposCustomizados,
      dadosResponsavelFull: dadosResponsavelFull,
    };
  }

  /**
   * Monto os dados do responsável do "Mostrar menos"
   */
  montaCamposResponsavelResumido(dadosResponsavel) {
    let dadosResponsavelResumido = angular.copy(dadosResponsavel);

    // Escolho os campos que desejo exibir
    const camposDadosResponsavelToInclude = [
      'nomeCompleto',
      'email',
      'telefone'
    ];

    // Filtro com os campos que desejo exibir
    dadosResponsavelResumido.forEach((dadosResponsavel) => {
      dadosResponsavel.objeto = this.camposCustomizadosParaIncluir(
        camposDadosResponsavelToInclude,
        dadosResponsavel
      );
    });

    // adiciono ao objeto de campos customizados
    this.camposCustomizados = {
      ...this.camposCustomizados,
      dadosResponsavelResumido: dadosResponsavelResumido,
    };
  }

  /**
   * Monto os dados do falecido
   */
  montaCamposDadosFalecido(dadosFalecido) {
    let dadosFalecidoResumido = angular.copy(dadosFalecido);

    // Escolho os campos que desejo exibir
    const camposDadosFalecidoToInclude = [
      'nome',
      'peso',
      'altura'
    ];

    // Filtro com os campos que desejo exibir
    dadosFalecidoResumido.forEach((dadosFalecido) => {
      dadosFalecido.objeto = this.camposCustomizadosParaIncluir(
        camposDadosFalecidoToInclude,
        dadosFalecido
      );
    });

    // adiciono ao objeto de campos customizados
    this.camposCustomizados = {
      ...this.camposCustomizados,
      dadosFalecidoResumido: dadosFalecidoResumido
    };
  }

  /**
   * Busco os campos e dados do responsável
   */
  async getCamposcustomizadosResposavel(): Promise<ICamposCustomizados[]> {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosResponsavel' }, true),
    })) as any;

    return data;
  }

  /**
   * Busco os campos e dados do falecido
   */
  async getCamposcustomizadosFalecidoViewResumo() {
    const { data } = (await this.$http({
      method: 'GET',
      url: this.nsjRouting.generate('ns_camposcustomizados_index', { secao: 'dadosFalecido' }, true),
    })) as any;

    return data;
  }

  /**
   * Busco os dados dos clientes
   */
  getDadosClientes() {
    for (let i in this.entity.dadosAtc.responsaveisfinanceiros) {
      this.entity.dadosAtc.respfinanceiroprincipal = angular.copy(
        this.entity.dadosAtc.responsaveisfinanceiros[i].responsavelfinanceiro.nomefantasia
      );
      this.NsClientes.get(this.entity.dadosAtc.responsaveisfinanceiros[i].responsavelfinanceiro.cliente);
    }

    this.entity.dadosAtc.dadosClientes = [];

    this.$scope.$on('ns_clientes_loaded', (event, dadosCliente) => {
      this.entity.dadosAtc.dadosClientes.push(dadosCliente);
    });
  }

  /**
   * Filtra mantendo apenas os campos passados na variável camposArr
   */
  camposCustomizadosParaIncluir(camposArr: string[], camposCustomizados) {
    return camposCustomizados.objeto.filter((campo) => camposArr.includes(campo.nome));
  }

  /**
   * Filtra removendo os campos passados na variável camposArr
   */
  camposCustomizadosParaExcluir(camposArr: string[], camposCustomizados) {
    return camposCustomizados.objeto.filter((campo) => !camposArr.includes(campo.nome));
  }

  /**
   * Alterna entre modo de visualização e edição
   */
  setEdit(value: boolean): void {
    this.editMode = value;
  }

  /**
   * Toggle entre mostrar mais e menos informações
   */
  mostrarMaisInformacoes(value: boolean): void {
    this.mostrarMais = value;
  }

  /**
   * Abre a modal de templates, seja para definir o projeto como template ou gerenciar um template existente
   * @param entity Dados do projeto que são necessários para envio da informação
   * @param desfazerVinculo Controla visibilidade do botão de desfazer vinculo na modal
   */
  async abreModalTemplate(entity: any, desfazerVinculo: boolean) {

    let modal = await this.modalTemplateService.open(entity, desfazerVinculo);

    modal.result.then(async (subentity: any) => {

      if (subentity) {

        //Objeto enviado para o backend
        let projTempObj: IProjetoTemplate = {
          projeto: subentity.dadosprojeto.projeto,
          templatenome: subentity.templatenome,
          templatedescricao: subentity.templatedescricao,
          template: subentity.dadosprojeto.template
        }

        //Salvando a entidade enviada
        await this.projetosTemplatesService.save(projTempObj, false);

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

  /**
   * Faz o controle da desativação do botão do template no projeto
   */
  controleBtnTemplate() {

    this.desativaBtnTemplate = true;

  }

  /**
   * Carrega a lista de escopos
   */
  loadEscopoList() {
    this.escopoService.constructors = { projeto: this.entity.projeto };
    this.busyEscopo = true;
    this.escopoEntities = [];
    this.escopoService.loadParams.to_load = 1;
    this.escopoService.loadParams.finished = false;
    this.escopoService.entities = [];
    this.escopoEntities = this.escopoService.reload();
  }

  /**
   * Carrega a lista de formularios
   */
  loadFormulariosList() {
    this.formulariosModulosService.constructors = { modulo: this.entity.projeto, tipomodulo: 0 };
    this.busyFormulario = true;
    this.formulariosEntities = [];
    this.formulariosModulosService.loadParams.to_load = 1;
    this.formulariosModulosService.loadParams.finished = false;
    this.formulariosModulosService.entities = [];
    this.formulariosEntities = undefined;
    this.formulariosRespostas = undefined;
    this.formulariosEntities = this.formulariosModulosService.reload();
  }

  onFormulariosListLoad() {
    this.$scope.$on('formularios_modulos_list_finished', async () => {
      let acumuladorFormulariosRespostas = [];
      this.formulariosEntities.forEach(async (formulario, index, arr) => {
        this.formulariosModulosRespostasService.get({ formulariomodulo: formulario.formulariomodulo }).then((respostasFormulario: IFormularioRespostas[]) => {
          acumuladorFormulariosRespostas = [...acumuladorFormulariosRespostas, ...respostasFormulario ];
        }).finally(() => {
          // Só adiciono no arr de formulariosRespostas quando todas respostas tiverem sido buscadas (para previnir layout-shift)
          if (index === arr.length - 1) {
            this.formulariosRespostas = acumuladorFormulariosRespostas;
          }
          this.busyFormulario = false;
        });
      });

      if (!this.formulariosEntities.length) {
        this.busyFormulario = false;
      }
    });
  }

  onFormularioUpdated() {
    this.$scope.$on('projetos_alterou_formulario', () => {
      this.loadFormulariosList();
    });
  }


  /**
   * Evento ao terminar de carregar a lista de escopos
   */
  onEscopoListLoad(): void {
    this.$scope.$on('projetositensescopos_list_finished', () => {
      this.busyEscopo = false;
    });
  }
}
