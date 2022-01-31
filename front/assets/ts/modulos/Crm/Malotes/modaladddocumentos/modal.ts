import angular = require('angular');
import { EnumAtcDocumentoStatus, IMaloteDocumentoApi, IMaloteApi } from '../classes/classes';

/**
 * Serviço que vai chamar a modal de Adicionar documentos ao malote
 */
export class MalotesAdicionarDocumentosModalService {
    static $inject = ['$uibModal', '$http', 'nsjRouting', 'toaster'];
    
    constructor(
        public $uibModal: any, 
        public $http: angular.IHttpService, 
        public nsjRouting: any, 
        public toaster: any
    ) {}
    
    open(parameters: any, subentity: any) {
        return  this.$uibModal.open({
            template: require('.././../../Crm/Malotes/modaladddocumentos/modal.html'),
            controller: 'MalotesAdicionarDocumentosModalController',
            controllerAs: 'crm_mlts_doc_add_cntrllr',
            windowClass: '',
            size: 'lg',
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
 * Controller da tela modal de Adicionar documentos ao malote
 */
export class MalotesAdicionarDocumentosModalController {
    static $inject = [
        '$uibModalInstance',
        'entity',
        'nsjRouting',
        '$scope',
        'toaster',
        '$http',
        'moment'
    ];

    private busy: boolean = false;
    /**
     * Lista de atendimentos apresentados na tela, ao lado direito.
     */
    private arrAtendimentos: AtendimentoTela[] = [];
    /**
     * Cópia de arrAtendimentos, utilizada para fazer filtros sem perder as informações da lista original, 
     * apresentando os dados do lado esquerdo da tela.
     */
    private arrAtendimentosApresentadosNaTela: AtendimentoTela[] = [];

    constructor (
        public $uibModalInstance: any,
        public entity: any,
        public nsjRouting: any,
        public $scope: any,
        public toaster: any,
        public $http: any,
        public moment: any
    ){}

    $onInit() {
        // Apresento loading de carregamento
        this.busy = true;
        // Chamo função de carregamento inicial da tela
        this.carregarDadosTela();
    }

    isBusy(): boolean {
        return this.busy;
    }

    /**
     * Faz o carregamento e conversão dos dados necessários ao funcionamento da tela
     */
    private async carregarDadosTela(){
        let arrDocumentosSemMaloteFromApi: IAtcDocumentoSemMaloteApi[] = await this.getAtcsDocumentosSemMaloteFromApi((<IMaloteApi>this.entity).requisitantecliente.cliente);

        // Array para guardar a chave dos atendimentos que já foram criados
        const arrAtendimentosJaCriados: string[] = [];

        // Removo da lista de documentos sem malote os documentos do malote atual
        arrDocumentosSemMaloteFromApi = arrDocumentosSemMaloteFromApi.filter((docSemMaloteForeach) => {
            let entidade: IMaloteApi = this.entity;
            return !entidade.documentos.some((docSome) => {
                return docSome.documento.negociodocumento == docSemMaloteForeach.id_documento;
            })
        });

        // Percorro a lista de documentos sem malote para montar os objetos de atendimento e documentos da tela
        arrDocumentosSemMaloteFromApi.forEach((docSemMaloteForeach) => {
            let atendimento: AtendimentoTela = null;

            // Se já possui atendimento, busco o objeto do atendimento
            if (arrAtendimentosJaCriados.indexOf(docSemMaloteForeach.id_negocio) > -1) {
                atendimento = this.arrAtendimentos.find((atendimentoFind) => {
                    return atendimentoFind.negocio == docSemMaloteForeach.id_negocio;
                });
            }
            // Senão: Instancio outro objeto de atendimento 
            else {
                atendimento = new AtendimentoTela(
                    docSemMaloteForeach.id_negocio,
                    docSemMaloteForeach.codigo_negocio,
                    docSemMaloteForeach.nome_negocio
                );

                // Adiciono a lista de atendimentos já criados
                arrAtendimentosJaCriados.push(docSemMaloteForeach.id_negocio);

                // Adiciono a lista de atendimentos da tela. Ao mexer na referencia do item aqui, ainda estarei modificando o objeto adicionado a lista
                this.arrAtendimentos.push(atendimento);
            }

            // Defino tipos de copias do documento
            const arrDocumentoTiposCopias: EnumAtcDocumentoTipoCopia[] = [];

            if (docSemMaloteForeach.copiasimples) {
                arrDocumentoTiposCopias.push(EnumAtcDocumentoTipoCopia.ndtcSimples);
            }
            if (docSemMaloteForeach.copiaautenticada) {
                arrDocumentoTiposCopias.push(EnumAtcDocumentoTipoCopia.ndtcAutenticada);
            }
            if (docSemMaloteForeach.original) {
                arrDocumentoTiposCopias.push(EnumAtcDocumentoTipoCopia.ndtcOriginal);
            }

            // Instancio objeto do documento
            const documentoTela: AtcDocumentoTela = new AtcDocumentoTela(
                docSemMaloteForeach.id_documento,
                docSemMaloteForeach.nome_documento,
                this.moment(docSemMaloteForeach.data_recebimento).toDate(),
                docSemMaloteForeach.situacaodocumento,
                arrDocumentoTiposCopias,
                docSemMaloteForeach.url_documento
            );

            // Adiciono documentos ao objeto de atendimento da tela
            atendimento.arrAtcsDocumentos.push(documentoTela);
        });

        // Copio lista de atendimentos para o array que é apresentado na tela, ao lado esquerdo
        this.arrAtendimentosApresentadosNaTela = [].concat(this.arrAtendimentos);

        // Removo loading de carregamento
        this.busy = false;
        this.$scope.$applyAsync();
    }

    /**
     * Retorna a lista de documentos sem malote da api
     */
    private getAtcsDocumentosSemMaloteFromApi(seguradora: string): Promise<IAtcDocumentoSemMaloteApi[]>{
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_atcsdocumentos_get_atcs_documentos_sem_malotes', { id: seguradora }, false)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })      
        });
    } 

    /**
     * Evento disparado ao clicar em um atendimento na lista
     */
    private onAtendimentoClick(atendimento: AtendimentoTela){
        const atendimentoMarcado = atendimento.checked();

        // Se o atendimento estiver marcado, desmarco todos os documentos do atendimento.
        // Senão, marco todos os documentos do atendimento
        atendimento.arrAtcsDocumentos.forEach((documentoForeach) => {
            documentoForeach.checked = !atendimentoMarcado;
        });
    }

    /**
     * Retorna a data formatada no estilo 15/01/2000
     * @param data 
     */
    private getDataFormatada(data: Date): string {
        return this.moment(data).format('DD/MM/YYYY');
    }

    /**
     * Verifica se algum documento já foi selecionado na tela
     */
    private possuiDocumentosSelecionados(): boolean {
        return this.arrAtendimentos.some((atendimentoSome) => {
            return atendimentoSome.checked();
        });
    }

    /**
     * Realiza os filtros da tela, modificando o arrAtendimentosApresentadosNaTela
     * @param filtroTxt 
     */
    private filtrar(filtro: {
        search: string
    }) {
        const filtroTxt = filtro.search.toUpperCase();
        this.arrAtendimentosApresentadosNaTela = this.arrAtendimentos.filter((atendimentoFilter) => {
            return (atendimentoFilter.codigo.toUpperCase().indexOf(filtroTxt) > -1) ||
                (atendimentoFilter.descricao.toUpperCase().indexOf(filtroTxt) > -1);
        });
    }

    /**
     * Monta e retorna uma lista de malotes documentos baseado nos documentos selecionados
     */
    salvar() {
        // Monto lista de documentos, setando os documentos que já estão no malote
        const arrMalotesDocumentos: IMaloteDocumentoApi[] = (<IMaloteApi>this.entity).documentos;

        // Depois procuro todos os documentos marcados na tela, e adiciono a lista
        this.arrAtendimentos.filter((atendimentoFilter) => {
            return atendimentoFilter.checked();
        }).forEach(atendimentoForeach => {
            atendimentoForeach.arrAtcsDocumentos.filter((atcDocFilter) => {
                return atcDocFilter.checked;
            }).forEach((atcDocForeach) => {
                const maloteDoc: IMaloteDocumentoApi = {
                    malote: this.entity.malote,
                    tenant: this.entity.tenant,
                    documento: {
                        negocio: atendimentoForeach.negocio,
                        negociodocumento: atcDocForeach.negociodocumento,
                        tenant: this.entity.tenant,
                        status: atcDocForeach.status,
                        descricao: atcDocForeach.descricao,
                        url: atcDocForeach.url,
                        negocio_nome: atendimentoForeach.descricao,
                        tipodocumento_nome: atcDocForeach.descricao,
                        created_at: this.moment(atcDocForeach.dataRecebimento).format('YYYY-MM-DD')
                    }
                }
                arrMalotesDocumentos.push(maloteDoc);
            });
        });

        this.fecharTela({tipo: "sucesso", arrMalotesDocumentos: arrMalotesDocumentos});
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

// Classes, interfaces e Enuns

/**
 * Atendimentos apresentados na tela
 */
class AtendimentoTela {
    /**
     * Lista de documentos do atendimento
     */
    public arrAtcsDocumentos: AtcDocumentoTela[] = [];

    constructor(
        public negocio: string,
        public codigo: string,
        public descricao: string
    ){}

    /**
     * Retorna verdadeiro quando pelo menos um documento do atendimento está marcado
     */
    checked(): boolean {
        return this.arrAtcsDocumentos.some((docSome) => {
            return docSome.checked;
        });
    }
}

/**
 * Documentos de um atendimento
 */
class AtcDocumentoTela {
    public checked: boolean = false;

    constructor(
        public negociodocumento: string,
        public descricao: string,
        public dataRecebimento: Date,
        public status: EnumAtcDocumentoStatus,
        public arrTiposCopias: EnumAtcDocumentoTipoCopia[] = [],
        public url: string
    ){}

    /**
     * Retorna uma string com os tipos de cópias do documento no formato
     * [Tipo1] / [Tipo2] / [Tipo3]
     */
    public getTiposCopiasDescricao(): string {
        let txtTiposCopias = '';

        this.arrTiposCopias.forEach((tipoForeach) => {
            txtTiposCopias += (txtTiposCopias != '' ? ' / ' : '');
            switch (tipoForeach) {
                case EnumAtcDocumentoTipoCopia.ndtcSimples: {
                    txtTiposCopias += 'Simples';
                    break;
                }
                case EnumAtcDocumentoTipoCopia.ndtcOriginal: {
                    txtTiposCopias += 'Original';
                    break;
                }
                case EnumAtcDocumentoTipoCopia.ndtcAutenticada: {
                    txtTiposCopias += 'Autenticada';
                    break;
                }
            }
        });

        return txtTiposCopias;
    }

    /**
     * Retorna a descrição do status do documento
     */
    public getStatusDescricao(): string {
        switch (this.status) {
            case EnumAtcDocumentoStatus.ndsPendente: {
                return 'Pendente';
            }
            case EnumAtcDocumentoStatus.ndsRecebido: {
                return 'Recebido';
            }
            case EnumAtcDocumentoStatus.ndsPreAprovado: {
                return 'Pré-aprovado';
            }
            case EnumAtcDocumentoStatus.ndsEnviadoparaRequisitante: {
                return 'Enviado para Requisitante';
            }
            case EnumAtcDocumentoStatus.ndsAprovado: {
                return 'Aprovado';
            }
            case EnumAtcDocumentoStatus.ndsRecusado: {
                return 'Recusado';
            }
        }
    }
}

/**
 * Enumerado dos tipos de cópias do Negócio Documento
 */
enum EnumAtcDocumentoTipoCopia {
    ndtcSimples,
    ndtcOriginal,
    ndtcAutenticada
}

/**
 * Atcs documentos sem malote recebido da api
 */
interface IAtcDocumentoSemMaloteApi {
    // Dados do atendimento
    id_negocio: string;
    codigo_negocio: string;
    nome_negocio: string;
    // Dados do documento
    id_documento: string;
    nome_documento: string;
    data_recebimento: string;
    situacaodocumento: EnumAtcDocumentoStatus;
    copiasimples: boolean;
    copiaautenticada: boolean;
    original: boolean;
    url_documento: string
}

    