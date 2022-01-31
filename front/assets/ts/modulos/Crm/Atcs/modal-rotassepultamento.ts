import angular = require('angular');
import { AtcsDocumentosTreeService } from '../Atcsconfiguracoesdocumentos/atcsDocumentosTreeService';
import { ITreeControl, treeExpand } from '../../Commons/utils';
import { IAtcConfiguracaoDocumento, IAtcConfiguracaoTemplateEmail } from '../Atcsconfiguracoesdocumentos/classes';
import { Tree } from '../../Commons/tree';

/**
 * Serviço que vai chamar a modal de exibir rotas do endereço para cidade de sepultamento
 */
export class RotasSepultamentoModalService {
    static $inject = [
        '$uibModal', 
        '$http', 
        'nsjRouting', 
        'toaster',
    ];
    
    constructor(
        public $uibModal: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any,
    ) {}
    
    open(parameters: any, subentity: any) {
        return  this.$uibModal.open({
            template: require('../../Crm/Atcs/modal-rotassepultamento.html'),
            controller: 'RotasSepultamentoModalController',
            controllerAs: 'crm_atcs_rotas_sepultamento_mdl_ctrl',
            windowClass: '',
            size: 'md',
            resolve: {
                entity: subentity,
                constructors: () => {
                    return parameters;
                }
            }
        });
    }
}

/**
 * Controller da tela modal de enviar atendimento por e-mail
 */
export class RotasSepultamentoModalController {
    static $inject = [
        '$uibModalInstance',
        'entity',
        'nsjRouting',
        '$scope',
        'toaster',
        '$http',
        'moment',
        'Tree',
        'AtcsDocumentosTreeService',
        'CrmAtcs'
    ];

    private busy: boolean = false;

    public rotas;
    public error;

    constructor (
        public $uibModalInstance: any,
        public entity: any,
        public nsjRouting: any,
        public $scope: any,
        public toaster: any,
        public $http: any,
        public moment: any,
        public tree: Tree,
        public AtcsDocumentosTreeService: AtcsDocumentosTreeService,
        public entityService: any
    ){
    }

    $onInit() {
        this.busy = true;
        this.carregarDadosTela();
    }

    isBusy(): boolean {
        return this.busy;
    }

    /**
     * Faz o carregamento e conversão dos dados necessários ao funcionamento da tela
     */
    private async carregarDadosTela(){
        let enderecos = this.processaEnderecos();
        let dados = await this.postRotasSepultamento(
            enderecos.enderecoorigem,
            enderecos.enderecodestino,
            null
        );
        this.rotas = dados;

        // Removo loading de carregamento
        this.busy = false;
        this.reloadScope();
    }

    private processaEnderecos(){
        let logradouroDescricao = this.entity.localizacaotipologradouro?.descricao ?? '';
        let rua = this.entity.localizacaorua ?? '';
        let bairro = this.entity.localizacaobairro ?? '';
        let municipio = this.entity.localizacaomunicipio?.nome ?? '';
        let uf = this.entity.localizacaoestado?.uf ?? '';
        return {
            'enderecoorigem': (`${logradouroDescricao} ${rua} ` +
                `${bairro} ${municipio} ` +
                `${uf}`).trim(),
            'enderecodestino': `${this.entity.localizacaomunicipiosepultamento?.nome} ${this.entity.localizacaomunicipiosepultamento?.uf?.uf}`,
        };
    }

    /**
     * Atualiza a visualização da tree
     */
    reloadScope() {
        this.$scope.$applyAsync();
    }

    // Simulando função do service
    private postRotasSepultamento(enderecoorigem, enderecodestino, busca: string) {
        this.error = null;
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'POST',
                data: angular.copy({'enderecoorigem': enderecoorigem, 'enderecodestino': enderecodestino}),
                url: this.nsjRouting.generate('crm_atcs_index_rotas_google_directions', {id: this.entity.negocio, filter: busca}, true),
            }).then((dados: any) => {
                resolve(dados.data);
            }).catch((error) => {
                reject(error);
                this.error = error.data.message;
                this.busy = false;
                this.close();
                this.toaster.pop({
                    type: 'error',
                    title: error.data.message
                });
            });
        });
    }

    fecharTela(resposta: any){
        this.$uibModalInstance.close(resposta);
    }

    close() {
        if (!this.isBusy()) {
            this.$uibModalInstance.dismiss('fechar');
        }
    }
}

