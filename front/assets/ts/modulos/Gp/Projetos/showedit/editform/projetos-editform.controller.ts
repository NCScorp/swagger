import { IProjeto } from "../../interfaces/projetos.interface";
import { ICamposCustomizados } from "../../interfaces/campos-customizados.interface";
import { ProjetosService } from "../../projetos.service";
import { IToaster } from "assets/ts/shared/interfaces/toaster.interface";
import angular from "angular";
import { ProjetosDocumentosNecessariosModalService } from "./../documentosnecessariosmodal/documentosnecessarios.modal.service";

export class ProjetosEditFormController implements angular.IController {

  static $inject = [
    'projetosService',
    '$scope',
    'toaster',
    'projetosDocumentosNecessariosModalService'
  ];

  public busySubmit: boolean = false;
  public camposCustomizados;
  public config = {
    scope: this,
    rule: /camposcustomizados|\\/g,
    dictionary: {
      camposcustomizados: 'this.originalEntity.camposcustomizados',
      '\\': '',
    },
    stringIndex: 'indexcampocustomizado',
  };
  public dadosProjetos: ICamposCustomizados;
  public dadosResponsavelPrimario = [];
  public dadosResponsavelSecundario = [];
  public dadosFalecido = [];
  public editMode: boolean;
  public originalEntity: IProjeto;
  public entity: IProjeto;
  public form: angular.IFormController;
  public validos = {};
  public dadosSeguradoras = {};

  constructor(
    public projetosService: ProjetosService,
    public $scope: ng.IScope,
    public toaster: IToaster,
    public projetosDocumentosNecessariosModalService: ProjetosDocumentosNecessariosModalService
  ) { }

  $onInit() {
    this.watchEntity();
    this.watchCamposCustomizados();
    this.watchDadosClientes();
    this.checkIsDirty();
    this.onSubmitSuccess();
    this.onSubmitError();
  }

  /**
   * Faço uma cópia por valor da entidade ao recebê-la
   */
  watchEntity() {
    this.$scope.$watch('$ctrl.originalEntity', () => {
      /**
       * One-way data binding não foi o suficiente nesse caso por ser um objeto
       * e o Javascript passar o objeto *por referência*. 
       * Por isso, faço uma cópia *por valor* da entidade original, para que
       * as modificações aqui não reflitam na entity original enquanto não
       * forem salvas.
      */
      this.entity = angular.copy(this.originalEntity);
    });
  }

  /**
   * Pego os dados do responsável e dados do falecido ao receber essas variáveis
   */
  watchCamposCustomizados() {
    this.$scope.$watch('$ctrl.camposCustomizados', () => {
      if (this.camposCustomizados.dadosResponsavelFull) {
        this.getDadosResponsavel();
      }
      if (this.camposCustomizados.dadosFalecidoResumido) {
        this.getDadosFalecido();
      }
    }, true);
  }

  /**
   * Monto os dados da seguradora ao receber a variável de dadosClientes
   */
  watchDadosClientes() {
    this.$scope.$watch('$ctrl.originalEntity.dadosAtc.dadosClientes', () => {
      if (this.originalEntity?.dadosAtc && this.originalEntity.dadosAtc.dadosClientes.length > 0) {
        this.montaDadosSeguradorasFull();
      }
    }, true);
  }

  /**
   * Salvo as alterações
   */
  submit(): void {
    this.form.$submitted = true;
    if (this.form.$valid && !this.entity.$$__submitting){

      // stringify necessário para o backend conseguir processar esse JSON
      this.entity.responsavel_conta_nasajon = JSON.stringify(this.entity.responsavel_conta_nasajon);

      this.busySubmit = true;
      this.projetosService.save(this.entity);
    } else {
      this.toaster.pop({
        type: 'error',
        title: 'Alguns campos do formulário apresentam erros.'
      });
    }
  }

  /**
   * Evento disparado quando as alterações são salvas com sucesso
   */
  onSubmitSuccess(): void {
    this.$scope.$on('projetos_submitted', () => {
      // Atualizado a entidade com os dados modificados
      this.originalEntity = angular.copy(this.entity);
      // desfaço o stringify para poder exibir os dados em tela
      this.originalEntity.responsavel_conta_nasajon = JSON.parse(this.entity.responsavel_conta_nasajon);

      // Saio do modo de edição
      this.editMode = false;
      this.busySubmit = false;
      
      this.toaster.pop({
        type: 'success',
        title: 'Projeto editado com sucesso!'
      });
    });
  }

  /**
   * Evento disparado quando há algum erro no salvamento das alterações
   */
  onSubmitError(): void {
    this.$scope.$on('projetos_submit_error', (event, args) => {
      
      this.busySubmit = false;
      this.toaster.pop({
        type: 'error',
        title: 'Ocorreu um erro ao editar o projeto.',
        body: args?.response?.data?.message ? args.response.data.message : ''
      });
    });
  }

  /**
   * Método usado para alternar entre modo de edição e visualização
   */  
  setEdit(value: boolean): void {
    this.editMode = value;
  }

  /**
   * Por não usar o componente de campos customizados na tela de edição, é necessário separar esses dados manualmente
   */
  getDadosResponsavel() {
    let camposDadosResponsaveis;

    // Busco o campo de Dados Responsáveis basedo no UUID do campo
    for (let campoUuid in this.entity.dadosAtc.camposcustomizados) {
      if (campoUuid === this.camposCustomizados.dadosResponsavelFull[0].campocustomizado) {
        camposDadosResponsaveis = this.entity.dadosAtc.camposcustomizados[campoUuid][0];
        break;
      }
    }



    // Monto array de dadosResponsavel para percorrer com ng-repeat no HTML
    for (let key in camposDadosResponsaveis) {
      this.camposCustomizados.dadosResponsavelFull[0].objeto.forEach((campo) => {
        if (key[key.length - 1] !== '2') {
          if (key === campo.nome) {
            this.dadosResponsavelPrimario.push({
              label: campo.label,
              value: camposDadosResponsaveis[key]
            });
          }
        } else {
          if (key === campo.nome) {
            this.dadosResponsavelSecundario.push({
              label: campo.label,
              value: camposDadosResponsaveis[key]
            });
          }
        }
      });
    }
  }

  /**
   * Por não usar o componente de campos customizados na tela de edição, é necessário separar esses dados manualmente
   */
  getDadosFalecido() {
    let camposDadosFalecido;

        // Busco o campo de Dados Falecido basedo no UUID do campo
        for (let campoUuid in this.entity.dadosAtc.camposcustomizados) {
          if (campoUuid === this.camposCustomizados.dadosFalecidoResumido[0].campocustomizado) {
            camposDadosFalecido = this.entity.dadosAtc.camposcustomizados[campoUuid][0];
            break;
          }
        }
    
        // Monto array de dadosResponsavel para percorrer com ng-repeat no HTML
        for (let key in camposDadosFalecido) {
          this.camposCustomizados.dadosFalecidoResumido[0].objeto.forEach((campo) => {
            if (key === campo.nome) {
              this.dadosFalecido.push({
                label: campo.label,
                value: camposDadosFalecido[key]
              });
            }
          });
        }
  }

  /**
   * Prpearo os dados para preencher a table com as informações da seguradora
   */
  montaDadosSeguradorasFull(): void {
    this.originalEntity.dadosAtc.responsaveisfinanceiros.forEach((respfin) => {

      this.originalEntity.dadosAtc.respfinanceiroprincipal = angular.copy(
        respfin.responsavelfinanceiro.nomefantasia
      );

      const cliente = this.originalEntity.dadosAtc.dadosClientes.find((cliente) => cliente.cliente === respfin.responsavelfinanceiro.cliente);
      if (cliente) {
        let dadosSeguradora: any = {
          [cliente.cliente] : {}
        };

        if (cliente.contatos.length > 0 && cliente.contatos[0].telefones.length > 0) {
          dadosSeguradora[cliente.cliente] = {
            respfinanceiroddi: cliente.contatos[0].telefones[0].ddi,
            respfinanceiroddd: cliente.contatos[0].telefones[0].ddd,
            respfinanceirotelefone: cliente.contatos[0].telefones[0].telefone
          };
        }
        if (respfin.principal) {
          dadosSeguradora[cliente.cliente].respfinanceiroprincipaltelefone =
            cliente.contatos.length > 0 && cliente.contatos[0].telefones.length > 0
              ? cliente.contatos[0].telefones[0].ddi +
              cliente.contatos[0].telefones[0].ddd +
              cliente.contatos[0].telefones[0].telefone
              : null;
          dadosSeguradora[cliente.cliente].respfinanceiroprincipaltelefoneramal =
            cliente.contatos.length > 0 && cliente.contatos[0].telefones.length > 0
              ? cliente.contatos[0].telefones[0].ramal
              : null;
        }

        this.dadosSeguradoras = { ...this.dadosSeguradoras, ...dadosSeguradora };
      }
    });
  }

  /**
   * Abre modal de documentos necessários
   */
  openDocumentosNecessariosModal(documentosNecessarios) {
    this.projetosDocumentosNecessariosModalService.open(documentosNecessarios);
  }

  /**
   * Atualizo a entity ao alterar algum dos camposCustomizados
   */
  onCampoChange({ detail }) {
    const campos = this.fromJson(detail.data);
    Object.keys(campos).forEach((key) => {
      // Verifica se é do tipo string pois ao salvar camposcustomizados vem como 'string'
      if (this.entity && typeof this.entity.dadosAtc.camposcustomizados !== 'string') {
        this.entity.dadosAtc.camposcustomizados[key] = angular.copy(campos[key]);
      }
    });
    this.entity.dadosAtc.camposcustomizados = angular.copy(this.entity.dadosAtc.camposcustomizados);
    this.validos[detail.validity.key] = detail.validity.valid;

    this.entity.dadosAtc.camposcustomizadosvalidos = this.temTodosValidos(this.validos);
    this.$scope.$evalAsync();
  }

  /**
   * Verifico a validade dos campos
   */
  temTodosValidos(objeto: object): boolean {
    return Object.keys(objeto).every((key) => objeto[key] === true);
  }

  /**
   * Converto uma string para JSON
   */
  fromJson(param) {
    return typeof param === 'string' ? JSON.parse(param) : param;
  }

  /**
   * Verifico se a entity foi alterada
   */
  checkIsDirty() {
    this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
      if (newValue !== oldValue) {
          this.form.$setDirty();
      }
    }, true);
  }
}
