import angular = require('angular');

export class CrmBuscaPropostaItemService {
    static $inject = [ 'nsjRouting', '$http'];

    constructor( public nsjRouting: any, public $http: any) {
    }

    teste(constructors: any, offset: any, search: string, filters?: any, useFilterPromise?: boolean) {

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('ns_fornecedores_index', {'offset': offset, 'filter': search}, false),
                // timeout: useFilterPromise ? this.loading_deferred_filter.promise : this.loading_deferred.promise
            }).then((response: any) => {
                 resolve(response.data);
             }).catch((erro: any) => {
                 reject(erro);
            });

        });

    }

    /* Carrega propostas itens */
    carregaPropostasitens(proposta, atc) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_index', { negocio: atc, proposta }, false, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Carrega propostas itens funções
     * @param propostaitem 
     */
    carregaPropostasitensFuncoes(propostaitem) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_funcoes_index', { propostaitem }, false, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Carrega propostas itens famílias
     * @param propostaitem 
     */
    carregaPropostasitensFamilias(propostaitem) {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_familias_index', { propostaitem }, false, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Carrega orçamentos
     * @param propostaitem 
     */
    carregaOrcamento(propostaitem: string, arrPropostaitem: string[] = []){
        let constructors = {};
        let paramFilters: any = {
            offset: ''
        }

        if (arrPropostaitem.length == 0) {
            paramFilters.propostaitem = propostaitem
        } else {
            arrPropostaitem.forEach((propostaitemId, index) => {
                paramFilters[`propostaitem[${index}]`] = propostaitemId
            })
        }

        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_orcamentos_index', angular.extend({}, constructors, paramFilters), true, true),
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    async carregaFilhos(entity){
        // recebe entidade que contem lista de propostas itens de carregaPropostasitens()
        for (let i = 0; i < entity.length; i++) {
            const propostaItensFuncoes: any = await this.carregaPropostasitensFuncoes(entity[i].propostaitem);
            const propostaItensFamilias: any = await this.carregaPropostasitensFamilias(entity[i].propostaitem);
            entity[i].propostasitensfamilias = propostaItensFamilias;
            entity[i].propostasitensfuncoes = propostaItensFuncoes;
        }
    }

}
;