import angular = require('angular');
import { ISecaoController } from './classes/isecaocontroller';

const imgBanned = require('../../../../img/banned.svg');

export class CrmAtcsFornecedoresAccordionController {
    public EnumFornecedorStatus = EFornecedorStatus;
    static $inject = [
        '$scope',
        'toaster',
        'entity',
        '$http',
        'nsjRouting',
        'moment',
        'secaoctrl'
    ];

    private busy: boolean = false;
    private busyInicializacao: boolean = false;
    private arrFornecedores: IAtcFornecedorDetalhado[] = [];
    private imgBanned = imgBanned;
    public inicializado: boolean = false;
    /**
     * Define se a atualização da tela está ativada
     */
    public atualizacaoAtivada: boolean = false;

    /**
     * Faz o controle se o accordion de prestadoras de serviços envolvidas já foi carregado uma vez.
     * Usado para o accordion ser carregado somente uma vez e não toda a vez que o accordion for acessado
     */
     public accordionCarregado: boolean = false;

    constructor(
        public $scope: any,
        public toaster: any,
        public entity: any,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public moment: any,
        /**
         * Controller da seção no atendimento comercial
         */
        public secaoCtrl: ISecaoController
    ) {
        // Implemento função de ativação da atualização chamada pelo atendimento
        this.secaoCtrl.ativarAtualizacao = () => {
            if (!this.inicializado) {
                this.Init();
            }

            //Se o accordion ainda não tiver sido carregado, entro no if para chamar função de carregar dados do accordion
            if (!this.accordionCarregado && !this.atualizacaoAtivada && this.inicializado) {
                // Informo que a atualização está ativada
                this.atualizacaoAtivada = true;
                this.busyInicializacao = true;
                // Chamo função que carrega dados da tela;
                this.carregarListaFornecedoresDoAtc();
            }
        }
        // Implemento função de desativação da atualização chamada pelo atendimento
        this.secaoCtrl.pararAtualizacao = () =>  {
            this.atualizacaoAtivada = false;
        }
    }

    /**
     * Faz o carregamento inicial da tela
     */
    Init(){
        this.ouvirMudancasPedido();
        if (this.entity && this.entity.negocio) {
            this.inicializado = true;
        }
    }

    /**
     * Função que é usada para atualizar dados do accordion se houver vinculo ou desvinculo de prestador no pedido
     */
    ouvirMudancasPedido(){

        this.$scope.$on('vinc_desv_fornecedor', (event: any, args: any) => {
            
            //Se houve vinculação ou desvinculação de fornecedor, atualizo accordion
            this.carregarListaFornecedoresDoAtc();

        });

    }

    /**
     * Carrega a lista de fornecedores do negócio
     */
    carregarListaFornecedoresDoAtc() {
        this.busy = true;
        
        this.getFornecedoresDetalhadosFromApi(this.entity.negocio).then((arrFornecedores) => {
            // Inicializo campos utilizados somente no frontEnd
            arrFornecedores.forEach((fornecedorForeach) => {
                fornecedorForeach.ver_mais_informacoes = false;
            });

            //Função que verifica se algum fornecedor envolvido não tem nenhum de seus dados bancários preenchidos
            this.arrFornecedores = this.verificaDadosBancariosNulos(arrFornecedores);

            //Função que inicializa contatos do fornecedor no formato esperado no accordion de prestadoras de serviços envolvidas e na modal de mais contatos
            if(arrFornecedores != null && arrFornecedores.length > 0){
                this.setInfoContatosFornecedor(arrFornecedores);
            }

            
        }).catch((err) => {
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro buscar lista de prestadores de serviço!'
            });
        }).finally(() => {
            this.busy = false;
            this.busyInicializacao = false;

            //Uma vez que o accordion foi carregado, seto a variável de controle para true
            this.accordionCarregado = true;

            this.$scope.$applyAsync();
        });
    }

    /**
     * Faz requisição para buscar a lista de fornecedores relacionados ao negócio, com informações detalhadas.
     */
    private getFornecedoresDetalhadosFromApi(atc: string): Promise<IAtcFornecedorDetalhado[]>{
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_fornecedoresenvolvidos_index_atc_fornecedores_detalhes', {atc: atc}, true, true)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            });
        });
    }

    /**
     * Recebe o array de fornecedores envolvidos para verificar se cada um deles não possui dados bancários para exibição
     * @param arrFornecedoresEnvolvidos 
     * @returns 
     */
    verificaDadosBancariosNulos(arrFornecedoresEnvolvidos: Array<IAtcFornecedorDetalhado>): Array<IAtcFornecedorDetalhado>{

        arrFornecedoresEnvolvidos.forEach( (fornecedor) => {

            //Se não há dados bancários
            if(fornecedor.conta_agenciadv == null && 
               fornecedor.conta_agencianumero == null && 
               fornecedor.conta_banco == null && 
               fornecedor.conta_contadv == null && 
               fornecedor.conta_contanumero == null
            ){
                fornecedor.conta_todos_nulos = true;
            }else{
                fornecedor.conta_todos_nulos = false;
            }
        });

        return arrFornecedoresEnvolvidos;
        
    }

    /**
     * Retorna uma string no formato "DD/MM/YYYY"
     * @param data 
     */
    private getDataFormatada(data: string): string {
        if (data != null) {
            return this.moment(data).format('DD/MM/YYYY');
        } else {
            return '';
        }
    }

    /**
     * Define se apresenta ou não Mais informações do fornecedor
     * @param fornecedor 
     */
    private verMais(fornecedor: IAtcFornecedorDetalhado): void{
        fornecedor.ver_mais_informacoes = !fornecedor.ver_mais_informacoes;
    }

    /**
     * Retorna o telafone do fornecedor formatado com DDD e Ramal
     * @param fornecedor 
     */
    private formatarTelFornecedor(fornecedor: IAtcFornecedorDetalhado): string {
        return (fornecedor.contato_tel_ddd ? '(' + fornecedor.contato_tel_ddd + ') ' : '') + // DDD
            (fornecedor.contato_tel_numero ? fornecedor.contato_tel_numero + ' ' : '') + // Telefone
            (fornecedor.contato_tel_ramal ? 'Ramal ' + fornecedor.contato_tel_ramal : '') // Ramal
    }

    /**
     * Retorna a descrição do status do fornecedor
     * @param status 
     */
    private getFornecedorStatusDescricao(status: EFornecedorStatus): string {
        let statusDescricao = '';

        switch (status){
            case EFornecedorStatus.fsAtivo: statusDescricao = 'Ativo'; break;
            case EFornecedorStatus.fsSuspenso: statusDescricao = 'Suspensa'; break;
            case EFornecedorStatus.fsBanido: statusDescricao = 'Banida'; break;
        }

        return statusDescricao;
    }

    /**
     * Função que recebe como parâmetro o array de fornecedores envolvidos para inicializar os seus contatos e setar na entidade no formato esperado para ser utilizado no accordion
     * de fornecedores envolvidos e na modal de visualizar mais contatos
     * @param arrFornecedoresEnvolvidos Array de fornecedores envolvidos
     */
    private setInfoContatosFornecedor(arrFornecedoresEnvolvidos: any){

        arrFornecedoresEnvolvidos.forEach(fornecedor => {
            
            //Se o fornecedor tiver contatos, faço sua inicialização
            if(fornecedor.contatos?.length > 0){

                fornecedor.contatoPrincipal = this.getContato(fornecedor.contatos, true);
                fornecedor.outrosContatos = this.getContato(fornecedor.contatos, false);

            }

        });        

    }

    /**
     * Busca informações do contato do fornecedor e as retorna. Pode buscar somente o contato principal ou todos os contatos que não sejam o contato principal
     * @param arrContatosFornecedor Array de contatos do fornecedor
     * @param buscaPrincipal Indica se é para buscar apenas o contato principal do fornecedor
     * @returns 
     */
    private getContato(arrContatosFornecedor: any, buscaPrincipal: boolean = false){

        let infoContatoPrincipal: any;
        let telsContato: any;
        let arrOutrosContatos = [];

        //Para cada contato do fornecedor, buscar suas informações
        for (let i = 0; i < arrContatosFornecedor.length; i++) {
            
            const contato = arrContatosFornecedor[i];

            //Se desejar retornar apenas as informações do contato principal
            if(buscaPrincipal && contato.principal){

                if(contato.telefones?.length > 0){
                    telsContato = this.getTelefonesContato(contato.telefones);
                }

                infoContatoPrincipal = {
                    nome: contato.cargo != null ? contato.cargo + '/' + contato.nome : contato.nome,
                    email: contato.email, 
                    telPrincipal: telsContato != null ? telsContato.telefonePrincipal : null,
                    outrosTelefones: telsContato?.outrosTelefones.length > 0 ? telsContato.outrosTelefones : null
                }

                return infoContatoPrincipal;

            }

            //Se estiver buscando os contatos que não são principais
            else if(!buscaPrincipal && !contato.principal){

                if(contato.telefones?.length > 0){
                    telsContato = this.getTelefonesContato(contato.telefones);
                }

                arrOutrosContatos.push(
                    {
                        nome: contato.cargo != null ? contato.cargo + '/' + contato.nome : contato.nome,
                        email: contato.email, 
                        telPrincipal: telsContato != null ? telsContato.telefonePrincipal : null,
                        outrosTelefones: telsContato?.outrosTelefones.length > 0 ? telsContato.outrosTelefones : null
                    }
                );

            }
    
        }

        return arrOutrosContatos;

    }

    /**
     * Busca os telefones de um contato e retorna. Retorna um objeto com o telefone principal e um array com os telefones não principais
     * @param arrTelefonesContato Array de telefones do contato
     * @returns 
     */
    private getTelefonesContato(arrTelefonesContato: any){

        let telPrincipal: any;
        let outrosTels = [];

        arrTelefonesContato.forEach(telefone => {
            
            //Se for o telefone principal
            if(telefone.principal){
                telPrincipal = telefone.ddd != null ? `(${telefone.ddd}) ${telefone.telefone}` : telefone.telefone
                
            }
            else{
                outrosTels.push(
                    {
                        telcompleto: telefone.ddd != null ? `(${telefone.ddd}) ${telefone.telefone}` : telefone.telefone
                    }
                );
            }

        });

        return {
            telefonePrincipal: telPrincipal,
            outrosTelefones: outrosTels
        }

    }

    isBusy() {
        return this.busy;
    }
}

// Interfaces e classes da tela
/**
 * Interface dos dados retornados na api de fornecedores detalhados do negócio
 */
interface IAtcFornecedorDetalhado {
    fornecedor: string;
    negocio: string;
    tenant: number;
    id_grupoempresarial: string;
    fornecedor_razaosocial: string;
    fornecedor_nomefantasia?: string;
    fornecedor_status: EFornecedorStatus;
    fornecedor_cnpj: string;
    path_logo?: string;
    // Informações de contato
    contato_nome?: string;
    contato_email?: string;
    contato_tel_ddd?: string;
    contato_tel_numero?: string;
    contato_tel_ramal?: string;
    // Informações de Advertências
    qtd_advertencia: number;
    data_ultima_advertencia?: string;
    // Campos utilizados somente no FrontEnd
    ver_mais_informacoes?: boolean;
    // Dados bancários da conta principal do fornecedor
    conta_banco: string;
    conta_agencianumero: string;
    conta_agenciadv: string;
    conta_contanumero: string;
    conta_contadv: string;
    conta_todos_nulos: boolean; //Indica se o fornecedor não tem nenhum de seus dados bancários preenchidos
}
/**
 * Enumerado dos status do fornecedor
 */
enum EFornecedorStatus {
    fsAtivo = 0,
    fsSuspenso = 1,
    fsBanido = 2
}
