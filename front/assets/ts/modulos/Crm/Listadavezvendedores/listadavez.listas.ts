import angular = require('angular');
import { CrmListaDaVezListasModalService } from './listadavez.listas.modal';
import { IListadavezVendedor } from './classes';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';

export class CrmListaDaVezListasController {
    /**
     * Lista de serviços injetados no controller
     */
    static $inject = [
        'CrmListadavezvendedores',
        'CommonsUtilsService',
        '$scope',
        'toaster'
    ];

    /**
     * Listagem das listas de vendedores
     */
    private entities: IListadavezVendedor[] = [];

    /**
     * Informa se a tela está processando algo
     */
    private busy: boolean = false;

    constructor(
        private service: any,
        private CommonsUtilsService: CommonsUtilsService,
        private $scope: any,
        private toaster: any
    ){
        this.ouvirSalvarListaModal();
        this.ouvirExcluirLista();
        this.service.filters = {};
        this.entities = this.service.reload();
    }

    /**
     * Evento disparado quando a modal retornar uma lista salva ou atualizada
     */
    private ouvirSalvarListaModal(){
        this.$scope.$on('crm_listadavezvendedores_modal_salvo', (event: any, args: any) => {
            this.entities = this.service.reload();
        });
    }

    /**
     * Evento disparado quando uma lista é excluida
     */
    private ouvirExcluirLista(){
        this.$scope.$on('crm_listadavezvendedores_deleted', (event: any, args: any) => {
            this.toaster.pop({
                type: 'success',
                title: 'A Lista de vendedores foi excluída com sucesso.'
            });
            this.entities = this.service.reload();
            this.busy = false;
        });
        this.$scope.$on('crm_listadavezvendedores_delete_error', (event: any, args: any) => {
            this.toaster.pop(
            {
                type: 'error',
                title: 'Erro ao excluir Lista de vendedores.'
            });
            this.busy = false;
        });

        this.$scope.$on('crm_listadavezvendedores_delete_error', (event: any, args: any) => {
            if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                this.toaster.pop(
                {
                    type: 'error',
                    title: 'Erro ao excluir Lista de vendedores.'
                });
            } else {
                this.toaster.pop(
                {
                    type: 'error',
                    title: 'Erro ao excluir Lista de vendedores.'
                });
            }
            this.busy = false;
        });
    }

    /**
     * Retorna se o service está buscando alguma informação
     */
    private isBusy(): boolean {
        return this.service.loadParams.busy || this.busy;
    }

    /**
     * Retorna uma data no formato padrão
     * @param data 
     */
    private getDataFormatada(data: string): string {
        if (data != null) {
            return this.CommonsUtilsService.getDataFormatoPadrao(data);
        } else {
            return "";
        }
    }

    /**
     * Ação de excluir lista de vendedores
     * @param lista 
     */
    private excluirLista(lista: IListadavezVendedor) {
        if (confirm('Tem certeza que deseja deletar?')) {
            this.busy = true;
            this.service.delete(lista, true);
        }
    }
}
