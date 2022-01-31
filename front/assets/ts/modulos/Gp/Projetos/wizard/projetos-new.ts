import angular = require('angular');
import { FormulariosModulosService } from '../formularios-modulos.service';
import { ProjetosService } from '../projetos.service';

export class ProjetosNewController {

  static $inject = ['utilService', '$scope', '$stateParams', '$state', 'projetosService', 'toaster', 'entity', 'formulariosModulosService'];
  public busy: boolean = false;
  public form: any;
  public constructors: any;
  public passo: number = 1;
  public opcoes = {
    validarFormulario: () => {}, 
    nextstep: false
  };
  public action: string = this.entity.projeto ? 'update' : 'insert';
  public msgToaster: string = this.entity.projeto ? 'O Projeto foi iniciado com sucesso!' : 'O Projeto foi criado com sucesso!';
  public formulariosSalvar = [];

  constructor(
    public utilService: any, 
    public $scope: any, 
    public $stateParams: any, 
    public $state: any, 
    public projetosService: ProjetosService, 
    public toaster: any, 
    public entity: any,
    public formulariosModulosService: FormulariosModulosService
  ){
    this.constructors = projetosService.constructors;
  }

  $onInit() {
    this.onSubmitSuccess();
    this.onSubmitError();
    this.onSubmitFormularioSuccess();
  }

  onSubmitSuccess() {
    this.$scope.$on('projetos_submitted', (event: any, args: any) => {
      this.formulariosSalvar = args.entity.formularioList;
      if (this.formulariosSalvar.length > 0) {
      this.formulariosSalvar.forEach((formulario) => {
        formulario.tipomodulo = 0;
        formulario.modulo = this.entity.projeto;
        this.formulariosModulosService.save(formulario)
      });
      }else {
        this.toaster.pop({
          type: 'success',
          title: this.msgToaster
        });

        this.$state.go('projetos');
      }
    });
  }

  onSubmitFormularioSuccess() {
    this.$scope.$on('formularios_modulos_submitted', (event: any, args: any) => {
      
      const indexSalvo = this.formulariosSalvar.indexOf(args.entity)
      this.formulariosSalvar.splice(indexSalvo, 1)
      if (this.formulariosSalvar.length == 0) {
        this.toaster.pop({
          type: 'success',
          title: this.msgToaster
        });

        this.$state.go('projetos');
      }
      
    });
  }

  onSubmitError() {
    this.$scope.$on('projetos_submit_error', (event: any, args: any) => {
      if (args.response.status === 409) {
        if (confirm(args.response.data.message)) {
          this.entity[''] = args.response.data.entity[''];
          this.projetosService.save(this.entity);
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
              title: 'Ocorreu um erro ao criar o Projeto!',
              body: args?.response?.data?.message ? args.response.data.message : ''
            });
        }
      }
    });
  }

  /**
   * Função que incrementa o passo, para exibir a tela correspondente e preencher barra de progresso
   */
  clicouContinuar(){
    
    this.opcoes.nextstep = true;
    let valid = this.opcoes.validarFormulario();

    if(valid){
      this.passo++;
      this.opcoes.nextstep = false;
    }

  }

  /**
   * Função que decrementa o passo, para exibir a tela correspondente e manipular barra de progresso
   */
  clicouVoltar(){
    
    this.passo--;

  }

  submit() {

    this.entity.responsavel_conta_nasajon = JSON.stringify(this.entity.responsavel_conta_nasajon);
    this.projetosService.save(this.entity, false);
  }

}
