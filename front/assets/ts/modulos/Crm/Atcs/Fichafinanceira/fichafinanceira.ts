import angular = require('angular');

import { CrmFornecedoresenvolvidos } from './../../Fornecedoresenvolvidos/factory';
import { CrmOrcamentos } from '../../Orcamentos/factory';
import { CrmAtcsFormHistoricoService } from '../../Atcshistorico/modal';
import { CrmFornecedoresenvolvidosFormService } from '../../Fornecedoresenvolvidos/modal';
import { CommonsUtilsService } from '../../../Commons/utils/utils.service';
import { Usuarios } from '../../../../usuarios/usuarios.service';
import { CrmAtcsFichaFinanceiraAdicionarOrcamentoService } from './modal-adicionar-orcamento/modal-adicionar-orcamento';
import { EOrcamentoStatus } from '../../Orcamentos/classes/eorcamentostatus';
import { FichaFinanceiraFornecedor } from './classes/fichafinanceira';
import { IFornecedorenvolvido } from '../../Fornecedoresenvolvidos/classes/ifornecedorenvolvido';
import { IOrcamento } from '../../Orcamentos/classes/iorcamento';
import { Orcamento } from '../../Orcamentos/classes/orcamento';
import { ISecaoController } from '../classes/isecaocontroller';
import { EFaturamentoTipo } from '../../Orcamentos/classes/efaturamentotipo';
import { EDescontoGlobalTipo } from '../../Fornecedoresenvolvidos/classes/edescontoglobaltipo';
import { ModalExclusaoItemOrcamentoService } from './modal-exclusao';

export class CrmAtcsFichaFinanceiraController {

    /**
     * Injeção de dependencias da tela.
     */
    static $inject = [
        '$scope',
        'toaster',
        'entity',
        'CrmFornecedoresenvolvidos',
        'CrmOrcamentos',
        'CrmAtcsFormHistoricoService',
        'CrmFornecedoresenvolvidosFormService',
        'CommonsUtilsService',
        'Usuarios',
        'CrmAtcsFichaFinanceiraAdicionarOrcamentoService',
        'secaoctrl',
        '$rootScope',
        'ModalExclusaoItemOrcamentoService',
        'nsjRouting', 
        '$http',
        '$timeout'
    ];

    public EOrcamentoStatus = EOrcamentoStatus;
    public EFaturamentoTipo = EFaturamentoTipo;
    public EDescontoGlobalTipo = EDescontoGlobalTipo;

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
     * Dados utilizados para apresentar ficha financeira por fornecedor
     */
    private arrDadosFicha: IDadosFichaFornecedor[] = [];
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    public colspanValorCobertoApolice:number = 2;
    public taxaAdministrativaView: ITaxaAdministrativaView;
    private taxaAdministrativaEntity: ITaxaAdministrativaEntity;

    /**
     * Faz o controle se o accordion de ficha financeira já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    /**
    * Define timeout para alteração de linha de ficha financeira
    */
    public fichaFinanceiraTimeout: ng.ITimeoutService;

    /**
    * Guarda linhas da Ficha Financeira a serem enviadas na requisição após o timeout
    */
    public saveOrcamentoChange = [];
    /**
    * Informa se algum dos prestadores/fornecedores é da empresa 
    */
    public possuiPrestadorDaEmpresa: boolean

    constructor(
        public $scope: any,
        public toaster: any,
        /**
         * Entidade do atendimento comercial
         */
        public entity: any,
        /**
         * Service de fornecedores envolvidos
         */
        public CrmFornecedoresenvolvidos: CrmFornecedoresenvolvidos,
        /**
         * Service de orçamentos
         */
        public CrmOrcamentos: CrmOrcamentos,
        /**
         * Service de modal de histórico do atendimento
         */
        public CrmAtcsFormHistoricoService: CrmAtcsFormHistoricoService,
        /**
         * Service da modal de fornecedores envolvidos
         */
        public CrmFornecedoresenvolvidosFormService: CrmFornecedoresenvolvidosFormService,
        /**
         * Service de funções comuns
         */
        public CommonsUtilsService: CommonsUtilsService,
        /**
         * Service de usuários
         */
        public Usuarios: Usuarios,
        /**
         * Service da modal de adicionar produtos e serviços ao orçamento
         */
        public CrmAtcsFichaFinanceiraAdicionarOrcamentoService: CrmAtcsFichaFinanceiraAdicionarOrcamentoService,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController,
        public $rootScope: angular.IRootScopeService,
        public ModalExclusaoItemOrcamentoService: ModalExclusaoItemOrcamentoService,
        public nsjRouting: any, 
        public $http: any,
        public $timeout: any,
    ) {
        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
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

            this.getTaxaAdministrativaStatus().then(status=>{
                this.taxaAdministrativaView = {
                    mensagem : this.getMensagemTaxaAdministrativa(status),
                    icone : this.getIconeTaxaAdministrativa(status),
                    classe : this.getClasseTaxaAdministrativa(status),
                    status : status
                }
                this.reloadScope();
            })
            .catch(error=> console.error(error))

            
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }

        //Se o atendimento for cancelado ou finalizado, recarrego card de pedidos e ficha financeira
        this.$scope.$on('cancelouFinalizouAtc', (event, data) => {
            
            this.carregarDadosTela();
            
        });

    }

    /* Carregamento */
    Init() {
        this.ouvirMudancasPedido();
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;
        }
    }

    /**
     * Função que é usada para atualizar dados da ficha financeira se houver vinculo ou desvinculo de prestador no pedido
     */
    ouvirMudancasPedido(){

        this.$scope.$on('vinc_desv_fornecedor', (event: any, args: any) => {
            
            //Se houve vinculação ou desvinculação de prestador, recarrego ficha financeira
            this.carregarDadosTela();

        });

    }

    getIconeTaxaAdministrativa(status: number){
        switch (status) {
            case ETaxaAdmiminstativaStatus.gerada:{
                return 'fas fa-check fa-fw'
            }
            case ETaxaAdmiminstativaStatus.naogerada:{
                return 'fas fa-exclamation-triangle fa-fw'
            }
            case ETaxaAdmiminstativaStatus.naoInformada:{
                return 'fas fa-info-circle fa-fw'
            }
        }
    }

    getClasseTaxaAdministrativa(status: number){
        switch (status) {
            case ETaxaAdmiminstativaStatus.gerada:{
                return 'alert-success'
            }
            case ETaxaAdmiminstativaStatus.naogerada:{
                return 'alert-warning'
            }
            case ETaxaAdmiminstativaStatus.naoInformada:{
                return 'alert-info'
            }
        }
    }

    getMensagemTaxaAdministrativa(status:number){
      switch (status) {
        case ETaxaAdmiminstativaStatus.gerada:{
            return `
                Taxa administrativa no valor de ${this.formatoMoedaBRL(this.taxaAdministrativaEntity.valor)} gerada por ${this.taxaAdministrativaEntity.responsavelCriacao} em 
                ${this.getDataFormatada(new Date(this.taxaAdministrativaEntity.dataCriacao))} às ${this.getDataFormatada(new Date(this.taxaAdministrativaEntity.dataCriacao),'HH:mm:ss')}.
            `
        }

        case ETaxaAdmiminstativaStatus.naogerada:{
            return `Taxa administrativa no valor de ${this.formatoMoedaBRL(this.taxaAdministrativaEntity.valor)} não gerada.`
        }

        case ETaxaAdmiminstativaStatus.naoInformada:{
            return 'Taxa administrativa não informada para essa seguradora.'
        }     
      }
    }

    formatoMoedaBRL(valor){
      const REAL =   Intl.NumberFormat("pt-BR", {
            style: "currency",
            currency: "BRL",
      })

      return REAL.format(valor)
    }

    // 1- taxa gerada  2 - taxa não gerada 3- taxa não informada 
    async getTaxaAdministrativaStatus(){
        if(this.entity.valortaxaadm){
            this.taxaAdministrativaEntity = {
                valor : this.entity.valortaxaadm,
                responsavelCriacao: this.entity.createdcontratotaxa_by?.nome,
                dataCriacao: this.entity.createdcontratotaxa_at
            }

            if(this.entity.contratotaxaadm){
                return ETaxaAdmiminstativaStatus.gerada
            } 

            return ETaxaAdmiminstativaStatus.naogerada;
        } else{
            const {data:taxaAdministrativa }= await this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_configuracoestaxasadministrativas_index', { 'estabelecimento': this.entity.estabelecimento.estabelecimento, 'seguradora': this.entity.cliente.cliente}, true, true)
            });
            
            if(taxaAdministrativa?.length){
                this.taxaAdministrativaEntity = {valor: taxaAdministrativa[0].valor}
                this.entity.configuracaoTaxaAdministrativa = taxaAdministrativa[0];
                return ETaxaAdmiminstativaStatus.naogerada;
            }
            
            return  ETaxaAdmiminstativaStatus.naoInformada;
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
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela() {

        if(!this.busyInicializacao){
            this.setBusy();
        }

        try {
            const arrDadosApi = await this.CrmFornecedoresenvolvidos.getDadosFichaFinanceira(this.entity.negocio, this.entity.propostas[0].proposta);
            const arrFichas = FichaFinanceiraFornecedor.getFichasFinanceiras(arrDadosApi);

            this.verificaPrestadorDaEmpresa(arrDadosApi);
            // Monto linhas de orçamentos utilizados na visualização da tabela
            this.arrDadosFicha = [];
            arrFichas.forEach((fichaForeach) => {
                this.montarDadosFichaFornecedor(fichaForeach);
            });
        } catch (error) {}

        // Desativo loading
        this.busyInicializacao = false;
        this.busy = false;
        
        //Uma vez que o accordion foi carregado, seto a variável de controle para true
        this.accordionCarregado = true;

        this.reloadScope();
    }

    /**
     * Verifica se algum dos prestadores/fornecedores é da própria empresa. Essa informação é usada para dizer quando exibir aviso/opção no menu de Taxa Administrativa
     * Se algum dos prestadores/fornecedores for da empresa, não é exibida aviso/opção no menu de Taxa Administrativa
     * Se nenhum dos prestadores/forncedores for da empresa, então o aviso/opção no menu são exibidos.
     */
    verificaPrestadorDaEmpresa(prestadores) {
      this.possuiPrestadorDaEmpresa = prestadores.some((prestador) => {
        if (prestador.fornecedor_estabelecimento) {
          return true; // quando o prestador possui estabelecimento, ele é da empresa
        }
      });
      this.entity.possuiPrestadorDaEmpresa = this.possuiPrestadorDaEmpresa;
      this.$rootScope.$broadcast('verificou_possui_prestador_empresa', this.possuiPrestadorDaEmpresa);
    }

    /**
     * Ativa o busy de processamento da tela.
     */
    private setBusy(mensagem = 'Carregando...'){
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
     * Monta dados utilizados para apresentar ficha financeira do fornecedor
     * @param ficha 
     */
    montarDadosFichaFornecedor(ficha: FichaFinanceiraFornecedor){
        const dadoFicha: IDadosFichaFornecedor = {
            ficha: ficha,
            linhasServicosProdutos: this.montarLinhasTabela(ficha),
            acoes: this.montarAcoesFicha(ficha)
        }

        // Busco o objeto referente aos dados
        let indice = this.arrDadosFicha.findIndex((dadoFichaFindIndex) => {
            return dadoFichaFindIndex.ficha.fornecedorEnvolvido.fornecedor.fornecedor == ficha.fornecedorEnvolvido.fornecedor.fornecedor;
        });

        // Atualizo dados da ficha
        if (indice > -1) {
            this.arrDadosFicha[indice] = dadoFicha;
        } else {
            this.arrDadosFicha.push(dadoFicha);
        }
    }

    // Abre modal de confirmação de exclusão do Item do Orçamento
    abrirModalExclusao(itemOrcamento: any): Promise<boolean>{

        return new Promise((resolve, reject) => {

            let modal = this.ModalExclusaoItemOrcamentoService.open(itemOrcamento);
            modal.result.then((dados: any)=>{

                resolve(true);

            }).catch((error)=>{
                if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                    reject(error);
                }
                else{
                    resolve(false);
                }
            });

        });
    }

    /**
     * Retorna lista de orçamentos de serviços e produtos para apresentar na tabela
     * @param ficha
     */
    montarLinhasTabela(ficha: FichaFinanceiraFornecedor): ILinhaTabela[]{
        let arrOrcamentosServicos = ficha.getOrcamentosServicos();
        let arrOrcamentosMercadorias = ficha.getOrcamentosMercadorias();

        let arrDados: ILinhaTabela[] = [];

        // Adiciono lista de serviços
        if (arrOrcamentosServicos.length > 0) {
            arrDados.push({tipo: 'titulo', nome: 'Serviços', acoes: []});

            arrOrcamentosServicos.forEach((orcamentoForeach) => {
                const linha: ILinhaTabela = {
                    tipo: 'valor',
                    orcamento: orcamentoForeach,
                    orcamento_old: angular.copy(orcamentoForeach),
                    acoes: []
                };
                if (ficha.acionado() && ficha.status.status == EOrcamentoStatus.osAberto) {
                    if (this.temPermissao('crm_orcamentos_create') && this.entity.status != 3 && this.entity.status != 4 ) {
                        // Excluir item de orçamento
                        linha.acoes.push({
                            nome: 'Excluir item',
                            icone: 'fas fa-trash',
                            funcao: (dado: IDadosFichaFornecedor, linha: ILinhaTabela) => {
                                this.onExcluirOrcamentoClick(dado, linha);
                            }
                        });
                    }
                }
                arrDados.push(linha);
            })
        }

        // Adiciono lista de mercadorias
        if (arrOrcamentosMercadorias.length > 0) {
            arrDados.push({tipo: 'titulo', nome: 'Produtos', acoes: []});
            arrOrcamentosMercadorias.forEach((orcamentoForeach) => {
                const linha: ILinhaTabela = {
                    tipo: 'valor',
                    orcamento: orcamentoForeach,
                    orcamento_old: angular.copy(orcamentoForeach),
                    acoes: []
                };
                if (ficha.acionado() && ficha.status.status == EOrcamentoStatus.osAberto && this.entity.status != 3 && this.entity.status != 4) {
                    // Excluir item de orçamento
                    linha.acoes.push({
                        nome: 'Excluir item',
                        icone: 'fas fa-trash',
                        funcao: (dado: IDadosFichaFornecedor, linha: ILinhaTabela) => {
                            this.onExcluirOrcamentoClick(dado, linha);
                        }
                    });
                }
                arrDados.push(linha);
            })
        }

        return arrDados;
    }

    /**
     * Retorna as ações referente ao fornecedor da ficha financeira
     * @param ficha 
     */
    montarAcoesFicha(ficha: FichaFinanceiraFornecedor): IDadosFichaFornecedorAcao[] {
        const arrAcoes: IDadosFichaFornecedorAcao[] = [];

        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não carrego as ações na ficha financeira 
        if (this.entity.status == 3 || this.entity.status == 4) return arrAcoes;

        // Se já foi acionado
        if (ficha.acionado()) {
            if (ficha.status.status == EOrcamentoStatus.osAberto) {
                // Adicionar produtos e/ou serviços
                if (this.temPermissao('crm_orcamentos_create')) {
                    arrAcoes.push({
                        nome: 'Adicionar Serviços e/ou Produtos',
                        icone: 'fas fa-plus',
                        funcao: (dado: IDadosFichaFornecedor) => {
                            this.onAdicionarOrcamentoClick(dado);
                        }
                    });
                }
                // Cancelar acionamento da prestadora
                if (this.temPermissao('crm_fornecedoresenvolvidos_delete')) {
                    arrAcoes.push({
                        nome: 'Cancelar Acionamento',
                        icone: 'fas fa-times',
                        funcao: (dado: IDadosFichaFornecedor) => {
                            this.onCancelarAcionamentoPrestadoraClick(dado);
                        }
                    });
                }
                // Aprovar orçamento
                if (this.temPermissao('crm_orcamentos_aprovar')) {
                    arrAcoes.push({
                        nome: 'Aprovar Orçamento',
                        icone: 'fas fa-check',
                        funcao: (dado: IDadosFichaFornecedor) => {
                            this.onAprovarOrcamentoPrestadoraClick(dado);
                        }
                    });
                }
                // Apresentar descontos
                if (!ficha.apresentarInformacoesDesconto() && this.podeEditarFicha(ficha)) {
                    arrAcoes.push({
                        nome: 'Configurações de Desconto',
                        icone: 'fas fa-comment-dollar',
                        funcao: (dado: IDadosFichaFornecedor) => {
                            dado.ficha.apresentarInfoDesconto = true;
                            dado.acoes = this.montarAcoesFicha(dado.ficha);
                        }
                    });
                }
            } else if (ficha.status.status == EOrcamentoStatus.osAprovado) {
                // Reabrir orçamento
                if (this.temPermissao('crm_orcamentos_reabrir')) {
                    arrAcoes.push({
                        nome: 'Reabrir Orçamento Aprovado',
                        icone: 'fas fa-undo',
                        funcao: (dado: IDadosFichaFornecedor) => {
                            this.onReabrirOrcamentoPrestadoraClick(dado);
                        }
                    });
                }
            }
        }

        return arrAcoes;
    }

    /**
     * Verifica se a ficha pode ser editada.
     * Retorna true caso o status do orçamento seja Aberto
     * @param ficha
     */
    podeEditarFicha(ficha: FichaFinanceiraFornecedor): boolean {
        return ficha.status.status == EOrcamentoStatus.osAberto;
    }

    /**
     * Verifica se pode editar um campo do orçamento
     * @param ficha 
     * @param linha 
     * @param campo 
     */
    podeEditarCampoOrcamento(ficha: FichaFinanceiraFornecedor, linha: ILinhaTabela, campo: string): boolean {
        // Verifico se ficha pode ser editada
        if (!this.podeEditarFicha(ficha)) {
            return false;
        }

        // Verifico se tem permissão de alteração
        if (!this.temPermissao('crm_orcamentos_create')) {
            return false;
        }

        // Se for um dos campos abaixo, deixo editar
        if (
            [
                'valorunitario', 'quantidade', 'desconto', 'valorreceber', 'faturamentotipo'
            ].indexOf(campo) > -1
        ) {
            return true;
        }

        // O custo só pode ser alterado caso o fornecedor possua estabelecimento
        if (campo == 'custo') {
            const estabelecimento = ficha.fornecedorEnvolvido.fornecedor.estabelecimentoid.estabelecimento;
            return estabelecimento != null && estabelecimento != '';
        }

        // O campo descrição só pode ser alterado, se estiver marcado que o orçamento possui descrição manual
        if (campo == 'descricao') {
            return linha.orcamento.descricaomanual;
        }
    }

    /**
     * Atualiza os descontos da ficha financeira
     * @param ficha 
     * @param descontoglobalunitario 
     * @param descontoglobaldirefenca 
     * @param orcamentodescontodiferenca 
     */
    private atualizarDescontosFicha(ficha: FichaFinanceiraFornecedor, descontoglobalunitario: number, descontoglobaldirefenca: number, orcamentodescontodiferenca: string, preservaValorReceber?: boolean){
        ficha.arrOrcamentos.forEach((orcamentoForeach) => {
            if (ficha.fornecedorEnvolvido.possuidescontoglobal) {
                if (orcamentoForeach.orcamento == orcamentodescontodiferenca) {
                    orcamentoForeach.descontoglobal = descontoglobaldirefenca;
                } else {
                    orcamentoForeach.descontoglobal = descontoglobalunitario;
                }
            } else {
                orcamentoForeach.descontoglobal = 0;
            }

            if (!ficha.fornecedorEnvolvido.possuidescontoparcial) {
              if (preservaValorReceber) {
                // alterar o desconto automaticamente recalcula o valorreceber, mas em alguns casos pode ser desejado preservá-lo
                const valorReceberAux = orcamentoForeach.valorreceber;
                orcamentoForeach.desconto = 0;
                orcamentoForeach.valorreceber = valorReceberAux;
              } else {
                orcamentoForeach.desconto = 0;
              }
            }
        });
    }

    /**
     * Verifica se pode realizar alterações de valor na ficha
     * @param ficha 
     * @param alterandoDescontoGlobal 
     */
    private podeAlterarCampoValor(ficha: FichaFinanceiraFornecedor, alterandoDescontoGlobal: boolean = false): boolean {
        // Busco totais
        let totalDescontos = ficha.getTotal('desconto');
        let totalDescontoGlobal = ficha.getTotal('descontoglobal');
        const totalOrcamento = ficha.getTotal('valor');

        if (!ficha.fornecedorEnvolvido.possuidescontoparcial) {
            totalDescontos = 0;
        }

        if (!ficha.fornecedorEnvolvido.possuidescontoglobal) {
            totalDescontoGlobal = 0;
        } else if (alterandoDescontoGlobal) {
            if (ficha.fornecedorEnvolvido.descontoglobaltipo == EDescontoGlobalTipo.dgtValor) {
                totalDescontoGlobal = ficha.fornecedorEnvolvido.descontoglobal;
            } else {
                totalDescontoGlobal = (totalOrcamento / 100) * ficha.fornecedorEnvolvido.descontoglobal;
            }
        }
        
        return (totalOrcamento >= (totalDescontos + totalDescontoGlobal));
    }
    /**
     * Quando valor de uma das propriedades do orçamento for alterado
     * @param ficha 
     * @param linha 
     * @param campo 
     */
    onCampoOrcamentoChange(ficha: FichaFinanceiraFornecedor, linha: ILinhaTabela, campo: string){
        if (this.podeEditarCampoOrcamento(ficha, linha, campo)) {
            // Verifico se houve mudanças
            let houveMudanca = false;

            houveMudanca = (linha.orcamento.descricao != linha.orcamento_old.descricao)
                || (linha.orcamento.custo != linha.orcamento_old.custo)
                || (linha.orcamento.valorunitario != linha.orcamento_old.valorunitario)
                || (linha.orcamento.quantidade != linha.orcamento_old.quantidade)
                || (linha.orcamento.desconto != linha.orcamento_old.desconto)
                || (linha.orcamento.valorreceber != linha.orcamento_old.valorreceber)
                || (linha.orcamento.faturamentotipo != linha.orcamento_old.faturamentotipo);

            //Se não houve mudança ou o atendimento está finalizado ou cancelado, saio da função
            if (!houveMudanca || (this.entity.status == 3 || this.entity.status == 4 )) {
                return;
            }

            // Se está alterando algum campo de valor e o desconto fica maior que o orçamento, apresento mensagem de erro
            if (['valorunitario', 'quantidade', 'desconto'].indexOf(campo) > -1 && !this.podeAlterarCampoValor(ficha)) {
                linha.orcamento.valorunitario = linha.orcamento_old.valorunitario;
                linha.orcamento.quantidade = linha.orcamento_old.quantidade;
                linha.orcamento.desconto = linha.orcamento_old.desconto;

                this.toaster.pop({
                    type: 'error',
                    title: 'O orçamento total não pode ser inferior a soma dos descontos.'
                });

                return;
            }

            // Reseto o temporizador caso outro campo seja alterado
            if (this.fichaFinanceiraTimeout) {
              this.$timeout.cancel(this.fichaFinanceiraTimeout);
            }

            // Adiciono a linha alterada em um array para enviar em uma única requisição
            let index = this.saveOrcamentoChange.findIndex((dados) => dados.linha.$$hashKey === linha.$$hashKey);
            if (index !== -1) {
              this.saveOrcamentoChange[index] = { linha: linha, ficha: ficha };
            } else {
              this.saveOrcamentoChange.push({linha: linha, ficha: ficha});
            }

            this.fichaFinanceiraTimeout = this.$timeout(() => {
              this.arrDadosFicha.map((dados) => {
                if (dados.ficha === ficha) {
                  dados.ficha['saving'] = true;
                }
              });
              this.saveOrcamentoChange.forEach((orcamento) => {
                this.CrmOrcamentos.put(orcamento.linha.orcamento.getDados()).then((retornoApi: any) => {
                  this.arrDadosFicha.map((dados) => {
                    if (dados.ficha['saving']) {
                      dados.ficha['saving'] = false;
                    }
                    dados.linhasServicosProdutos.map((linha) => {
                      if (linha === orcamento.linha) {
                        // Atualizo objeto de orçamento de verificação de mudanças
                        linha.orcamento_old = angular.copy(orcamento.linha.orcamento);
                      }
                      return linha === orcamento.linha;
                    });
                  });

                  // Atualizo desconto dos itens, caso esteja configurado
                  const descontoglobalunitario = retornoApi.descontoglobalunitario ? parseFloat(retornoApi.descontoglobalunitario) : 0;
                  const descontoglobalresto = retornoApi.descontoglobalresto ? parseFloat(retornoApi.descontoglobalresto) : 0;
                  const descontoglobalrestoorcamento = retornoApi.descontoglobalrestoorcamento || '';
                  const PRESERVA_VALOR_RECEBER = true;
                  this.atualizarDescontosFicha(orcamento.ficha, descontoglobalunitario, descontoglobalresto, descontoglobalrestoorcamento, PRESERVA_VALOR_RECEBER);
                  
                  // Atualizo informações
                  ficha.atualizarInformacoesGerais();
  
                  this.toaster.pop({
                    type: 'success',
                    title: 'Orçamento atualizado com sucesso.'
                  });
                })
                  .catch((error) => {
                    this.toaster.pop({
                      type: 'error',
                      title: error
                    });
                  })
                  .finally(() => {
                    this.busy = false;
                    this.reloadScope();
                  });
              });

              // Zero o array de orçamentos a serem salvos
              this.saveOrcamentoChange = [];

            }, 3000);
          }
    }

    /**
     * Disparado quando a ação de acionar prestador for clicada
     * @param dado 
     */
    onAcionarPrestadoraClick(dado: IDadosFichaFornecedor){

        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de abrir modal de acionamento
        if (this.entity.status == 3 || this.entity.status == 4) return;

        // Crio modal
        let modal = this.CrmFornecedoresenvolvidosFormService.open({
            atc: this.entity.negocio,
            proposta: this.entity.propostas[0].proposta
        }, {});

        // Abro modal e espero retorno
        modal.result.then((fornecedorenvolvido: IFornecedorenvolvido) => {
            fornecedorenvolvido.fornecedor = dado.ficha.fornecedorEnvolvido.fornecedor;
            fornecedorenvolvido.proposta = this.entity.propostas[0].proposta;

            this.setBusy('Acionando prestadora...');
            this.CrmFornecedoresenvolvidos.create(fornecedorenvolvido, this.entity.negocio).then(async () => {
                await this.carregarDadosTela();
                this.busy = false;

                // Apresento mensagem de sucesso
                this.toaster.pop({
                    type: 'success',
                    title: 'Prestadora acionada com sucesso.'
                });
            }).catch((error) => {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao tentar acionar prestadora. ' + error.message
                });
            }).finally(() => {
                this.busy = false;
                this.reloadScope();

                //disparar broadcast informando que os fornecedores envolvidos foram atualizados
                this.$rootScope.$broadcast('crm_fornecedoresenvolvidos_change');
                
            })
        }).catch((error) => {
            if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao tentar acionar prestadora.'
                });
            }
        })
    }

    /**
     * Disparado quando a ação de cancelar acionamento do prestador for clicada
     * @param dado 
     */
    onCancelarAcionamentoPrestadoraClick(dado: IDadosFichaFornecedor){

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        this.setBusy('Cancelando acionamento da prestadora...');
        this.CrmFornecedoresenvolvidos.excluir(dado.ficha.fornecedorEnvolvido.fornecedorenvolvido, this.entity.negocio).then(() => {
            dado.ficha.arrOrcamentos = [];
            dado.ficha.fornecedorEnvolvido.fornecedorenvolvido = null;
            dado.ficha.fornecedorEnvolvido.acionamentoaceito = false;
            dado.ficha.fornecedorEnvolvido.acionamentodata = null;
            dado.ficha.fornecedorEnvolvido.acionamentometodo = null;
            dado.ficha.fornecedorEnvolvido.acionamentorespostaprazo = null;

            dado.ficha.atualizarInformacoesGerais();

            this.montarDadosFichaFornecedor(dado.ficha);

            this.busy = false;

            // Apresento mensagem de sucesso
            this.toaster.pop({
                type: 'success',
                title: 'Acionamento cancelado com sucesso.'
            });

            //disparar broadcast informando que os fornecedores envolvidos foram atualizados
            this.$rootScope.$broadcast('crm_fornecedoresenvolvidos_change');
            
            dado.ficha.apresentarInfoDesconto = false;
        }).catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar cancelar acionamento da prestadora.'
            });
        }).finally(() => {
            this.busy = false;
            this.reloadScope();
        })
    }

    /**
     * Disparado quando a ação de aprovar orçamento do prestador for clicada
     * @param dado 
     */
    onAprovarOrcamentoPrestadoraClick(dado: IDadosFichaFornecedor){

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        if (dado.ficha.getOrcamentosServicos().length == 0) {
            this.toaster.pop({
                type: 'error',
                title: 'O orçamento precisa de ao menos um serviço para ser aprovado.'
            });
            return;
        }

        // Verifico se orçamentos estão preenchidos, caso não tenha configuração de permitir orçamento zerado
        if (!dado.ficha.permiteOrcamentoZerado) {
            const possuiOrcamentoZerado = dado.ficha.arrOrcamentos.some((orcamentoSome) => {
                // Verifico se possui orçamento zerado, quando marcado para faturar
                return orcamentoSome.faturamentotipo != EFaturamentoTipo.ftNaoFaturar && orcamentoSome.valor == 0;
            });

            // Se possui orçamento zerado, apresento erro e saio da função
            if (possuiOrcamentoZerado) {
                this.toaster.pop({
                    type: 'error',
                    title: 'Nao é possível aprovar orçamento, pois existem itens marcados para faturar sem orçamento preenchido.'
                });
                return;
            }
        }

        this.setBusy('Aprovando orçamento da prestadora...');
        dado.ficha.fornecedorEnvolvido.proposta = this.entity.propostas[0].proposta;
        this.CrmFornecedoresenvolvidos.aprovarOrcamentos(dado.ficha.fornecedorEnvolvido, this.entity.negocio).then(() => {
            // Atualizo situação e data de atualização
            dado.ficha.arrOrcamentos.forEach((orcamentoForeach) => {
                orcamentoForeach.status = EOrcamentoStatus.osAprovado;
                orcamentoForeach.updated_at = new Date();
            });

            dado.ficha.atualizarInformacoesGerais();

            this.montarDadosFichaFornecedor(dado.ficha);

            this.busy = false;

            // Apresento mensagem de sucesso
            this.toaster.pop({
                type: 'success',
                title: 'Orçamento aprovado com sucesso.'
            });
        }).catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar aprovar orçamento da prestadora. ' + error.message
            });
        }).finally(() => {
            this.busy = false;
            this.reloadScope();
        });
    }

    /**
     * Disparado quando a ação de reabrir orçamento do prestador for clicada
     * @param dado 
     */
    onReabrirOrcamentoPrestadoraClick(dado: IDadosFichaFornecedor){

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        this.setBusy('Reabrindo orçamento da prestadora...');
        dado.ficha.fornecedorEnvolvido.proposta = this.entity.propostas[0].proposta;
        this.CrmFornecedoresenvolvidos.reabrirOrcamentos(dado.ficha.fornecedorEnvolvido, this.entity.negocio).then(() => {
            // Atualizo situação e data de atualização
            dado.ficha.arrOrcamentos.forEach((orcamentoForeach) => {
                orcamentoForeach.status = EOrcamentoStatus.osAberto;
                orcamentoForeach.updated_at = new Date();
            });

            dado.ficha.atualizarInformacoesGerais();

            this.montarDadosFichaFornecedor(dado.ficha);

            this.busy = false;

            // Apresento mensagem de sucesso
            this.toaster.pop({
                type: 'success',
                title: 'Orçamento reaberto com sucesso.'
            });
        }).catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar reabrir orçamento da prestadora. ' + error.message
            });
        }).finally(() => {
            this.busy = false;
            this.reloadScope();
        })
    }

    /**
     * Disparado quando a ação de adicioanr item ao acionamento do prestador for clicada
     * @param dado 
     */
    onAdicionarOrcamentoClick(dado: IDadosFichaFornecedor){

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;

        // Crio modal
        let modal = this.CrmAtcsFichaFinanceiraAdicionarOrcamentoService.open({}, {
           atc: this.entity.negocio,
           fornecedor: {
               fornecedor: dado.ficha.fornecedorEnvolvido.fornecedor.fornecedor
           },
           faturamentotipo: this.getTipoFaturamentoDefault(dado.ficha),
           descricaomanual: false,
           proposta: this.entity.propostas[0].proposta,
           quantidade: 1
       });

       // Abro modal e espero retorno
       modal.result.then((orcamento: IOrcamento) => {
            this.setBusy('Adicionando item ao orçamento da prestadora...');
            this.CrmOrcamentos.create(orcamento).then(async (orcamentoAPI: IOrcamento) => {
                // Crio objeto de orçamento
                const orcamento = new Orcamento(orcamentoAPI);
                // Adiciono a ficha da prestadora
                dado.ficha.arrOrcamentos.push(orcamento);

                const descontoglobalunitario = orcamentoAPI.descontoglobalunitario ? parseFloat(orcamentoAPI.descontoglobalunitario) : 0;
                const descontoglobalresto = orcamentoAPI.descontoglobalresto ? parseFloat(orcamentoAPI.descontoglobalresto) : 0;
                const descontoglobalrestoorcamento = orcamentoAPI.descontoglobalrestoorcamento || '';
                this.atualizarDescontosFicha(dado.ficha, descontoglobalunitario, descontoglobalresto, descontoglobalrestoorcamento);

                // Atualizo informações
                dado.ficha.atualizarInformacoesGerais();

                this.montarDadosFichaFornecedor(dado.ficha);

                this.busy = false;

                // Apresento mensagem de sucesso
                this.toaster.pop({
                    type: 'success',
                    title: 'Item adicionado ao orçamento com sucesso.'
                });
            }).catch((error) => {
               this.toaster.pop({
                   type: 'error',
                   title: 'Erro ao tentar adicionar item no orçamento.'
               });
            }).finally(() => {
               this.busy = false;
               this.reloadScope();
            })
        }).catch((error) => {
            if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao tentar adicionar item no orçamento.'
                });
            }
        })
   }

    /**
     * Disparado quando a ação de excluir item orçamento do prestador for clicada
     * @param dado 
     */
    async onExcluirOrcamentoClick(dado: IDadosFichaFornecedor, linha: ILinhaTabela){

        //Se o atendimento estiver finalizado ou cancelado, não executar ação
        if (this.entity.status == 3 || this.entity.status == 4 ) return;
        
        try {
            let excluir = await this.abrirModalExclusao(linha);

            if(!excluir){
                return;
            }

        } catch (error) {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar excluir item do orçamento da prestadora. ' + error.message
            });
            return;
        }
        
        this.setBusy('Excluindo item do orçamento...');

        // Dados usados no momento de desvincular fornecedor na tree de pedidos
        let dadosOrcamento = linha.orcamento.getDados();
        let precisaDesvincular: boolean = dadosOrcamento.familia ? false : true;
        let propItemId = precisaDesvincular ? dadosOrcamento.propostaitem : null;
        
        this.CrmOrcamentos.excluir(linha.orcamento).then(async (retornoApi: any = {}) => {
            // Busco indice do item removido
            const itemIndex = dado.ficha.arrOrcamentos.findIndex((orcamentoFindIndex) => {
                return orcamentoFindIndex.orcamento == linha.orcamento.orcamento;
            });

            // Removo item
            dado.ficha.arrOrcamentos.splice(itemIndex, 1);

            // Atualizo desconto dos itens, caso esteja configurado
            const descontoglobalunitario = retornoApi.descontoglobalunitario ? parseFloat(retornoApi.descontoglobalunitario) : 0;
            const descontoglobalresto = retornoApi.descontoglobalresto ? parseFloat(retornoApi.descontoglobalresto) : 0;
            const descontoglobalrestoorcamento = retornoApi.descontoglobalrestoorcamento || '';
            this.atualizarDescontosFicha(dado.ficha, descontoglobalunitario, descontoglobalresto, descontoglobalrestoorcamento);

            // Atualizo informações
            dado.ficha.atualizarInformacoesGerais();

            this.montarDadosFichaFornecedor(dado.ficha);
            
            // Carregando dados da ficha financeira novamente caso seja apagado um orçamento de um serviço, os orçamentos filhos também são excluídos e ficha precisa ser atualizada
            await this.carregarDadosTela();

            this.busy = false;
            
            // Se houver proposta item para desvincular, atualizar a tree de pedidos
            if(precisaDesvincular){

                // Broadcast para atualizar tree de pedidos e limpar lookup de fornecedor
                this.$rootScope.$broadcast('desvinculaFornecedorTreePedidos', { propostaitem : propItemId });
            }

            // Apresento mensagem de sucesso
            this.toaster.pop({
                type: 'success',
                title: 'Item do orçamento excluído com sucesso.'
            });
        }).catch((error) => {
            this.toaster.pop({
                type: 'error',
                title: 'Erro ao tentar excluir item do orçamento da prestadora. ' + error.message
            });
        }).finally(() => {
            this.busy = false;
            this.reloadScope();
        })
    }

    /**
     * Quando valor de uma das propriedades de configuração de desconto for alterado
     * @param ficha 
     * @param campo 
     */
    onConfigDescontosChange(ficha: FichaFinanceiraFornecedor, campo: string){

        //Se o atendimento estiver finalizado ou cancelado (status = 3 ou 4) não executo ação de atualizar valores na ficha
        if (this.entity.status == 3 || this.entity.status == 4) return;

        if (this.podeEditarFicha(ficha)) {
            // Se for o tipo de desconto global, só zero o valor e aguardo outra informação ser atualizada
            if (campo == 'descontoglobaltipo') {
                ficha.fornecedorEnvolvido.descontoglobal = 0;
                return;
            }

            // Se for a configuração de possui desconto global, e estiver sendo alterada para true, não faço nada e aguardo o valor ser alterado
            if (campo == 'possuidescontoglobal') {
                ficha.fornecedorEnvolvido.descontoglobal = 0;
                ficha.fornecedorEnvolvido.descontoglobaltipo = EDescontoGlobalTipo.dgtValor;

                if (ficha.fornecedorEnvolvido.possuidescontoglobal) {
                    return;
                }
            }

            // Verifico se houve mudança
            const houveMudanca = ficha.fornecedorEnvolvido.possuidescontoparcial != ficha.configDescontosOld.possuidescontoparcial
                || ficha.fornecedorEnvolvido.possuidescontoglobal != ficha.configDescontosOld.possuidescontoglobal
                || ficha.fornecedorEnvolvido.descontoglobal != ficha.configDescontosOld.descontoglobal
                || ficha.fornecedorEnvolvido.descontoglobaltipo != ficha.configDescontosOld.descontoglobaltipo;
            
            if (!houveMudanca) {
                return;
            }
            
            // Não deixo um desconto percentual ser maior que 100%
            if (
                ficha.fornecedorEnvolvido.possuidescontoglobal && ficha.fornecedorEnvolvido.descontoglobaltipo == EDescontoGlobalTipo.dgtPercentual 
                && ficha.fornecedorEnvolvido.descontoglobal > 100
            ) {
                ficha.fornecedorEnvolvido.descontoglobal = 100;
            }

            if (['descontoglobal'].indexOf(campo) > -1 && !this.podeAlterarCampoValor(ficha, true)) {
                ficha.fornecedorEnvolvido.descontoglobal = ficha.configDescontosOld.descontoglobal;
                ficha.fornecedorEnvolvido.descontoglobaltipo = ficha.configDescontosOld.descontoglobaltipo;
                ficha.fornecedorEnvolvido.possuidescontoglobal = ficha.configDescontosOld.possuidescontoglobal;
                ficha.fornecedorEnvolvido.possuidescontoparcial = ficha.configDescontosOld.possuidescontoparcial;

                this.toaster.pop({
                    type: 'error',
                    title: 'O orçamento total não pode ser inferior a soma dos descontos.'
                });

                return;
            }

            this.setBusy('Atualizando configurações de desconto...');

            this.CrmFornecedoresenvolvidos.atualizarConfiguracoesDesconto(ficha.fornecedorEnvolvido, this.entity.negocio).then((retorno: any) => {
                // Atualizo situação e data de atualização
                ficha.arrOrcamentos.forEach((orcamentoForeach) => {
                    orcamentoForeach.updated_at = new Date();
                });
    
                const descontoglobalunitario = retorno.descontoglobalunitario ? parseFloat(retorno.descontoglobalunitario) : 0;
                const descontoglobalunitariodiferenca = retorno.descontoglobalresto ? parseFloat(retorno.descontoglobalresto) : 0;
                const orcamentodescontoglobaldiferenca = retorno.descontoglobalrestoorcamento || '';
                this.atualizarDescontosFicha(ficha, descontoglobalunitario, descontoglobalunitariodiferenca, orcamentodescontoglobaldiferenca);

                ficha.atualizarInformacoesGerais();
    
                this.montarDadosFichaFornecedor(ficha);
    
                this.busy = false;
    
                // Apresento mensagem de sucesso
                this.toaster.pop({
                    type: 'success',
                    title: 'Configurações de desconto atualizadas com sucesso.'
                });
            }).catch((error) => {
                this.toaster.pop({
                    type: 'error',
                    title: 'Erro ao tentar atualizar configurações de desconto. ' + error.message
                });

                ficha.fornecedorEnvolvido.descontoglobal = ficha.configDescontosOld.descontoglobal;
                ficha.fornecedorEnvolvido.descontoglobaltipo = ficha.configDescontosOld.descontoglobaltipo;
                ficha.fornecedorEnvolvido.possuidescontoglobal = ficha.configDescontosOld.possuidescontoglobal;
                ficha.fornecedorEnvolvido.possuidescontoparcial = ficha.configDescontosOld.possuidescontoparcial;
            }).finally(() => {
                this.busy = false;
                this.reloadScope();
            });
        }
    }

    /**
     * Abre modal de histórico
     * @param secao 
     */
    abrirHistoricoModal(secao: string = '') {
        this.setBusy('Carregando histórico da ficha financeira...');
        const parameters = { 'negocio': this.entity.negocio }
        let filters = secao ? { 'secao': secao } : null;
        const modal = this.CrmAtcsFormHistoricoService.open(parameters, {}, filters).result
            .then(()=>{})
            .catch((error)=>{
                if (error != "fechar" && error != "backdrop click" && error != 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: 'Erro ao tentar abrir histórico.'
                    });
                }
            }).finally(() => {
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
            return '';
        }
    }

    getFaturamentoDescricao(faturamentotipo: EFaturamentoTipo): string {
        return Orcamento.getFaturamentoDescricao(faturamentotipo);
    }

    /**
     * Pega o tipo de faturamento default por ficha se apresenta opção de tipo de faturamento
     * @param ficha 
     * @param faturamentotipo 
     */
    getTipoFaturamentoDefault(ficha: FichaFinanceiraFornecedor): EFaturamentoTipo {
        let tipofaturamento: EFaturamentoTipo = EFaturamentoTipo.ftNaoFaturar;
        const fornecedor = ficha.fornecedorEnvolvido.fornecedor;
        const estabelecimento = ficha.fornecedorEnvolvido.fornecedor.estabelecimentoid.estabelecimento;
        const fornecedorEstabelecimento = (estabelecimento != null && estabelecimento != '');

        if (this.entity.possuiseguradora) {
            // Se o fornecedor é terceirizado(não possui estabelecimento)
            if (!fornecedorEstabelecimento) {
                if (!fornecedor.esperapagamentoseguradora) {
                    tipofaturamento = EFaturamentoTipo.ftFaturarSeguradoraSemNFS;
                }
            } else {
                tipofaturamento = EFaturamentoTipo.ftFaturarSeguradoraComNFS;
            }
        } else {
            tipofaturamento = EFaturamentoTipo.ftFaturarClienteComNFS;
        }

        return tipofaturamento;
    }

    /**
     * Define se apresenta opção de tipo de faturamento
     * @param ficha 
     * @param faturamentotipo 
     */
    apresentarTipoFaturamento(ficha: FichaFinanceiraFornecedor, faturamentotipo: EFaturamentoTipo): boolean {
        const arrTiposFaturamentos: EFaturamentoTipo[] = [EFaturamentoTipo.ftNaoFaturar];
        const fornecedor = ficha.fornecedorEnvolvido.fornecedor;
        const estabelecimento = ficha.fornecedorEnvolvido.fornecedor.estabelecimentoid.estabelecimento;
        const fornecedorEstabelecimento = (estabelecimento != null && estabelecimento != '');

        if (this.entity.possuiseguradora) {
            // Se o fornecedor é terceirizado(não possui estabelecimento)
            if (!fornecedorEstabelecimento) {
                if (!fornecedor.esperapagamentoseguradora) {
                    arrTiposFaturamentos.push(EFaturamentoTipo.ftFaturarSeguradoraSemNFS);
                }
            } else {
                arrTiposFaturamentos.push(EFaturamentoTipo.ftFaturarSeguradoraComNFS);
            }
        } else {
            arrTiposFaturamentos.push(EFaturamentoTipo.ftFaturarClienteComNFS);
        }

        return arrTiposFaturamentos.indexOf(faturamentotipo) > -1;
    }
}

/**
 * Linha de dados representando serviços, produtos ou titulos na ficha financeira.
 */
interface ILinhaTabela {
    tipo: 'titulo' | 'valor';
    nome?: string;
    orcamento?: Orcamento;
    $$hashKey: string;
    /**
     * Utilizado para validar alterações no orçamento
     */
    orcamento_old?: Orcamento;
    acoes: IDadosFichaFornecedorAcao[];
}

interface IDadosFichaFornecedor {
    ficha: FichaFinanceiraFornecedor;
    linhasServicosProdutos: ILinhaTabela[];
    acoes: IDadosFichaFornecedorAcao[];
}

interface IDadosFichaFornecedorAcao{
    nome: string;
    icone?: string;
    funcao?: any
}

interface ITaxaAdministrativaView {
    mensagem:string ,
    icone:string , 
    classe:string,
    status?:number
};

interface ITaxaAdministrativaEntity{
    valor?:string
    dataCriacao?:string
    responsavelCriacao?: string
}


enum ETaxaAdmiminstativaStatus{
    gerada = 1,
    naogerada = 2,
    naoInformada = 3
}