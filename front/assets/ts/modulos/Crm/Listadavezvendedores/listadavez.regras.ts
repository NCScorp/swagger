import angular = require('angular');
import { ITreeControl, montarNoTree } from '../../Commons/utils';
import { Tree } from '../../Commons/tree';
import { CommonsUtilsService } from '../../Commons/utils/utils.service';
import { AtcsDocumentosTreeService } from '../Atcsconfiguracoesdocumentos/atcsDocumentosTreeService';
import { IListadavezregrasApi, EnumListadavezregrasTipoEntidadeApi } from '../Listadavezregras/classes';
import { IListadavezconfiguracoesApi, EnumListadavezconfiguracoesTipoRegistroApi } from '../Listadavezconfiguracoes/classes';
import { IListadavezregrasvaloresApi } from '../Listadavezregrasvalores/classes';
import { EnumModoTelaRegra } from './listadavez.full';
import { CrmListadavezconfiguracoes } from '../Listadavezconfiguracoes/factory';
import { CrmListadavezregras } from '../Listadavezregras/factory';
import { CrmListadavezregrasvalores } from '../Listadavezregrasvalores/factory';

export class CrmListaDaVezRegrasController {
    /**
     * Lista de serviços injetados no controller
     */
    static $inject = [
        '$scope',
        'toaster',
        'CrmListadavezconfiguracoes',
        'CrmListadavezregras',
        'CrmListadavezregrasvalores',
        'Tree',
        'AtcsDocumentosTreeService',
        'CommonsUtilsService',
        'abaRegrasCtrl'
    ];
    /**
     * Define se a tela está em processamento
     */
    public busy: boolean = false;
    /**
     * Define se a tela está em processamento de carregar os dados
     */
    public busyCarregamento: boolean = false;
    /**
     * Quando preenchido, o botão de expansão fica no campo informado
     */
    public campoExpansao: string = 'listadavezregra';
    /**
     * Configuração das colunas da tree.
     */
    public col_defs: any;
    /**
     * Objeto da tree de configurações
     */
    private treeConfiguracoes: ITreeControl = {};
    /**
     * Lista de dados da tree em linha
     */
    private arrDadosTreeEmLinha: INodeConfiguracao[] = [];
    /**
     * Lista de dados da tree em cascata
     */
    private dadosTreeEmCascata: INodeConfiguracao[] = [];
    /**
     * Lista de regras da lista da vez
     */
    private arrRegras: IListadavezregrasApi[] = [];
    /**
     * Lista de objetos da configuração da tela
     */
    private arrConfiguracoesTela: ConfiguracaoTela[] = [];
    /**
     * Utilizada para gerar novos ids de configurações
     */
    private listadavezconfiguracaonovo: number = 0;

    constructor(
        public $scope: any,
        public toaster: any,
        public entityService: CrmListadavezconfiguracoes,
        private regrasService: CrmListadavezregras,
        private regrasValoresService: CrmListadavezregrasvalores,
        public tree: Tree,
        private AtcsDocumentosTreeService: AtcsDocumentosTreeService,
        private CommonsUtilsService: CommonsUtilsService,
        private abaRegrasCtrl
    ){
        this.Init();
    }

    /* Carregamento */
    Init() {
        // Configuro ações da aba de regras
        this.abaRegrasCtrl.onAcaoEditar = () => {
            // Mudo o modo para edição
            this.abaRegrasCtrl.modoTela = EnumModoTelaRegra.mtrEdicao;

            // Se não possui configuração, seto a configuração padrão e um fluxo alternativo
            if (this.arrDadosTreeEmLinha.length == 0) {
                let arrConfiguracoes: IListadavezconfiguracoesApi[] = [];

                // Regra inicial
                let regraInicial = {
                    listadavezconfiguracaonovo: this.getNovoId(),
                    tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra,
                    ordem: 0,
                    listadavezregra: {
                        listadavezregra: null
                    }
                };
                arrConfiguracoes.push(regraInicial);
    
                // Fluxo alternativo da regra
                arrConfiguracoes.push({
                    listadavezconfiguracaonovo: this.getNovoId(),
                    idpainovo: regraInicial.listadavezconfiguracaonovo,
                    tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo,
                    ordem: 0,
                    listadavezregra: {
                        listadavezregra: null
                    }
                });

                this.arrDadosTreeEmLinha = this.getDadosTreeEmLinha(arrConfiguracoes);
            }

            // Atualizo scopo
            this.reloadScope();
        }
        this.abaRegrasCtrl.onAcaoCancelarEdicao = () => {
            // Mudo o estado da tela para visualização
            this.abaRegrasCtrl.modoTela = EnumModoTelaRegra.mtrVisualizacao;

            // Chamo função de inicialização da aba de regras.
            this.Init();
        }
        this.abaRegrasCtrl.onAcaoSalvar = () => {
            this.salvarConfiguracoes();
        }
        this.abaRegrasCtrl.isBusy = () => {
            return this.isBusy();
        }

        // Defino que está no modo de inicialização, 
        this.abaRegrasCtrl.modoTela = EnumModoTelaRegra.mtrVisualizacao;

        // Ativo loading de carregamento
        this.busyCarregamento = true;

        // Configuro a tree
        this.configurarTree();
        
        // Faz as requisições de carregamento da tela
        this.carregarDadosTela();
    }

    /**
     * Verifica se a tela está em processamento
     */
    private isBusy(): boolean {
        return this.busy || this.isBusyCarregamento();
    }

    /**
     * Verifica se a tela está em processamento de carregar os dados
     */
    private isBusyCarregamento(): boolean {
        return this.busyCarregamento;
    }

    /* tree */

    /**
     * Configura a tree de configuracoes
     */
    private configurarTree() {

        /* carrega as colunas */
        this.col_defs = [];

        // Defino escopo padrão de funções
        let cellTemplateScopePadrao = {
            // Funções do campo checked
            checkedFieldOnClick: (node: INodeConfiguracao) => {
                if (!this.configuracaoEstaNaListagem(node)) {
                    return;
                }
                
                node.obj.setChecked(!node.obj.checked);
            },
            // Funções do campo listadavezregra
            listadavezregraFieldOnRegraChange: (node: INodeConfiguracao) => {
                if (!this.configuracaoEstaNaListagem(node)) {
                    return;
                }

                if (node.obj.config.listadavezregra.listadavezregra == null || node.obj.config.listadavezregra.listadavezregra == '') {
                    node.obj.config.listadavezregra = {
                        listadavezregra: null
                    }
                } else {
                    const regra = this.arrRegras.find((regraFind) => {
                        return regraFind.listadavezregra == node.obj.config.listadavezregra.listadavezregra;
                    });
                    node.obj.config.listadavezregra = angular.copy(regra);
                }

                //Preencho regra do fluxo alternativo
                const fluxoAlternativo = node.obj.getFluxoAlternativo();

                if (fluxoAlternativo) {
                    fluxoAlternativo.config.listadavezregra = angular.copy(node.obj.config.listadavezregra);
                }
            },
            listadavezregraFieldGetRegrasDisponiveis: (node: INodeConfiguracao) => {
                if (!this.configuracaoEstaNaListagem(node)) {
                    return [];
                }

                return this.getRegrasDisponiveis(node);
            },
            listadavezregraFieldGetRegraFixaValoresDisponiveis: (node: INodeConfiguracao) => {
                if (!this.configuracaoEstaNaListagem(node)) {
                    return [];
                }

                return this.getRegraFixaValoresDisponiveis(node);
            },
            vendedorfixoEhOuPossuiPaiOpcaoJaEcliente: (node: INodeConfiguracao) => {
                return this.EhOuPossuiPaiOpcaoJaEcliente(node);
            },
            getEnumEntidade: () => {
                return EnumListadavezregrasTipoEntidadeApi;
            },
            getEnumModoTelaRegra: () => {
                return EnumModoTelaRegra;
            },
            getModoTela: () => {
                return this.abaRegrasCtrl.modoTela;
            },
            getRegraValorNome: (listadavezregra: string, listadavezregravalor: string) => {
                let nome = '';

                let regra = this.arrRegras.find((regraFind) => {
                    return regraFind.listadavezregra == listadavezregra;
                });

                if (regra) {
                    const regravalor = regra.itens.find((itemFind) => {
                        return itemFind.listadavezregravalor == listadavezregravalor;
                    });

                    if (regravalor) {
                        nome = regravalor.nome;
                    }
                }

                return nome;
            },
        }

        // Coluna Checkbox
        this.col_defs.push({
            field: 'checked',
            displayName: '  ',
            cellTemplate: `
                <label class="custom-checkbox">
                    <input 
                        type="checkbox" 
                        ng-checked="row.branch.obj.isChecked()" 
                        ng-click="cellTemplateScope.checkedFieldOnClick(row.branch)" 
                        ng-disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao" />
                    <span class="checkmark">
                    </span>
                </label>
            `,
            cellTemplateScope: cellTemplateScopePadrao
        });

        // Coluna Conjunto de Regras
        this.col_defs.push({
            field: 'listadavezregra',
            displayName: 'Conjunto de Regras',
            cellTemplate: `
                <div class="conjuntoderegras">
                    <!-- Regra -->
                    <div class="regra" ng-if="row.branch.tipo === 'regra' ">
                        <select class="form-control" id="regra" name="regra"
                            ng-model="row.branch.obj.config.listadavezregra.listadavezregra"
                            ng-change="cellTemplateScope.listadavezregraFieldOnRegraChange(row.branch)"
                            ng-disabled="row.branch.obj.possuiFilhosDependencias() || cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                            title="{{ row.branch.obj.config.listadavezregra && row.branch.obj.config.listadavezregra.listadavezregra ? row.branch.obj.config.listadavezregra.nome : '' }}"
                        >
                            <option value="">
                                Selecione uma regra
                            </option>
                            <option 
                                ng-repeat="regra in cellTemplateScope.listadavezregraFieldGetRegrasDisponiveis(row.branch)" 
                                ng-value="regra.listadavezregra"
                            >
                                {{regra.nome}}
                            </option>
                        </select>
                    </div>

                    <!-- Opção -->
                    <div class="opcao" ng-if="row.branch.tipo === 'opcao' ">
                        <!-- Opção Fixa -->
                        <div class="opcao-fixa" ng-if="row.branch.obj.config.listadavezregra.tipoentidade == cellTemplateScope.getEnumEntidade().lvrteValorFixo">
                            <select class="form-control"
                                ng-model="row.branch.obj.config.idlistadavezregravalor.listadavezregravalor"
                                ng-disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                                title="{{ cellTemplateScope.getRegraValorNome(row.branch.obj.config.listadavezregra.listadavezregra, row.branch.obj.config.idlistadavezregravalor.listadavezregravalor) }}"
                            >
                                <option value="">
                                    Selecione uma opção
                                </option>
                                <option 
                                    ng-repeat="regravalor in cellTemplateScope.listadavezregraFieldGetRegraFixaValoresDisponiveis(row.branch)" 
                                    ng-value="regravalor.listadavezregravalor"
                                >
                                    {{regravalor.nome}}
                                </option>
                            </select>
                        </div>

                        <!-- Opção UF -->
                        <div class="opcao-uf" ng-if="row.branch.obj.config.listadavezregra.tipoentidade == cellTemplateScope.getEnumEntidade().lvrteEstado">
                            <nsj-lookup
                                factory="NsEstados" 
                                ng-model="row.branch.obj.config.idestado"
                                disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                                max-label="2" 
                                constructor="{}" 
                                config='{ "uf":"UF", "nome":"Nome",  }'
                            >
                            </nsj-lookup>
                        </div>
                        
                        <!-- Opção Negócio Operação -->
                        <div class="opcao-negocio-operacao" ng-if="row.branch.obj.config.listadavezregra.tipoentidade == cellTemplateScope.getEnumEntidade().lvrteNegocioOperacao">
                            <nsj-lookup
                                factory="CrmNegociosoperacoes" 
                                ng-model="row.branch.obj.config.idnegociooperacao"
                                disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                                max-label="4" 
                                constructor="{}" 
                                config='{ "descricao":"Descrição",  }'
                            >
                            </nsj-lookup>
                        </div>

                        <!-- Opção Segmento de Atuação -->
                        <div class="opcao-segmento-atuacao" ng-if="row.branch.obj.config.listadavezregra.tipoentidade == cellTemplateScope.getEnumEntidade().lvrteSegmentoAtuacao">
                            <nsj-lookup
                                factory="CrmSegmentosatuacao" 
                                ng-model="row.branch.obj.config.idsegmentoatuacao"
                                disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                                max-label="4" 
                                constructor="{}" 
                                config='{ "descricao":"Descrição",  }'
                            >
                            </nsj-lookup>
                        </div>
                    </div>

                    <!-- Caso Alternativo -->
                    <div class="caso-alternativo" ng-if="row.branch.tipo === 'fluxoalternativo' ">
                        Se não
                    </div>
                </div>
            `,
            cellTemplateScope: cellTemplateScopePadrao
        });

        // Coluna Mesmo Vendedor da Ficha do Cliente
        this.col_defs.push({
            field: 'vendedorfixo',
            displayName: '  ',
            cellTemplate: `
                <!-- Coluna só aparece Se o tipo é "opção" ou "fluxo alternativo", se não tem filhos e se tem regra pai "Já é cliente?" com opção "Já é cliente" -->
                <div ng-if="(row.branch.tipo === 'opcao' || row.branch.tipo === 'fluxoalternativo') && row.branch.children.length == 0 && cellTemplateScope.vendedorfixoEhOuPossuiPaiOpcaoJaEcliente(row.branch)" class="container-checkbox">
                    <label class="custom-checkbox">
                        <input 
                            type="checkbox" 
                            ng-model="row.branch.obj.config.vendedorfixo" 
                            ng-disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                        />
                        <span class="checkmark">
                        </span>
                        <span class="texto">
                            Utilizar o mesmo consultor cadastrado na ficha
                        </span>
                    </label>
                </div>
            `,
            cellTemplateScope: cellTemplateScopePadrao
        });

        // Coluna Enviar para
        this.col_defs.push({
            field: 'listadavezvendedor',
            displayName: 'Enviar para',
            cellTemplate: `
                <!-- Coluna só aparece se não opção ou fluxo alternativo-->
                <div class="listadavezvendedor-container" ng-if="(row.branch.tipo === 'opcao' || row.branch.tipo === 'fluxoalternativo') && row.branch.children.length == 0">
                    <div class="listadavez" ng-if="!row.branch.obj.config.vendedorfixo">
                        <nsj-lookup
                            factory="CrmListadavezvendedores" 
                            ng-model="row.branch.obj.config.listadavezvendedor"
                            disabled="cellTemplateScope.getModoTela() == cellTemplateScope.getEnumModoTelaRegra().mtrVisualizacao"
                            max-label="2" 
                            constructor="{}" 
                            config='{ "nome":"Nome"  }'
                        >
                        </nsj-lookup>
                    </div>
                    <!-- Quando 'vendedorfixo' estiver marcado, não se deve selecionar uma lista da vez -->
                    <div class="vendedorfixo" ng-if="row.branch.obj.config.vendedorfixo">
                        Mesmo consultor do cadastro
                    </div>
                </div>
            `,
            cellTemplateScope: cellTemplateScopePadrao
        });

        // Ações da tabela
        this.col_defs.push({
            field: null,
            cellTemplate: `
                <div class='table-btns-actions'
                    ng-class="row.branch.getActions().length == 0 ? 'naopossuiacoes' : ''" >
                    <nsj-actions>
                       <nsj-action 
                            ng-repeat="action in row.branch.actions" 
                            ng-click='action.acao(row.branch)'
                            ng-if='action.isVisible(row.branch)' 
                            icon='{{action.icon}}'
                            title='{{action.label}}'
                        >
                        </nsj-action>
                    </nsj-actions>
                </div>
            `,
            cellTemplateScope: cellTemplateScopePadrao
        });
    }

    /**
     * Busca todas as informações necessárias para o funcionamento da tela
     */
    private async carregarDadosTela() {
        try {
            let arrConfiguracoes: IListadavezconfiguracoesApi[] = [];
            // Busco regras e configurações da Api
            [this.arrRegras, arrConfiguracoes] = await Promise.all([this.getRegrasFromApi(), this.entityService.getAll()]);

            // Atualizo as regras presentes nas configurações, para adicionar o campo "itens"
            if (arrConfiguracoes.length > 0) {
                arrConfiguracoes.forEach((configForeach) => {
                    const regra = this.arrRegras.find((regraFind) => {
                        return regraFind.listadavezregra == configForeach.listadavezregra.listadavezregra;
                    });
                    configForeach.listadavezregra.itens = angular.copy(regra.itens);
                });
            }
            
            this.arrDadosTreeEmLinha = this.getDadosTreeEmLinha(arrConfiguracoes);
        } catch (error) {
            // Toaster de não foi possível carregar dados
        }

        // Desativo loading de carregamento
        this.busyCarregamento = false;
        this.reloadScope();
    }

    /**
     * Busca as regras utilizadas na lista da vez
     */
    private getRegrasFromApi(): Promise<IListadavezregrasApi[]> {
        return new Promise(async (resolve, reject) => {
            // Busco regras da API
            const arrRegras = await this.regrasService.getAll();

            // Busco valores das regras da api, todos de uma vez.
            let arrRetornos = [];
            let arrPromisses = [];
            let arrRegrasIds: string[] = [];

            arrRegras.forEach((regraForeach) => {
                regraForeach.itens = [];

                // Só busco valores de regras do tipo Valor Fixo
                if (regraForeach.tipoentidade == EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo) {
                    // Adiciono link de retorno da promisse
                    arrRetornos.push([]);
    
                    // Adiciono promisse para buscar valor
                    arrPromisses.push(this.regrasValoresService.getAll(regraForeach.listadavezregra));

                    // Adiciono id da regra
                    arrRegrasIds.push(regraForeach.listadavezregra);
                }
            });

            // Faço requisição
            arrRetornos = await Promise.all(arrPromisses);

            // Seto valores nas respectivas regras
            arrRetornos.forEach((retornoForeach: IListadavezregrasvaloresApi[], index) => {
                const listadavezregra = arrRegrasIds[index];
                const regra = arrRegras.find((regraFind) => {
                    return regraFind.listadavezregra == listadavezregra;
                });
                regra.itens = retornoForeach;
            })


            resolve(arrRegras);
        });
    }

    /**
     * Utilizado para montar nodes a partir da lista de configurações
     */
    private getDadosTreeEmLinha(arrConfiguracoes: IListadavezconfiguracoesApi[]): any {
        let arrDados: any[] = [];

        // Para cada configuração, crio um node
        arrConfiguracoes.forEach(config => {
            let configPai: IListadavezconfiguracoesApi;
            let idPai = null;
            if (config.idpai || config.idpainovo) {
                configPai = arrConfiguracoes.find((configFind) => {
                    return (config.idpai != null && config.idpai == configFind.listadavezconfiguracao) ||
                        (config.idpainovo != null && config.idpainovo == configFind.listadavezconfiguracaonovo);
                });

                idPai = this.getTipoRegistroDescricao(configPai.tiporegistro) + this.obterIdEntity(configPai);
            }

            // Crio objeto de configuração da tela
            let configTela = new ConfiguracaoTela(config);

            // Crio node da árvore
            const itemArvore = this.obterNovoNode(configTela, idPai);
            
            // Adiciono node a lista
            arrDados.push(itemArvore);
        });

        return arrDados;
    }
    
    /**
     * Atualiza a referência dos objetos ConfiguracaoTela de acordo com a lista de nodes
     * @param node 
     */
    private atualizarReferenciaConfiguracoesTela(node: INodeConfiguracao){
        // Inicializo lista de filhos da configuração de tela
        const arrFilhos: ConfiguracaoTela[] = [];

        // Atualizo referência dos filhos
        node.children.forEach((nodeForeach) => {
            this.atualizarReferenciaConfiguracoesTela(nodeForeach);

            // Adiciono objeto de configuração tela a lista de filhos
            arrFilhos.push(nodeForeach.obj);
        });

        node.obj.children = arrFilhos;
    }

    /**
     * Verifica se o node está na listagem. Será utilizada pra não chamar funções quando houver delay da tela.
     * @param node 
     */
    private configuracaoEstaNaListagem(node: INodeConfiguracao): boolean {
        return this.arrDadosTreeEmLinha.findIndex((nodeFindIndex) => {
            return nodeFindIndex._id_ == node._id_;
        }) > -1;
    }
    /**
     * Atualiza a visualização da tree
     */
    reloadScope() {
        this.atualizarDadosTree();
        this.$scope.$applyAsync();
    }

    /**
     * Atualiza dados utilizados pela tree
     */
    private atualizarDadosTree(){
        this.dadosTreeEmCascata = this.tree.getArvore(this.arrDadosTreeEmLinha);

        if (this.dadosTreeEmCascata.length > 0) {
            this.atualizarReferenciaConfiguracoesTela(this.dadosTreeEmCascata[0]);
        }

        this.organizarOpcoes();
    }

    /**
    * Criação de novo nó e suas ações
    * @param tipo 
    * @param entity 
    * @param pai 
    */
    private obterNovoNode(entity: ConfiguracaoTela, pai) {
        const icon = 'fas fa-minus';
        const entityId = this.obterIdEntity(entity.config);
        const actions = this.obterAcoesDoNode(entity.config);
        const noNovo = (montarNoTree(this.getTipoRegistroDescricao(entity.config.tiporegistro), entity, entityId, pai, '', actions, icon));

        return noNovo;
    }

    /**
     * Retorna a lista de ações de acordo com a entidade passada
     * @param entity 
     */
    private obterAcoesDoNode(entity: IListadavezconfiguracoesApi): any[] {
        let arrAcoes: {
            label?: string;
            icon?: string;
            size?: string;
            color?: string;
            acao: (node: INodeConfiguracao) => void,
            isVisible?: (node: INodeConfiguracao) => boolean,
        }[] = [];

        // Regra
        if (entity.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
            // Adicionar Opção
            arrAcoes.push({
                label: 'Adicionar opção',
                icon: 'fas fa-network-wired',
                size: 'xs', 
                color: 'primary',
                acao: (node: INodeConfiguracao) => {
                    this.acaoOnAddOpcao(node);
                },
                isVisible: (node: INodeConfiguracao) => {
                    if (!this.configuracaoEstaNaListagem(node)) {
                        return false;
                    }

                    const config = node.obj.config;

                    // Se estiver no modo de visualização, não pode ver ações
                    if (this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao) {
                        return false;
                    }

                    // Se não tiver regra definida, não pode adicionar opções
                    if (config.listadavezregra.listadavezregra == null) {
                        return false;
                    }

                    // Se a regra for do tipo fixo e já tiver atingido o máximo de opções
                    if (
                        config.listadavezregra.tipoentidade == EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo &&
                        config.listadavezregra.totalvalores == node.obj.getOpcoes().length
                    ) {
                        return false;
                    }

                    return true;
                }
            });

            // Remover Regra
            arrAcoes.push({
                label: 'Remover regra',
                icon: 'fas fa-trash',
                size: 'xs', 
                color: 'primary',
                acao: (node: INodeConfiguracao) => {
                    this.acaoOnRemoverConfiguracao(node);
                },
                isVisible: (node: INodeConfiguracao) => {
                    if (!this.configuracaoEstaNaListagem(node)) {
                        return false;
                    }
                    
                    // Se estiver no modo de visualização, não pode ver ações
                    if (this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao) {
                        return false;
                    }

                    // Se for a regra inicial, não pode remover
                    if (node._parentId_ == null || node._parentId_ == '') {
                        return false;
                    }

                    return true;
                }
            });
        } 
        // Opções e Fluxos alternativos
        else if ([EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao, EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo].indexOf(entity.tiporegistro) > -1) {
            // Adicionar regra
            arrAcoes.push({
                label: 'Adicionar regra',
                icon: 'fas fa-network-wired',
                size: 'xs', 
                color: 'primary',
                acao: (node) => {
                    this.acaoOnAddRegra(node);
                },
                isVisible: (node: INodeConfiguracao) => {
                    if (!this.configuracaoEstaNaListagem(node)) {
                        return false;
                    }

                    // Se estiver no modo de visualização, não pode ver ações
                    if (this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao) {
                        return false;
                    }

                    // Se já tiver uma regra filha, não deixar visível
                    if (node.children.length > 0) {
                        return false;
                    }

                    // Se não houver mais regras a adicionar
                    if (this.getRegrasDisponiveis(node).length == 0) {
                        return false
                    }

                    const config = node.obj.config;

                    const nodePai = this.arrDadosTreeEmLinha.find((nodeFind) => {
                        return (config.idpai != null && config.idpai == nodeFind.obj.config.listadavezconfiguracao) ||
                        (config.idpainovo != null && config.idpainovo == nodeFind.obj.config.listadavezconfiguracaonovo);
                    });

                    const configPai = nodePai.obj.config;

                    // Se não tiver regra definida na configuração pai, não pode adicionar regra
                    if (configPai.listadavezregra.listadavezregra == null) {
                        return false;
                    }

                    // Se for uma opção e o valor não estive preenchido
                    if (config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao &&
                        !this.getValorOpcaoPreenchido(config, configPai.listadavezregra.tipoentidade)) {
                        return false;
                    }

                    return true;
                }
            });

            // Se for uma opção
            if (entity.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao) {
                // Remover opção
                arrAcoes.push({
                    label: 'Remover opção',
                    icon: 'fas fa-trash',
                    size: 'xs', 
                    color: 'primary',
                    acao: (node: INodeConfiguracao) => {
                        this.acaoOnRemoverConfiguracao(node);
                    },
                    isVisible: (node: INodeConfiguracao) => {
                        if (!this.configuracaoEstaNaListagem(node)) {
                            return false;
                        }

                        // Se estiver no modo de visualização, não pode ver ações
                        if (this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao) {
                            return false;
                        }
                        
                        return true;
                    }
                });
            }
        }

        return arrAcoes;
    }

    /**
     * Chamada quando a ação de adicionar opção for disparada
     */
    private acaoOnAddOpcao(node: INodeConfiguracao) {
        // Defino a configuração
        let configOpcao: IListadavezconfiguracoesApi = {
            tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao,
            listadavezconfiguracaonovo: this.getNovoId(),
            ordem: this.getNovaOrdem(node),
            listadavezregra: angular.copy(node.obj.config.listadavezregra)
        }

        // Preencho campos de valores de acordo com o tipo de regra
        switch (node.obj.config.listadavezregra.tipoentidade) {
            case EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo: {
                configOpcao.idlistadavezregravalor = {
                    listadavezregravalor: null
                }

                break;
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteEstado: {
                configOpcao.idestado = {
                    uf: null
                }
                break;
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteNegocioOperacao: {
                configOpcao.idnegociooperacao = {
                    proposta_operacao: null
                }
                break;
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteSegmentoAtuacao: {
                configOpcao.idsegmentoatuacao = {
                    segmentoatuacao: null
                }
                break;
            }
        }

        let nodeOpcaoNova = this.adicionarConfiguracao(configOpcao, node);

        // Se a regra for do tipo valor fixo, já atribuo um valor aleatório
        if (node.obj.config.listadavezregra.tipoentidade == EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo){
            nodeOpcaoNova.obj.config.idlistadavezregravalor.listadavezregravalor = this.getRegraFixaValoresDisponiveis(nodeOpcaoNova)[0].listadavezregravalor;

            // Se atingiu o máximo de valores, excluir fluxo alternativo.
            if (node.obj.config.listadavezregra.totalvalores == node.obj.getOpcoes().length) {
                let nodeFluxoAlternativo = node.children.find((nodeFind) => {
                    return nodeFind.obj.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo;
                });

                if (nodeFluxoAlternativo) {
                    this.excluirConfiguracao(nodeFluxoAlternativo);
                }
            }
        }

        // Atualizo o scopo da tela
        this.reloadScope();
    }

    /**
     * Chamada quando a ação de adicionar regra for disparada
     */
    private acaoOnAddRegra(node: INodeConfiguracao) {
        // Defino a configuração
        let configRegra: IListadavezconfiguracoesApi = {
            tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra,
            listadavezconfiguracaonovo: this.getNovoId(),
            ordem: this.getNovaOrdem(node),
            listadavezregra: {
                listadavezregra: null
            }
        }

        let configFluxoAlternativo: IListadavezconfiguracoesApi = {
            tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo,
            listadavezconfiguracaonovo: this.getNovoId(),
            idpainovo: configRegra.listadavezconfiguracaonovo,
            ordem: this.getNovaOrdem(node)
        }

        // Adiciono configuração da regra
        const nodeRegra = this.adicionarConfiguracao(configRegra, node);

        // Adiciono configuração do fluxo alternativo da regra
        this.adicionarConfiguracao(configFluxoAlternativo, nodeRegra);

        // Atualizo o scopo da tela
        this.reloadScope();
    }

    /**
     * Chamada quando as ações de remover regra, remover opção ou remover fluxo alternativo forem disparadas
     * @param node 
     */
    private acaoOnRemoverConfiguracao(node: INodeConfiguracao) {
        let excluir = confirm('Tem certeza que deseja excluir a configuração?');

        // Se não for excluir, saio da função
        if (!excluir) {
            return ;
        }

        // Chamo exclusão da configuração e seus filhos
        this.excluirConfiguracao(node);

        // Atualizo o scopo
        this.reloadScope();
    }

    /**
     * Chamada quando o botão de excluir em massa for clicado
     */
    private acaoOnExcluirEmMassa() {
        if (this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao) {
            return ;
        }
        
        if (this.getTotalSelecionados() == 0) {
            return ;
        }

        const nodeInicial = this.dadosTreeEmCascata[0];

        if (confirm('Tem certeza que deseja excluir as configurações selecionadas?') && this.validarExclusaoEmMassa(nodeInicial)) {
            this.excluirEmMassa(nodeInicial);

            this.reloadScope();
        }
    }

    /**
     * Valida se a exclusão em massa pode ser realizada para os itens selecionados
     * @param node 
     */
    private validarExclusaoEmMassa(node: INodeConfiguracao): boolean {
        // Se a configuração inicial estiver marcada, retorno false
        if (node._parentId_ == null && node.obj.isChecked()) {
            this.mostrarMensagem('Configuração inicial não pode ser excluída!');
            return false;
        }

        // Se o node for regra, não for ser excluído e seu fluxo alternativo estiver marcado, retorno erro
        if (
            node.obj.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra && !node.obj.isChecked()
        ) {
            const fluxoAlternativo = node.obj.getFluxoAlternativo();

            if (fluxoAlternativo && fluxoAlternativo.isChecked()) {
                this.mostrarMensagem('Fluxo alternativo não pode ser excluído!');
                return false;
            }
        }

        // Chamo validação para os filhos
        for (let index = 0; index < node.children.length; index++) {
            const nodeChild = node.children[index];

            if (!this.validarExclusaoEmMassa(nodeChild)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Exclui árvore em massa a partir de um node, quando item estiver marcado
     */
    private excluirEmMassa(node: INodeConfiguracao) {
        const excluir = node.obj.isChecked();

        // Se o node será excluído, chamo exclusão de configurações para o node e seus filhos
        if (excluir) {
            this.excluirConfiguracao(node);
        } 
        // Se o node não será excluído, chamo excluirEmMassa para seus filhos, que serão excluídos somente se estiverem marcados
        else {
            node.children.forEach((nodeForeach) => {
                this.excluirEmMassa(nodeForeach);
            })
        }
    }

    /**
     * Exclui configuração
     * @param node 
     * @param paiSeraExcluido Deve ser true para informar que após essa função ser chamada, o node pai será excluído.
     */
    private excluirConfiguracao(node: INodeConfiguracao, paiSeraExcluido: boolean = false){
        // Excluo filhos da configuração
        node.children.forEach((nodeForeach) => {
            this.excluirConfiguracao(nodeForeach, true);
        });

        // Busco índice do node
        const index = this.arrDadosTreeEmLinha.findIndex((nodeFindIndex) => {
            return nodeFindIndex._id_ == node._id_;
        });

        // Removo da lista
        this.arrDadosTreeEmLinha.splice(index, 1);

        // Se o pai não será excluído, estiver excluindo uma opção com regra de valor fixo e o node pai não tiver fluxo alternativo: Adiciono fluxo alternativo
        if (!paiSeraExcluido && node.obj.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao &&
            node.obj.config.listadavezregra.tipoentidade == EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo
        ) {
            const nodePai = this.arrDadosTreeEmLinha.find((nodeFind) => {
                return nodeFind._id_ == node._parentId_;
            });

            // Se o pai não tiver fluxo alternativo, adiciono
            if (!nodePai.obj.getFluxoAlternativo()) {
                let configFluxoAlternativo: IListadavezconfiguracoesApi = {
                    tiporegistro: EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo,
                    listadavezregra: angular.copy(nodePai.obj.config.listadavezregra),
                    listadavezconfiguracaonovo: this.getNovoId(),
                    idpainovo: nodePai.obj.config.listadavezconfiguracaonovo,
                    ordem: this.getNovaOrdem(node)
                }

                this.adicionarConfiguracao(configFluxoAlternativo, nodePai);
            }
        }
    }

    /**
     * Adiciona uma nova configuração
     */
    private adicionarConfiguracao(novaConfig: IListadavezconfiguracoesApi, nodePai: INodeConfiguracao): INodeConfiguracao{
        // Seto o pai
        if (nodePai.obj.config.listadavezconfiguracao) {
            novaConfig.idpai = nodePai.obj.config.listadavezconfiguracao;
        } else {
            novaConfig.idpainovo = nodePai.obj.config.listadavezconfiguracaonovo;
        }
        const idPai = this.getTipoRegistroDescricao(nodePai.obj.config.tiporegistro) + 
            this.obterIdEntity(nodePai.obj.config);

        // Crio configuração da tela
        let opcao = new ConfiguracaoTela(novaConfig);
        // Crio node da árvore
        const itemArvore = this.obterNovoNode(opcao, idPai);
        // Adiciono node da tree a lista
        this.arrDadosTreeEmLinha.push(<any>itemArvore);
        // Adiciono aos filhos da configuração pai
        nodePai.obj.children.push(opcao);

        // Expando o pai
        nodePai.expanded = true;

        return <any>itemArvore;
    }

    /**
     * Retorna se o valor da configuração tipo opção está preenchido
     * @param config 
     * @param tipoRegra 
     */
    private getValorOpcaoPreenchido(config: IListadavezconfiguracoesApi, tipoRegra: EnumListadavezregrasTipoEntidadeApi): boolean {
        switch (tipoRegra) {
            case EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo: {
                return config.idlistadavezregravalor && config.idlistadavezregravalor.listadavezregravalor != null && config.idlistadavezregravalor.listadavezregravalor != '';
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteEstado: {
                return config.idestado && config.idestado.uf != null && config.idestado.uf != '';
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteNegocioOperacao: {
                return config.idnegociooperacao && config.idnegociooperacao.proposta_operacao != null && config.idnegociooperacao.proposta_operacao != '';
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteSegmentoAtuacao: {
                return config.idsegmentoatuacao && config.idsegmentoatuacao.segmentoatuacao != null && config.idsegmentoatuacao.segmentoatuacao != '';
            }
        }
    }

    /**
     * Retorna o valor da configuração tipo opção
     * @param config 
     * @param tipoRegra 
     */
    private getValorOpcao(config: IListadavezconfiguracoesApi, tipoRegra: EnumListadavezregrasTipoEntidadeApi) {
        switch (tipoRegra) {
            case EnumListadavezregrasTipoEntidadeApi.lvrteValorFixo: {
                return config.idlistadavezregravalor.listadavezregravalor;
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteEstado: {
                return config.idestado.uf;
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteNegocioOperacao: {
                return config.idnegociooperacao.proposta_operacao;
            }
            case EnumListadavezregrasTipoEntidadeApi.lvrteSegmentoAtuacao: {
                return config.idsegmentoatuacao.segmentoatuacao;
            }
        }
    }

    /**
     * Retorna o id da entidade
     * @param entity 
     */
    private obterIdEntity(entity) {
        let id = entity.listadavezconfiguracao || entity.listadavezconfiguracaonovo;

        return id;
    }

    /**
     * Pega a descrição referente ao tipo de registro da configuração
     */
    private getTipoRegistroDescricao(tipo: EnumListadavezconfiguracoesTipoRegistroApi) {
        switch (tipo) {
            case EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra: {
                return 'regra';
            }
            case EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao: {
                return 'opcao';
            }
            case EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo: {
                return 'fluxoalternativo';
            }
        }
    }

    /**
     * Retorna regras disponíveis para seleção
     * @param node 
     */
    private getRegrasDisponiveis(node: INodeConfiguracao): IListadavezregrasApi[]{
        // Pegos regras que foram buscadas da API
        let arrRegras = this.arrRegras;
        // Lista de regras que devem ser removidas do retorno
        const arrRegrasRemover: string[] = [];

        // busco lista de regras utilizadas pelos pais para
        let nodePai: INodeConfiguracao = null;

        do {
            let idPai = node._parentId_;

            if (nodePai != null) {
                idPai = nodePai._parentId_;
            }

            // Se possuir pai, busco o node pai.
            if (idPai != null && idPai != '') {
                nodePai = this.arrDadosTreeEmLinha.find((nodeFind) => {
                    return nodeFind._id_ == idPai;
                });

                // Se o pai for do tipo regra, removo da lista de regras
                if (nodePai.obj.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
                    arrRegrasRemover.push(nodePai.obj.config.listadavezregra.listadavezregra);
                }
            } else {
                nodePai = null;
            }
        } while (nodePai != null);

        // Removo regras dos pais do retorno
        arrRegras = arrRegras.filter((regraFilter) => {
            return arrRegrasRemover.indexOf(regraFilter.listadavezregra) == -1;
        });

        // Retorno regras
        return arrRegras;
    }

    /**
     * Retorna valores de uma regra fixa disponíveis para seleção
     * @param node 
     */
    private getRegraFixaValoresDisponiveis(node: INodeConfiguracao): IListadavezregrasvaloresApi[]{
        // Pegos valores da regra
        let arrRegrasValores = node.obj.config.listadavezregra.itens;
        // Lista de valores regras que devem ser removidas do retorno
        const arrRegrasValorRemover: string[] = [];

        // Busco o node pai referente a regra
        let nodePai = this.arrDadosTreeEmLinha.find((nodeFind) => {
            return nodeFind._id_ == node._parentId_;
        });

        // Busco valores já utilizados por opções irmãs
        nodePai.obj.getOpcoes().forEach((opcaoForeach) => {
            let configNode = node.obj.config;
            let configForeach = opcaoForeach.config;

            // Verifica se são opções diferentes
            const mesmaOpcao = (
                configNode.listadavezconfiguracao != null && configNode.listadavezconfiguracao == configForeach.listadavezconfiguracao ||
                configNode.listadavezconfiguracaonovo != null && configNode.listadavezconfiguracaonovo == configForeach.listadavezconfiguracaonovo
            );

            // Se são opções diferentes e a opção tem valor preenchido
            if (!mesmaOpcao && opcaoForeach.config.idlistadavezregravalor.listadavezregravalor != null && opcaoForeach.config.idlistadavezregravalor.listadavezregravalor != '') {
                arrRegrasValorRemover.push(opcaoForeach.config.idlistadavezregravalor.listadavezregravalor);
            }
        });

        // Removo valores da regra já utilizados
        arrRegrasValores = arrRegrasValores.filter((regraValorFilter) => {
            return arrRegrasValorRemover.indexOf(regraValorFilter.listadavezregravalor) == -1;
        });

        // Retorno valores da regra
        return arrRegrasValores;
    }

    /**
     * Verifica se é uma opção ou filho de alguma opção da regra "Já é cliente?" com valor da opção "Sim"
     * @param node 
     */
    private EhOuPossuiPaiOpcaoJaEcliente(node: INodeConfiguracao): boolean {
        let nodePai: INodeConfiguracao = node;

        while (nodePai != null) {
            const configPai = nodePai.obj.config;

            // Se a configuração pai for do tipo opção, com regra Já é cliente ? e valor Já é cliente, retorno true;
            if (
                configPai.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao &&
                configPai.listadavezregra.nome == 'Já é Cliente?'
            ) {
                let regra = this.arrRegras.find((regraFind) => {
                    return regraFind.listadavezregra == configPai.listadavezregra.listadavezregra;
                });

                if (regra) {
                    const regravalor = regra.itens.find((itemFind) => {
                        return itemFind.listadavezregravalor == configPai.idlistadavezregravalor.listadavezregravalor;
                    });

                    if (regravalor && regravalor.valor == "1") {
                        return true;
                    }
                }

                break;
            }

            // Busco o node pai
            nodePai = this.arrDadosTreeEmLinha.find((nodeFind) => {
                return nodeFind._id_ == nodePai._parentId_;
            });
        }

        return false;
    }

    /**
     * Organiza as opções da regra, sempre deixando o fluxo alternativo por último
     * @param node 
     */
    private organizarOpcoes(node: INodeConfiguracao = null){
        if(node == null && this.dadosTreeEmCascata.length > 0) {
            node = this.dadosTreeEmCascata[0];
        }

        if (node != null) {
            // Só ordeno os filhos quando o node é do tipo regra
            if (node.obj.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
                node.children = node.children.sort((nodeA, nodeB) => {
                    // Explicação do retorno: -1 (A antes do B); 1 (B antes do A); 0 ( Sem alteração )

                    const configA = nodeA.obj.config;
                    const configB = nodeB.obj.config;

                    if (configA.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo) {
                        return 1
                    } else if (configB.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo) {
                        return -1;
                    } else if (configA.ordem < configB.ordem) {
                        return -1;
                    } else {
                        return 1;
                    }
                });
            }

            // Chamo a função de organizar opções para os filhos
            node.children.forEach((nodeForeach) => {
                this.organizarOpcoes(nodeForeach);
            })
        }
    }

    /**
     * Retorna um novo id para a configuração
     */
    private getNovoId(){
        this.listadavezconfiguracaonovo++;
        return this.listadavezconfiguracaonovo.toString();
    }

    /**
     * Retorna a nova ordem de acordo com as opções do node pai
     */
    private getNovaOrdem(nodePai: INodeConfiguracao){
        const configPai = nodePai.obj.config;

        if (configPai.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
            let maiorOrdem = 0;

            nodePai.obj.getOpcoes().forEach((configTelaForeach) => {
                if (configTelaForeach.config.ordem > maiorOrdem) {
                    maiorOrdem = configTelaForeach.config.ordem;
                }
            });

            return ++maiorOrdem;
        } else {
            return 0;
        }
    }

    /**
     * Salva lista de configurações na API
     */
    private async salvarConfiguracoes(){
        // Se configurações forem inválidas, não salvo configurações
        if (!this.validarConfiguracoes()) {
            return;
        }

        // Ativo loading
        this.busy = true;
        this.reloadScope();

        // Monto lista de configurações
        const arrConfiguracoes: IListadavezconfiguracoesApi[] = [];

        this.arrDadosTreeEmLinha.forEach((nodeForeach) => {
            arrConfiguracoes.push(nodeForeach.obj.config);
        });

        // Monto objeto de configuração em lote
        const configuracaoEmLote = {
            listadavezconfiguracoes: arrConfiguracoes
        }

        // Chamo requisição de salvar configurações
        try {
            await this.entityService.salvarEmLote(configuracaoEmLote);
            // Altero a tela pro modo de visualização
            this.abaRegrasCtrl.modoTela = EnumModoTelaRegra.mtrVisualizacao;
            // Apresento mensagem de sucesso
            this.mostrarMensagem('Regras salvas com sucesso!', 'success');
        } catch (error) {
            this.mostrarMensagem(error.data.message);
        }

        // Desativo loading
        this.busy = false;
        this.reloadScope();
    }

    /**
     * Valida a lista de configurações
     */
    private validarConfiguracoes(): boolean{
        // Percorro nodes para validação
        for (let index = 0; index < this.arrDadosTreeEmLinha.length; index++) {
            const node = this.arrDadosTreeEmLinha[index];
            
            // Pego a configuração do node
            const config = node.obj.config;

            // Pego configuração pai, caso exista
            const nodePai = this.arrDadosTreeEmLinha.find((nodeFind) => {
                return (config.idpai != null && config.idpai == nodeFind.obj.config.listadavezconfiguracao) ||
                (config.idpainovo != null && config.idpainovo == nodeFind.obj.config.listadavezconfiguracaonovo);
            });

            const configPai = (nodePai) ? nodePai.obj.config : null;

            // Quando o node for do tipo regra
            if (config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
                // Valido se configuração do tipo Regra está preenchida
                if (config.listadavezregra.listadavezregra == null || config.listadavezregra.listadavezregra == ''){
                    this.mostrarMensagem('Configuração sem regra selecionada!');
                    return false;
                }

                // Valido se tem opções com valores repetidos
                const arrValoresUtilizados: string[] = [];
                const arrOpcoes = node.obj.getOpcoes();
                for (let indexOpcao = 0; indexOpcao < arrOpcoes.length; indexOpcao++) {
                    const opcaoFor = arrOpcoes[indexOpcao];
                    
                    // Se tem valor da opção preenchido
                    if (this.getValorOpcaoPreenchido(opcaoFor.config, config.listadavezregra.tipoentidade)) {
                        // Se valor da opção já está sendo utilizado, retorno mensagem de erro
                        if (arrValoresUtilizados.indexOf(this.getValorOpcao(opcaoFor.config, config.listadavezregra.tipoentidade)) > -1) {
                            this.mostrarMensagem('Opções com valores duplicados!');
                            return false;
                        }
                        
                        // Adiciono valor ao array de valores utilizados
                        arrValoresUtilizados.push(this.getValorOpcao(opcaoFor.config, config.listadavezregra.tipoentidade))
                    }

                }
            } 
            // Quando o node for do tipo Opção ou Caso alternativo
            else if ([EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao, EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo].indexOf(config.tiporegistro) > -1){
                // Valido se configuração do tipo Opção está preenchida
                if (
                    config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao &&
                    !this.getValorOpcaoPreenchido(config, configPai.listadavezregra.tipoentidade)
                ){
                    this.mostrarMensagem('Configuração sem valor selecionado!');
                    return false;
                }

                // Valido se folha, quando não tem vendedor fixo, está com a listadavezvendedor selecionada
                if (
                    node.children.length == 0 &&
                    !config.vendedorfixo &&
                    (!config.listadavezvendedor || !config.listadavezvendedor.listadavezvendedor)
                ){
                    this.mostrarMensagem('Configuração sem lista de vendedores selecionada!');
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Apresenta um toaster
     * @param mensagem 
     * @param tipo
     */
    private mostrarMensagem(mensagem: string, tipo: 'error' | 'success' = 'error'){
        this.toaster.pop({
            type: tipo,
            title: mensagem
        });
    }

    /**
     * Retorna se todos os itens estão marcados
     */
    private estaoTodosMarcados(): boolean {
        return this.dadosTreeEmCascata.length > 0 && this.dadosTreeEmCascata[0].obj.isChecked();
    }

    /**
     * Retorna a quantidade de itens selecionados
     */
    private getTotalSelecionados(): number {
        return this.arrDadosTreeEmLinha.filter((nodeFilter) => {
            return nodeFilter.obj.isChecked();
        }).length;
    }

    /**
     * Marca ou desmarca todos os itens
     */
    private marcarDesmarcarTodos() {
        if (this.abaRegrasCtrl.modoTela == EnumModoTelaRegra.mtrVisualizacao) {
            return ;
        }
        
        const todosMarcados = this.estaoTodosMarcados();

        if (this.dadosTreeEmCascata.length > 0) {
            this.dadosTreeEmCascata[0].obj.setChecked(!todosMarcados);
        }
    }
}


/**
 * Classe referente as configurações da tela
 */
class ConfiguracaoTela {
    checked: boolean = false;
    children: ConfiguracaoTela[] = [];

    constructor(
        public config: IListadavezconfiguracoesApi
    ){}

    /**
     * Retorna se o item está marcado.
     * Caso o item tenha filhas, retorna true se todos os filhos estiverem marcados
     */
    isChecked(): boolean {
        let isChecked = false;

        if (this.children.length == 0) {
            isChecked = this.checked;
        } else {
            isChecked = this.children.every((configEvery) => {
                return configEvery.isChecked();
            });
        }

        this.checked = isChecked;
        return isChecked;
    }

    /**
     * Marca/Desmarca a configuração e seus filhos
     * @param checked 
     */
    setChecked(checked: boolean) {
        if (this.children.length == 0) {
            this.checked = checked;
        } else {
            this.children.forEach((configForeach) => {
                configForeach.setChecked(checked);
            });
        }
    }

    /**
     * Retorna os filhos que são opções
     */
    getOpcoes(): ConfiguracaoTela[] {
        if (this.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
            return this.children.filter((configFilter) => {
                return configFilter.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrOpcao;
            });
        } else {
            return [];
        }
    }

    /**
     * Retorna o fluxo alternativo, caso tenha
     */
    getFluxoAlternativo(): ConfiguracaoTela {
        if (this.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
            const arrFluxosAlternativos = this.children.filter((configFilter) => {
                return configFilter.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrFluxoAlternativo;
            });

            if (arrFluxosAlternativos.length > 0) {
                return arrFluxosAlternativos[0];
            }
        } else {
            return null;
        }
    }

    /**
     * Retorna true se possuir filhos dependentes.
     * No caso de regras: Ter filhos opções ou ter netos.
     * No caso de opções/fluxo alternativo: Possuir filhos
     */
    possuiFilhosDependencias(): boolean {
        // Regra
        if (this.config.tiporegistro == EnumListadavezconfiguracoesTipoRegistroApi.lvctrRegra) {
            // Se possuir qualquer opção
            if (this.getOpcoes().length > 0) {
                return true;
            }

            // Se o fluxo alternativo possuir filho
            const fluxoalternativo = this.getFluxoAlternativo();

            if (fluxoalternativo != null && fluxoalternativo.children.length > 0) {
                return true;
            }
        } 
        // Opção / Fluxo alternativo
        else {
            return this.children.length > 0;
        }

        return false;
    }
}

/**
 * Interface referente aos nodes da árvore
 */
interface INodeConfiguracao {
    _id_: string;
    _parentId_: string;
    children: INodeConfiguracao[];
    obj: ConfiguracaoTela;
    expanded: boolean;
}