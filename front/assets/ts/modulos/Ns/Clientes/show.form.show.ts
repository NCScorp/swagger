import angular = require('angular');

import { CrmTemplatespropostasgrupos } from './../../Crm/Templatespropostasgrupos/factory';
import { CrmTemplatespropostasFormService } from './../../Crm/Templatespropostas/modal';
import { CrmTemplatespropostas } from './../../Crm/Templatespropostas/factory';
import { CrmTemplatespropostascapitulos } from './../../Crm/Templatespropostascapitulos/factory';
import { CrmTemplatespropostascapitulosFormService } from './../../Crm/Templatespropostascapitulos/modal';
import { CrmTemplatespropostascapituloscomposicoes } from './../../Crm/Templatespropostascapituloscomposicoes/factory';
import { CrmTemplatespropostascapitulosComposicoesFormService } from '../../Crm/Templatespropostascapituloscomposicoes/modal';
import { CrmComposicoesFormService } from '../../Crm/Composicoes/modal';
import { CrmComposicoes } from './../../Crm/Composicoes/factory';
import { CrmTemplatescomposicoesfamiliasFormService } from './../../Crm/Templatescomposicoesfamilias/modal';
import { CrmTemplatescomposicoesfamilias } from './../../Crm/Templatescomposicoesfamilias/factory';
import { CrmTemplatescomposicoesfuncoesFormService } from './../../Crm/Templatescomposicoesfuncoes/modal';
import { CrmTemplatescomposicoesfuncoes } from './../../Crm/Templatescomposicoesfuncoes/factory';

import { Tree } from '../../Commons/tree';
import { inserirNode, editarNode } from '../../Commons/utils';
import { timeStamp } from 'console';

interface NsClientesScope extends angular.IScope {
    carregarFilhos: any
}

/**
 * Sobreescrevendo incluir tree-grid
 * @todo colocar informação de que o nó está vazio
 */
export class NsClientesShowShowController {

    static $inject = [
        'NsEnderecosFormService',
        'NsEnderecosFormShowService',
        'NsContatosFormService',
        'NsContatosFormShowService',
        'CrmTemplatespropostasgruposFormService',
        'NsClientesdocumentosFormService',
        'NsClientesdocumentosFormShowService',
        '$scope',
        'toaster',
        'NsClientes',
        'utilService',
        'CrmTemplatespropostas',
        '$rootScope',
        'CrmTemplatespropostascapitulos',
        '$http',
        'nsjRouting',
        'Tree',
        'CrmTemplatespropostasgrupos',
        'CrmTemplatespropostasFormService',
        'CrmTemplatespropostascapitulosFormService',
        'CrmTemplatespropostascapitulosComposicoesFormService',
        'CrmTemplatespropostascapituloscomposicoes',
        'CrmComposicoesFormService',
        'CrmComposicoes',
        'CrmTemplatescomposicoesfamiliasFormService',
        'CrmTemplatescomposicoesfamilias',
        'CrmTemplatescomposicoesfuncoesFormService',
        'CrmTemplatescomposicoesfuncoes'
    ];

    public entity: any;
    public form: any;
    public constructors: any;
    public collection: any;
    public busy: boolean;
    public action: string;
    public idCount: number = 0;

    public myTree: any = {};
    public expandingProperty: any;
    public colDefs: any[] = [];
    public dados: Map<any, any> = new Map(); //Array simples para guardar todos os nós e facilitar buscas
    public dadosTree: any[] = []; //matriz com os nós
    public listaDeNodesCarregados = new Map();
    _templatepropostagrupo: any;
    _templateproposta: any;
    _templatepropostacapitulo: any;
    _templatepropostacomposicao: any;
    _templatecomposicaofamilia: string;
    _templatecomposicaofuncao: string;

    public isBusy: boolean = false;

    public propostasGrupos: any[] = [];
    public templatesPropostas: any[] = [];
    public templatesCapitulos: any[] = [];
    public templatesComposicoes: any[] = [];
    public templatesFamilias: any[] = [];
    public templatesFuncoes: any[] = [];

    public enderecosConfig = {
        nome: 'Nome',
        tipoendereco: 'Tipo de Endereço',
        rua: 'Nome do Logradouro',
        numero: 'Número',
        actions: null
    }

    public contatosConfig = {
        nome: 'Nome',
        primeironome: 'Primeiro nome',
        sobrenome: 'Sobrenome',
        cargo: 'Cargo',
        email: 'Email',
        principal: 'Principal',
        actions: null
    }

    public documentosConfig = {
        nome: 'Nome',
        copiasimples: 'Cópia simples',
        copiaautenticada: 'Cópia autenticada',
        original: 'Original',
        enviarporemail: 'Enviar por e-mail',
        pedirinformacoesadicionais: 'Pedir informações adicionais',
        naoexibiremrelatorios: 'Não exibir nos relatórios de documentos automáticos',
        actions: null
    }

    public anexosConfig = {
        nome: 'Nome do arquivo',
        // actions: null
    }
    /**
     * Indica se o cliente possui itens na listagem de templates de apólices
     */
    public haItensListaTemplates: boolean = false;

    constructor(
        public NsEnderecosFormService: any,
        public NsEnderecosFormShowService: any,
        public NsContatosFormService: any,
        public NsContatosFormShowService: any,
        public CrmTemplatespropostasgruposFormService: any,
        public NsClientesdocumentosFormService: any,
        public NsClientesdocumentosFormShowService: any,
        // public $scope: angular.IScope,
        public $scope: NsClientesScope,
        public toaster: any,
        public entityService: any,
        public utilService: any,
        public crmTemplatespropostas: CrmTemplatespropostas,
        public $rootScope: angular.IScope,
        public crmTemplatespropostascapitulos: CrmTemplatespropostascapitulos,
        public $http: angular.IHttpService,
        public nsjRouting: any,
        public tree: Tree,
        public crmTemplatespropostasgrupos: CrmTemplatespropostasgrupos,
        public crmTemplatespropostasFormService: CrmTemplatespropostasFormService,
        public crmTemplatespropostascapitulosFormService: CrmTemplatespropostascapitulosFormService,
        public crmTemplatespropostascapitulosComposicoesFormService: CrmTemplatespropostascapitulosComposicoesFormService,
        public crmTemplatespropostascapituloscomposicoes: CrmTemplatespropostascapituloscomposicoes,
        public crmComposicoesFormService: CrmComposicoesFormService,
        public crmComposicoes: CrmComposicoes,
        public CrmTemplatescomposicoesfamiliasFormService: CrmTemplatescomposicoesfamiliasFormService,
        public crmTemplatescomposicoesfamilias: CrmTemplatescomposicoesfamilias,
        public crmTemplatescomposicoesfuncoesFormService: CrmTemplatescomposicoesfuncoesFormService,
        public crmTemplatescomposicoesfuncoes: CrmTemplatescomposicoesfuncoes
    ) {
        this.montaListaEnderecos = this.montaListaEnderecos.bind(this);
        this.montaListaContatos = this.montaListaContatos.bind(this);
        this.montaListaDocumentos = this.montaListaDocumentos.bind(this);
        this.montaListaAnexos = this.montaListaAnexos.bind(this);
    }

    /**
   * Carrega o primeiro nível da árvore
   */
    $onInit() {

        this.$scope.$watch('$ctrl.entity', (newValue, oldValue) => {
            if (newValue !== oldValue) {
                this.form.$setDirty();
            }
        }, true);
        this.$scope.$on('ns_enderecos_loaded', () => {
            this.busy = false;
        });
        this.$scope.$on('ns_contatos_loaded', () => {
            this.busy = false;
        });
        this.$scope.$on('crm_templatespropostasgrupos_loaded', () => {
            this.busy = false;
        });

        this.$scope.carregarFilhos = (branch) => {
            if (!this.listaDeNodesCarregados.has(branch._id_)) {
                this.carregarFilhos(branch)
            }
        }

        this.expandingProperty = {
            field: 'nome',
            displayName: 'Nome',
            sortable: true,
            filterable: true
        }

        /* carrega as colunas */
        this.colDefs = [
            {
                field: 'qtd',
                displayName: 'Quantidade'
            },
            {
                field: 'valor',
                displayName: 'Valor'
            },
            {
                field: null,
                cellTemplate: `
              <div class='table-btns-actions'>
                <nsj-actions>  
                  <nsj-action  
                    ng-repeat="action in row.branch.actions" 
                    ng-click='cellTemplateScope.click(action.method)' 
                    icon='{{action.icon}}'
                    title='{{action.label}}'>
                  </nsj-action>
                </nsj-actions>
              </div>
              `,
                cellTemplateScope: {
                    click: (acao: any) => {
                        this.traduzMetodo(acao);
                        //   this.findNoRemove(this.$scope.tree_data, branch)
                    }
                }
            }
        ];

        /* chama o método que vai carregar o primeiro nível da árvore */
        // this.carregarGrupos(this.entity.cliente);
        this.loadDados(this.entity.cliente);

        this.onTemplateGrupos();
        this.onCrmTemplatesPropostasGruposRemover();

        this.onCrmTemplatePropostaForm();
        this.onCrmTemplatePropostaRemover();

        this.onCrmCapitulo();
        this.onCrmCapituloRemover();

        this.onCrmTemplatePropostaComposicao();
        this.onCrmTemplatePropostaComposicaoRemover();

        this.onCrmComposicaoFamilia();
        this.onCrmComposicaoFamiliaRemover();

        this.onCrmComposicaoFuncao();
        this.onCrmComposicaoFuncaoRemover();

        // Somente concatenando ao número de dias para vencimento do pagamento a string 'dias', para ficar explícito no modo visualização
        if(this.entity.diasparavencimento != null){
            this.entity.diasparavencimento += ' Dia(s)';
        }

    }

    montaListaContatos(subentity: any) {

        const config = {
            entity: {
                principal: subentity.principal ? 'Sim' : 'Não'
            },
            actions: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.NsContatosFormShow(subentity)
                    }
                }
            ]
        }

        return config;
    }

    montaListaEnderecos(subentity: any) {

        let tipoEnderecoTexto;

        switch (subentity.tipoendereco) {
            case 0:
                tipoEnderecoTexto = 'Local'
                break;
            case 1:
                tipoEnderecoTexto = 'Entrega'
                break;
            case 2:
                tipoEnderecoTexto = 'Cobrança'
                break;
            default:
                tipoEnderecoTexto = 'Comercial'
                break;
        }
        
        const config = {

            entity:{
                endereco: subentity.endereco,
                nome: subentity.nome,
                tipoendereco: tipoEnderecoTexto
            },
            
            actions: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.nsEnderecosFormShow(subentity)
                    }
                }
            ]
        }

        return config;
    }

    montaListaDocumentos(subentity: any) {
        
        const config = {

            entity:{
                clientedocumento: subentity.clientedocumento,
                nome: subentity.tipodocumento.nome,
                copiasimples: subentity.copiasimples ? 'Sim' : 'Não',
                copiaautenticada: subentity.copiaautenticada ? 'Sim' : 'Não',
                original: subentity.original ? 'Sim' : 'Não',
                enviarporemail: subentity.permiteenvioemail ? 'Sim' : 'Não',
                pedirinformacoesadicionais: subentity.pedirinformacoesadicionais ? 'Sim' : 'Não',
                naoexibiremrelatorios: subentity.naoexibiremrelatorios ? 'Sim' : 'Não',
            },

            actions: [
                {
                    label: 'Visualizar',
                    icon: 'fas fa-eye',
                    method: (subentity: any, index: any) => {
                        this.nsClientesdocumentosFormShow(subentity)
                    }
                }
            ]
        }

        return config;
    }

    montaListaAnexos(subentity: any) {
        
        const config = {

            entity:{
                clientesanexo: subentity.clientesanexo,
                nome: subentity.nomearquivo,
            },
            actions: []
        }

        return config;
    }

    NsContatosFormShow(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };

        if (!subentity) {
            this.busy = true;
        }
        var modal = this.NsContatosFormShowService.open(parameter, subentity);
    }

    private async loadDados(id: string) {
        this.isBusy = true;
        //buscando todos os templates propostas grupos
        const { data: propostasGrupos } = await this.obterEntidade('crm_templatespropostasgrupos_index', 'cliente', id);

        this.propostasGrupos = propostasGrupos;
        // console.log('propostasGrupos', propostasGrupos);

        //para cada grupo, busco suas templates propostas
        let arrayPromisesTemplateProposta = [];
        let arrayPromisesTemplatePropostaResult = [];
        let templatesPropostas = [];
        propostasGrupos.forEach((grupo) => {
            // adicionar a promise de template proposta
            arrayPromisesTemplateProposta.push(this.obterEntidade('crm_templatespropostas_index', 'templatepropostagrupo', grupo.templatepropostagrupo));
            arrayPromisesTemplatePropostaResult.push([]);
        });
        //executando todas as chamadas adicionadas acima
        arrayPromisesTemplatePropostaResult = await Promise.all(arrayPromisesTemplateProposta);
        //reorganizando as templates propostas em um array horizontal
        arrayPromisesTemplatePropostaResult.forEach(templatesDoGrupo => {
            templatesPropostas = templatesPropostas.concat(templatesDoGrupo.data);
        });
        this.templatesPropostas = templatesPropostas;
        // console.log('templatesPropostas',templatesPropostas);

        // para cada template proposta, buscar templates propostas capitulos
        let arrayPromisesTemplateCapitulo = [];
        let arrayPromisesTemplateCapituloResult = [];
        let templatesCapitulos = [];
        templatesPropostas.forEach((proposta) => {
            // adicionar a promise
            arrayPromisesTemplateCapitulo.push(this.obterEntidade('crm_templatespropostascapitulos_index', 'templateproposta', proposta.templateproposta));
            arrayPromisesTemplateCapituloResult.push([]);
        });
        arrayPromisesTemplateCapituloResult = await Promise.all(arrayPromisesTemplateCapitulo);
        arrayPromisesTemplateCapituloResult.forEach(capitulosDaProposta => {
            templatesCapitulos = templatesCapitulos.concat(capitulosDaProposta.data);
        });
        this.templatesCapitulos = templatesCapitulos;
        // console.log('templatesCapitulos',templatesCapitulos);


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
        // console.log('templatesComposicoes',templatesComposicoes);

        // para cada template capitulo composicao, buscar familias e funcoes
        let dadosFuncoesFamilia: {
            arrPromises: Promise<any>[],
            arrTipoInfo: ('familia' | 'funcao')[],
            arrRetornoApi: any[]
        } = {
            arrPromises: [], // Promisses de busca por família e função
            arrTipoInfo: [], // Se é família ou função
            arrRetornoApi: [] // Dados retornados das requisições
        };
        // let arrayFamilias = [];
        // let arrayFuncoes = [];

        templatesComposicoes.forEach((composicao) => {
            // Adiciono promise de templatescomposicoesfamilias
            dadosFuncoesFamilia.arrPromises.push(this.obterEntidade('crm_templatescomposicoesfamilias_index', 'templatepropostacomposicao', composicao.templatepropostacomposicao));
            dadosFuncoesFamilia.arrTipoInfo.push('familia');
            dadosFuncoesFamilia.arrRetornoApi.push([]);
            // Adiciono promise de templatescomposicoesfuncoes
            dadosFuncoesFamilia.arrPromises.push(this.obterEntidade('crm_templatescomposicoesfuncoes_index', 'templatepropostacomposicao', composicao.templatepropostacomposicao));
            dadosFuncoesFamilia.arrTipoInfo.push('funcao');
            dadosFuncoesFamilia.arrRetornoApi.push([]);
        });
        dadosFuncoesFamilia.arrRetornoApi = await Promise.all(dadosFuncoesFamilia.arrPromises);
        // console.log('dadosFuncoesFamilia',dadosFuncoesFamilia.arrRetornoApi);

        dadosFuncoesFamilia.arrTipoInfo.forEach((tipo, index) => {
            if (tipo == "familia") {
                this.templatesFamilias = this.templatesFamilias.concat(dadosFuncoesFamilia.arrRetornoApi[index].data);
            } else {
                this.templatesFuncoes = this.templatesFuncoes.concat(dadosFuncoesFamilia.arrRetornoApi[index].data);
            }
        });
        // console.log('templatesFamilias', this.templatesFamilias);
        // console.log('templatesFuncoes', this.templatesFuncoes);

        //apos isso, organizar os dados.
        this.organizarDadosTela();
        this.atualizarTree();
        this.isBusy = false;
    }

    organizarDadosTela() {
        this.propostasGrupos.forEach((grupo) => {

            //para cada grupo, monta o nó
            const node = this.montarNo('grupo', grupo, grupo.templatepropostagrupo);

            //passa o grupo pedindo para montar as propostas
            this.processaProposta(grupo);
            //adiciona o nó na tree
            this.tree.adicionarNo(node, this.dados);
        });
    }


    processaProposta(grupo) {
        //filtra as propostas pertencentes ao grupo
        const arrayPropostas = this.templatesPropostas.filter((proposta) => {
            return proposta.templatepropostagrupo == grupo.templatepropostagrupo
        });
        // console.log('grupo',grupo);
        // console.log('arrayPropostas',arrayPropostas);
        arrayPropostas.forEach(templateProposta => {
            //para cada proposta, gera o nó
            const node = this.montarNo('template', templateProposta, templateProposta.templateproposta, 'grupo.' + grupo.templatepropostagrupo);

            //processa seus capitulos
            this.processaCapitulo(templateProposta);

            //adiciona o nó na tree
            this.tree.adicionarNo(node, this.dados);
        });
    }

    processaCapitulo(proposta) {
        //filtra os capitulos pertencentes a proposta
        const arrayCapitulos = this.templatesCapitulos.filter((capitulo) => {
            return capitulo.templateproposta == proposta.templateproposta
        });

        // console.log('proposta',proposta);
        // console.log('arrayCapitulos',arrayCapitulos);

        arrayCapitulos.forEach(capitulo => {
            //para cada capitulo, gera o nó
            let pai = capitulo.pai == null ? 'template.' + proposta.templateproposta : 'capitulo.' + capitulo.pai;
            const node = this.montarNo('capitulo', capitulo, capitulo.templatepropostacapitulo, pai);

            //processa sua composição
            this.processaComposicao(capitulo);

            //adiciona o nó na tree
            this.tree.adicionarNo(node, this.dados);
        });
    }

    processaComposicao(capitulo) {
        //filtra as composicoes pertencentes ao capitulo
        const arrayComposicoes = this.templatesComposicoes.filter((composicao) => {
            return composicao.templatepropostacapitulo == capitulo.templatepropostacapitulo
        });
        // console.log('capitulo', capitulo);
        // console.log('arrayComposicoes', arrayComposicoes);

        arrayComposicoes.forEach(composicao => {
            //para cada capitulo, gera o nó
            composicao.nome = composicao.nome || composicao.composicao.nome
            let novoNo = this.montarNo('templatecomposicao', composicao, composicao.templatepropostacomposicao, 'capitulo.' + composicao.templatepropostacapitulo);

            //adiciona o nó na tree
            this.tree.adicionarNo(novoNo, this.dados);

            //processa sua composição
            this.processaFuncao(composicao, novoNo._id_);
            this.processaFamilia(composicao, novoNo._id_);

        });
    }

    processaFamilia(composicao, noPaiIdentificacao) {
        const arrayFamilias = this.templatesFamilias.filter((familia) => {
            return familia.templatepropostacomposicao == composicao.templatepropostacomposicao
        });

        // console.log('composicao', composicao);
        // console.log('arrayFamilias', arrayFamilias);

        arrayFamilias.forEach(composicaoFamilia => {
            //para cada composicaoFamilia, gera o nó
            composicaoFamilia.qtd = composicaoFamilia.quantidade || composicaoFamilia.familia.quantidade;
            composicaoFamilia.nome = composicaoFamilia.nome || composicaoFamilia.familia.nome;
            composicaoFamilia.descricao = composicaoFamilia.descricao || composicaoFamilia.familia.descricao;
            const node = this.montarNo('composicao.familia', composicaoFamilia, composicaoFamilia.templatecomposicaofamilia, noPaiIdentificacao);
            this.tree.adicionarNo(node, this.dados);
        });
    }

    processaFuncao(composicao, noPaiIdentificacao) {
        const arrayFuncoes = this.templatesFuncoes.filter((funcao) => {
            return funcao.templatepropostacomposicao == composicao.templatepropostacomposicao
        });

        // console.log('composicao', composicao);
        // console.log('arrayFuncoes', arrayFuncoes);

        arrayFuncoes.forEach(composicaoFuncao => {
            //para cada composicaoFamilia, gera o nó
            composicaoFuncao.qtd = composicaoFuncao.quantidade || composicaoFuncao.funcao.quantidade;
            composicaoFuncao.nome = composicaoFuncao.nome || composicaoFuncao.funcao.nome;
            composicaoFuncao.valor = composicaoFuncao.valor || composicaoFuncao.funcao.valor;
            composicaoFuncao.descricao = composicaoFuncao.descricao || composicaoFuncao.funcao.descricao;;
            const node = this.montarNo('composicao.funcao', composicaoFuncao, composicaoFuncao.templatecomposicaofuncao, noPaiIdentificacao);
            this.tree.adicionarNo(node, this.dados);
        });
    }


    //   nsContatosFormShow(subentity: any) {
    //     let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
    //     if (parameter.identifier) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsContatosFormShowService.open(parameter, subentity);
    // }

    /**
     * Sobrescrito porque o MDA não fez a conversão correta da entidade
     */
    nsContatosFormShow(subentity: any) {
        let parameter = { 'pessoa': subentity.cliente, 'identifier': subentity.contato };
        if (!subentity) {
            this.busy = true;
        }
        const modal = this.NsContatosFormShowService.open(parameter, subentity);
    }

    //   nsClientesdocumentosFormShow(subentity: any) {
    //     let parameter = { 'cliente': subentity.cliente, 'identifier': subentity.clientedocumento };
    //     if (parameter.identifier) {
    //         this.busy = true;
    //     }
    //     var modal = this.NsClientesdocumentosFormShowService.open(parameter, subentity);
    // } 

    nsClientesdocumentosFormShow(subentity: any) {
        let parameter = { 'cliente': this.entity.cliente, 'identifier': subentity.clientedocumento };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsClientesdocumentosFormShowService.open(parameter, subentity);
        this.busy = false;
    }

    //   crmTemplatespropostasgruposForm() {
    //     let modal = this.CrmTemplatespropostasgruposFormService.open({}, {});
    //     modal.result.then((subentity: any) => {
    //         subentity.$id = this.idCount++;
    //         if (this.entity.templates === undefined) {
    //             this.entity.templates = [subentity];
    //         } else {
    //             this.entity.templates.push(subentity);
    //         }
    //     })
    //         .catch((error: any) => {
    //             if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
    //                 this.toaster.pop({
    //                     type: 'error',
    //                     title: error
    //                 });
    //             }
    //         });
    // }

    //Grupos
    crmTemplatesPropostasGruposForm(cliente: string, templatepropostagrupo: string) {

        let templatepropostagrupoObj = {};

        //Definindo mensagens dos toasters para exibir mensagem personalizada se for adição ou edição
        let msgSucessoToaster = templatepropostagrupo.length == 0 ? "O Grupo de Template foi adicionado com sucesso!" : "O Grupo de Template foi editado com sucesso!";
        let msgErroToaster = templatepropostagrupo.length == 0 ? "Ocorreu um erro ao adicionar o Grupo de Template!" : "Ocorreu um erro ao editar o Grupo de Template!";

        if (templatepropostagrupo !== null) {
            templatepropostagrupoObj = { cliente, identifier: templatepropostagrupo };
        } else {
            templatepropostagrupoObj = {};
        }

        let modal = this.CrmTemplatespropostasgruposFormService.open(templatepropostagrupoObj, {});

        modal.result
            .then((item: any) => {
                this.crmTemplatespropostasgrupos.constructors = { cliente };
                this.crmTemplatespropostasgrupos.save(item, false);

                this.toaster.pop({
                    type: 'success',
                    title: msgSucessoToaster
                });

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: msgErroToaster
                    });
                }
            });
    }

    onTemplateGrupos() {
        this.$scope.$on('crm_templatespropostasgrupos_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const templatepropostagrupo = isNew ? response.data : entity;

            const node = this.montarNo('grupo', templatepropostagrupo, templatepropostagrupo.templatepropostagrupo);

            if (isNew) {
                this.adicionarNo(node);

            } else {
                editarNode(this.dadosTree, node);

            }
            // this.busy = false;
        })
    }


    crmTemplatesPropostasGruposRemover(cliente: string, templatepropostagrupo: string) {
        this.crmTemplatespropostasgrupos.constructors = { cliente };
        this.crmTemplatespropostasgrupos.delete(templatepropostagrupo, false);
        this._templatepropostagrupo = templatepropostagrupo

    }

    onCrmTemplatesPropostasGruposRemover() {
        this.$scope.$on('crm_templatespropostasgrupos_deleted', () => {
            const chaveExcluida = 'grupo.' + this._templatepropostagrupo;
            this.removerNo(chaveExcluida);

            this.toaster.pop({
                type: 'success',
                title: 'O Grupo de Template foi excluído com sucesso!'
            });

        });

        this.$scope.$on('crm_templatespropostasgrupos_delete_error', () => {
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao excluir o Grupo de Template!'
            });
        });

    }


    //Apólices
    crmTemplatePropostaForm(templatepropostagrupo: string, templateproposta: string, editarDocumentos: boolean = false) {
        let templatepropostaObj = {};
        let msgSucessoToaster;
        let msgErroToaster;

        if (templateproposta !== null && templateproposta !== undefined) {
            templatepropostaObj = { templatepropostagrupo, editarDocumentos, templateproposta };

            //Edição
            msgSucessoToaster = 'O Template de Apólice foi editado com sucesso!';
            msgErroToaster = 'Ocorreu um erro ao editar o Template de Apólice!'

        } else {
            templatepropostaObj = { editarDocumentos };

            //Adição
            msgSucessoToaster = 'O Template de Apólice foi adicionado com sucesso!';
            msgErroToaster = 'Ocorreu um erro ao adicionar o Template de Apólice!';

        }

        const { result } = this.crmTemplatespropostasFormService.open(templatepropostaObj, {});

        result
            .then((item: any) => {
                this.crmTemplatespropostas.constructors = { templatepropostagrupo };
                this.crmTemplatespropostas.save(item, false);

                this.toaster.pop({
                    type: 'success',
                    title: msgSucessoToaster
                });

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: msgErroToaster
                    });
                }
            });
    }

    onCrmTemplatePropostaForm() {
        this.$scope.$on('crm_templatespropostas_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const templateproposta = isNew ? response.data : entity;
            const pai = 'grupo.' + templateproposta.templatepropostagrupo
            const node = this.montarNo('template', templateproposta, templateproposta.templateproposta, pai);

            if (isNew) {
                this.adicionarNo(node);
                this.carregarFilhos(node);

            } else {
                editarNode(this.dadosTree, node);

            }
        })
    }

    crmTemplatePropostaDocumentos(templateproposta: string, templatepropostadocumentos: string = null) {

        let templatepropostadocumentosObj = {};

        if (templatepropostadocumentos !== null) {
            templatepropostadocumentosObj = { templateproposta, identifier: templatepropostadocumentos };
        } else {
            templatepropostadocumentosObj = {};
        }
    }

    crmTemplatePropostaRemover(templatepropostagrupo: string, templateproposta: string) {
        this.crmTemplatespropostas.constructors = { templatepropostagrupo };
        this.crmTemplatespropostas.delete(templateproposta, false);
        this._templateproposta = templateproposta
    }

    onCrmTemplatePropostaRemover() {
        this.$scope.$on('crm_templatespropostas_deleted', () => {
            const chaveExcluida = 'template.' + this._templateproposta;
            this.removerNo(chaveExcluida);

            this.toaster.pop({
                type: 'success',
                title: 'O Template de Apólice foi excluído com sucesso!'
            });

        });

        this.$scope.$on('crm_templatespropostas_delete_error', () => {
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao excluir o Template de Apólice!'
            });
        });

    }


    //Agrupadores
    crmCapituloForm(templateproposta: string, templatepropostacapitulo: string) {

        let capituloObj = {};
        let msgSucessoToaster;
        let msgErroToaster;
        if (templatepropostacapitulo !== null && templatepropostacapitulo !== undefined) {
            capituloObj = { templateproposta, identifier: templatepropostacapitulo };

            //Edição
            msgSucessoToaster = 'O Agrupador foi editado com sucesso!';
            msgErroToaster = 'Ocorreu um erro ao editar o Agrupador!';

        } else {
            capituloObj = {};

            //Adição
            msgSucessoToaster = 'O Agrupador foi adicionado com sucesso!';
            msgErroToaster = 'Ocorreu um erro ao adicionar o Agrupador!';

        }
        let modal = this.crmTemplatespropostascapitulosFormService.open(capituloObj, {});

        modal.result
            .then((item: any) => {
                this.crmTemplatespropostascapitulos.constructors = { templateproposta };
                this.crmTemplatespropostascapitulos.save(item, false);

                this.toaster.pop({
                    type: 'success',
                    title: msgSucessoToaster
                });

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: msgErroToaster
                    });
                }
            });
    }

    onCrmCapitulo() {
        this.$scope.$on('crm_templatespropostascapitulos_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const templatepropostacapitulo = isNew ? response.data : entity;
            const pai = 'template.' + templatepropostacapitulo.templateproposta;
            const node = this.montarNo('capitulo', templatepropostacapitulo, templatepropostacapitulo.templatepropostacapitulo, pai);

            if (isNew) {
                this.adicionarNo(node);
            } else {
                editarNode(this.dadosTree, node);
            }
            // this.busy = false;
        })
    }


    crmCapituloRemover(templateproposta: string, templatepropostacapitulo: string) {
        this.crmTemplatespropostascapitulos.constructors = { templateproposta };
        this.crmTemplatespropostascapitulos.delete(templatepropostacapitulo, false);
        this._templatepropostacapitulo = templatepropostacapitulo

    }

    onCrmCapituloRemover() {
        this.$scope.$on('crm_templatespropostascapitulos_deleted', () => {
            const chaveExcluida = 'capitulo.' + this._templatepropostacapitulo;
            this.removerNo(chaveExcluida);

            this.toaster.pop({
                type: 'success',
                title: 'O Agrupador foi excluído com sucesso!'
            });

        });

        this.$scope.$on('crm_templatespropostascapitulos_delete_error', () => {

            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao excluir o Agrupador!'
            });

        });

    }


    //Serviços
    crmTemplatePropostaComposicaoForm(templatepropostacapitulo: string, composicao: string) {

        let composicaoObj = {};
        let msgSucessoToaster;
        let msgErroToaster;
        if (composicao !== null && composicao !== undefined) {
            composicaoObj = { templatepropostacapitulo, identifier: composicao };

            //Edição
            msgSucessoToaster = 'O Serviço foi editado com sucesso!';
            msgErroToaster = 'Ocorreu um erro ao editar o Serviço!';

        } else {
            composicaoObj = {};

            //Adição
            msgSucessoToaster = 'O Serviço foi adicionado com sucesso!';
            msgErroToaster = 'Ocorreu um erro ao adicionar o Serviço!';

        }

        const modal = this.crmTemplatespropostascapitulosComposicoesFormService.open(composicaoObj, {});

        modal.result
            .then((item: any) => {
                this.crmTemplatespropostascapituloscomposicoes.constructors = { templatepropostacapitulo };
                this.crmTemplatespropostascapituloscomposicoes.save(item, false);

                this.toaster.pop({
                    type: 'success',
                    title: msgSucessoToaster
                });

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: msgErroToaster
                    });
                }
            });
    }

    onCrmTemplatePropostaComposicao() {
        this.$scope.$on('crm_templatespropostascapituloscomposicoes_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const templatepropostacapitulocomposicao = isNew ? response.data : entity;
            const pai = 'capitulo.' + templatepropostacapitulocomposicao.templatepropostacapitulo;
            templatepropostacapitulocomposicao.nome = templatepropostacapitulocomposicao.nome || templatepropostacapitulocomposicao.composicao.nome
            templatepropostacapitulocomposicao.descricao = templatepropostacapitulocomposicao.descricao || templatepropostacapitulocomposicao.composicao.descricao

            const node = this.montarNo(
                'templatecomposicao',
                templatepropostacapitulocomposicao,
                templatepropostacapitulocomposicao.templatepropostacomposicao,
                pai
            );

            if (isNew) {
                this.carregarFuncoes(templatepropostacapitulocomposicao.templatepropostacomposicao, node._id_);
                this.carregarCategoriasProdutos(templatepropostacapitulocomposicao.templatepropostacomposicao, node._id_);
                this.adicionarNo(node);

            } else {
                editarNode(this.dadosTree, node);

            }
            this.busy = false;
        })
    }

    crmTemplatePropostaComposicaoRemover(templatepropostacapitulo: string, templatepropostacomposicao: string) {
        this.crmTemplatespropostascapituloscomposicoes.constructors = { templatepropostacapitulo };
        this.crmTemplatespropostascapituloscomposicoes.delete(templatepropostacomposicao, false);
        this._templatepropostacomposicao = templatepropostacomposicao;
    }


    onCrmTemplatePropostaComposicaoRemover() {
        this.$scope.$on('crm_templatespropostascapituloscomposicoes_deleted', () => {
            const chaveExcluida = 'templatecomposicao.' + this._templatepropostacomposicao
            this.removerNo(chaveExcluida);

            this.toaster.pop({
                type: 'success',
                title: 'O Serviço foi excluído com sucesso!'
            });

        });

        this.$scope.$on('crm_templatespropostascapituloscomposicoes_delete_error', () => {
            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao excluir o Serviço!'
            });
        });

    }

    //Produto
    crmComposicaoFamiliaForm(templatepropostacomposicao: string, composicaofamilia: string) {
        let composicaoFamiliaObj = {};

        if (composicaofamilia !== null && composicaofamilia !== undefined) {
            composicaoFamiliaObj = { templatepropostacomposicao, identifier: composicaofamilia };

        } else {
            composicaoFamiliaObj = {};

        }

        const modal = this.CrmTemplatescomposicoesfamiliasFormService.open(composicaoFamiliaObj, {});

        modal.result
            .then((item: any) => {
                this.crmTemplatescomposicoesfamilias.constructors = { templatepropostacomposicao };
                this.crmTemplatescomposicoesfamilias.save(item, false);

                this.toaster.pop({
                    type: 'success',
                    title: 'O Produto foi editado com sucesso!'
                });

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: 'Ocorreu um erro ao editar o Produto!'
                    });
                }
            });
    }

    onCrmComposicaoFamilia() {
        this.$scope.$on('crm_templatescomposicoesfamilias_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const composicaoFuncao = isNew ? response.data : entity;
            const pai = 'templatecomposicao.' + composicaoFuncao.templatepropostacomposicao;
            const node = this.montarNo('composicao.funcao', composicaoFuncao, composicaoFuncao.templatecomposicaofamilia, pai);

            if (isNew) {
                this.adicionarNo(node);

            } else {
                editarNode(this.dadosTree, node);

            }
            this.atualizarTree();

            this.busy = false;
        })
    }

    crmComposicaoFamiliaRemover(templatepropostacomposicao: string, templatecomposicaofamilia: string) {
        this.crmTemplatescomposicoesfamilias.constructors = { templatepropostacomposicao };
        this.crmTemplatescomposicoesfamilias.delete(templatecomposicaofamilia, false);
        this._templatecomposicaofamilia = templatecomposicaofamilia;
    }

    onCrmComposicaoFamiliaRemover() {
        this.$scope.$on('crm_templatescomposicoesfamilias_deleted', () => {
            const chaveExcluida = 'composicao.familia.' + this._templatecomposicaofamilia
            this.removerNo(chaveExcluida);

            this.toaster.pop({
                type: 'success',
                title: 'O Produto foi excluído com sucesso!'
            });

        });

        this.$scope.$on('crm_templatescomposicoesfamilias_delete_error', () => {

            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao excluir o Produto!'
            });

        });

    }

    crmComposicaoFuncaoForm(templatepropostacomposicao: string, templatecomposicaofuncao: string) {

        let composicaoFuncaoObj = {};
        if (templatecomposicaofuncao !== null) {
            composicaoFuncaoObj = { templatepropostacomposicao, identifier: templatecomposicaofuncao };
        } else {
            composicaoFuncaoObj = {};
        }
        const modal = this.crmTemplatescomposicoesfuncoesFormService.open(composicaoFuncaoObj, {});

        modal.result
            .then((item: any) => {
                this.crmTemplatescomposicoesfuncoes.constructors = { templatepropostacomposicao };
                this.crmTemplatescomposicoesfuncoes.save(item, false);

                this.toaster.pop({
                    type: 'success',
                    title: 'O Profissional foi editado com sucesso!'
                });

            })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.busy = false;
                    this.toaster.pop({
                        type: 'error',
                        title: 'Ocorreu um erro ao editar o Profissional!'
                    });
                }
            });
    }


    onCrmComposicaoFuncao() {
        this.$scope.$on('crm_templatescomposicoesfuncoes_submitted', (event, { response, entity }) => {
            const isNew = !(response.config.method === 'PUT');
            const composicaoFuncao = isNew ? response.data : entity;
            const pai = 'templatecomposicao.' + composicaoFuncao.templatepropostacomposicao;
            const node = this.montarNo('capitulo', composicaoFuncao, composicaoFuncao.templatecomposicaofuncao, pai);

            if (isNew) {
                this.adicionarNo(node);

            } else {
                editarNode(this.dadosTree, node);

            }
            this.atualizarTree();

            this.busy = false;
        })
    }

    crmComposicaoFuncaoRemover(templatepropostacomposicao: string, templatecomposicaofuncao: string) {
        this.crmTemplatescomposicoesfuncoes.constructors = { templatepropostacomposicao };
        this.crmTemplatescomposicoesfuncoes.delete(templatecomposicaofuncao, false);
        this._templatecomposicaofuncao = templatecomposicaofuncao;
    }

    onCrmComposicaoFuncaoRemover() {
        this.$scope.$on('crm_templatescomposicoesfuncoes_deleted', () => {
            const chaveExcluida = 'composicao.funcao.' + this._templatecomposicaofuncao
            this.removerNo(chaveExcluida);

            this.toaster.pop({
                type: 'success',
                title: 'O Profissional foi excluído com sucesso!'
            });

        });

        this.$scope.$on('crm_templatescomposicoesfuncoes_delete_error', () => {

            this.toaster.pop({
                type: 'error',
                title: 'Ocorreu um erro ao excluir o Profissional!'
            });

        });

    }


    /**
     * Cria os nós relacionados ao grupo
     * @param grupos 
     */
    async carregarGrupos(id: string) {
        const { data: propostasGrupos } = await this.obterEntidade('crm_templatespropostasgrupos_index', 'cliente', id);

        for (const proposta of propostasGrupos) {
            const node = this.montarNo('grupo', proposta, proposta.templatepropostagrupo)
            this.tree.adicionarNo(node, this.dados);
        }

        this.atualizarTree();

        this.isBusy = false;
    }


    /**
     * Cria os nós relacionados ao template que pertencem a um determinado grupo
     * @param id 
     */
    async carregarTemplates(id: string, idPai: string) {
        const { data: templatesPropostas, status } = await this.obterEntidade('crm_templatespropostas_index', 'templatepropostagrupo', id);

        if (status === 200) {
            this.listaDeNodesCarregados.set(id, idPai)
        }

        if (templatesPropostas.length == 0) {
            this.recarregarTree();
        }

        for (const templateProposta of templatesPropostas) {
            const node = this.montarNo('template', templateProposta, templateProposta.templateproposta, 'grupo.' + id);
            this.tree.adicionarNo(node, this.dados);
        }

        this.atualizarTree();

        this.isBusy = false;
    }

    /**
     * Cria os nós relacionados aos capítulos que pertencem a um determinado template
     * Obs: diferente dos demais, ele traz mais capítulos e subcapitulos ao mesmo tempo.
     * @param id 
     */
    async carregarCapitulos(id: string, idPai?: string) {
        const { data: templatePropostasCapitulos, status } = await this.obterEntidade('crm_templatespropostascapitulos_index', 'templateproposta', id);

        if (templatePropostasCapitulos.length == 0) {
            this.recarregarTree();
        }

        if (status === 200) {
            this.listaDeNodesCarregados.set(id, idPai)
        }

        for (const templatePropostaCapitulo of templatePropostasCapitulos) {
            let pai = templatePropostaCapitulo.pai == null ? 'template.' + id : 'capitulo.' + templatePropostaCapitulo.pai;
            const node = this.montarNo('capitulo', templatePropostaCapitulo, templatePropostaCapitulo.templatepropostacapitulo, pai);
            this.tree.adicionarNo(node, this.dados);
        }

        this.atualizarTree();

        this.isBusy = false;
    }

    /**
     * Cria os nós relacionados às funções que pertencem a uma determinada composição
     * Obs: Como as composições podem se repetir dentro da listagem porém em capítulos diferente, é necessário informar o id do nó pai e não apenas o uuid do banco de dados
     * @param id 
     * @param noPaiIdentificacao 
     */
    async carregarFuncoes(id: string, noPaiIdentificacao: string) {
        const { data: composicoesFuncoes } = await this.obterEntidade('crm_templatescomposicoesfuncoes_index', 'templatepropostacomposicao', id);

        for (let composicaoFuncao of composicoesFuncoes) {
            composicaoFuncao.qtd = composicaoFuncao.quantidade || composicaoFuncao.funcao.quantidade;
            composicaoFuncao.nome = composicaoFuncao.nome || composicaoFuncao.funcao.nome;
            composicaoFuncao.valor = composicaoFuncao.valor || composicaoFuncao.funcao.valor;
            composicaoFuncao.descricao = composicaoFuncao.descricao || composicaoFuncao.funcao.descricao;;
            const node = this.montarNo('composicao.funcao', composicaoFuncao, composicaoFuncao.templatecomposicaofuncao, noPaiIdentificacao);
            this.tree.adicionarNo(node, this.dados);
        }

        this.atualizarTree();

        this.isBusy = false;
    }


    /**
     * Cria os nós relacionados às famílias de produtos que pertencem a uma determinada composição
     * Obs: Como as composições podem se repetir dentro da listagem porém em capítulos diferente, é necessário informar o id do nó pai e não apenas o uuid do banco de dados   * 
     * @param id 
     * @param noPaiIdentificacao 
     */
    async carregarCategoriasProdutos(id: string, noPaiIdentificacao: string) {
        const { data: composicaoFamilias } = await this.obterEntidade('crm_templatescomposicoesfamilias_index', 'templatepropostacomposicao', id);

        for (let composicaoFamilia of composicaoFamilias) {
            composicaoFamilia.qtd = composicaoFamilia.quantidade || composicaoFamilia.familia.quantidade;
            composicaoFamilia.nome = composicaoFamilia.nome || composicaoFamilia.familia.nome;
            composicaoFamilia.descricao = composicaoFamilia.descricao || composicaoFamilia.familia.descricao;
            const node = this.montarNo('composicao.familia', composicaoFamilia, composicaoFamilia.templatecomposicaofamilia, noPaiIdentificacao);
            this.tree.adicionarNo(node, this.dados);
        }

        this.atualizarTree();
        this.isBusy = false;
    }

    /**
     * Cria os nós relacionados às composições e chama as funções para carregar as funções famílias de produto que estão relacionadas a ela.
     * Obs: o id da composição é mais complexo do que os demais porque podem existir mais de uma composição na árvore, desde que estejam em capítulos distintos.
     * @param id 
     */
    async carregarComposicoes(id: string, idPai: string) {
        const { data: composicoes, status } = await this.obterEntidade('crm_templatespropostascapituloscomposicoes_index', 'templatepropostacapitulo', id);

        if (composicoes.length == 0) {
            this.recarregarTree();
        }

        if (status === 200) {
            this.listaDeNodesCarregados.set(id, idPai)
        }

        for (const composicao of composicoes) {

            composicao.nome = composicao.nome || composicao.composicao.nome
            let novoNo = this.montarNo('templatecomposicao', composicao, composicao.templatepropostacomposicao, 'capitulo.' + composicao.templatepropostacapitulo);
            this.carregarFuncoes(composicao.templatepropostacomposicao, novoNo._id_);
            this.carregarCategoriasProdutos(composicao.templatepropostacomposicao, novoNo._id_);
            this.tree.adicionarNo(novoNo, this.dados);
        }

        this.atualizarTree();

        this.isBusy = false;
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
     * Monta o nó de acordo com o formato esperado pela treelist
     * @param tipo 
     * @param entity 
     * @param identificador 
     * @param pai 
     * @param hashAuxiliar 
     */
    montarNo(tipo: string, entity: any, identificador: string, pai = null, hashAuxiliar = '') {
        const no = {
            _id_: tipo + '.' + identificador + hashAuxiliar,
            _parentId_: pai,
            children: [],
            __hashKey__: tipo + '.' + identificador + hashAuxiliar,
            _info_: null,
            tipo: tipo,
            nome: entity.nome ? entity.nome : entity.descricao,
            qtd: entity.qtd ? entity.qtd : null,
            valor: entity.valor ? entity.valor : null,
            obj: entity,
            actions: this.obterAcoesDoNode(tipo, entity),
            icons: { iconLeaf: this.obterIcone(tipo) }
        }

        return no;
    }

    /**
     * @description 
     * Recebe um metodo em formato string e extrai seu nome e os argumentos.
     * Por fim chama no contexto object `this`
     * @param metodo 
     */
    traduzMetodo(metodo: string) {
        const [metodoChamado, restoDaString] = metodo.split('(');
        const args = restoDaString.split(')')[0].split(',');
        Object.getPrototypeOf(this)[metodoChamado].apply(this, args);

    };

    recarregarTree() {
        this.$scope.$applyAsync();
    }


    atualizarTree() {
        const dados = Array.from(this.dados.values());
        this.dadosTree = this.tree.getArvore(dados);
        this.dadosTree = this.OrdernarNos(this.dadosTree);

        // Controlar exibição da mensagem indicando que não há itens na parte de templates de apólice
        this.haItensListaTemplates = this.dadosTree.length > 0 ? true : false;

        this.recarregarTree();
    }


    /* Ordena nós em ordem alfabética */
    OrdernarNos(tree: any) {
        for (let i = 1; i < tree.length; i++) {
            for (let j = 0; j < tree.length; j++) {
                if (i >= 1) {
                    let termo1 = tree[i].nome.toLowerCase();
                    let termo2 = tree[j].nome.toLowerCase();
                    if (termo1 < termo2) {
                        let temp = tree[i];
                        tree[i] = tree[j];
                        tree[j] = temp;
                    }
                }
            }
        }
        return tree;
    }


    /* Ordena nós em ordem alfabética */
    adicionarNo(node: any) {
        this.tree.adicionarNo(node, this.dados);
        this.atualizarTree();
    }

    removerNo(chaveDeletada: string) {
        this.tree.removerNo(chaveDeletada, this.dados);
        this.atualizarTree();
    }


    /**
     * Coloca os ícones nas folhas quando são filhas da composição
     * @param node 
     */
    obterIcone(tipo: string): string {
        //atributos da composição
        if (tipo === 'composicao.funcao') {
            return 'fas fa-id-badge';
        } else if (tipo === 'composicao.familia') {
            return 'fas fa-tag';
        }
        return 'fas fa-chevron-right';
    }


    /* Carregamento de dados */

    /**
     * Método chamado no on-click para carregar os filhos do nó clicado.
     * @param node 
     */
    carregarFilhos(node: any) {

        this.isBusy = true;

        switch (node.tipo) {
            case 'grupo': {
                this.isBusy = true;
                this.carregarTemplates(node.obj.templatepropostagrupo, node._id_);
                break;
            }
            case 'template': {
                this.isBusy = true;
                this.carregarCapitulos(node.obj.templateproposta, node._id_);
                break;
            }
            case 'capitulo': {
                this.isBusy = true;
                if (!node.obj.possuifilho) {
                    this.carregarComposicoes(node.obj.templatepropostacapitulo, node._id_);
                }
                break;
            }
        }
        this.isBusy = false;
    }


    obterAcoesDoNode(tipo: string, entity: any) {
        switch (tipo) {
            case 'grupo':
                return [
                    { 'label': 'Adicionar Apólice', 'permission': true, 'icon': 'fas fa-plus', 'size': 'xs', 'method': `crmTemplatePropostaForm(${entity.templatepropostagrupo})` },
                    { 'label': 'Editar Grupo', 'permission': true, 'icon': 'fas fa-edit', 'size': 'xs', 'method': `crmTemplatesPropostasGruposForm(${entity.cliente},${entity.templatepropostagrupo})` },
                    { 'label': 'Excluir Grupo', 'permission': true, 'icon': 'fas fa-trash-alt', 'size': 'xs', 'method': `crmTemplatesPropostasGruposRemover(${entity.cliente},${entity.templatepropostagrupo})` }
                ];

            case 'template':
                return [
                    { 'label': 'Adicionar Agrupador', 'permission': true, 'icon': 'fas fa-plus', 'size': 'xs', 'method': `crmCapituloForm(${entity.templateproposta})` },
                    { 'label': 'Documentos da apólice', 'permission': true, 'icon': 'fas fa-id-card', 'size': 'xs', 'method': `crmTemplatePropostaForm(${entity.templatepropostagrupo},${entity.templateproposta},${true})` },
                    { 'label': 'Editar Apólice', 'permission': true, 'icon': 'fas fa-edit', 'size': 'xs', 'method': `crmTemplatePropostaForm(${entity.templatepropostagrupo},${entity.templateproposta})` },
                    { 'label': 'Excluir Apólice', 'permission': true, 'icon': 'fas fa-trash-alt', 'size': 'xs', 'method': `crmTemplatePropostaRemover(${entity.templatepropostagrupo},${entity.templateproposta})` }
                ];

            case 'capitulo':
                return [
                    { 'label': 'Adicionar Serviços', 'permission': true, 'icon': 'fas fa-plus', 'size': 'xs', 'method': `crmTemplatePropostaComposicaoForm(${entity.templatepropostacapitulo})` },
                    { 'label': 'Editar Agrupador', 'permission': true, 'icon': 'fas fa-edit', 'size': 'xs', 'method': `crmCapituloForm(${entity.templateproposta},${entity.templatepropostacapitulo})` },
                    { 'label': 'Excluir Agrupador', 'permission': true, 'icon': 'fas fa-trash-alt', 'size': 'xs', 'method': `crmCapituloRemover(${entity.templateproposta},${entity.templatepropostacapitulo})` }
                ];

            case 'templatecomposicao':
                return [
                    // { 'label': 'Adicionar Composição', 'permission': true, 'icon': 'fas fa-plus', 'size': 'xs', 'method': `crmComposicaoForm('${entity.templatepropostacapitulo}','')` },
                    { 'label': 'Editar Serviço', 'permission': true, 'icon': 'fas fa-edit', 'size': 'xs', 'method': `crmTemplatePropostaComposicaoForm(${entity.templatepropostacapitulo},${entity.templatepropostacomposicao})` },
                    { 'label': 'Excluir Serviço', 'permission': true, 'icon': 'fas fa-trash-alt', 'size': 'xs', 'method': `crmTemplatePropostaComposicaoRemover(${entity.templatepropostacapitulo},${entity.templatepropostacomposicao})` }
                ];

            case 'composicao.familia':
                return [
                    { 'label': 'Editar Produto', 'permission': true, 'icon': 'fas fa-edit', 'size': 'xs', 'method': `crmComposicaoFamiliaForm(${entity.templatepropostacomposicao},${entity.templatecomposicaofamilia})` },
                    { 'label': 'Excluir Produto', 'permission': true, 'icon': 'fas fa-trash-alt', 'size': 'xs', 'method': `crmComposicaoFamiliaRemover(${entity.templatepropostacomposicao},${entity.templatecomposicaofamilia})` }

                ];

            case 'composicao.funcao': return [
                { 'label': 'Editar Profissional', 'permission': true, 'icon': 'fas fa-edit', 'size': 'xs', 'method': `crmComposicaoFuncaoForm(${entity.templatepropostacomposicao},${entity.templatecomposicaofuncao})` },
                { 'label': 'Excluir Profissional', 'permission': true, 'icon': 'fas fa-trash-alt', 'size': 'xs', 'method': `crmComposicaoFuncaoRemover(${entity.templatepropostacomposicao},${entity.templatecomposicaofuncao})` }
            ];

        }
    }

    nsEnderecosForm() {
        let modal = this.NsEnderecosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.icrmComposicaoFamiliaFormdCount++;
            if (this.entity.endereco === undefined) {
                this.entity.endereco = [subentity];
            } else {
                this.entity.endereco.push(subentity);
            }
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsContatosForm() {
        let modal = this.NsContatosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.contatos === undefined) {
                this.entity.contatos = [subentity];
            } else {
                this.entity.contatos.push(subentity);
            }
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsClientesdocumentosForm() {
        let modal = this.NsClientesdocumentosFormService.open({}, {});
        modal.result.then((subentity: any) => {
            subentity.$id = this.idCount++;
            if (this.entity.clientesdocumentos === undefined) {
                this.entity.clientesdocumentos = [subentity];
            } else {
                this.entity.clientesdocumentos.push(subentity);
            }
        })
            .catch((error: any) => {
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsEnderecosFormEdit(subentity: any) {
        let parameter = { 'identifier': subentity.endereco };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsEnderecosFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.endereco) {
                    if ((this.entity.endereco[key].endereco !== undefined && this.entity.endereco[key].endereco === subentity.endereco)
                        || (this.entity.endereco[key].$id !== undefined && this.entity.endereco[key].$id === subentity.$id)) {
                        this.entity.endereco[key] = subentity;
                    }
                }
            })
            .catch((error: any) => {
                this.busy = false;
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsContatosFormEdit(subentity: any) {
        let parameter = { 'pessoa': subentity.pessoa, 'identifier': subentity.contato };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsContatosFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.contatos) {
                    if ((this.entity.contatos[key].contato !== undefined && this.entity.contatos[key].contato === subentity.contato)
                        || (this.entity.contatos[key].$id !== undefined && this.entity.contatos[key].$id === subentity.$id)) {
                        this.entity.contatos[key] = subentity;
                    }
                }
            })
            .catch((error: any) => {
                this.busy = false;
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } crmTemplatespropostasgruposFormEdit(subentity: any) {
        let parameter = { 'cliente': subentity.cliente, 'identifier': subentity.templatepropostagrupo };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.CrmTemplatespropostasgruposFormService.open(parameter, subentity);
        modal.result.then(
            (subentity: any) => {
                let key;
                for (key in this.entity.templates) {
                    if ((this.entity.templates[key].templatepropostagrupo !== undefined && this.entity.templates[key].templatepropostagrupo === subentity.templatepropostagrupo)
                        || (this.entity.templates[key].$id !== undefined && this.entity.templates[key].$id === subentity.$id)) {
                        this.entity.templates[key] = subentity;
                    }
                }
            })
            .catch((error: any) => {
                this.busy = false;
                if (error !== 'backdrop click' && error !== 'fechar' && error !== 'escape key press') {
                    this.toaster.pop({
                        type: 'error',
                        title: error
                    });
                }
            });
    } nsEnderecosFormShow(subentity: any) {
        let parameter = { 'identifier': subentity.endereco };
        if (parameter.identifier) {
            this.busy = true;
        }
        var modal = this.NsEnderecosFormShowService.open(parameter, subentity);
        this.busy = false;
    }



    uniqueValidation(params: any): any {
        if (this.collection) {
            let validation = { 'unique': true };
            for (var item in this.collection) {
                if (this.collection[item][params.field] === params.value) {
                    validation.unique = false;
                    break;
                }
            }
            return validation;
        } else {
            return null;
        }
    }
}
