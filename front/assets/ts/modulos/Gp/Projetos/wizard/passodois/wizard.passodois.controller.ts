import angular = require("angular");
import { CommonsUtilsService } from "../../../../Commons/utils/utils.service";
import { Usuarios } from "assets/ts/usuarios/usuarios.service";

export class WizardPassodoisController {
  static $inject = [
    "$scope",
    "CommonsUtilsService",
    "Usuarios",
    "$http",
    "nsjRouting",
  ];
  public form: any;
  public opcoes: any;
  public entity;
  public dadosProjeto: any;
  public busy: boolean = false;

  constructor(
    public $scope: angular.IScope,
    public CommonsUtilsService: CommonsUtilsService,
    public Usuarios: Usuarios,
    public $http: angular.IHttpService,
    public nsjRouting: any
  ) {
    //Inicializações no contrutor
  }

  async $onInit() {
    this.busy = true;
    this.$scope.$watch(
      "$ctrl.entity",
      (newValue, oldValue) => {
        if (newValue !== oldValue) {
          this.form?.$setDirty();
        }
      },
      true
    );

    this.opcoes.validarFormulario = () => {
      return this.validarForm();
    };

    //Se houver um template selecionado, busco informações do projeto baseado nele
    if (this.entity.projetotemplate) {
      this.dadosProjeto = await this.carregaDadosProjeto(
        this.entity.projetotemplate
      );

      this.entity.formularioList = await this.carregaDadosFormulario(
        this.entity.projetotemplate)

        this.entity.formularioList.map((formulario) => formulario.nome = formulario.formularionome)

      //Se há dados do projeto, chamo função para popular entidade
      if (this.dadosProjeto) {
        this.atribuiDadosProjeto(this.dadosProjeto);
      }
    }

    //Caso a data de início ainda não esteja selecionada, atribuir a data atual
    if (this.entity.datainicio == null || this.entity.datainicio == "") {
      this.setDataAtualInicio();
    }

    //Caso não haja responsável selecionado, atribuo ao campo o usuário logado
    if (this.entity.responsavel_conta_nasajon == null) {
      let dadosProfile = this.Usuarios.getProfileNoPromise();
      this.entity.responsavel_conta_nasajon = {
        email: dadosProfile.email,
        nome: dadosProfile.nome,
      };
    }

    this.busy = false;
    this.reloadScope();
  }

  validarForm() {
    return this.form.$valid;
  }

  /**
   * Função que atribui a data atual a data de inicio
   */
  setDataAtualInicio() {
    this.entity.datainicio = this.CommonsUtilsService.getDataFormatada(
      new Date().toDateString(),
      "YYYY-MM-DD"
    );
  }

  /**
   * Função que retorna as informações do projeto
   * @param idProjeto
   * @returns
   */
  carregaDadosProjeto(idProjeto) {
    return new Promise((resolve, reject) => {
      this.$http({
        method: "GET",
        url: this.nsjRouting.generate(
          "financas_projetos_get",
          { id: idProjeto },
          true,
          true
        ),
      })
        .then((response: any) => {
          resolve(response.data);
        })
        .catch((erro: any) => {
          reject(erro);
        });
    });
  }

  /**
   * Função que retorna as informações do projeto
   * @param idProjeto
   * @returns
   */
   carregaDadosFormulario(idProjeto) {
    return new Promise((resolve, reject) => {
      this.$http({
        method: "GET",
        url: this.nsjRouting.generate(
          "crm_formularios_modulos_get",
          { modulo: this.entity.projetotemplate, 
            tipomodulo: 0,
          },
          true,
          true
        ),
      })
        .then((response: any) => {
          resolve(response.data);
        })
        .catch((erro: any) => {
          reject(erro);
        });
    });
  }

  /**
   * Função que seta na entidade os campos do projeto escolhido no template do passo 1
   * @param dadosProjeto Os dados do projeto que serão atribuídos a entidade
   */
  atribuiDadosProjeto(dadosProjeto: any) {
    //Preencho estabelecimento e cliente apenas se os campos não estiverem preenchidos
    if (!this.entity.estabelecimento) {
      this.entity.estabelecimento = dadosProjeto.estabelecimento
        ? dadosProjeto.estabelecimento
        : null;
    }

    if (!this.entity.cliente) {
      this.entity.cliente = dadosProjeto.cliente ? dadosProjeto.cliente : null;
    }

    this.entity.tipoprojeto = dadosProjeto.tipoprojeto
      ? dadosProjeto.tipoprojeto
      : null;

    this.entity.declaracaoescopo = dadosProjeto.declaracaoescopo
      ? dadosProjeto.declaracaoescopo
      : null;
    this.entity.objetivoscriterios = dadosProjeto.objetivoscriterios
      ? dadosProjeto.objetivoscriterios
      : null;
    this.entity.premissas = dadosProjeto.premissas
      ? dadosProjeto.premissas
      : null;
    this.entity.restricoes = dadosProjeto.restricoes
      ? dadosProjeto.restricoes
      : null;
    this.entity.estimativasrisco = dadosProjeto.estimativasrisco
      ? dadosProjeto.estimativasrisco
      : null;
  }

  /**
   * Atualiza o escopo da página
   */
  private reloadScope() {
    this.$scope.$applyAsync();
  }
}
