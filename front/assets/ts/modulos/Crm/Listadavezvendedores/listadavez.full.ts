import angular = require('angular');
import { IListadavezVendedor } from './classes';
import { CrmListaDaVezListasModalService } from './listadavez.listas.modal';

export class CrmListaDaVezFullController {
    static $inject = [
        'CrmListaDaVezListasModalService',
        '$rootScope'
    ];

    // Declaro enuns para utilizar no HTML
    private EnumAbasTela = EnumAbasTela;
    private EnumAcoesTela = EnumAcoesTela;

    /**
     * Define qual aba está ativa no momento.
     */
    private abaAtiva: EnumAbasTela = EnumAbasTela.atListas;

    /**
     * Objeto utilizado para fazer controle da chamada de acões da aba de Regras
     */
    public abaRegrasCtrl = {
        modoTela: EnumModoTelaRegra.mtrVisualizacao,
        /**
         * Ação chamada ao editar regra
         */
        onAcaoEditar: () => {},
        /**
         * Ação chamada ao cancelar edição da regra
         */
        onAcaoCancelarEdicao: () => {},
        /**
         * Ação chamada ao salvar regra
         */
        onAcaoSalvar: () => {},
        /**
         * Verifica se a aba de regras está ocupada
         */
        isBusy: () => false
    }

    constructor(
        private CrmListaDaVezListasModalService: CrmListaDaVezListasModalService,
        private $rootScope: any
    ){}

    /**
     * Verifica se uma ação pode ser apresentada
     * @param acao 
     */
    private mostrarAcao(acao: EnumAcoesTela): boolean {
        let mostrar = true;

        switch (acao) {
            // Lista: Criar
            case EnumAcoesTela.atListaCriar: {
                mostrar = this.abaAtiva == EnumAbasTela.atListas;
                break;
            }

            // Regra: Editar
            case EnumAcoesTela.atRegrasEditar: {
                mostrar = this.abaAtiva == EnumAbasTela.atRegras
                    && this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao;
                break;
            }

            // Regra: Cancelar Edição
            case EnumAcoesTela.atRegrasCancelarEdicao: {
                mostrar = this.abaAtiva == EnumAbasTela.atRegras
                    && this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrEdicao;
                break;
            }

            // Regra: Salvar
            case EnumAcoesTela.atRegrasSalvar: {
                mostrar = this.abaAtiva == EnumAbasTela.atRegras
                    && this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrEdicao;
                break;
            }

            default:
                break;
        }

        return mostrar;
    }

    /**
     * Abre modal de criação/edição da lista da vez.
     * Caso a lista seja passada por parâmetro, abre a modal para visualizar a lista.
     * 
     * @param lista 
     */
    private abrirModalLista(lista: IListadavezVendedor = null) {
        const modal = this.CrmListaDaVezListasModalService.open(lista);

        modal.result.then((result) => {
            this.$rootScope.$broadcast('crm_listadavezvendedores_modal_salvo');
        }).catch((err) => {});
    }

    /**
     * Define qual aba vai ficar ativa
     */
    private ativarAba(tab: EnumAbasTela) {
        // Se a aba ativa for a aba de regras e ela estiver ocupada, não permito mudar de aba
        if (this.abaAtiva == EnumAbasTela.atRegras && this.abaRegrasCtrl.isBusy()) {
            return;
        }
        this.abaAtiva = tab;
    }
}

/**
 * Representação das Abas da tela
 */
enum EnumAbasTela {
    atListas,
    atRegras
}

/**
 * Representação das ações da tela
 */
enum EnumAcoesTela {
    atListaCriar,
    atRegrasEditar,
    atRegrasSalvar,
    atRegrasCancelarEdicao
}

/**
 * Representação do estado da aba de regras
 */
export enum EnumModoTelaRegra {
    mtrVisualizacao,
    mtrEdicao
}

