import angular = require("angular");


export const readRule = (ruleStr: string = '', rule: any, dictionary: object) => {
  const result =typeof ruleStr === 'string' 
    && ruleStr.replace(rule, (matched) => dictionary[matched]);
  return result;
}

export const evalString = (scope:any, stringValue: string = '') => {
  try {
    const result = new Function('return ' + stringValue).call(scope);
    return result;

  } catch (error) {
    console.error(error.message);  
    return false;
  }
  
}

export const uniqByProp_map = (arr, prop) =>
  Array.from(
    arr.reduce((acc, item) => (
      item && item[prop] && acc.set(item[prop], item),
      acc
    ), // using map (preserves ordering)
      new Map()
    )
      .values()
  );

export interface Item {
  id?: string
  parentId?: string | null,
  [key: string]: any,
}

export interface TreeItem {
  id?: string,
  parentId?: string | null,
  [key: string]: Item | any,
  children: TreeItem[]
}

export interface Config {
  id: string,
  parentId: string,
  dataField: string | null,
}

const defaultConfig: Config = {
  id: '_id_',
  parentId: '_parentId_',
  dataField: null,
}

/**
 * Unflattens an array to a tree with runtime O(n)
 */
export function arrayToTree(items: Item[], config: Partial<Config> = {}): TreeItem[] {
  const conf: Config = { ...defaultConfig, ...config }

  // the resulting unflattened tree
  const rootItems: TreeItem[] = []

  // stores all already processed items with ther ids as key so we can easily look them up
  const lookup: { [id: string]: TreeItem } = {}

  // idea of this loop:
  // whenever an item has a parent, but the parent is not yet in the lookup object, we store a preliminary parent
  // in the lookup object and fill it with the data of the parent later
  // if an item has no parentId, add it as a root element to rootItems
  for (const item of items) {
    const itemId = item[conf.id]
    const parentId = item[conf.parentId]

    // look whether item already exists in the lookup table
    if (!Object.prototype.hasOwnProperty.call(lookup, itemId)) {
      // item is not yet there, so add a preliminary item (its data will be added later)
      lookup[itemId] = { children: [] }
    }

    // add the current item's data to the item in the lookup table
    if (conf.dataField) {
      lookup[itemId][conf.dataField] = item
    } else {
      lookup[itemId] = { ...item, children: lookup[itemId].children }
    }

    const TreeItem = lookup[itemId]

    if (parentId === null) {
      // is a root item
      rootItems.push(TreeItem)
    } else {
      // has a parent

      // look whether the parent already exists in the lookup table
      if (!Object.prototype.hasOwnProperty.call(lookup, parentId)) {
        // parent is not yet there, so add a preliminary parent (its data will be added later)
        lookup[parentId] = { children: [] }
      }

      // add the current item to the parent
      lookup[parentId].children.push(TreeItem)
    }
  }

  return rootItems
}

/**
  * 
  * @param tipo 
  * @param entity 
  * @param identificador 
  * @param pai 
  * @param hashAuxiliar 
  * @param actions 
  * @param iconLeaf
  */

export const montarNoTree = function (tipo, entity, identificador, pai = null, hashAuxiliar = '', actions = [], iconLeaf) {
  const cod = tipo + identificador + hashAuxiliar;
  const no = {
    _id_: cod,
    _parentId_: pai,
    _info_: null,
    children: [],
    tipo: tipo,
    nome: entity.nome ? entity.nome : entity.descricao,
    quantidade: entity.quantidade ? entity.quantidade : null,
    valor: entity.valor ? entity.valor : null,
    actions: actions,
    getActions: function() {
        return this.actions.filter((actionFilter) => {
            return !actionFilter.isVisible || actionFilter.isVisible(this);
        });
    },
    icons: { iconLeaf },
    obj: entity
  }

  return no;
}


export const inserirNode = function (nodes: any[], newChild) {

  if (nodes.length == 0 || newChild._parentId_ == null) {
    nodes.push(newChild)
  } else {

    for (const [index, node] of Object.entries(nodes)) {
      if (node._id_ === newChild._parentId_) {
        node.children.push(newChild)
        break;
      }
      if (node && node.children && node.children.length) {
        this.inserirNode(node.children, newChild)
      }
    }
  }
}

export const editarNode = function (nodes: any[], update: any) {
  for (let [index, nodeParaAlterar] of Object.entries(nodes)) {
    if (nodeParaAlterar._id_ === update._id_) {
      /**
       * guarda o array de filhos para garantir que 
       * não sejam perdidos
       */
      const { children } = nodeParaAlterar;
      angular.copy(update, nodeParaAlterar);
      nodeParaAlterar['children'] = children;
    }
    if (nodeParaAlterar && nodeParaAlterar.children && nodeParaAlterar.children.length) {
      this.editarNode(nodeParaAlterar.children, update)
    }
  }
}

export const removerNode = function (nodes: any[], id: string, key: string, hashIdentificador?: string) {

  for (const [index, node] of Object.entries(nodes)) {
    if (node.obj[key] === id) {
      nodes.splice(parseInt(index), 1);
      break;
    }

    if (node && node.children && node.children.length) {
      this.removerNode(node.children, id, key, hashIdentificador);
    }
  }
}

function obterTipodaChave({ tipo }) {
  return tipo === 'item' ? 'propostaitem' : 'propostacapitulo'
}

export const inArray = function (arr: [], attr, value){
    let result = arr.filter(function(item){
        return evalString(item, `this.${attr}`) == value;
    });
    return result.length > 0;
}

export type treeExpand = {
    field: string;
    displayName: string;
    sortable: boolean;
    filterable: boolean;
    cellTemplate?: string;
    cellTemplateScope?: any;
};

export interface ITreeControl {
    expand_all?: () => any
    collapse_all?: () => any
    get_first_branch?: () => any
    select_first_branch?: () => any
    get_selected_branch?: () => any
    get_parent_branch?: (b) => any
    select_branch?: (b) => any
    get_children?: (b) => any
    select_parent_branch?: (b) => any
    add_branch?: (parent, new_branch) => any
    add_root_branch?: (new_branch) => any
    expand_branch?: (b) => any
    collapse_branch?: (b) => any
    get_siblings?: (b) => any
    get_next_sibling?: (b) => any
    get_prev_sibling?: (b) => any
    select_next_sibling?: (b) => any
    select_prev_sibling?: (b) => any
    get_first_child?: (b) => any
    get_closest_ancestor_next_sibling?: (b) => any
    get_next_branch?: (b) => any
    select_next_branch?: (b) => any
    last_descendant?: (b) => any
    get_prev_branch?: (b) => any
    select_prev_branch?: (b) => any
}