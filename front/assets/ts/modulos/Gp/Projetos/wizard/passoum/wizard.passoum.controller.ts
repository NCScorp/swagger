import angular = require("angular");

export class WizardPassoumController {
  static $inject = ["$scope", "$http", "nsjRouting"];

  private entity: any;
  public form: any;
  public opcoes: any;
  public projetotemplate: any;
  public busy: boolean = false;

  constructor(
    public $scope: angular.IScope,
    public $http: angular.IHttpService,
    public nsjRouting: any
  ) {}

  async $onInit() {
    this.entity.projetosTemplates = await this.listaTemplates();

    //Colocando o template em branco no inicio do array de templates
    this.entity.projetosTemplates.unshift({
      templatenome: "Em branco",
      templatedescricao: "Template em branco",
      selecionado: true,
    });

    this.$scope.$watch(
      "this.entity",
      (newValue, oldValue) => {
        if (newValue !== oldValue) {
          this.form.$setDirty();
        }
      },
      true
    );

    this.opcoes.validarFormulario = () => {
      return this.validarForm();
    };

    this.reloadScope();
  }

  /**
   * Seleciona o template clicado e atribui sua informação a entidade
   * @param projetoTemplate
   */
  selecionaTemplate(projetoTemplate: any) {
    this.entity.projetosTemplates.map((projetoTemplate) => {
      if (projetoTemplate.hasOwnProperty("selecionado")) {
        projetoTemplate.selecionado = false;
      }
    });

    projetoTemplate.selecionado = true;

    if (projetoTemplate.projeto) {
      this.entity.projetotemplate = projetoTemplate.projeto;
    } else {
      this.entity.projetotemplate = null;
    }
  }

  /**
   * Retorna todos os templates projetos existentes
   * @returns
   */
  listaTemplates() {
    this.busy = true;

    return new Promise((resolve, reject) => {
      this.$http({
        method: "GET",
        url: this.nsjRouting.generate(
          "financas_projetos_templatesindex",
          {},
          true,
          true
        ),
      })
        .then((response: any) => {
          resolve(response.data);
          this.busy = false;
        })
        .catch((erro: any) => {
          reject(erro);
          this.busy = false;
        });
    });
  }

  /**
   * Atualiza o escopo da página
   */
  private reloadScope() {
    this.$scope.$applyAsync();
  }

  validarForm() {
    return true;
  }
}
