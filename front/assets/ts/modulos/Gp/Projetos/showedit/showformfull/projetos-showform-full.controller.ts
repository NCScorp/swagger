import angular from "angular";
import { ProjetosDocumentosNecessariosModalService } from "./../documentosnecessariosmodal/documentosnecessarios.modal.service";
import { ICamposCustomizados } from "../../interfaces/campos-customizados.interface";
import { IProjeto } from "../../interfaces/projetos.interface";

type TDadosSeguradora = {
  respfinanceiroddi: string;
  respfinanceiroddd: string;
  respfinanceirotelefone: string;
  respfinanceiroprincipaltelefone?: string;
  respfinanceiroprincipaltelefoneramal?: string
};

export class ProjetosShowFormFullController {
  static $inject = ['$scope', 'projetosDocumentosNecessariosModalService']

  public entity: IProjeto;
  public camposCustomizados: { dadosResponsaveis: ICamposCustomizados };
  public camposCustomizadosConfig;
  public dadosSeguradoras = {};

  constructor(public $scope: ng.IScope, public projetosDocumentosNecessariosModalService: ProjetosDocumentosNecessariosModalService) { }

  $onInit() {
    this.watchDadosClientes();
  }
  
  /**
   * Monto os dados da seguradora ao receber a variável de dadosClientes
   */
  watchDadosClientes() {
    this.$scope.$watch('$ctrl.entity.dadosClientes', () => {
      this.montaDadosSeguradorasFull();
    }, true);
  }

  /**
   * Prpearo os dados para preencher a table com as informações da seguradora
   */
  montaDadosSeguradorasFull(): void {

    this.entity.dadosAtc?.responsaveisfinanceiros?.forEach((respfin) => {

      this.entity.dadosAtc.respfinanceiroprincipal = angular.copy(
        respfin.responsavelfinanceiro.nomefantasia
      );

      const cliente = this.entity.dadosAtc.dadosClientes.find((cliente) => cliente.cliente === respfin.responsavelfinanceiro.cliente);
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
   * Abro modal de documentos necessários
   */
  openDocumentosNecessariosModal(documentosNecessarios) {
    this.projetosDocumentosNecessariosModalService.open(documentosNecessarios);
  }
}
