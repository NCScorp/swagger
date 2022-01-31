import angular from "angular";
import { ProjetosDocumentosNecessariosModalService } from "./../documentosnecessariosmodal/documentosnecessarios.modal.service";
import { ICamposCustomizados } from "../../interfaces/campos-customizados.interface";
import { IProjeto } from "../../interfaces/projetos.interface";

type TDadosSeguradora = {
  respfinanceiroddi: number;
  respfinanceiroddd: number;
  respfinanceirotelefone: number;
  respfinanceiroprincipaltelefone?: number;
  respfinanceiroprincipaltelefoneramal?: number
};

export class ProjetosShowFormController {
  static $inject = ['$scope', 'projetosDocumentosNecessariosModalService']

  public entity: IProjeto;
  public dadosSeguradora: TDadosSeguradora;
  public camposCustomizados: { dadosResponsaveis: ICamposCustomizados };
  public camposCustomizadosConfig;

  constructor(public $scope: ng.IScope, public projetosDocumentosNecessariosModalService: ProjetosDocumentosNecessariosModalService) { }

  $onInit() {
    this.watchDadosClientes();
  }

  /**
   * Monto os dados da seguradora ao receber a variável de dadosClientes
   */
   watchDadosClientes() {
     
   this.$scope.$watch('$ctrl.entity.dadosAtc.dadosClientes', () => {

      if (this.entity.dadosAtc && this.entity.dadosAtc.dadosClientes.length > 0) {
        this.montaDadosSeguradoraResumido();
      }
    }, true);
  }

  /**
   * Prpearo os dados para preencher a table com as informações da seguradora
   */
  montaDadosSeguradoraResumido() {
    
    this.entity.dadosAtc.responsaveisfinanceiros.forEach((respfin) => {

      this.entity.dadosAtc.respfinanceiroprincipal = angular.copy(
        respfin.responsavelfinanceiro.nomefantasia
      );

      const cliente = this.entity.dadosAtc.dadosClientes.find((cliente) => cliente.cliente === respfin.responsavelfinanceiro.cliente);
      if (cliente) {
        if (cliente.contatos.length > 0 && cliente.contatos[0].telefones.length > 0) {
          this.dadosSeguradora = {
            respfinanceiroddi: cliente.contatos[0].telefones[0].ddi,
            respfinanceiroddd: cliente.contatos[0].telefones[0].ddd,
            respfinanceirotelefone: cliente.contatos[0].telefones[0].telefone
          };
        }
        if (respfin.principal) {
          this.dadosSeguradora.respfinanceiroprincipaltelefone =
            cliente.contatos.length > 0 && cliente.contatos[0].telefones.length > 0
              ? cliente.contatos[0].telefones[0].ddi +
              cliente.contatos[0].telefones[0].ddd +
              cliente.contatos[0].telefones[0].telefone
              : null;
          this.dadosSeguradora.respfinanceiroprincipaltelefoneramal =
            cliente.contatos.length > 0 && cliente.contatos[0].telefones.length > 0
              ? cliente.contatos[0].telefones[0].ramal
              : null;
        }
      }
    });

  }

  /**
   * Abro modal de documentos necessários
   */
  openDocumentosNecessariosModal(documentosNecessarios) {
    this.projetosDocumentosNecessariosModalService.open(documentosNecessarios);
  }
}
