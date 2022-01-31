import angular = require('angular');

enum tipoEnderecoEnum {
  LOCAL = 0,
  ENTREGA = 1,
  COBRANCA = 2,
  COMERCIAL = 3
};

type TEndereco = {
  pais: { pais: string, nome: string };
  tipoendereco: number,
  municipio: { codigo: string, ibge: string },
  $$__submitting: boolean
};
export class AdicionarEnderecoEscopoFormController {

  static $inject = ['$scope', 'toaster', 'NsEnderecos', 'utilService'];

  public form: ng.IFormController;
  public entity: TEndereco;

  constructor(public $scope: angular.IScope, public toaster: any, public entityService: any, public utilService: any) { }

  $onInit() {
    this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
      if (newValue !== oldValue) {
        this.form.$setDirty();
      }
    }, true);

    // Iniciar pré selecionado como Brasil
    this.entity.pais = { pais: '1058', nome: "BRASIL" };

    // O tipo de endereço, nesse caso, é sempre Local
    this.entity.tipoendereco = tipoEnderecoEnum.LOCAL;
  }

  submit() {
    this.form.$submitted = true;
    if (this.form.$valid && !this.entity.$$__submitting) {

      return new Promise((resolve) => {
        this.entity.municipio.codigo = this.entity?.municipio.ibge;

        this.entityService._save(this.entity).then(({ data }) => {
          data.close = true;
          resolve(data);
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
}
