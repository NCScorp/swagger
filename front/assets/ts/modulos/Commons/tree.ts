import angular = require('angular');
//import {NODATA} from 'dns';

export class Tree {

  static $inject = [];


  public tree: any = {};
  public expandingProperty: any = {
    titleClass: 'text-center',
    cellClass: 'v-middle',
    displayName: ' '
  }

  constructor() { }

  /**
   * Adiciona o nó no array simples. 
   * o array facilita a busca
   * @param noNovo 
   * @param dados  
   * @todo verificar porque o array de dados originais está sendo modificado
   */
  adicionarNo(noNovo: any, dados: Map<any, any>) {
    dados.set(noNovo._id_, noNovo);
  }

  removerNo(key: string, dados: Map<any, any>) {
    dados.delete(key);
  }

  editarNo(noNovo: any, dados: any[]) {

    for (let no in dados) {
      if (dados[no]._id_ === noNovo._id_) {
        angular.copy(noNovo, dados[no]);
      }
    }
    return dados;
  }

  /**
   * A tree-dnd trabalha com uma matriz para gerar a árvore, então esse método chama a função que vai reorganizar os nós em matriz.
   * @param dados 
   */
  getArvore(dados: any[]) {
    let arvore = this._reorganizarNos(dados);
    return arvore;
  }

  /**
   * Retorna um array linear com todos itens filhos do node 
   * @param array 
   */
  flatNode(array): Array<any> {
    return array.reduce((r: any, a: any) => {
      r.push(a);
      if (a.children && Array.isArray(a.children)) {
        r = r.concat(this.flatNode(a.children));
      }
      return r;
    }, []);
  }

  /**
   * Esse método pega um array e reorganiza os dados em uma matriz que será consumida pela tree-dnd.
   * @param dados 
   */
  private _reorganizarNos(dados: any[]) {
    let dadosAux = dados.map(function (d) {
      d.children = [];
      return d;
    });

    let dadosTree = [];

    for (let dadoIndex in dadosAux) {
      if (dadosAux[dadoIndex]._parentId_ == null) {
        let no = dadosAux[dadoIndex];
        dadosAux.slice(parseInt(dadoIndex), 1);
        let children = this._adicionarFilhos(no, dadosAux);
        if (children.length > 0) {
          angular.extend(no.children, children);
        }
        dadosTree.push(no);
      }
    }

    return dadosTree;

  }

  /**
   * O método é chamado pelo reorganizarNos() e adiciona os filhos no formato esperado.
   * @param pai 
   * @param dadosAux 
   */
  private _adicionarFilhos(pai: any, dadosAux: any[]) {
    let nos = [];
    for (let dadoIndex in dadosAux) {
      if (dadosAux[dadoIndex]._parentId_ === pai._id_) {
        let no = dadosAux[dadoIndex];
        dadosAux.slice(parseInt(dadoIndex), 1);
        let children = this._adicionarFilhos(no, dadosAux);
        if (children.length > 0) {
          angular.extend(no.children, children);
        }
        nos.push(no);
      }
      // if(dadosAux[dadoIndex].children.includes())
    }
    return nos;
  }

}
export const TreeDnDModule = angular.module('TreeDnDModule', [])
  .service('Tree', Tree).name;

angular.module('template/TreeDnD/TreeDnD.html', []).run(
  ['$templateCache', function ($templateCache) {
    $templateCache.put(
      'template/TreeDnD/TreeDnD.html',
      '<table ng-class="$tree_class">' +
      '   <thead>' +
      '       <tr>' +
      '           <th ng-class="expandingProperty.titleClass" ng-style="expandingProperty.titleStyle">' +
      '               {{expandingProperty.displayName || expandingProperty.field || expandingProperty}}' +
      '           <\/th>' +
      '           <th ng-repeat="col in colDefinitions" ng-class="col.titleClass" ng-style="col.titleStyle">' +
      '               {{col.displayName || col.field}}' +
      '           </th>' +
      '       </tr>' +
      '   </thead>' +
      '   <tbody tree-dnd-nodes>' +
      '       <tr tree-dnd-node="node" ng-repeat="node in tree_nodes track by node.__hashKey__" ' +
      '           ng-if="(node.__inited__ || node.__visible__)"' +
      '           ng-click="$ctrl.carregarFilhos(node)" ' +
      '           ng-class="(node.__selected__ ? \' active\':\'\')">' +
      '           <td tree-dnd-node-handle' +
      '               ng-style="expandingProperty.cellStyle ? expandingProperty.cellStyle : {\'padding-left\': $callbacks.calsIndent(node.__level__)}"' +
      '               ng-class="expandingProperty.cellClass"' +
      '               compile="expandingProperty.cellTemplate">' +
      '               <a data-nodrag>' +
      '                  <span ng-if="node.__children__.length == 0"> <i ng-if="node.__children__.length == 0" ng-class="$ctrl.getIcon(node)" ng-click="toggleExpand(node)" class="tree-icon"></i> </span>' +
      '                  <i ng-class="node.__icon_class__" ng-click="toggleExpand(node)" class="tree-icon"></i>  {{node["nome"]}} <span class="badge badge-info" ng-if="node._info_">{{node["_info_"]}}</span>' +
      '               </a>' +
      '              {{node[expandingProperty.field] || node[expandingProperty]}}' +
      '           </td>' +
      '           <td ng-repeat="col in colDefinitions" ng-class="col.cellClass" ng-style="col.cellStyle" compile="col.cellTemplate">' +
      '               {{node[col.field]}}' +
      '           </td>' +
      '       </tr>' +
      '   </tbody>' +
      '</table>'
    );

    $templateCache.put(
      'template/TreeDnD/TreeDnDStatusCopy.html',
      '<label><i class="fa fa-copy"></i>&nbsp;<b>Copying</b></label>'
    );

    $templateCache.put(
      'template/TreeDnD/TreeDnDStatusMove.html',
      '<label><i class="fa fa-file-text"></i>&nbsp;<b>Moving</b></label>'
    );
  }]
);
