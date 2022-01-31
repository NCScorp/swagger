import angular = require('angular');

import { CrmAtcsFormHistoricoService } from '../../Atcshistorico/modal';
import { CommonsUtilsService } from '../../../Commons/utils/utils.service';
import { Usuarios } from '../../../../usuarios/usuarios.service';
import { ISecaoController } from '../classes/isecaocontroller';
import { CrmAtcContaPagarService } from './factory';
import { CrmAtcContaPagar } from './classes/crmatccontapagar';
import { ECrmAtcContaPagarSituacao } from './classes/ecrmatccontapagarsituacao';
import { DesfazerContaPagarModalService } from './modal-desfazer/desfazer-conta-pagar-modal.service';
import { ContaEmprestimoModalService } from './modal-conta-emprestimo/conta-emprestimo-modal.service';
import { NsFormaspagamentos } from '../../../Ns/Formaspagamentos/factory';
import { ETipoFormaPagamento } from '../../../Ns/Formaspagamentos/classes/formapagamento.enums';
import { IFormaPagamento } from '../../../Ns/Formaspagamentos/classes/formapagamento.interfaces';
import { ECrmAtcContaPagarTipoProcessamento } from './classes/ecrmatccontapagartipoprocessamento';

export class CrmAtcContaPagarController {
  /**
   * Injeção de dependencias da tela.
   */
  static $inject = [
    '$scope',
    'toaster',
    'entity',
    'CrmAtcsFormHistoricoService',
    'CommonsUtilsService',
    'crmAtcContaPagarService',
    'Usuarios',
    'secaoctrl',
    'desfazerContaPagarModalService',
    'contaEmprestimoModalService',
    'NsFormaspagamentos',
  ];

  /**
   * Define se a tela foi inicializada.
   */
  public inicializado: boolean = false;
  /**
   * Define se a tela está em processamento
   */
  public busy: boolean = false;
  /**
   * Define mensagem de processamento
   */
  public busyMensagem: string = 'Carregando...';
  /**
   * Define se a tela está em processamento de inicialização
   */
  public busyInicializacao: boolean = false;
  /**
   * Dados utilizados para apresentar contas a pagar por fornecedor/documento
   */
  private arrDadosTabela: ILinhaTabela[] = [];
  /**
   * Define se a atualização da tela está ativada
   */
  public atualizacaoAtivada: boolean = false;

  // Enuns
  public ECrmAtcContaPagarSituacao = ECrmAtcContaPagarSituacao;

  /**
 * Faz o controle se o accordion de contas a pagar já foi carregado uma vez.
 * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
 */
  public accordionCarregado: boolean = false;

  constructor(
    public $scope: any,
    public toaster: any,
    /**
     * Entidade do atendimento comercial
     */
    public entity: any,
    /**
     * Service de modal de histórico do atendimento
     */
    public CrmAtcsFormHistoricoService: CrmAtcsFormHistoricoService,
    /**
     * Service de funções comuns
     */
    public CommonsUtilsService: CommonsUtilsService,
    /**
     * Service de contas a pagar
     */
    public crmAtcContaPagarService: CrmAtcContaPagarService,
    /**
     * Service de usuários
     */
    public Usuarios: Usuarios,
    /**
     * Controller da seção no atendimento comercial
     */
    public secaoCtrl: ISecaoController,

    private desfazerContaPagarModalService: DesfazerContaPagarModalService,
    private contaEmprestimoModalService: ContaEmprestimoModalService,
    private nsFormasPagamentos: NsFormaspagamentos
  ) {
    // Implemento função de ativação da atualização chamada pelo atendimento
    this.secaoCtrl.ativarAtualizacao = () => {
      if (!this.inicializado) {
        this.init();
      }

      //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar dados do accordion
      if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
        // Ativo busy de inicialização
        this.busyInicializacao = true;
        // Informo que a atualização está ativada
        this.atualizacaoAtivada = true;
        // Chamo função que carrega dados da tela;
        this.carregarDadosTela();
      }
    };
    // Implemento função de desativação da atualização chamada pelo atendimento
    this.secaoCtrl.pararAtualizacao = () => {
      this.atualizacaoAtivada = false;
    };
  }

  /* Carregamento */
  init() {
    if (this.entity && this.entity.negocio) {
      this.inicializado = true;
    }
  }

  /**
   * Verifica se possui permissão
   * @param chave
   */
  temPermissao(chave: any): boolean {
    return this.Usuarios.temPermissao(chave);
  }

  /**
   * Ativa o busy de processamento da tela.
   */
  private setBusy(mensagem = 'Carregando...') {
    this.busyMensagem = mensagem;
    this.busy = true;
  }

  /**
   * Atualiza a visualização do escopo tela
   */
  reloadScope() {
    this.$scope.$applyAsync();
  }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
     private async carregarDadosTela() {

      if(!this.busyInicializacao){
          this.setBusy();
      }

      try {
          let arrDadosApi = await this.crmAtcContaPagarService.getAll(this.entity.negocio);
          this.arrDadosTabela = this.montarLinhasTabela(arrDadosApi);
      } catch(error) {
      } finally{
          // Desativo loading
          this.busyInicializacao = false;
          this.busy = false;

          //Uma vez que o accordion foi carregado, seto a variável de controle para true
          this.accordionCarregado = true;

          this.reloadScope();

      }   
}

  /**
   * Retorna lista de linhas da tabela de contas a pagar
   * @param ficha
   */
  montarLinhasTabela(arrDadosApi: CrmAtcContaPagar[]): ILinhaTabela[] {
    // Organizo lista por fornecedor/numero do documento
    arrDadosApi = arrDadosApi.sort((cpA, cpB) => {
      // Se for fornecedor diferente, organizo pelo nome
      if (cpA.prestador.fornecedor != cpB.prestador.fornecedor) {
        if (cpA.prestador.nomefantasia < cpA.prestador.nomefantasia) {
          return -1;
        } else {
          return 1;
        }
      }

      // Se for o mesmo fornecedor, organizo pelo número do documento
      if (cpA.numerodocumento < cpA.numerodocumento) {
        return -1;
      } else {
        return 1;
      }
    });

    // Crio dados para popular tabela
    let arrDados: ILinhaTabela[] = [];
    let fornecedor = null;
    let documento = null;

    arrDadosApi.forEach((contaPagarForeach) => {
      // Se mudou o fornecedor ou o documento, crio uma linha de dados do fornecedor
      if (contaPagarForeach.prestador.fornecedor != fornecedor || contaPagarForeach.negociodocumento != documento) {
        const itemListaFornecedor: ILinhaTabela = {
          tipo: 'fornecedor',
          contapagar: contaPagarForeach,
          acoes: [],
        };
        itemListaFornecedor.acoes = this.montarAcoes(itemListaFornecedor);
        arrDados.push(itemListaFornecedor);

        // Atualizo novo fornecedor e documento
        fornecedor = contaPagarForeach.prestador.fornecedor;
        documento = contaPagarForeach.negociodocumento;
      }

      // Crio item do tipo 'contapagar' na lista
      const itemLista: ILinhaTabela = {
        tipo: 'contapagar',
        contapagar: contaPagarForeach,
        contapagar_old: angular.copy(contaPagarForeach),
        acoes: [],
      };
      arrDados.push(itemLista);
    });

    return arrDados;
  }

  /**
   * Retorna as ações referente ao contas a pagar
   * @param linha
   */
  montarAcoes(linha: ILinhaTabela): ILinhaTabelaAcao[] {
    const arrAcoes: ILinhaTabelaAcao[] = [];

    if (linha.tipo == 'fornecedor') {
      const { situacao } = linha.contapagar;

      if (
        situacao === ECrmAtcContaPagarSituacao.cacpsProcessado ||
        situacao === ECrmAtcContaPagarSituacao.cacpsFalhaAoCancelar
      ) {
        //Desfazer geração de Conta a Pagar
        arrAcoes.push({
          nome: 'Desfazer a geração de Conta a Pagar',
          icone: 'fas fa-undo-alt',
          funcao: (linha: ILinhaTabela) => {
            this.onDesfazerContaPagar(linha);
          },
        });
      }

      if (
        situacao === ECrmAtcContaPagarSituacao.cacpsNaoProcessado ||
        situacao === ECrmAtcContaPagarSituacao.cacpsFalhaAoGerar
      ) {
        arrAcoes.push({
          nome: 'Gerar Conta a Pagar',
          icone: 'fas fa-file-invoice-dollar',
          funcao: (linha: ILinhaTabela) => {
            this.onGerarContaPagar(linha.contapagar);
          },
        });
      }
    }

    return arrAcoes;
  }

  onDesfazerContaPagar(item: ILinhaTabela) {
    const modal = this.desfazerContaPagarModalService.open({}, { linha: [item] });

    modal.result
      .then((contaPagar) => {
        if (contaPagar) {
          this.setBusy('Cancelando contas a pagar...');

          this.crmAtcContaPagarService.desfazerContaPagar(item.contapagar.getDados())
            .then(() => {
              this.toaster.pop({
                type: 'success',
                title: 'A Conta a Pagar foi cancelada com sucesso!',
              });

              this.busyInicializacao = true;
              this.carregarDadosTela();
            })
            .catch((error) => {
              let mensagem =
                error instanceof Error
                  ? error.message
                  : error.status == 400
                    ? error.data.message
                    : 'Ocorreu um erro ao cancelar conta a pagar.';

              this.toaster.pop({
                type: 'error',
                title: mensagem,
              });
            })
            .finally(() => {
              this.busy = false;
              this.reloadScope();
            });
        }
      })
      .catch((e) => { });
  }

  async onGerarContaPagar(item: CrmAtcContaPagar) {
    let contaPagar = item.getDados();

    try {
      if (!item.prestador.forma_pagamento) {
        throw new Error('É necessário uma forma de pagamento');
      }

      const formaPagamento = (await this.nsFormasPagamentos.get(item.prestador.forma_pagamento)) as IFormaPagamento;

      if (formaPagamento.tipo === ETipoFormaPagamento.emprestimo) {
        const modal = this.contaEmprestimoModalService.open({}, {});
        const contaEmprestimoInformada = await modal.result.catch(() => { });

        if (!contaEmprestimoInformada) {
          return;
        }

        contaPagar.contaemprestimo = contaEmprestimoInformada;
      }

      this.setBusy('Gerando contas a pagar...');
      this.crmAtcContaPagarService.gerarContaPagar(contaPagar).then(() => {
        this.toaster.pop({
          type: 'success',
          title: 'A geração da Conta a Pagar foi feita com sucesso!',
        });

        this.busyInicializacao = true;
        this.carregarDadosTela();
      });
    } catch (error) {
      const mensagem =
        error instanceof Error
          ? error.message
          : error.status == 400
            ? error.data.message
            : 'Ocorreu um erro ao gerar conta a pagar.';

      this.toaster.pop({
        type: 'error',
        title: mensagem,
      });
    } finally {
      this.busy = false;
      this.reloadScope();
    }
  }

  /**
   * Verifica se a conta a pagar pode ser editada.
   * @param contapagar
   */
  podeEditarContaPagar(contapagar: CrmAtcContaPagar): boolean {
    return (
      [
        ECrmAtcContaPagarSituacao.cacpsImpedido,
        ECrmAtcContaPagarSituacao.cacpsNaoProcessado,
        ECrmAtcContaPagarSituacao.cacpsFalhaAoGerar,
      ].indexOf(contapagar.situacao) > -1
    );
  }

  /**
   * Quando valor de uma das propriedades do contas a pagar for alterado
   * @param linha
   * @param campo
   */
  onCampoContaPagarChange(linha: ILinhaTabela) {
    if (this.podeEditarContaPagar(linha.contapagar)) {
      // Verifico se houve mudanças
      let houveMudanca = false;

      houveMudanca =
        linha.contapagar.quantidade != linha.contapagar_old.quantidade ||
        linha.contapagar.valorpagar != linha.contapagar_old.valorpagar;

      if (!houveMudanca) {
        return;
      }

      this.setBusy('Atualizando conta a pagar...');

      this.crmAtcContaPagarService.put(linha.contapagar.getDados())
        .then(() => {
          // Atualizo objeto de conta a pagar de verificação de mudanças
          linha.contapagar_old = angular.copy(linha.contapagar);

          this.toaster.pop({
            type: 'success',
            title: 'Conta a pagar atualizada com sucesso.',
          });
        })
        .catch((error) => {
          this.toaster.pop({
            type: 'error',
            title: error,
          });
          linha.contapagar.quantidade = linha.contapagar_old.quantidade;
          linha.contapagar.valorpagar = linha.contapagar_old.valorpagar;
        })
        .finally(() => {
          this.busy = false;
          this.reloadScope();
        });
    }
  }

  /**
   * Abre modal de histórico
   * @param secao
   */
  abrirHistoricoModal(secao: string = 'contasapagar') {
    this.setBusy('Carregando histórico de contas a pagar...');
    const parameters = { negocio: this.entity.negocio };
    let filters = secao ? { secao: secao } : null;
    const modal = this.CrmAtcsFormHistoricoService.open(parameters, {}, filters)
      .result.then(() => { })
      .catch((error) => {
        if (error != 'fechar' && error != 'backdrop click' && error != 'escape key press') {
          this.toaster.pop({
            type: 'error',
            title: 'Erro ao tentar abrir histórico.',
          });
        }
      })
      .finally(() => {
        this.busy = false;
      });
  }

  /**
   * Retorna data formatada com data e hora
   * @param data
   */
  getDataFormatada(data: Date, formato: string = 'DD/MM/YYYY'): string {
    if (data != null) {
      return this.CommonsUtilsService.getDataFormatada(data.toISOString(), formato);
    } else {
      return '-';
    }
  }

  getTooltipPosition(index: number, tipo: string = 'impedido'): string {
    if (tipo != 'impedido') {
      return 'top';
    }

    if (index < 2) {
      return 'bottom';
    } else {
      return 'top';
    }
  }

  limparCampoValor(linha: ILinhaTabela, campo: string) {
    linha.contapagar[campo] = 0;

    this.onCampoContaPagarChange(linha);
  }
}

/**
 * Linha de dados representando serviços, produtos ou titulos na ficha financeira.
 */
interface ILinhaTabela {
  tipo: 'fornecedor' | 'contapagar';
  nome?: string;
  contapagar?: CrmAtcContaPagar;
  /**
   * Utilizado para validar alterações no contas a pagar
   */
  contapagar_old?: CrmAtcContaPagar;
  acoes: ILinhaTabelaAcao[];
}

interface ILinhaTabelaAcao {
  nome: string;
  icone?: string;
  funcao?: any;
}

