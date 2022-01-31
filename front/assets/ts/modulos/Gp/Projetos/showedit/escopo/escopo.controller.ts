import angular from "angular";
import moment from "moment";
import { IEscopo } from "../../interfaces/escopo.interface";
import { IProjeto } from "../../interfaces/projetos.interface";
import { EscopoService } from "./escopo.service";
import { uuidGenerator } from "../../../../../shared/helpers/uuid-generator";
import { tipoItemEnum } from "../../enums/tipoItem.enum";
import { IEscopoNode } from "../../interfaces/escoponode.interface";
import { OrdensServicosTemplatesService } from './odensservicostemplates.service';
import { IOrdensServicosTemplates } from "../../interfaces/ordensservicostemplates.interface";
import { SituacaoOsEnum } from "../../enums/situacaoOsEnum";
import { SituacaoEnum } from "../../enums/situacaoEnum";
import { ModalExclusaoItemEscopoService } from './modalexclusao/modal-exclusao.service';
import { IOrdemServico } from "assets/ts/modulos/Servicos/Ordens-servicos/ordem-servico.interfaces";
import { EtapasEnum } from "../../../Etapas/enum/etapas.enum";
import { VeiculoOcupadoModalService } from "./veiculoocupadomodal/veiculo-ocupado.modal.service";

type veiculoOcupadoType = {
  descricao: string;
  datahorainicio: string;
  datahorafim: string;
  projeto: IProjeto
};

type ColDefType = {
  field: string;
  titleClass: string;
  cellClass: string;
  displayName: string;
  cellTemplate?: string;
};

type nodeToEditType = {
  node: IEscopoNode,
  nodeField: string | string [];
};

/**
 * Objeto usado para ajudar na manipulação da tree
 */
type treeModifierType = {
  /**
   * Informa se deve buscar o node baseado no NodeID (útil quando o Node acabou de ser criado no backend)
   */
  searchByNodeID?: boolean,
  /**
   * projetoitemescopo buscado
   */
  searchedProjetoItemEscopo?: string;
  /**
   * Node buscado
   */
  searchedNode?: Partial<IEscopoNode>,
  /**
   * Nome do campo buscado
   */
  searchedField?: string | string[],
  /**
   * Quando true, o Node passado é removido do tree_data quando encontrado
   */
  removeNode?: boolean;
  /**
   * Função de callback a ser executada quando o Node ou o campo (nodeField) buscado for encontrado.
   * Importante fazer o .bind(this) ao passá-la, caso deseje acessar o escopo do controller dentro dela.
   * @param searchedNode Node buscado <IEscopoNode>
   * @param searchedField Nome do campo buscado <string>
   */
  callbackFunction?(
    searchedNode?: IEscopoNode,
    searchedField?: string | string[],
    parentNode?: IEscopoNode,
    nextNode?: IEscopoNode,
    previousNode?: IEscopoNode
  ): boolean,
};

export class EscopoController implements ng.IController {

  static $inject = [
    'escopoService',
    '$scope',
    '$TreeDnDConvert',
    '$TreeDnDControl',
    '$element',
    'toaster',
    '$interval',
    '$timeout',
    'ordensServicosTemplatesService',
    'modalExclusaoItemEscopoService',
    'veiculoOcupadoModalService'
  ];

  /**
   * Entidades do card de Escopo
   */
  public escopoEntities: IEscopo[];

  /**
   * Entidade do Projeto
   */
  public entity: IProjeto;

  /**
   * Situação do projeto
   */
  public SITUACAO_CONST = {
    0: 'Aberto',
    1: 'Cancelado',
    2: 'Em andamento',
    3: 'Finalizado',
    4: 'Parado',
    6: 'Aguardando a inicialização',
  };

  /**
   * Situação de uma OS
   */
  public SITUACAO_OS_CONST = {
    0 : 'Preliminar',
    1 : 'Agendada',
    2 : 'Encerrada',
    3 : 'FaturadaSemCobranca',
    4 : 'Faturada',
    5 : 'Cancelada',
    6 : 'FaturadoParcialmente',
    7 : 'Acaminho',
    8 : 'Aguardando',
    9 : 'EmExecução',
    10 : 'EmPausa',
    11 : 'RealizadaComSucesso',
    12 : 'RealizadComPendência',
    13 : 'NaoRealizado',
    14 : 'Impedido',
    15 : 'EmTrânsito',
  }  

  /**
   * Array com os nodes da tree
   */
  public tree_data: IEscopoNode[];

  /**
   * Define as colunas da tree
   */
  public col_defs: ColDefType[];

  /** 
   * Define a coluna expansível da tree
   */
  public expanding_property: ColDefType;

  /**
   * Permite acessar métodos de controle do Scope da tree (ver: https://thienhung1989.github.io/angular-tree-dnd/docs/ng-tree-dnd.debug.js.html)
   */
  public treeControl;

  /**
   * Scope da tree
   */
  public treeScope;

  /**
   * Informa se há algum input sendo salvo no momento
   */
  public savingOtherInput: boolean = false;
  /**
   * 
   */
  public editedNodeCopy: IEscopoNode;

  /**
   * Node a ser editado em seguida
   */
  public nodeToEdit: nodeToEditType;

  /**
   * Nomes das colunas da tree
   */
  public columnsList: string[] = [];

  public ordensServicosTemplates: IOrdensServicosTemplates[];

  constructor(
    public escopoService: EscopoService,
    public $scope: angular.IScope,
    public $TreeDnDConvert,
    public $TreeDnDControl,
    public $element,
    public toaster,
    public $interval: ng.IIntervalService,
    public $timeout: ng.ITimeoutService,
    public ordensServicosTemplatesService: OrdensServicosTemplatesService,
    public modalExclusaoItemEscopoService: ModalExclusaoItemEscopoService,
    public veiculoOcupadoModalService: VeiculoOcupadoModalService
  ) { }

  $onInit() {
    this.setTreeControl();
    this.setTreeExpandingProperty();
    this.setColumnsList();
    this.onEscopoEntitiesLoaded();
    this.setTreeColumns();
    this.loadOrdensServicosTemplates();
    this.onOrdensServicosTemplatesLoad();
    this.onNodeSave();
    this.onNodeSaveError();
  }

  /**
   * Observo quando as entities do escopo são carregadas
   */
  onEscopoEntitiesLoaded(): void {
    this.$scope.$watch('$ctrl.escopoEntities', () => {
      // ordenação baseada na datahorainicio (mais novos são exibidos primeiro)
      this.escopoEntities = this.escopoEntities.sort((a, b) => {
        if (b.numero < a.numero) {
          return 1;
        } else if (b.numero > a.numero) {
          return -1;
        }

        return 0;
      });

      // transformo as entities do escopo em nodes da tree

      this.tree_data = this.convertToTreeNodes(this.escopoEntities);

    });
  }

  loadOrdensServicosTemplates(): void {
    this.ordensServicosTemplatesService.loadParams.to_load = 1;
    this.ordensServicosTemplatesService.loadParams.finished = false;
    this.ordensServicosTemplatesService.entities = [];
    this.ordensServicosTemplatesService.load();
  }

  onOrdensServicosTemplatesLoad() {
    this.$scope.$on('ordensservicos_templates_list_finished', (event, templates: IOrdensServicosTemplates[]) => {
      this.ordensServicosTemplates = templates;
    });
  }

  setColumnsList() {
    this.$scope.$watch('$ctrl.col_defs', (newValue: ColDefType[]) => {
      newValue.forEach((column) => {
        if (!this.columnsList.includes(column.field)) {
          this.columnsList.push(column.field);
        }
      });
    }, true);

    this.$scope.$watch('$ctrl.expanding_property', (newValue: ColDefType) => {
      if (!this.columnsList.includes(newValue.field)) {
        this.columnsList.push(newValue.field);
      }
    }, true);
  }

  /**
   * Converto os objetos recebidas em nodes para a tree
   * @param objectsToConvert {Array<IEscopo>}
   * @returns treeLine {Array<IEscopoNode>}
   */
  convertToTreeNodes(objectsToConvert: IEscopo[]): IEscopoNode[] {
    return this.$TreeDnDConvert.line2tree(objectsToConvert, 'projetoitemescopo', 'projetoitemescopopai');
  }

  /**
   * Disparado ao perder o foco do input do node
   * @param node 
   * @param nodeField 
   */
  handleNodeChange(node: IEscopoNode, nodeField?: string | string[]): void {
    let inputElement: HTMLInputElement;

    if (node.creatingNode) {
      if (typeof nodeField !== 'string') {
        this.checkValidDates(node, nodeField, inputElement);
      }

      this.formatDate(node);

      if (node.tipoitem === tipoItemEnum.ETAPA) {
        this.saveNewEtapa(node);
      } else if (node.tipoitem === tipoItemEnum.ORDEM_DE_SERVICO) {
        this.saveNewOrdemDeServico(node);
      }
    } else if (node.editingMode) {
      const isFocused = (inputElement == document.activeElement);

      if (!isFocused) {
        if (typeof nodeField !== 'string') {
          this.checkValidDates(node, nodeField, inputElement);
        }

        this.updateEditedNode(node, nodeField);
      }
    }
  }

  /**
 * Realiza uma busca na tree e faz mudanças baseado nas propriedades do treeModifier
 * 
 * @param treeModifier especifica o que está sendo buscado e o que deve ser feito quando encontrar
 * através das propriedades de searchedNode, searchedField, searchById e callbackFunction.
 */
  modifyTree(treeModifier: treeModifierType): void {
    this.treeScope.treeData.map((currentNode: IEscopoNode, index: number, nodeArray: IEscopoNode[]) => {
      if (treeModifier.removeNode && (currentNode.projetoitemescopo === treeModifier.searchedNode.projetoitemescopo)) {
        treeModifier.callbackFunction(currentNode);
      } else {
        let parentNode = currentNode;

        let nextNode = index + 1 < nodeArray.length ? nodeArray[index + 1] : undefined;

        let previousNode = index - 1 >= 0 ? nodeArray[index - 1] : undefined;

        this.treeTraverser(
          currentNode, 
          treeModifier, 
          parentNode,
          nextNode,
          previousNode
        );
      }
    });
  }

  /**
   * Método recursivo que atravessa e desce nos filhos de cada um dos Nodes da Tree
   * 
   * @param currentNode Node atual da iteração
   * @param treeModifier especifica o que está sendo buscado e o que deve ser feito quando encontrar
   * através das propriedades de searchedNode, searchedField, searchById e callbackFunction.
   * 
   * @todo refatorar esse método para não precisar de tantos if's.
   */
  treeTraverser(
      currentNode: IEscopoNode, 
      treeModifier: treeModifierType, 
      parentNode: IEscopoNode,
      nextNode: IEscopoNode,
      previousNode: IEscopoNode,
    ) {
    // verifico se é para remover o node buscado
    if (treeModifier.removeNode && currentNode.projetoitemescopo === treeModifier.searchedNode.projetoitemescopo) {

      treeModifier.callbackFunction(currentNode)

    } else {

      // verifico se a busca está sendo feita pelo nodeID
      if (treeModifier.searchByNodeID && currentNode.nodeID !== undefined && treeModifier.searchedNode.nodeID !== undefined && currentNode.nodeID === treeModifier.searchedNode.nodeID) {

        treeModifier.callbackFunction(currentNode, treeModifier.searchedField, parentNode, nextNode, previousNode);

      } else {

        // se não estiver sendo feita pelo nodeID, verifico se o que foi passado foi o valor do projetoItemEscopo ao invés de um node
        if (treeModifier.searchedProjetoItemEscopo && currentNode.projetoitemescopo === treeModifier.searchedProjetoItemEscopo) {

          treeModifier.callbackFunction(currentNode, '', parentNode), nextNode, previousNode;

          // se o que foi passado na busca foi um node, busco o node que tem o projetoitemescopo igual ao do node passado
        } if (!treeModifier.searchedProjetoItemEscopo && currentNode.projetoitemescopo === treeModifier.searchedNode.projetoitemescopo) {

          treeModifier.callbackFunction(currentNode, treeModifier.searchedField, parentNode, nextNode, previousNode);

        }
      }

      if (currentNode.__children__?.length > 0) {

        let parentNode = currentNode;
        currentNode.__children__.forEach((childNode, index, nodeArray) => {
          let nextNode = index + 1 < nodeArray.length ? nodeArray[index + 1] : undefined;
          let previousNode = index - 1 >= 0 ? nodeArray[index - 1] : undefined;

          if (!previousNode) {
            previousNode = parentNode;
          }

          return this.treeTraverser(
            childNode,
            treeModifier,
            parentNode,
            nextNode,
            previousNode
          );
        });
      }
    }
  }

  /**
 * Adiciona uma etapa a tree
 */
  addNode(tipoItem: 'ETAPA' | 'ORDEM_DE_SERVICO', nodePai?: IEscopoNode, ordemservico?: IOrdemServico): void {
    // monta o objeto da nova etapa
    let novoNode: Partial<IEscopoNode> = {
      projetoitemescopo: uuidGenerator.generate(), // guid temporário para poder adicionar na tree
      projetoitemescopopai: nodePai ? nodePai.projetoitemescopo : null,
      addingMode: true,
      creatingNode: true,
      etapa: null,
      projeto: { projeto: this.entity.projeto },
      tipoitem: tipoItem === 'ETAPA' ? tipoItemEnum.ETAPA : tipoItemEnum.ORDEM_DE_SERVICO,
      ordemservico: tipoItem === 'ORDEM_DE_SERVICO' ? ordemservico : null,
      ordemservicotemplate: tipoItem === 'ORDEM_DE_SERVICO' ? ordemservico.ordemservicotemplate : null,
      nodeID: uuidGenerator.generate() // id do node para usar no frontend
    };

    if (novoNode.ordemservico) {
      novoNode.ordemservico.situacao = SituacaoOsEnum.PRELIMINAR;
      novoNode.descricao = novoNode.ordemservico.nome;
    }


    if (!novoNode.projetoitemescopopai) {
      // converte o objeto da nova etapa em um node da tree
      let treeNode = this.$TreeDnDConvert.line2tree([novoNode], 'projetoitemescopo', 'projetoitemescopopai')[0];

      // adiciona nova etapa na primeira posição do array tree_data
      this.treeControl.add_node(undefined, treeNode, 0);
    } else {
      this.treeControl.add_node(nodePai, novoNode);
      this.modifyTree({
        searchedNode: novoNode,
        callbackFunction: ((node: IEscopoNode) => {
          this.formatDate(node);
        }).bind(this)
      });
    }

    // recarrega o escopo da tree para ver as mudanças em tela
    this.treeControl.reload_data();
  }

  /**
   * Salva etapa criada
   * @param node 
   */
  saveNewEtapa(node: IEscopoNode) {
    if (node.etapa && node.datahorainicio && node.datahorafim) {
      // this.validateDates(node);
      this.modifyTree({
        searchedNode: node,
        callbackFunction: ((node: IEscopoNode) => {
          node.__saving = true;

          let nodeToSave = angular.copy(node);

          // Deleta o guid temporário gerado pelo front-end
          delete nodeToSave.projetoitemescopo;

          // Converte as datas para um horário aceito pelo backend
          nodeToSave.datahorainicio = moment(nodeToSave.datahorainicio).format('YYYY-MM-DD HH:mm:ss');
          nodeToSave.datahorafim = moment(nodeToSave.datahorafim).format('YYYY-MM-DD HH:mm:ss');

          this.escopoService.save(nodeToSave);
        }).bind(this)
      });
    }
  }

  saveNewOrdemDeServico(node: IEscopoNode) {
    if (node.descricao !== '' && node.enderecoorigem && node.datahorainicio && node.datahorafim) {
      // this.validateDates(node);
      this.modifyTree({
        searchedNode: node,
        callbackFunction: ((node: IEscopoNode) => {
          node.__saving = true;

          let nodeToSave = angular.copy(node);

          // Deleta o guid temporário gerado pelo front-end
          delete nodeToSave.projetoitemescopo;
          delete nodeToSave.situacao;
          delete nodeToSave.etapa;

          // Converte as datas para um horário aceito pelo backend
          nodeToSave.datahorainicio = moment(nodeToSave.datahorainicio).format('YYYY-MM-DD HH:mm:ss');
          nodeToSave.datahorafim = moment(nodeToSave.datahorafim).format('YYYY-MM-DD HH:mm:ss');

          this.escopoService.save(nodeToSave);
        }).bind(this)
      });
    }
  }

  /**
 * Evento de sucesso ao salvar alterações no node
 */
  onNodeSave() {
    this.$scope.$on('projetositensescopos_submitted', (event, args) => {
      if (args.entity.creatingNode) {
        this.modifyTree({
          searchedNode: args.entity,
          /**
           * usado porque, nesse momento, o projetoitemescopo existente
           * no node ainda é fictício e devo atualizá-lo com o do BD
           */
          searchByNodeID: true,
          callbackFunction: ((node: IEscopoNode) => {
            // Coloca o node salvo em modo de visualização
            node.addingMode = false;
            node.__saving = false;
            node.creatingNode = false;

            // Atualiza o guid do node com o guid do banco de dados
            node.projetoitemescopo = args.response.data.projetoitemescopo;

            if (node.tipoitem === tipoItemEnum.ORDEM_DE_SERVICO) {
              node.ordemservico.situacao = args.response.data.ordemservico.situacao;
              node.ordemservico.ordemservico = args.response.data.ordemservico.ordemservico;
            }

            this.toaster.pop({
              type: 'success',
              title: `A ${node.tipoitem === tipoItemEnum.ETAPA ? 'etapa' : 'ordem de serviço'} foi criada com sucesso!`
            });
          }).bind(this)
        });
      } else if (args.entity.editingMode) {
        this.toaster.pop({
          type: 'success',
          title: `A ${args.entity.tipoitem === tipoItemEnum.ETAPA ? 'etapa' : 'ordem de serviço'} foi editada com sucesso!`
        });

        this.modifyTree({
          searchedNode: args.entity,
          searchedField: args.entity.editedField ? args.entity.editedField : args.entity.editedFields,
          callbackFunction: this.setFieldToViewMode.bind(this)
        });
      }
    });
  }

  /**
   * Evento de falha ao não conseguir salvar alterações no node
   */
  onNodeSaveError(): void {
    this.$scope.$on('projetositensescopos_submit_error', (event, args) => {
      if (args.entity.creatingNode) {
        this.modifyTree({
          searchedNode: args.entity,
          searchByNodeID: true,
          callbackFunction: ((node: IEscopoNode) => {
            node.__saving = false;
            this.savingOtherInput = false;
            this.toaster.pop({
              type: 'error',
              title: `Ocorreu um erro ao criar a ${node.tipoitem === tipoItemEnum.ETAPA ? 'etapa' : 'ordem de serviço'}.`,
              body: args?.response?.data?.message ? args.response.data.message : ''
            });
          }).bind(this)
        });
      } else {
        this.modifyTree({
          searchedNode: args.entity,
          searchedField: args.entity.editedField,
          callbackFunction: ((node: IEscopoNode) => {
            node.__saving = false;
            this.savingOtherInput = false;
            this.toaster.pop({
              type: 'error',
              title: `Ocorreu um erro ao editar a ${node.tipoitem === tipoItemEnum.ETAPA ? 'etapa' : 'ordem de serviço'}.`,
              body: args?.response?.data?.message ? args.response.data.message : ''
            });
          }).bind(this)
        });
      }
    });
  }

  /**
   * Coloca o campo da tree no modo de edição
   * @param node 
   * @param nodeField 
   */
  setFieldToEditMode(node: IEscopoNode, nodeField: string | string[]): void {
    if (!(this.entity.situacao === SituacaoEnum.CANCELADO || this.entity.situacao === SituacaoEnum.FINALIZADO)) {
      let canEdit: boolean = true;

      this.modifyTree({
        searchedNode: node,
        searchedField: nodeField,
        callbackFunction: ((searchedNode: IEscopoNode, searchedField: string[], parentNode: IEscopoNode) => {
          if (searchedField.includes('datahorainicio') || searchedField.includes('datahorafim')) {
            if (searchedNode.projetoitemescopopai && (!parentNode.datahorainicio || !parentNode.datahorafim)) {
              this.toaster.pop({
                type: 'error',
                title: 'É necessário preencher as datas de início e fim da etapa pai para editar esse campo.'
              });
              canEdit = false;
            }
          }
        }).bind(this)
      });

      if (canEdit) {
        /**
         * Se nenhum input estiver sendo salvo, coloca o input selecionado em
         * modo de edição
         */
        if (!this.savingOtherInput) {
          // timeout usado para previnir bug do ng-blur :)
          this.$timeout(() => {
            let treeModifier: treeModifierType = {
              searchedNode: node,
              searchedField: nodeField,
              callbackFunction: ((node: IEscopoNode, nodeFields: string | string[]) => {
                if (!node.__editingFields) {
                  node.__editingFields = [];
                }

                node.editingMode = true;
                this.editedNodeCopy = angular.copy(node);

                if (typeof nodeFields === 'string') {
                  node.__editingFields.push(<string>nodeFields);

                  if (nodeFields === 'datahorainicio' || nodeFields === 'datahorafim') {
                    this.formatDate(node);
                    node[nodeFields] = node[nodeFields] ? new Date(node[nodeFields]) : '';
                  }
                } else {
                  nodeFields.forEach((nodeField) => {
                    node.__editingFields.push(nodeField);

                    if (nodeField === 'datahorainicio' || nodeField === 'datahorafim') {
                      this.formatDate(node);
                      node[nodeField] = node[nodeField] ? new Date(node[nodeField]) : '';
                    }
                  });
                }
              }).bind(this)
            };
            this.modifyTree(treeModifier);
          }, 500);

          /**
          * Se algum input estiver sendo salvo, armazena o input selecionado para
          * editar após o save do input em questão
           */
        } else {
          this.nodeToEdit = {
            node: node,
            nodeField: nodeField
          };
        }
      }
    }
  }

  /**
   * Salva etapa editada
   * @param node 
   * @param nodeField 
   */
  updateEditedNode(node: IEscopoNode, nodeField: string | string[]) {
    // Faco uma cópia do node e deleto a propriedade __selected__ de ambos, caso exista, para garantir que isso não interfira na comparação
    let nodeToCompare = angular.copy(node)

    // Se o Node não foi alterado, não dispara requisição para o backend
    if (this.equalNodes(nodeToCompare, this.editedNodeCopy)) {
      this.modifyTree({
        searchedNode: node,
        searchedField: nodeField,
        callbackFunction: this.setFieldToViewMode.bind(this)
      });
      return;
    }
    if (typeof nodeField === 'string' || node.__editingFields.every((field) => node[field])) {
      this.modifyTree({
        searchedNode: node,
        searchedField: nodeField,
        callbackFunction: ((node: IEscopoNode, nodeField: string | string[]) => {
          node.__saving = true;
          this.savingOtherInput = true;

          let nodeToSave = angular.copy(node);

          if (typeof nodeField === 'string') {
            nodeToSave.editedField = <string>nodeField;
          } else {
            nodeToSave.editedFields = nodeField;
          }

          nodeToSave.datahorainicio = moment(nodeToSave.datahorainicio).format('YYYY-MM-DD HH:mm:ss');
          nodeToSave.datahorafim = moment(nodeToSave.datahorafim).format('YYYY-MM-DD HH:mm:ss');
          this.editedNodeCopy = null;

          this.escopoService.save(nodeToSave);
        }).bind(this)
      });
    }
  }

  /**
   * 
   * @param node 
   */
  setFieldToViewMode(node: IEscopoNode, searchedField: string | string[]) {
    // Coloca o node salvo em modo de visualização
    node.editingMode = false;
    node.__saving = false;
    this.savingOtherInput = false;

    // Remove o node da lista de campos sendo editados
    node.__editingFields = node.__editingFields.filter((nodeField) => { 
      if (typeof searchedField === 'string') {
        return nodeField !== searchedField;
      } else {
        return !searchedField.includes(nodeField);
      }
    });

    // Se houver algum node a ser editado, ele é colocado no modo de edição
    if (this.nodeToEdit) {
      this.setFieldToEditMode(this.nodeToEdit.node, this.nodeToEdit.nodeField);
    }

    // Esvazia a variável de nodes a editar
    this.nodeToEdit = null;
  }

  /**
 * Método para tratar mudanças no lookup de Veículo.
 * Criado separadamente por ter um comportamento bem diferente dos outros campos.
 * @param node 
 * @param nodeField 
 */
  handleVeiculoChange(node: IEscopoNode, nodeField: string) {
    this.modifyTree({
      searchedNode: node,
      callbackFunction: ((searchedNode: IEscopoNode) => {
        searchedNode.__saving = true;

        this.isVeiculoOcupado(node).then(({ veiculoOcupado, data }) => {
          if (veiculoOcupado) {
            let modal = this.veiculoOcupadoModalService.open(data);
            modal.result.then((procederComAtribuicao: boolean) => {
              if (procederComAtribuicao) {
                node.__saving = false;
                this.handleNodeChange(node, nodeField);
              } else {
                // volta o node para o valor antigo
                searchedNode.__saving = false;
                searchedNode.veiculo = this.editedNodeCopy.veiculo;
                this.handleNodeChange(searchedNode, nodeField);
              }
    
            }).catch(() => {
              // volta o node para o valor antigo
              searchedNode.__saving = false;
              searchedNode.veiculo = this.editedNodeCopy.veiculo;
              this.handleNodeChange(searchedNode, nodeField);
            });
          } else {
            this.handleNodeChange(node, nodeField);
          }
        });
      }).bind(this)
    });
  }

  /**
   * Verifica se o veículo selecionado está em uso em alguma OS
   */
  isVeiculoOcupado(node: IEscopoNode): Promise<{ veiculoOcupado: boolean, data?: veiculoOcupadoType[] }> {
    return new Promise((resolve, reject) => {
      try {
        this.escopoService.getVeiculosOcupados(node)
          .then((data: veiculoOcupadoType[]) => {
            if (data.length > 0) {
              resolve({ veiculoOcupado: true, data: data });
            } else {
              resolve({ veiculoOcupado: false });
            }
          });
      } catch (err) {
        this.toaster.pop({
          type: 'error',
          title: 'Ocorreu um erro ao selecionar o veículo escolhido.'
        });
        resolve({ veiculoOcupado: false });
      }
    })
  }

  /**
   * Usado para contornar o bug do ng-blur não ser disparado ao clicar duas vezes em outro input
   * @param node 
   * @param nodeField 
   */
  saveOtherFields() {
    if (!(this.entity.situacao === SituacaoEnum.CANCELADO || this.entity.situacao === SituacaoEnum.FINALIZADO)) {
      this.columnsList.forEach((column) => {
        let inputElement = document.querySelector(`[id^="${column}[`);

        // se há outro input ativo, faz requisição para salvá-lo
        if (inputElement) {
          let elementProjetoItemEscopo = inputElement.id.match(/\[(.*?)\]/)[1];

          this.modifyTree({
            searchedProjetoItemEscopo: elementProjetoItemEscopo,
            callbackFunction: ((node: IEscopoNode) => {
              node.editingMode = true;
              this.handleNodeChange(node, column);
              this.formatDate(node);
            }).bind(this)
          });
        }
      });
    }
  }

  /**
   * Deleta o node passado
   * @param node 
   */
  deleteNode(node: IEscopoNode): Promise<void> {
    if (node.creatingNode) {
      this.modifyTree({
        searchedNode: node,
        removeNode: true,
        callbackFunction: (() => {
          this.treeControl.remove_node(node);
          this.treeControl.reload_data();
        }).bind(this)
      });
      return;
    }


    let modal = this.modalExclusaoItemEscopoService.open(node);

    modal.result.then(async (confirmDelete: boolean) => {
      if (confirmDelete) {
        await this.escopoService.delete(node.projetoitemescopo, true).then(() => {
          this.modifyTree({
            searchedNode: node,
            removeNode: true,
            callbackFunction: this.onNodeDelete.bind(this)
          });
        }).catch((err) => {
          this.toaster.pop({
            type: 'error',
            title: `Ocorreu um erro ao excluir a ${node.tipoitem === tipoItemEnum.ETAPA ? 'etapa' : 'ordem de serviço'}.`,
            body: err?.data?.message ? err.data.message : ''
          });
        });
      }
    });
  }

  onNodeDelete(node: IEscopoNode) {
    if (node.tipoitem === tipoItemEnum.ETAPA) {
      this.toaster.pop({
        type: 'success',
        title: 'A etapa foi excluída com sucesso!'
      });
    } else if (node.tipoitem === tipoItemEnum.ORDEM_DE_SERVICO) {
      this.toaster.pop({
        type: 'success',
        title: 'A ordem de serviço foi excluída com sucesso!'
      });
    }

    this.treeControl.remove_node(node);
    this.treeControl.reload_data();
  }

  validateDates(node: IEscopoNode, nodeField?: string): boolean {
    if (!node.datahorainicio || !node.datahorafim) {
      let editedField = '';

      let canEdit: boolean = true;
      this.modifyTree({
        searchedNode: node,
        searchedField: nodeField,
        callbackFunction: ((searchedNode: IEscopoNode, searchedField: string, parentNode: IEscopoNode) => {
          if (searchedField === 'datahorainicio' || searchedField === 'datahorafim') {
            if (searchedNode.projetoitemescopopai) {
              if (!node.datahorainicio) {
                editedField = 'datahorainicio';
                this.toaster.pop({
                  type: 'error',
                  title: 'O horário de início deve ser após o início da etapa pai.'
                });
                canEdit = false;
              } else {
                editedField = 'datahorafim';

                this.toaster.pop({
                  type: 'error',
                  title: 'O horário de fim deve ser antes do fim da etapa pai.'
                });
                canEdit = false;
              }
            }
          }
        }).bind(this)
      });

      if (canEdit) {
        return true;
      } else {
        this.modifyTree({
          searchedNode: node,
          callbackFunction: ((node: IEscopoNode) => {
            node.datahorainicio = this.editedNodeCopy.datahorainicio;
            node.datahorafim = this.editedNodeCopy.datahorafim;
            this.setFieldToViewMode(node, editedField);
          }).bind(this)
        });
        return false;
      }
    }

    return true;
  }

  checkValidDates(node: IEscopoNode, nodeField: string[], inputElement: HTMLInputElement): void {
    this.modifyTree({
      searchedNode: node,
      searchedField: nodeField,
      callbackFunction: ((node: IEscopoNode, searchedFields: string[], parentNode: IEscopoNode, nextNode: IEscopoNode, previousNode: IEscopoNode) => {
        // necessário ver o tipo de etapa pai para decidir qual mensagem exibir no tooltipo de data inválida
        if (previousNode?.tipoitem === tipoItemEnum.ETAPA) {
          node['tipoetapa_pai'] = EtapasEnum.PARALELA; // Quanto o node anterior for uma etapa, usar a mesma mensagem de erro que usamos numa etapa paralela (datas mínimas e máximas devem estar contidas no range do node pai)
        } else if (previousNode?.tipoitem === tipoItemEnum.ORDEM_DE_SERVICO) {
          node['tipoetapa_pai'] = parentNode.etapa.tipo; // Se o node anterior é uma OS, usar a mensagem de erro da etapa pai (paralela ou sequencial)
        }
      }).bind(this)
    });


    nodeField.some((field) => {
      inputElement = <HTMLInputElement>document.getElementById(`${field}[${node.projetoitemescopo}]`);
      if (inputElement?.className.includes('ng-invalid-min')) {
        node[`${field}_invalid`] = true;
        return true;
      } else if (inputElement?.className.includes('ng-invalid-max')) {
        node[`${field}_invalid`] = true;
        return true;
      } else {
        node[`${field}_invalid`] = false;
        return false;
      }
    });
  }

  /**
   * Método usado para colocar as datas num formato aceito pelas diretivas "max" e "min" da tag input
   * @param node 
   */
  formatDate(node: IEscopoNode): void {
    this.modifyTree({
      searchedNode: node,
      callbackFunction: (
        (node: IEscopoNode, searchedField: string, parentNode: IEscopoNode, nextNode: IEscopoNode, previousNode: IEscopoNode) => {
          let datahorainicioFormatada = node.datahorainicio ? moment(node.datahorainicio).format('YYYY-MM-DD HH:mm') : null;
          node.datahorainicioFormatada = datahorainicioFormatada ? datahorainicioFormatada.slice(0, 10) + 'T' + datahorainicioFormatada.slice(11) : null;

          let datahorafimFormatada = node.datahorafim ? moment(node.datahorafim).format('YYYY-MM-DD HH:mm') : null;
          node.datahorafimFormatada = datahorafimFormatada ? datahorafimFormatada.slice(0, 10) + 'T' + datahorafimFormatada.slice(11) : null;


          if (node.projetoitemescopopai && node.projetoitemescopopai === parentNode.projetoitemescopo) {
            parentNode.datahorainicioFormatada = parentNode.datahorainicio ? moment(parentNode.datahorainicio).format('YYYY-MM-DD HH:mm') : null;
            parentNode.datahorainicioFormatada = parentNode.datahorainicioFormatada?.slice(0, 10) + 'T' + parentNode.datahorainicioFormatada?.slice(11);

            parentNode.datahorafimFormatada = parentNode.datahorafim ? moment(parentNode.datahorafim).format('YYYY-MM-DD HH:mm') : null;
            parentNode.datahorafimFormatada = parentNode.datahorafimFormatada?.slice(0, 10) + 'T' + parentNode.datahorafimFormatada?.slice(11);
          }

          this.setMinAndMaxDates(node, parentNode, nextNode, previousNode);
        }
      ).bind(this)
    });
  }

  setMinAndMaxDates(node: IEscopoNode, parentNode: IEscopoNode, nextNode: IEscopoNode, previousNode: IEscopoNode) {
    if (
      parentNode.etapa.tipo === EtapasEnum.PARALELA ||
      node.tipoitem === tipoItemEnum.ETAPA ||
      (previousNode.projetoitemescopo === parentNode.projetoitemescopo && !nextNode)
    ) {

      node.datahorainicioMin = parentNode.datahorainicioFormatada;

      if (!node.datahorafimFormatada) {

        node.datahorainicioMax = parentNode.datahorafimFormatada;

      } else {

        node.datahorainicioMax = moment(node.datahorafimFormatada).isBefore(parentNode.datahorafimFormatada) ? node.datahorafimFormatada : parentNode.datahorafimFormatada;

      }

      node.datahorafimMax = parentNode.datahorafimFormatada;

      node.datahorafimMin = node.datahorainicioFormatada;
      
      if (!node.datahorainicioFormatada && parentNode.datahorafimFormatada) {

        node.datahorafimMin = moment(node.datahorainicioFormatada).isAfter(parentNode.datahorainicioFormatada) ? node.datahorainicioFormatada : parentNode.datahorainicioFormatada;

      } else if (node.datahorainicioFormatada && parentNode.datahorafimFormatada) {

        node.datahorafimMin = moment(node.datahorainicioFormatada).isAfter(parentNode.datahorainicioFormatada) ? node.datahorainicioFormatada : parentNode.datahorainicioFormatada;

      }

    } else if (
      parentNode.etapa.tipo === EtapasEnum.SEQUENCIAL &&
      node.tipoitem === tipoItemEnum.ORDEM_DE_SERVICO
    ) {

      // A data de início mínima é igual a data de fim de uma etapa anterior, caso exista
      let datahorainicioMin: string;
      if (previousNode?.tipoitem === tipoItemEnum.ETAPA) {
        datahorainicioMin = previousNode?.datahorainicio ? moment(previousNode.datahorainicio).format('YYYY-MM-DD HH:mm').slice(0, 10) + 'T' + moment(previousNode.datahorainicio).format('YYYY-MM-DD HH:mm')?.slice(11) : '';
      } else {
        datahorainicioMin = previousNode?.datahorafim ? moment(previousNode.datahorafim).format('YYYY-MM-DD HH:mm').slice(0, 10) + 'T' + moment(previousNode.datahorafim).format('YYYY-MM-DD HH:mm')?.slice(11) : '';
      }

      node.datahorainicioMin = datahorainicioMin;

      // A data de início máxima é igual a data de fim
      node.datahorainicioMax = node.datahorafim ? moment(node.datahorafim).format('YYYY-MM-DD HH:mm').slice(0, 10) + 'T' + moment(node.datahorafim).format('YYYY-MM-DD HH:mm').slice(11) : '';

      // A data de fim máxima é igual a data de início da próxima etapa, caso exista, se não, é a data da etapa pai
      let datahorafimMax: string;
      if (nextNode?.datahorainicio) {
        datahorafimMax = nextNode?.datahorainicio ? moment(nextNode.datahorainicio).format('YYYY-MM-DD HH:mm').slice(0, 10) + 'T' + moment(nextNode.datahorainicio).format('YYYY-MM-DD HH:mm')?.slice(11) : '';
      } else {
        datahorafimMax = parentNode?.datahorafim ? moment(parentNode.datahorafim).format('YYYY-MM-DD HH:mm').slice(0, 10) + 'T' + moment(parentNode.datahorafim).format('YYYY-MM-DD HH:mm')?.slice(11) : '';
      }

      node.datahorafimMax = datahorafimMax; 

      // A data de fim mínima é igual a data de início
      node.datahorafimMin = node.datahorainicio ? moment(node.datahorainicio).format('YYYY-MM-DD HH:mm').slice(0, 10) + 'T' + moment(node.datahorainicio).format('YYYY-MM-DD HH:mm').slice(11) : '';
    }
  }

  /**
   * Converte as datas de um node para o formato YYYY-MM-DD HH:mm:ss
   * @param node 
   * @returns node
   */
  convertDatesToString(node: IEscopoNode) {
    if (node?.datahorainicio && typeof node.datahorainicio !== 'string') {
      node.datahorainicio = moment(node.datahorainicio).format('YYYY-MM-DD HH:mm:ss');
    }

    if (node?.datahorafim && typeof node.datahorafim !== 'string') {
      node.datahorafim = moment(node.datahorafim).format('YYYY-MM-DD HH:mm:ss');
    }

    return node;
  }

  /**
 * 
 * @param nodeA 
 * @param nodeB 
 * @returns {boolean}
   */
  equalNodes(nodeA: IEscopoNode, nodeB: IEscopoNode): boolean {
    nodeA = this.convertDatesToString(nodeA);
    nodeB = this.convertDatesToString(nodeB);

    if (
      nodeA &&
      nodeB &&
      nodeA.datahorainicio === nodeB.datahorainicio &&
      nodeA.datahorafim === nodeB.datahorafim &&
      nodeA.responsavelos?.tecnico === nodeB.responsavelos?.tecnico &&
      nodeA.descricao === nodeB.descricao &&
      angular.equals(nodeA.enderecodestino, nodeB.enderecodestino) &&
      angular.equals(nodeA.enderecoorigem, nodeB.enderecoorigem) &&
      angular.equals(nodeA.etapa, nodeB.etapa) &&
      angular.equals(nodeA.ordemservicotemplate, nodeB.ordemservicotemplate) &&
      angular.equals(nodeA.projeto, nodeB.projeto) &&
      angular.equals(nodeA.veiculo, nodeB.veiculo)
    ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Ações a serem executadas ao pressionar determinadas teclas quando focado em um campo de um node
   * @param node 
   * @param nodeField 
   * @param event 
   */
  onKeyDownOperations(node: IEscopoNode, nodeField: string, event) {
    if (event.which === 13) { // Enter Key
      if (node.editingMode) {
        this.updateEditedNode(node, nodeField);
      }
    }
  }

  /**
 * Seto variável que permite acessar métodos do Scope da tree
 */
  setTreeControl() {
    this.$scope.$watch('$ctrl.$TreeDnDControl', () => {
      this.treeScope = <ng.IScope | EscopoController>angular.element(document.getElementById('tree-dnd')).scope();
      this.treeControl = this.$TreeDnDControl(this.treeScope);
    });
  }

  /**
   * Define a propriedade expansível da tree
   */
  setTreeExpandingProperty(): void {
    this.expanding_property = {
      field: 'descricao',
      titleClass: 'dark-gray',
      cellClass: 'v-middle',
      displayName: 'Descrição',
      cellTemplate: `
        <span class="font-weight-normal">
          <span ng-click="toggleExpand(node);" ng-if="node.__children__.length">
            <i class="mr-5" ng-class="node.__expanded__ ? 'fas fa-chevron-down' : !node.__expanded__ ? 'fas fa-chevron-right' : ''"></i>
          </span>
          <nsj-lookup ng-if="node.tipoitem === 0 && (node.addingMode || node.__editingFields.includes('etapa'))" name="etapa"
            factory="etapasService"
            id="descricao[{{node.projetoitemescopo}}]"
            class="inline-block"+
            ng-class="node.addingMode ? 'normal-input' : 'reduced-input'"
            ng-keydown="$ctrl.onKeyDownOperations(node, 'descetaparicao', $event)"
            ng-model="node.etapa" ng-change="$ctrl.handleNodeChange(node, 'etapa')" max-label="1" constructor="{ }"
            config='{ "nome":"etapa",  }' disabled="node.__saving">
          </nsj-lookup>

          <input 
            id="descricao[{{node.projetoitemescopo}}]"
            ng-if="node.tipoitem === 1 && (node.addingMode || node.__editingFields.includes('ordemservico'))" 
            ng-blur="$ctrl.handleNodeChange(node, 'ordemservico')" 
            ng-keydown="$ctrl.onKeyDownOperations(node, 'ordemservico', $event)"
            ng-model="node.descricao" ng-disabled="node.__saving" 
            type="text" name="descricao" class="form-control"
          />
          <span ng-if="node.tipoitem === 0 && !node.addingMode && !node.__editingFields.includes('etapa')">
            <b ng-class="{ 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }" ng-dblclick="$ctrl.setFieldToEditMode(node, 'etapa'); $ctrl.saveOtherFields();">{{ node.etapa.nome }}</b>
          </span>
        </span>

        <i ng-if="node.etapa && node.tipoitem === 0" class="float-right dark-gray-icon mt-10" ng-class="{ 'fas fa-layer-group' : node.etapa.tipo === 1, 'fas fa-pause' : node.etapa.tipo === 0 }"></i>

        <span class="font-weight-normal mt-10" ng-if="node.tipoitem === 1 && !node.addingMode && !node.__editingFields.includes('ordemservico')"
          ng-class="{ 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }"
          ng-dblclick="$ctrl.setFieldToEditMode(node, 'ordemservico'); $ctrl.saveOtherFields();"
        >
          <i class="far fa-file cyan-icon mr-5"></i> {{ node.descricao }}
        </span>
      `
    };
  }

  /**
   * Define as colunas da tree
   */
  setTreeColumns(): void {
    this.col_defs = [
      {
        field: 'enderecoorigem',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: 'Local/Origem',
        cellTemplate: `
          <span ng-if="node.tipoitem === 1 && (!node.addingMode && !node.__editingFields.includes('enderecoorigem'))"
            ng-class="{ 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }" 
            ng-dblclick="$ctrl.setFieldToEditMode(node, 'enderecoorigem'); $ctrl.saveOtherFields()"
          > {{ node.enderecoorigem.nome ? node.enderecoorigem.nome  : 'Não informado' }} </span>

          <nsj-lookup class="normal-input inline-block" id="enderecoorigem[{{node.projetoitemescopo}}]" ng-if="node.tipoitem === 1 && (node.addingMode || node.__editingFields.includes('enderecoorigem'))" ng-change="$ctrl.handleNodeChange(node, 'enderecoorigem')" name="localizacaocarregar" id-name="endereco" factory="NsEnderecos"
            ng-model="node.enderecoorigem" disabled="node.__saving"
            ng-keydown="$ctrl.onKeyDownOperations(node, 'enderecoorigem', $event)"
            ng-change="$ctrl.handleNodeChange(node, 'enderecoorigem')"
            add-new-option-text="'Adicionar novo local'"
            template="adicionar-endereco-escopo-form"
            filter="{ 'nome': [ {'condition':'isNotNull','value':'' },  ], }" max-label="1"
            constructor="{  }" config='{ "nome":"Nome",  }'>
          </nsj-lookup>
        `
      },
      {
        field: 'enderecodestino',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: 'Destino',
        cellTemplate:
          `
        <span ng-if="node.tipoitem === 1 && (!node.addingMode && !node.__editingFields.includes('enderecodestino'))"
          ng-class="{ 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }" 
          ng-dblclick="$ctrl.setFieldToEditMode(node, 'enderecodestino'); $ctrl.saveOtherFields()"
        > {{ node.enderecodestino.nome ? node.enderecodestino.nome  : 'Não informado' }} </span>

        <nsj-lookup class="normal-input inline-block" id="enderecodestino[{{node.projetoitemescopo}}]" ng-if="node.tipoitem === 1 && (node.addingMode || node.__editingFields.includes('enderecodestino'))" ng-change="$ctrl.handleNodeChange(node, 'enderecodestino')" name="localizacaocarregar" id-name="endereco" factory="NsEnderecos"
          ng-model="node.enderecodestino" disabled="node.__saving"
          ng-change="$ctrl.handleNodeChange(node, 'enderecodestino')"
          add-new-option-text="'Adicionar novo local'"
          template="adicionar-endereco-escopo-form"
          ng-keydown="$ctrl.onKeyDownOperations(node, 'enderecodestino', $event)"
          filter="{ 'nome': [ {'condition':'isNotNull','value':'' },  ], }" max-label="1"
          constructor="{  }" config='{ "nome":"Nome",  }'>
        </nsj-lookup>
        `
      },
      {
        field: 'responsavelos',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: `Responsável`,
        cellTemplate: `
          <span ng-if="node.tipoitem === 1 && !node.addingMode && !node.__editingFields.includes('responsavelos')"
            ng-class="{ 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }" 
            ng-dblclick="$ctrl.setFieldToEditMode(node, 'responsavelos'); $ctrl.saveOtherFields()"
          > {{ node.responsavelos.nome ? node.responsavelos.nome  : 'Não informado' }} </span>

          <nsj-lookup class="normal-input inline-block" id="responsavelos[{{node.projetoitemescopo}}]" ng-if="node.tipoitem === 1 && (node.addingMode || node.__editingFields.includes('responsavelos'))"
            ng-change="$ctrl.handleNodeChange(node, 'responsavelos')"
            name="tecnico"
            factory="tecnicosService"
            ng-model="node.responsavelos"
            ng-keydown="$ctrl.onKeyDownOperations(node, 'responsavelos', $event)"
            disabled="node.__saving"
            list-excluded="$ctrl.collection"
            max-label="1"
            constructor="{  }"
            config='{ "nome":"nome",  }'
        >
        </nsj-lookup>

        `
      },
      {
        field: 'datahorainicio',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: 'Início',
        cellTemplate: `
          <span ng-if="(node.addingMode || (node.__editingFields && node.__editingFields.includes('datahorainicio')))">
            <input
              class="inline-block"
              ng-class="{'datetime-input' : node.datahorainicio_invalid }"
              id="datahorainicio[{{node.projetoitemescopo}}]"
              ng-model="node.datahorainicio"
              value="{{ node.datahorainicioFormatada }}"
              max="{{ node.datahorainicioMax }}"
              min="{{ node.datahorainicioMin }}"
              ng-keydown="$ctrl.onKeyDownOperations(node, 'datahorainicio', $event)"
              ng-blur="$ctrl.handleNodeChange(node, ['datahorainicio', 'datahorafim'])"
              ng-disabled="node.__saving" type="datetime-local" name="datahorainicio">
              <span uib-tooltip="{{ node.tipoetapa_pai === 0 ? 'O horário de início deve estar dentro do intervalo do horário de início e fim da etapa pai' : 'O horário de início deve ser após o horário de fim do item anterior e antes do horário de início do próximo' }}" class="validation"></span>
          </span>
          <span class="time-input" ng-class="{ 'bold' : node.tipoetapa === 0, 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }" 
            ng-dblclick="$ctrl.setFieldToEditMode(node, ['datahorainicio', 'datahorafim']); $ctrl.saveOtherFields()"
            ng-if="!node.addingMode && !node.__editingFields.includes('datahorainicio')">
            {{ node.datahorainicio | asDateTime | date: 'dd/MM/yyyy HH:mm' }}
            {{ !node.datahorainicio ? ' - ' : '' }}
          </span>
       `,
      },
      {
        field: 'datahorafim',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: 'Fim',
        cellTemplate: `
          <span ng-if="(node.addingMode || (node.__editingFields && node.__editingFields.includes('datahorafim')))">
            <input 
              class="inline-block"
              ng-class="{ 'datetime-input' : node.datahorafim_invalid }"
              id="datahorafim[{{node.projetoitemescopo}}]"
              ng-model="node.datahorafim"
              value="{{ node.datahorafimFormatada }}" 
              max="{{ node.datahorafimMax }}"
              min="{{ node.datahorafimMin }}"
              ng-keydown="$ctrl.onKeyDownOperations(node, 'datahorafim', $event)"
              ng-blur="$ctrl.handleNodeChange(node, ['datahorainicio', 'datahorafim'])"
              ng-disabled="node.__saving" type="datetime-local" name="datahorafim">
              <span uib-tooltip="{{ node.tipoetapa_pai === 1 ? 'O horário de fim deve estar dentro do intervalo do horário de início e fim da etapa pai' : 'O horário de fim deve ser antes do horário de início do próximo item e após o horário de início do anterior' }}" class="validation"></span>
          </span>
          <span class="time-input" ng-class="{ 'bold' : node.tipoetapa === 0, 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }"
            ng-dblclick="$ctrl.setFieldToEditMode(node, ['datahorainicio', 'datahorafim']); $ctrl.saveOtherFields()"
            ng-if="!node.addingMode && !node.__editingFields.includes('datahorafim')">
              {{ node.datahorafim | asDateTime | date: 'dd/MM/yyyy HH:mm' }}
              {{ !node.datahorafim ? ' - ' : '' }}
          </span>
       `,
      },
      {
        field: 'veiculo',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: 'Veículo',
        cellTemplate: `
          <span ng-if="node.tipoitem === 1 && !node.addingMode && !node.__editingFields.includes('veiculo')"
            ng-class="{ 'edit-input' : !($ctrl.entity.situacao === 1 || $ctrl.entity.situacao === 3) }" 
            ng-dblclick="$ctrl.setFieldToEditMode(node, 'veiculo'); $ctrl.saveOtherFields()"
          >{{node.veiculo.placa ? node.veiculo.placa : 'Não informado'}}</span>
          <span ng-if="node.tipoitem === 1 && (node.addingMode || node.__editingFields.includes('veiculo'))">
            <nsj-lookup class="normal-input inline-block" id="veiculo[{{node.projetoitemescopo}}]" config='{ "placa":"placa",  }' constructor="{  }"
              disabled="!node.datahorainicio || !node.datahorafim || node.__saving"
              ng-change="$ctrl.handleVeiculoChange(node, 'veiculo')"
              factory="estoqueVeiculosService" id-name="veiculo"
              max-label="1" name="veiculo" ng-model="node.veiculo" disabled="node.__saving">
            </nsj-lookup>
          </span>
        `,
      },
      {
        field: 'situacao',
        titleClass: 'dark-gray',
        cellClass: 'v-middle text-center',
        displayName: 'Situação',
        cellTemplate: `
            <nsj-label ng-if="node.tipoitem === 1" ng-class="{
              'label-preliminar': node.ordemservico.situacao == '0',
              'label-agendada': node.ordemservico.situacao == '1',
              'label-encerrada': node.ordemservico.situacao == '2',
              'label-faturadaSemCobranca': node.ordemservico.situacao == '3',
              'label-faturada': node.ordemservico.situacao == '4',
              'label-cancelada': node.ordemservico.situacao == '5',
              'label-faturadoParcialmente': node.ordemservico.situacao == '6',
              'label-aCaminho': node.ordemservico.situacao == '7',
              'label-aguardando': node.ordemservico.situacao == '8',
              'label-emExecucao': node.ordemservico.situacao == '9',
              'label-emPausa': node.ordemservico.situacao == '10',
              'label-realizadaComSucesso': node.ordemservico.situacao == '11',
              'label-realizadoComPendencia': node.ordemservico.situacao == '12',
              'label-naoRealizado': node.ordemservico.situacao == '13',
              'label-impedido': node.ordemservico.situacao == '14',
              'label-emTransito': node.ordemservico.situacao == '15',
          }" content="{{ $ctrl.SITUACAO_OS_CONST[node.ordemservico.situacao] }}"
          </nsj-label>
          {{ !node.ordemservico.situacao && node.ordemservico.situacao !== 0 ? ' - ' : '' }}
        `,
      },
    ];
  }
}
