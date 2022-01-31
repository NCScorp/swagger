import angular = require('angular');

import { CrmFornecedorespedidoselecao } from './../../Crm/Fornecedorespedidoselecao/factory';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';

/**
 * Controller da modal de escolher prestadores do pedido.(Propostaitem)
 */
export class CrmPropostasitensPretadoresModalController {
    static $inject = [
        '$uibModalInstance',
        'constructors',
        'CrmFornecedorespedidoselecao',
        'CommonsUtilsService',
        'toaster',
        '$scope'
    ];

    // Declaro o enums no controller para utilizar no html
    private EnumOrigemConsulta = EnumOrigemConsulta;
    private EnumPrestadoraStatus = EnumPrestadoraStatus;

    /**
     * Lista de entidades da tela
     */
    private entities: IPrestadoraRanqueada[] = [];

    //Armazenam os identificadores dos municípios de falecimento e sepultamento
    public municipioFalecimento;
    public municipioSepultamento;

    constructor(
        public $uibModalInstance: any,
        public constructors: any,
        public service: CrmFornecedorespedidoselecao,
        public CommonsUtilsService: CommonsUtilsService,
        public toaster: any,
        public $scope: any,
    ) {
        this.service.filters = {};

        this.service.filters['municipioibge[0]'] = '99999999';

        //Se houver município de falecimento e de sepultamento, uso os codigos de ambos para filtrar
        if(this.constructors.temDoisMunicipios){

            //Quando há 2 municípios, a primeira posição SEMPRE será o município de falecimento e a segunda o de sepultamento
            this.service.filters['municipioibge[1]'] = this.constructors.municipios[0];
            this.service.filters['municipioibge[2]'] = this.constructors.municipios[1];

            this.municipioFalecimento = this.constructors.municipios[0];
            this.municipioSepultamento = this.constructors.municipios[1];

        }

        //Se não houverem 2 municípios, faço a busca usando apenas o município que foi passado
        if (this.constructors.municipioibge != null && !this.constructors.temDoisMunicipios) {
            this.service.filters['municipioibge[1]'] = this.constructors.municipioibge;
        }

        this.entities = this.service.reload();

        //Quando os prestadores forem carregados
        this.$scope.$on('crm_fornecedorespedidoselecao_list_finished', (event: any, args: any) => {

            //Função que ordena a lista de prestadores
            this.entities = this.getListaOrdenada(args);
        });

    }

    /**
     * Retorna se o service está buscando alguma informação
     */
    private isBusy(): boolean {
        return this.service.loadParams.busy;
    }

    /**
     * Fecha a modal, sem retornar informações
     */
    private close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Faz a busca dos dados, passando filtros
     * @param filter 
     */
    search(filter: any) {
        filter.filters['municipioibge[0]'] = '99999999';

        //Se houver município de falecimento e de sepultamento, uso os codigos de ambos para filtrar
        if(this.constructors.temDoisMunicipios){
            this.service.filters['municipioibge[1]'] = this.constructors.municipios[0];
            this.service.filters['municipioibge[2]'] = this.constructors.municipios[1];

            filter.filters['municipioibge[1]'] = this.constructors.municipios[0];
            filter.filters['municipioibge[2]'] = this.constructors.municipios[1];
        }

        //Se não houverem 2 municípios, faço a busca usando apenas o município que foi passado
        if (this.constructors.municipioibge != null && !this.constructors.temDoisMunicipios) {
            filter.filters['municipioibge[1]'] = this.constructors.municipioibge;
        }

        let entities = this.service.search(filter);

        return entities;
    }

    /**
     * Disparado quando uma linha da lista for clicada
     */
    onPrestadoraClick(prestadora: IPrestadoraRanqueada) {

        // Retorno prestadora selecionada
        this.$uibModalInstance.close({
            fornecedor: prestadora
        });
    }

    /**
     * Retorna a data e hora formatada
     * @param dataStr 
     */
    private getData(dataStr: string): string {
        let data = '';

        if (dataStr != null) {
            data = this.CommonsUtilsService.getDataFormatada(dataStr, 'DD/MM/YYYY [às] HH:mm');
        }
        
        return data;
    }

    /**
     * Retorna o nome do status da prestadora
     */
    private getStatusNome(status: EnumPrestadoraStatus): string {
        let descricao = '';

        switch (status) {
            case EnumPrestadoraStatus.psAtiva: {
                descricao = 'ATIVA';
                break;
            }
            case EnumPrestadoraStatus.psBanida: {
                descricao = 'BANIDA';
                break;
            }
            case EnumPrestadoraStatus.psSuspensa: {
                descricao = 'SUSPENSA';
                break;
            }
        
            default:
                break;
        }

        return descricao;
    }

    /**
     * Recebe um CPF ou CNPJ por parâmetro e retorna o documento formatado
     * @param documento CPF ou CNPJ
     * @returns 
     */
    getDocumentoFormatado(documento: string){

        //Se o documento for nulo, retorno "Não Informado"
        if(documento == null){
            return 'Não Informado';
        }
        else{
            //Removendo caracteres que não são dígitos, caso possua, e verificando quantidade de dígitos para saber se é CPF ou CNPJ
            let docFormatado = documento.replace(/[^\d]/g, "").length == 11 
                ? this.CommonsUtilsService.getCpfFormatado(documento) 
                : this.CommonsUtilsService.getCnpjFormatado(documento);
            return docFormatado;
        }
    }

    
    /**
     * Função que retorna a lista de prestadores ordenada. Caso haja município de falecimento e sepultamento, os prestadores do município de falecimento devem ser exibidos primeiro, depois os prestadores do município de sepultamento e por último prestadores não rankeados.
     * Caso tenha somente um município, retorno os prestadores dele e depois os prestadores não rankeados.
     * Em um cenário onde não há municípios, simplesmente retorno a lista.
     * @param listaPrestadores Lista de prestadores de serviço a ser ordenada
     * @returns 
     */
    getListaOrdenada(listaPrestadores: any){

        let listaOrdenada = [];
        let ordenacaoFalecimento = [];
        let ordenacaoSepultamento = [];
        let ordenacaoSemRank = [];

        //Cenário em que há município de falecimento e sepultamento
        if(this.constructors.temDoisMunicipios && this.municipioFalecimento != null && this.municipioSepultamento != null){

            for (let i = 0; i < listaPrestadores.length; i++) {
                const prestador = listaPrestadores[i];

                //Quando a prestadora for do município de falecimento, não altero sua ordem
                if(prestador.municipioibge == this.municipioFalecimento){
                    ordenacaoFalecimento.push(prestador);
                }
                else if (prestador.municipioibge == this.municipioSepultamento){
                    ordenacaoSepultamento.push(prestador);
                }
                else{
                    ordenacaoSemRank.push(prestador);
                }
                
            }

            //Verificando campo ordem dos prestadores de falecimento, para ordenação correta ser usada na busca
            for (let i = 0; i < ordenacaoFalecimento.length; i++) {
                const prestFal = ordenacaoFalecimento[i];

                //Cálculo: índice atual + 1
                prestFal.ordem = i + 1;

            }

            //Alterando campo ordem dos prestadores de sepultamento, para acumular ranking dos prestadores de falecimento
            for (let i = 0; i < ordenacaoSepultamento.length; i++) {
                const prestSep = ordenacaoSepultamento[i];

                //Cálculo: O comprimento do array de falecimento + índice atual + 1
                prestSep.ordem = ordenacaoFalecimento.length + i + 1;

            }

            //Alterando campo ordem dos prestadores não rankeados, para acumular ranking dos prestadores de falecimento e sepultamento
            for (let i = 0; i < ordenacaoSemRank.length; i++) {
                const prestSemRank = ordenacaoSemRank[i];

                //Cálculo: (O comprimento do array de falecimento + sepultamento) + índice atual + 1
                prestSemRank.ordem = (ordenacaoFalecimento.length + ordenacaoSepultamento.length) + i + 1;

            }

            //Retornando lista ordenada, seguindo o critério definido
            listaOrdenada.push(...ordenacaoFalecimento, ...ordenacaoSepultamento, ...ordenacaoSemRank);

        }

        //Cenário em que só há um dos dois municípios possíveis
        else if(!this.constructors.temDoisMunicipios && this.constructors.municipioibge != null){

            for (let i = 0; i < listaPrestadores.length; i++) {
                const prestador = listaPrestadores[i];

                //Quando a prestadora for do município, não altero sua ordem
                if(prestador.municipioibge == this.constructors.municipioibge){
                    listaOrdenada.push(prestador);
                }
                else{
                    ordenacaoSemRank.push(prestador);
                }
                
            }

            //Adicionando a lista os prestadores sem rankeamento
            listaOrdenada.push(...ordenacaoSemRank);

        }

        //Cenário em que não há nenhum dos dois municípios possíveis. Apenas retorno a lista, sem nenhuma alteração
        if(!this.constructors.temDoisMunicipios && this.constructors.municipioibge == null){
            return listaPrestadores;
        }

        return listaOrdenada;
        
    }

}

/**
 * Service da modal de escolher prestadores do pedido.(Propostaitem)
 */
export class CrmPropostasitensPretadoresModalService {
    static $inject = [
        '$uibModal'
    ];
 
    constructor(
        public $uibModal: any
    ) {}
    
 
    open(parameters: any) {
        return  this.$uibModal.open({
            template: require('./../../Crm/Propostasitens/modal-prestadores.html'),
            controller: 'CrmPropostasitensPretadoresModalController',
            controllerAs: 'crm_prpststns_fncd_mdl_ctrl',
            windowClass: '',
            size: 'lg',
            resolve: {
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}

// Interfaces utilizadas na tela
interface IPrestadoraRanqueada {
    fornecedor?: string;
    razaosocial?: string;
    nomefantasia?: string;
    status?: EnumPrestadoraStatus;
    origemconsulta?: EnumOrigemConsulta;
    ordem?: number;
    ultimaatualizacao?: string;
    estabelecimentoid?: IEstabelecimento;
}

interface IEstabelecimento {
    estabelecimento?: string;
}

enum EnumPrestadoraStatus {
    psAtiva = 0,
    psSuspensa = 1,
    psBanida = 2
}

enum EnumOrigemConsulta {
    pocRanking = 1,
    pocNormal = 2
}
