import angular = require('angular');

import { AtcDocumentoTela } from './../classes/atcdocumentotela';
import { CrmAtcDocumento } from '../../../Atcsdocumentos/classes/crmatcdocumento';
import { ECrmAtcDocumentoTipoNotaFiscal } from '../../../Atcsdocumentos/classes/ecrmatcdocumentostiponf';
import { CrmAtcContaPagarService } from '../../Atcscontaspagar/factory';
import { CrmAtcContaPagar } from '../../Atcscontaspagar/classes/crmatccontapagar';
import { ECrmAtcContaPagarSituacao } from '../../Atcscontaspagar/classes/ecrmatccontapagarsituacao';

export class CrmAtcsDocumentosInformacoesAdicionaisModalController {
    static $inject = [
        'toaster',
        '$scope', 
        '$uibModalInstance', 
        'constructors', 
        'documentotela', 
        '$http', 
        'nsjRouting', 
        'moment',
        'CrmAtcContaPagarService'
    ];
    public action: string;
    public form: any;
    public submitted: boolean = false;
    /**
     * Define se a tela está em processamento
     */
    public busy: boolean = false;
    /**
     * Define mensagem de processamento
     */
    public busyMensagem: string = 'Carregando...';

    public atcDocumento: CrmAtcDocumento = null;
    public arrContasPagar: {
        checked: boolean,
        contapagar: CrmAtcContaPagar
    }[] = [];

    public ECrmAtcDocumentoTipoNotaFiscal = ECrmAtcDocumentoTipoNotaFiscal;
    public dataAtual = new Date();

    constructor(
        public toaster: any,
        public $scope: any,
        public $uibModalInstance: any, 
        public constructors: any,
        public documentoTela: AtcDocumentoTela,
        public $http: any,
        public nsjRouting: any,
        public moment: any,
        public CrmAtcContaPagarService: CrmAtcContaPagarService
    ) {
        // Preencho informações da data atual
        this.dataAtual = new Date(`${this.dataAtual.getFullYear()}-${this.dataAtual.getMonth() + 1}-${this.dataAtual.getDate()}`);
        this.dataAtual.setUTCHours(23,59,59);

        // Preencho informações iniciais do documento
        this.atcDocumento = new CrmAtcDocumento({});
        this.atcDocumento.emissaodocumento = this.dataAtual.toISOString();
        this.atcDocumento.tiponf = ECrmAtcDocumentoTipoNotaFiscal.cadtnfNFs;
        
        this.setBusy('Buscando serviços...');
        this.carregarServicos();
    }

    /**
     * Atualiza a visualização do escopo tela
     */
    reloadScope() {
        this.$scope.$applyAsync();
    }

    /**
     * Ativa o busy de processamento da tela.
     */
    private setBusy(mensagem = 'Carregando...'){
        this.busyMensagem = mensagem;
        this.busy = true;
    }

    /**
     * Realiza busca dos serviços de contas a pagar para apresentar na tela
     */
    async carregarServicos(){
        try {
            this.arrContasPagar = [];
            // Busca dados da api
            let arrDadosApi = await this.CrmAtcContaPagarService.getAll(this.constructors.atc, {
                prestador: this.documentoTela.atctipodocumentorequisitante.tipodocumento.prestador
            });
            // Monta lista de contas a pagar na estrutura da tela
            arrDadosApi.filter((cpFilter) => {
                return cpFilter.situacao == ECrmAtcContaPagarSituacao.cacpsImpedido;
            }).forEach((cpForeach) => {
                this.arrContasPagar.push({
                    checked: false,
                    contapagar: cpForeach
                });
            });
        } catch(error) {
            this.close();
        } finally{
            // Desativo loading
            this.busy = false;
            this.reloadScope();
        }
    }

    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Retorna objeto de atcDocumento com informações adicionais preenchidas
     */
    adicionar(){
        // Verifico se escolheu o tipo de nf
        if (this.atcDocumento.tiponf == null) {
            this.toaster.pop({
                type: 'error',
                title: 'Selecione o tipo de nota fiscal!'
            });
            return;
        }

        // Verifico se preencheu a série
        if (!this.atcDocumento.seriedocumento || this.atcDocumento.seriedocumento.trim() == '') {
            this.toaster.pop({
                type: 'error',
                title: 'Preencha a série/sub-série!'
            });
            return;
        }

        if (!/^[0-9]+$/. test(this.atcDocumento.seriedocumento)){
            this.toaster.pop({
                type: 'error',
                title: 'A série/sub-série deve conter apenas números!'
            });
            return;
        }

        // Verifico se preencheu o número
        if (!this.atcDocumento.numerodocumento || this.atcDocumento.numerodocumento.trim() == '') {
            this.toaster.pop({
                type: 'error',
                title: 'Preencha o número do documento!'
            });
            return;
        }

        // Verifico se preencheu o a data de emissão
        if (!this.atcDocumento.emissaodocumento || this.atcDocumento.emissaodocumento.trim() == '') {
            this.toaster.pop({
                type: 'error',
                title: 'Preencha a data de emissão do documento!'
            });
            return;
        }

        // Verifico se a data de emissão é maior do que hoje
        let dtEmissao = new Date(this.atcDocumento.emissaodocumento);
        dtEmissao.setUTCHours(0,0,0);
        
        if (dtEmissao.valueOf() > this.dataAtual.valueOf()) {
            this.toaster.pop({
                type: 'error',
                title: 'A data de emissão do documento não pode ser maior que a data atual!'
            });
            return;
        }

        // Verifico se algum serviço foi selecionado
        this.atcDocumento.orcamentos = [];
        this.arrContasPagar.filter((cpSome) => {
            return cpSome.checked;
        }).forEach((cpForeach) => {
            this.atcDocumento.orcamentos.push({
                orcamento: cpForeach.contapagar.orcamento,
                xmlauxiliar: true
            });
        });

        if (this.atcDocumento.orcamentos.length == 0) {
            this.toaster.pop({
                type: 'error',
                title: 'Selecione ao menos um serviço!'
            });
            return;
        }

        // Retorno atc documento com informações extras preenchidas
        this.$uibModalInstance.close(this.atcDocumento);
    }

    isBusy(): boolean {
        return this.busy;
    }
}

/**
 * Service responsável por apresentar modal
 */
export class CrmAtcsDocumentosInformacoesAdicionaisModalService {
    static $inject = [
        '$uibModal'
    ];
 
    constructor(
        public $uibModal: any
    ) {}
 
    open(parameters: any = {}, documentoTela: any) {
        return this.$uibModal.open({
            template: require('./modal.html'),
            controller: 'CrmAtcsDocumentosInformacoesAdicionaisModalController',
            controllerAs: 'ctrlModal',
            windowClass: '',
            resolve: {
                constructors: () => {
                    return parameters;
                },
                documentotela: () => {
                    return documentoTela;
                }
            }
        });
    }
}
