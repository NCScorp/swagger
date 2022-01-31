import angular = require('angular');
import { IPropostaItem, IComposicao } from '../Atcs/interfaces';

export class CrmTemplatespropostasCreatePropostaFormModalController {
    static $inject = [
        'toaster', 
        '$uibModalInstance', 
        'entity', 
        'constructors', 
        'nsjRouting', 
        '$http', 
        '$scope', 
        'CrmTemplatespropostas',
        '$rootScope',
    ];

    /**
     * Declaro enum dentro do controller para poder utilizar na view.
     */
    private EnumSituacaoServico = EnumSituacaoServico;

    public action: string;

    public form: any;

    public submitted: boolean = false;
    public parameters: any;
    public busy: boolean = false;
    public checkboxMarcarDesmarcarTodos: boolean = false;

    public testeLista: any;
    public listaSegurosTreeFormat: any;

    public propostasGrupos: any[];
    public templatesPropostas: any[];
    public templatesCapitulos: any[];
    public templatesComposicoes: any[];
    public templatesFamilias: any[];
    public templatesFuncoes: any[];
    private arrPropostasitens: IPropostaItem[] = [];

    /**
     * Faz a verificação se pelo menos um item da tree está marcado, para ativar ou não o botão
     */
    public todosDesmarcados: boolean = true;

    constructor(
        public toaster: any, 
        public $uibModalInstance: any, 
        public entity: any, 
        public constructors: any, 
        public nsjRouting: any, 
        public $http: angular.IHttpService, 
        public $scope: any, 
        public CrmTemplatespropostas: any,
        public $rootScope: angular.IRootScopeService,
    ) {
        this.action = entity.templateproposta ? 'update' : 'insert';
    }

    $onInit() {
        this.busy = true;
        this.parameters = this.entity.parameters;
        this.entity = this.entity.entity;
        if (this.entity.length) {
            this.loadDados(this.parameters.atcsdadosseguradoras);
        }

    }

    reloadScope() {
      this.$scope.$applyAsync();
    }

    isBusy(){
        return this.busy;
    }

    marcarDesmarcarTodos() {
        if(this.checkboxMarcarDesmarcarTodos) {
            //desmarcar
            this.checkboxMarcarDesmarcarTodos = false;
        } else if (!this.checkboxMarcarDesmarcarTodos){
            // marcar
            this.checkboxMarcarDesmarcarTodos = true;
        }

        for(let i = 0; i < this.listaSegurosTreeFormat.length; i++){
            let primeiroNivel = this.listaSegurosTreeFormat[i];
            for(let i = 0; i < primeiroNivel.itens.length; i++){
                let segundoNivel = primeiroNivel.itens[i];
                for(let j = 0; j < segundoNivel.itens.length; j++){
                    let terceiroNivel = segundoNivel.itens[j];
                    terceiroNivel.checked = this.checkboxMarcarDesmarcarTodos;
                }
            }
        }
        this.verificaTodosItensDesmarcados(this.listaSegurosTreeFormat);
        this.reloadScope();
    }

     /**
     * retorna dados da entidade buscada
     * @param rota rota para obter os dados da entidade
     * @param campoDeFiltro campo do constructor 
     * @param id id de consulta
     */
    obterEntidade(rota: string, campoDeFiltro: string, id: string): Promise<any> {
        return new Promise(async (resolve, reject) => {
        await this.$http({
            method: 'GET',
            url: this.nsjRouting.generate(rota, angular.extend({}, { [campoDeFiltro]: id }, { 'offset': {}, 'filter': {} }, {}), true),
            timeout: null
        })
            .then(response => resolve(response))
            .catch(error => console.log(error));
        })
    }


    /**
     * Busca templates familia e funcao de um dado template proposta.
     * @param templatepropostagrupo 
     * @param templateproposta 
     */
     private getTemplateFamiliaFuncaoTemplateProposta(templatepropostagrupo: string, templateproposta: string): Promise<any> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate(
                    'crm_templatespropostas_get_templates_familias_funcoes', 
                    { templatepropostagrupo: templatepropostagrupo, id:templateproposta }, 
                    false)
            }).then((response: any) => {
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    /**
     * Busca propostas itens da API
     * @param atc 
     * @param proposta 
     */
    private getPropostasitensFromApi(atc: string, proposta: string): Promise<IPropostaItem[]> {
        return new Promise((resolve, reject) => {
            this.$http({
                method: 'GET',
                url: this.nsjRouting.generate('crm_propostasitens_index', { negocio: atc, proposta }, false, true)
            }).then((response: any) => {
                response.data.forEach(propostaItem => {
                    propostaItem.negocio = {negocio: atc}
                });
                resolve(response.data);
            }).catch((erro: any) => {
                reject(erro);
            })
        });
    }

    private async loadDados(atcsDadosSeguradoras) {
        this.busy = true;

        //monta lista de grupos
        let propostasGrupos = [];
        atcsDadosSeguradoras.forEach(element => {
            //procura dadoseguradora.templatepropostagrupo na lista de seguros
            const found = propostasGrupos.find((seguro) => {
                if(seguro.templatepropostagrupo == element.produtoseguradora.templatepropostagrupo){
                    return true; //se achou, este pai já existe no array.
                }
            });
            //se não encontrou, monta o item nivel 1 e pusha para o array.
            if(found == undefined){
                let entity = {
                    "primeironivel": true,
                    "nome": element.produtoseguradora.nome,
                    "templatepropostagrupo": element.produtoseguradora.templatepropostagrupo,
                    "itens":[],
                }
                propostasGrupos.push(entity);
            }
        });
        this.propostasGrupos = propostasGrupos;
        //monta lista de apolices templateproposta
        let apolices = {
            data : []
        };
        atcsDadosSeguradoras.forEach(element => {
            apolices.data.push(element.apolice);
        });
        this.templatesPropostas = apolices.data;

        // Busco lista de propostas itens do atencimento
        this.arrPropostasitens = await this.getPropostasitensFromApi(
            this.constructors.proposta.negocio, this.constructors.proposta.proposta
        );

        // para cada template proposta, buscar templates propostas capitulos
        let arrayPromisesTemplateCapitulo = [];
        let arrayPromisesTemplateCapituloResult = [];
        let templatesCapitulos = [];
        apolices.data.forEach((proposta) => {
            // adicionar a promise
            arrayPromisesTemplateCapitulo.push(this.obterEntidade('crm_templatespropostascapitulos_index', 'templateproposta', proposta.templateproposta));
            arrayPromisesTemplateCapituloResult.push([]);
        });
        arrayPromisesTemplateCapituloResult = await Promise.all(arrayPromisesTemplateCapitulo);
        arrayPromisesTemplateCapituloResult.forEach(capitulosDaProposta => {
          templatesCapitulos = templatesCapitulos.concat(capitulosDaProposta.data);
        });
        this.templatesCapitulos = templatesCapitulos;
    
        // para cada template capitulo, buscar templates propostas capitulos composicoes
        let arrayPromisesTemplateComposicao = [];
        let arrayPromisesTemplateComposicaoResult = [];
        let templatesComposicoes = [];
        templatesCapitulos.forEach((capitulo) => {
            // adicionar a promise
            arrayPromisesTemplateComposicao.push(this.obterEntidade('crm_templatespropostascapituloscomposicoes_index', 'templatepropostacapitulo', capitulo.templatepropostacapitulo));
            arrayPromisesTemplateComposicaoResult.push([]);
        });
        arrayPromisesTemplateComposicaoResult = await Promise.all(arrayPromisesTemplateComposicao);
        arrayPromisesTemplateComposicaoResult.forEach(composicoesDoCapitulo => {
          templatesComposicoes = templatesComposicoes.concat(composicoesDoCapitulo.data);
        });
        this.templatesComposicoes = templatesComposicoes;
    
        // para cada template capitulo composicao, buscar familias e funcoes
        // let dadosFuncoesFamilia: {
        //   arrPromises: Promise<any>[],
        //   arrTipoInfo: ('familia' | 'funcao')[],
        //   arrRetornoApi: any[]
        // } = {
        //   arrPromises: [], // Promisses de busca por família e função
        //   arrTipoInfo: [], // Se é família ou função
        //   arrRetornoApi: [] // Dados retornados das requisições
        // };

        // templatesComposicoes.forEach((composicao) => {
        //   // Adiciono promise de templatescomposicoesfamilias
        //   dadosFuncoesFamilia.arrPromises.push(this.obterEntidade('crm_templatescomposicoesfamilias_index', 'templatepropostacomposicao', composicao.templatepropostacomposicao));
        //   dadosFuncoesFamilia.arrTipoInfo.push('familia');
        //   dadosFuncoesFamilia.arrRetornoApi.push([]);
        //   // Adiciono promise de templatescomposicoesfuncoes
        //   dadosFuncoesFamilia.arrPromises.push(this.obterEntidade('crm_templatescomposicoesfuncoes_index', 'templatepropostacomposicao', composicao.templatepropostacomposicao));
        //   dadosFuncoesFamilia.arrTipoInfo.push('funcao');
        //   dadosFuncoesFamilia.arrRetornoApi.push([]);
        // });
        // dadosFuncoesFamilia.arrRetornoApi = await Promise.all(dadosFuncoesFamilia.arrPromises);

        // let templatesFamilias = [];
        // let templatesFuncoes = [];
        // dadosFuncoesFamilia.arrTipoInfo.forEach((tipo, index) => {
        //   if (tipo == "familia") {
        //     templatesFamilias = templatesFamilias.concat(dadosFuncoesFamilia.arrRetornoApi[index].data);
        //   } else {
        //     templatesFuncoes = templatesFuncoes.concat(dadosFuncoesFamilia.arrRetornoApi[index].data);
        //   }
        // });
        // this.templatesFamilias = templatesFamilias;
        // this.templatesFuncoes = templatesFuncoes;

        //o trecho acima foi refatorado para disparar uma menor quantidade de requisições
        //ao invés de 1 req. de templatefamilia e 1 de templatefuncao para cada composicao
        //agora dispara 1 req. que tras todos os templatefamilia e templatefuncao relacionados a um templateproposta
        //antes, eram feitas duas requests para cada composição
        //agora, para cada apolice, é feita uma única request.
        let dadosConjuntosPromises = [];
        apolices.data.forEach(async apolice => {
            dadosConjuntosPromises.push(this.getTemplateFamiliaFuncaoTemplateProposta(
                apolice.templatepropostagrupo,
                apolice.templateproposta
            ));
        });
        let dadosConjuntos = await Promise.all(dadosConjuntosPromises);

        let templatesFamiliasNew = [];
        let templatesFuncoesNew = [];
        dadosConjuntos.forEach(familiasfuncoes => {
            familiasfuncoes.familias.forEach(familia => {
                templatesFamiliasNew.push(familia)
            });
            familiasfuncoes.funcoes.forEach(funcao => {
                templatesFuncoesNew.push(funcao)
            });
        });

        this.templatesFamilias = templatesFamiliasNew;
        this.templatesFuncoes = templatesFuncoesNew;

        //apos isso, organizar os dados.
        this.organizarDadosTela(atcsDadosSeguradoras);
        this.busy = false;
        this.reloadScope();
    }

    organizarDadosTela(atcsdadosseguradoras){
        let apolices = {data:[]};
        apolices.data = this.templatesPropostas;
        this.listaSegurosTreeFormat = this.propostasGrupos;

        for (let index = 0; index < atcsdadosseguradoras.length; index++) {
            const template = atcsdadosseguradoras[index].produtoseguradora;
            for (const element of this.listaSegurosTreeFormat) {
                if(element.templatepropostagrupo !== template.templatepropostagrupo){
                    continue;
                }

                //para cada templatepropostagrupo que está no atcsdadosseguradoras
                element.itens = [];
                for (const apoliceData of apolices.data as Array<any>){
                    if(element.templatepropostagrupo !== apoliceData.templatepropostagrupo) {
                        continue;
                    }

                    //para cada templatespropostas (apolice) que pertence ao grupo
                    //monta o nó
                    let listaProdutos = [];
                    let apolice = {
                        "segundonivel": true,
                        "nome": apoliceData.nome,
                        "templatepropostagrupo": apoliceData.templatepropostagrupo,
                        "templateproposta": apoliceData.templateproposta,
                        "valorapolice": apoliceData.valorapolice,
                        "itens":[],
                    }
                    const capitulos = this.templatesCapitulos;
                    for (const capitulo of capitulos as Array<any>) {
                        if(capitulo.templateproposta !== apoliceData.templateproposta) {
                            continue;
                        }

                        //para cada capitulo que pertence ao templateproposta
                        for (const composicao of this.templatesComposicoes as Array<any>) {
                            if(composicao.templatepropostacapitulo !== capitulo.templatepropostacapitulo) {
                                continue;
                            }

                            //para cada composicao que pertence ao templatecapitulo
                            //monta o nó e pusha
                            let composicaoTree = {
                                "composicaoFull": composicao,
                                "checked": false,
                                situacao: this.getSituacaoServico(composicao.composicao, apolice.templateproposta),
                            }
                            apolice.itens.push(composicaoTree);

                            for (let index = 0; index < this.templatesFamilias.length; index++) {
                                const familia = this.templatesFamilias[index];
                                if(familia.templatepropostacomposicao !== composicao.templatepropostacomposicao) {
                                    continue;
                                }

                                //para cada familia que pertence a composicao
                                let itemproduto = {
                                    "composicaoFull": {
                                        "composicao":{
                                            'nome': familia.familia.descricao
                                        }
                                    },
                                    "checked": 'disabled',
                                }
                                listaProdutos.push(itemproduto);
                            }
                        }
                    }
                    listaProdutos.forEach(produto => {
                        apolice.itens.push(produto);
                    });
                    //da o push da apolice criada
                    element.itens.push(apolice);
                }
            }
        }
    }

    /**
     * Faz a verificação se na modal de templates de apólice ao menos um item está marcado para atualizar a propriedade todosDesmarcados, que controla a ativação do botão
     * @param listaSeguros 
     */
    verificaTodosItensDesmarcados(listaSeguros){
        let todosDesmarcados = true;
        //Loop para descer nível a nível até chegar no nível onde os itens são marcados ou desmarcados para realizar a verificação
        for(let i = 0; i < listaSeguros.length; i++){
            let primeiroNivel = listaSeguros[i];

            if (!todosDesmarcados) {
                break;
            }
            for(let i = 0; i < primeiroNivel.itens.length; i++){
                let segundoNivel = primeiroNivel.itens[i];

                for(let i = 0; i < segundoNivel.itens.length; i++){

                    //Verificando se pelo menos um dos itens está marcado, se estiver, a propriedade todosDesmarcados recebe false
                    todosDesmarcados = !segundoNivel.itens.some(function(element){
                        return element.checked == true;
                    });

                    //Se pelo menos um estiver marcado, interrompe esse for no último nível
                    if(!todosDesmarcados){
                        break;
                    }

                }

            }

        }
        this.todosDesmarcados = todosDesmarcados;
    }

    checkUncheck(composicao: any){
        //Cada vez que um item é marcado ou desmarcado, é necessário verificar se todos os itens estão desmarcados, para controlar a ativação do botão
        if(composicao.checked){
            composicao.checked = false;
            this.verificaTodosItensDesmarcados(this.listaSegurosTreeFormat);
        } else {
            composicao.checked = true;
            this.verificaTodosItensDesmarcados(this.listaSegurosTreeFormat);
        }
        this.reloadScope();
    }

    async adicionarNovo(){
        let arrayChecked = {"templatescomposicoes": []};
        let composicoesUtilizadas: string[] = [];
        let composicoesDuplicadas: string[] = [];
        this.listaSegurosTreeFormat.forEach(seguro => {
            seguro.itens.forEach(apolice => {
                apolice.itens.forEach( (composicao, index) => {
                    
                    if(composicao.checked === true){
                        if (composicoesUtilizadas.indexOf(composicao.composicaoFull.composicao.composicao) > -1) {
                            if (composicoesDuplicadas.indexOf(composicao.composicaoFull.composicao.nome) < 0) {
                                composicoesDuplicadas.push(composicao.composicaoFull.composicao.nome);
                            }
                        }
                        composicoesUtilizadas.push(composicao.composicaoFull.composicao.composicao);
                        let composicaoCheckada = {
                            "templateproposta": apolice.templateproposta,
                            "templatepropostagrupo": apolice.templatepropostagrupo,
                            "templatepropostacomposicao": composicao.composicaoFull.templatepropostacomposicao,
                            "templatepropostacapitulo": composicao.composicaoFull.templatepropostacapitulo,
                            "nome": composicao.composicaoFull.composicao.nome,
                            "descricao": composicao.composicaoFull.composicao.descricao,
                            "composicao": {
                                "composicao": composicao.composicaoFull.composicao.composicao
                            },
                            "valorapolice": apolice.valorapolice
                        }
                        arrayChecked.templatescomposicoes.push(composicaoCheckada);
                    }
                });
            });
        });

        // Caso tenha escolhido o mesmo serviço mais de uma vez, retorno erro.
        if (composicoesDuplicadas.length > 0) {
            let servicosDuplicadosTxt = '';

            composicoesDuplicadas.forEach((servico, index) => {
                if (index > 0) {
                  if (index < (composicoesDuplicadas.length -1)) {
                    servicosDuplicadosTxt += ', ';
                  } else {
                    servicosDuplicadosTxt += ' e ';
                  }
                }

                servicosDuplicadosTxt += servico;
            })

            let mensagemErro = `Os serviços ${servicosDuplicadosTxt} foram escolhidos mais de uma vez!`;

            if (composicoesDuplicadas.length == 1) {
              mensagemErro = `O serviço ${servicosDuplicadosTxt} foi escolhido mais de uma vez!`;
            }

            this.toaster.pop({
                type: 'error',
                title: mensagemErro,
            });

            // Saio da função
            return;
        }

        // crm_propostasitens_template_gera_itens                      
        // POST             
        // ANY      ANY    /api/{tenant}/{atc}/{proposta}/templateGeraItens/ 

        this.busy = true;

        const constructorsTemplateComposicao = { 
            'negocio': this.constructors.proposta.negocio,  
            'proposta': this.constructors.proposta.proposta,
        }
        let templatecomposicao = await this.$http({
            method: 'POST',
            data: angular.copy(arrayChecked),
            url: this.nsjRouting.generate('crm_propostasitens_template_gera_itens', angular.extend({}, constructorsTemplateComposicao, { 'offset': '', 'filter': '' }), true),
        })
        .then((response) => {
            this.busy = false;
            this.close();
            this.toaster.pop({
                type: 'success',
                title: 'Os serviços foram criados/atualizados através do seguro com sucesso!',
            });

            //recarregar a lista de propostasitens.
            let listaPropostasItens: any = response.data;
            listaPropostasItens.forEach(propostaitem => {
                response.data = propostaitem;
                let newResponse = {...response};

                // Se o propostaitem já existia, defino que o método era put, para manter integridade de quem estiver escutando.
                if (this.arrPropostasitens.find((propostaitemFind) => {
                    return propostaitemFind.propostaitem == propostaitem.propostaitem
                })) {
                    newResponse.config.method = 'PUT';
                }

                this.$rootScope.$broadcast('crm_propostasitens_submitted', {
                    response: newResponse,
                    entity: {...propostaitem, updateFromModalSeguro: true}
                });
            });
        })
        .catch((error) => {
            this.busy = false;
            this.toaster.pop({
                type: 'error',
                title: error.data.message,
            });
        });
    }

    submit() {
        this.submitted = true;
        if (this.form.$valid) {
            this.$uibModalInstance.close(this.entity);
        } else {
            this.toaster.pop({
                type: 'error',
                title: 'Alguns campos do formulário apresentam erros.'
            });
        }
    }
    close() {
        this.$uibModalInstance.dismiss('fechar');
    }

    /**
     * Retorna a descrição da situação atual do serviço da apólice.
     * @param pSituacao 
     * @param pApoliceUsoNome Só deve ser passado caso o serviço esteja sendo utilizado por uma apólice diferente
     */
    getDescricaoSituacaoServico(pSituacao: EnumSituacaoServico, pNomeApoliceUso: string = ''): string {
        switch (pSituacao) {
            case EnumSituacaoServico.ssNaoExiste: {
                return '';
            }
            case EnumSituacaoServico.ssExisteNoPedido: {
                return '(Serviço existente na lista de pedidos)';
            }
            case EnumSituacaoServico.ssEmUso: {
                return '(Em uso)';
            }
            case EnumSituacaoServico.ssEmUsoApoliceDiferente: {
                return `(Em uso pela apólice ${pNomeApoliceUso})`;
            }
        }
    }

    /**
     * Retorna o código e a descrição da situacao do serviço(composição)
     * @param composicao 
     */
    getSituacaoServico(composicao: IComposicao, apoliceId: string){
        let situacao = EnumSituacaoServico.ssNaoExiste;
        let nomeApoliceUso = '';

        let propostaItem = this.arrPropostasitens.find((propostaitemFind) => {
            return propostaitemFind.composicao.composicao == composicao.composicao;
        });
        
        // Se existe no pedido, faço alterações na situação
        if (propostaItem != null) {
            // Por default, defino que existe no pedido.
            situacao = EnumSituacaoServico.ssExisteNoPedido;

            // Se tem apólice, verifico se é do item atual, ou de uma apólice diferente.
            if (propostaItem.id_apolice != null) {
                if (propostaItem.id_apolice == apoliceId) {
                    situacao = EnumSituacaoServico.ssEmUso;
                } else {
                    situacao = EnumSituacaoServico.ssEmUsoApoliceDiferente;
                    
                    const templateProposta = this.templatesPropostas.find((templatePropostaFind) => {
                        return templatePropostaFind.templateproposta == propostaItem.id_apolice;
                    });

                    if (templateProposta != null) {
                        nomeApoliceUso = templateProposta.nome;
                    }
                }
            }
        }

        return {
            codigo: situacao,
            descricao: this.getDescricaoSituacaoServico(situacao, nomeApoliceUso)
        }
    }
}

export class CrmTemplatespropostasCreatePropostaFormService {
    static $inject = ['CrmTemplatespropostasgrupos', '$uibModal', 'nsjRouting', '$http'];

    constructor(public entityService: any, public $uibModal: any, public nsjRouting: any, public $http: any) {
    }

    open(parameters: any, subentity: any) {
        return this.$uibModal.open({
            template: require('./../../Crm/Templatespropostas/createproposta.html'),
            controller: 'CrmTemplatespropostasCreatePropostaFormModalController',
            controllerAs: 'crm_tmpltsprpsts_crt_prpst_frm_dflt_cntrllr',
            windowClass: '',
            size: 'lg',
            resolve: {
                entity: async () => {
                    if (parameters.templatepropostagrupo) {
                        this.entityService.constructor = {};
                        this.entityService.constructors['templatepropostagrupo'] = parameters.templatepropostagrupo;
                        this.entityService.constructors['cliente'] = parameters.cliente;

                        let { data: entity } = await this.$http({
                            method: 'GET',
                            url: this.nsjRouting.generate('crm_templatespropostasgrupos_index', angular.extend({}, this.entityService.constructors, { 'offset': '', 'filter': '' }), true),

                        });
                        return {entity, parameters};
                    } else {
                        return angular.copy(subentity);
                    }
                },
                constructors: () => {
                    return parameters;
                }
            }
        });
    }

}

/**
 * Define o estado da utilizacao do serviço da apólice
 */
enum EnumSituacaoServico {
    ssNaoExiste,
    ssExisteNoPedido,
    ssEmUso,
    ssEmUsoApoliceDiferente
}
