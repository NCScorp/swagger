angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
                    'entity': ['AtendimentoCategorias', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['categoria']) {
                                    return entityService.get($stateParams['categoria']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };
                $stateProvider
                        .state('atendimento_categorias', {
                            url: "/categorias",
                            parent: 'atendimento_admin_configuracoes',
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_categorias_template'),
                            controller: 'AtendimentoCategoriasListController',
                            controllerAs: 'tndmnt_ctgrs_lst_cntrllr'
                        })

                        .state('atendimento_categorias_new', {
                            parent: 'atendimento_categorias',
                            url: "/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_categorias_template_form'),
                            controller: 'Atendimento\CategoriasFormController',
                            controllerAs: 'tndmnt_ctgrs_frm_cntrllr'
                        })
                        .state('atendimento_categorias_edit', {
                            parent: 'atendimento_categorias',
                            url: "/:categoria/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_categorias_template_form'),
                            controller: 'Atendimento\CategoriasFormController',
                            controllerAs: 'tndmnt_ctgrs_frm_cntrllr'
                        });
            }])
        .factory('artigos', ['$q', '$http', 'nsjRouting', function($q, $http, nsjRouting){
            var self =  {
              load: function(constructors, offset, filter){
                var canceler = $q.defer();
                self.constructors = constructors;
                var request = {
                    "method": "GET",
                    "url": nsjRouting.generate('atendimento_admin_artigos_index', angular.extend(constructors, {'offset': offset, 'filter': filter}), false),
                    "timeout": canceler.promise,
                    "cancel": canceler
                };
                
                return $q(function (resolve, reject) {
                    self.isBusy = true;
                    $http(request).then(function (response) {
                        resolve(response.data);
                    }).catch(function (response) {
                        reject(response);
                    }).finally(function(){self.isBusy = false;});
                });
                
              }
            };
            return self;
        }])
        .controller('AtendimentoCategoriasListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'AtendimentoCategorias',
            '$uibModal', 'toaster', 'artigos', 'entity','nsjRouting','AtendimentoAdminArtigos',
            function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, AtendimentoCategorias,
                $uibModal, toaster, artigos, entity, nsjRouting, AtendimentoAdminArtigos) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.entity = entity;
                self.finished = false;
                self.filter = AtendimentoCategorias.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = AtendimentoCategorias.search;
                self.reload = AtendimentoCategorias.reload;
                self.load = AtendimentoCategorias.load;
                self.loadMore = AtendimentoCategorias.loadMore;
                self.service = AtendimentoCategorias;
                self.constructors = AtendimentoCategorias.constructors = [];
                self.listaCategorias = [];
                self.categorias = [];
                self.subcategorias = [];
                self.secao = [];
                self.artigos = artigos;
                self.categoriasToggle = [];
                
                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        AtendimentoCategorias.constructors[i] = $stateParams[i];
                    }
                }

                // Passa no constructors um indicativo de que é para buscar apenas os nomes das categorias.
                // Isso diminui o custo da requisição e otimiza o tempo de resposta, porque não faz a soma dos quantitativos etc.
                
                if (!$rootScope.listaCategorias) {
                  AtendimentoCategorias.constructors = { 'buscarApenasCategorias': true };
                  $rootScope.listaCategorias = self.entities = AtendimentoCategorias.load();
                } else {

                  self.categorias = [];
                  self.subcategorias = [];
                  self.secao = [];

                  $rootScope.listaCategorias.forEach(function(cat, i) {
                    if (cat.subcategorias) {
                      cat.subcategorias.forEach(function(sub, ii) {
                        if (sub.secoes) {
    
                          sub.secoes.forEach(function(secao, iii) {
                            secao.categoria = secao.categoria || secao.id;
                            secao.ordem = iii;
                            secao.isCollapsed = (secao.isCollapsed != undefined) ? secao.isCollapsed : true;

                            delete secao.fechada;

                            self.secao.push(secao);
                          });
    
                          sub.categoria = sub.categoria || sub.id;
                          sub.ordem = ii;
    
                          delete sub.fechada;
    
                          sub.secoes = sub.secoes;
    
                          sub.exibir = true;
                          sub.isCollapsed = (sub.isCollapsed != undefined) ? sub.isCollapsed : true;
                          sub.qtdfilhos = sub.secoes.length;

                          self.subcategorias.push(sub);
                        }
                      });
    
                      cat.ordem = i;
                      cat.categoria = cat.categoria || cat.id;
                      cat.subcategorias = cat.subcategorias;
                      cat.exibir = true;
                      cat.isCollapsed = (cat.isCollapsed != undefined) ? cat.isCollapsed : true;
                      cat.qtdfilhos = cat.subcategorias.length;

                      delete cat.fechada;
    
                      self.categorias.push(cat);
                    }
                  });
                  
                  self.entities = $rootScope.listaCategorias;
                  $rootScope.categorias = self.categorias;
                  $rootScope.subcategorias = self.subcategorias;
                  $rootScope.secoes = self.secao;
                }
                
                self.limparRootScope = function() {
                  $rootScope.listaCategorias = [];
                  $rootScope.categorias = $rootScope.subcategorias = $rootScope.secoes = null;
                }

                $scope.tiposordenacao = {
                  1: { 'label': 'Manualmente'},
                  2: { 'label': 'Data de Criação, recentes primeiro' },
                  3: { 'label': 'Data de Criação, antigos primeiro' },
                  4: { 'label': 'Alfabética pelo Título' }
                };
                
                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };
                
                self.isBusy = function () {
                    return AtendimentoCategorias.busy;
                };
                
                self.toggle = function (node, doToggle) {
                  // // adiciona ou remove categoria sinalizada como aberta ou fechada
                  if(  self.categoriasToggle.indexOf(node.id) == -1 ){
                    self.categoriasToggle.push(node.id);
                  } else {
                    self.categoriasToggle.splice( self.categoriasToggle.indexOf(node.id), 1 );
                  } 

                  if(node.nodes.length === 0){
                    self.carregaArtigos(node);
                  }
                  if(doToggle){
                    node.fechada = !node.fechada;
                  }
                };
                
                self.carregaArtigos = function(node){
                  if(node.tipo === 3){
                    self.artigos.load({ secao: node.id, orderfield: node.tipoordenacao  }, null, null).then(function(data){
                    if(data.length > 0){
                      for(var x in data){
                          self.listaCategorias.map(function(cat){
                            cat.nodes.map(function(subcat){
                              subcat.nodes.map(function(sec){
                                if(sec.id === data[x].secao.categoria){
                                  sec.nodes.push({
                                    titulo: data[x].titulo,
                                    fixarnotopo: data[x].fixarnotopo,
                                    tipo: 4, 
                                    status: data[x].status ,
                                    id: data[x].artigo,
                                    ordenacaoSecaoPai: sec.tipoordenacao
                                  });
                                }
                              });
                            });
                          });
                      }
                    }else{
                      toaster.pop({
                        type: 'warnning',
                        title: 'Não há artigos para exibir'
                      });
                    }
                      
                    }).catch(function(data){
                      toaster.pop({
                        type: 'error',
                        title: 'Falha ao carregar artigos'
                      });
                    });
                  }
                };
              
                $rootScope.$on("atendimento_categorias_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });

                $rootScope.$on("artigo_movido", function(event, args){
                  window.location.reload();
                });

              self.abreModalCategoria = function(entity){
                  $scope.entity = {
                    titulo: entity?entity.titulo:'',
                    tipo: entity?entity.tipo:1,
                    status: entity?entity.status:0, 
                    categoria: entity?entity.id:null, 
                    categoriapai: entity?entity.categoriapai:null,
                    fechada: entity ? entity.fechada : false
                  };
                  $scope.modalInstance = $uibModal.open({
                      animation: false,
                      templateUrl: nsjRouting.generate('template_categoria_modal', {}, false),
                      scope: $scope
                  });
              };

              self.abreModalArtigo = function(artigo) {
                var modalInstance =  $uibModal.open({
                    templateUrl: nsjRouting.generate('template_artigo_modal', {}, false),
                    controller: "ArtigoModalCtrl",
                    controllerAs: 'tndmnt_rtg_mdl_ctrl',
                    size: '',
                    resolve: {
                        ArtigoId: function () {
                            return artigo.id;
                        }
                    }
                });
              };

              self.abreModalSubCategoria = function(entity){
                  $scope.entity = {
                    titulo: entity?entity.titulo:'',
                    tipo: entity?entity.tipo:1,
                    status: entity?entity.status:0, 
                    categoria: entity?entity.id:null, 
                    categoriapai: entity?entity.categoriapai:null,
                    descricao: entity?entity.descricao:'',
                    fechada: entity ? entity.fechada : false
                  };
                  
                  $scope.modalInstance = $uibModal.open({
                      animation: false,
                      templateUrl: nsjRouting.generate('template_subcategoria_modal', {}, false),
                      scope: $scope
                  });
              };

              self.abreModalSecao = function(entity){
                  $scope.entity = {
                    titulo: entity?entity.titulo:'',
                    tipo: entity?entity.tipo:1,
                    status: entity?entity.status:0, 
                    categoria: entity?entity.id:null, 
                    categoriapai: entity?entity.categoriapai:null,
                    descricao: entity?entity.descricao:'',
                    tipoordenacao: entity?entity.tipoordenacao.toString():'',
                    fechada: entity ? entity.fechada : false
                  };
                  
                  $scope.modalInstance = $uibModal.open({
                      animation: false,
                      templateUrl: nsjRouting.generate('template_secao_modal', {}, false),
                      scope: $scope
                  });
              };

              self.modalClose = function(){
                  self.entity = {};
                  $scope.modalInstance.dismiss();
              };
              
              self.treeOptions = {
                beforeDrag: function() {
                  return !self.fazendoRequisicao;
                },

                beforeDrop: function(e){

                  var sourceValue = e.source.nodeScope.$modelValue.tipo;
                  var id = e.source.nodeScope.$modelValue.id;
                  var destValue = e.dest.nodesScope.node ? e.dest.nodesScope.node.tipo : 0;
                  var idpai = e.dest.nodesScope.node ? e.dest.nodesScope.node.id : null;
                  var idpai_anterior = e.source.nodeScope.$modelValue.categoriapai?e.source.nodeScope.$modelValue.categoriapai.categoria : null;
                  var posicao = e.dest.index;
                  var posicao_anterior = e.source.index;
                  
                  if(idpai == idpai_anterior && posicao == posicao_anterior){
                      return false;
                  }else{
                  
                    if(destValue === (sourceValue - 1)  ){

                      // caso seja artigo
                      if(sourceValue === 4){
                        $http({
                          method: 'POST',
                          url: nsjRouting.generate('atendimento_admin_artigos_reordenar', { 'id': id } , false),
                          data: { 'categoria': idpai, 'posicao': posicao }
                        }).then(function(response){
                           toaster.pop({
                              type: 'success',
                              title: 'Artigo reordenado'
                            });
                        }).catch(function(response){
                            toaster.pop({
                              type: 'error',
                              title: 'Reordenação falhou'
                            });

                            window.location.reload();
                        });
                      }else{
                        // Caso nao seja artigo

                        self.fazendoRequisicao = true;

                        $http({
                          method: 'POST',
                          url: nsjRouting.generate('atendimento_categorias_reordenar', { 'categoria': id } , false),
                          data: { 'categoriapai': idpai, 'ordem': posicao }
                        }).then(function(response){

                          self.fazendoRequisicao = false;

                          toaster.pop({
                              type: 'success',
                              title: 'Reordenado com sucesso'
                            });
                        }).catch(function(response){
                          
                          self.fazendoRequisicao = false;

                            toaster.pop({
                              type: 'error',
                              title: 'Reordenação falhou'
                            });

                            window.location.reload();
                        });
                      }

                      return true;
                    }else{
                      return false;
                    }
                }
                },
                dropped: function(e){
//                  console.log(self.listaCategorias);
                }
              };
              
              
              self.deleteCategoria = function(categoria){
                var msg = "Deseja excluir o item " + categoria.titulo + "?";
                if (confirm(msg)) {
                  $http({
                    method: 'delete',
                    url: self.generateRoute('atendimento_categorias_delete', { "id": categoria.id }, false),
                    data: {}
                  }).then(function(response){
                    toaster.pop({
                        type: 'success',
                        title: 'Excluído com sucesso!'
                      });

                      window.location.reload();
                      
                  }, function errorCallBack(){
                    toaster.pop({
                        type: 'error',
                        title: 'Falha ao excluir'
                      });
                  });
                }
              };
              
              self.salvarCategoria = function(tipo){

                window.localStorage.setItem("ultima_categoria_aberta", JSON.stringify({ categoria: $scope.entity.categoria, fechada: $scope.entity.fechada }));

                $scope.submitted = true;
                $scope.entity.tipo = tipo;
                $scope.entity.status = $scope.entity.status?$scope.entity.status:0;
                $scope.entity.categoriapai = $scope.entity.categoriapai?$scope.entity.categoriapai.categoria:null;
                
                if ($scope.tndmnt_ctgrs_lst_cntrllr.form.$valid && !$scope.entity.submitting) {
                  if($scope.entity.categoria){
                      var request = {
                         method: 'put',
                         url: self.generateRoute('atendimento_categorias_put', { "id": $scope.entity.categoria }, false),
                         data: $scope.entity
                       };
                       var acao = "Alterado";
                   }else{
                      var request = {
                         method: 'post',
                         url: self.generateRoute('atendimento_categorias_create', {}, false),
                         data: $scope.entity
                       };
                       var acao = "Criado";
                       
                   }
                    if (!$scope.entity.categoriapai && tipo !== 1){return;}
                    $http(request).
                    then(function successCallback(response){
                      self.modalClose();


                      // AtendimentoCategorias.constructors = { 'buscarApenasCategorias': true };
                      // AtendimentoCategorias.reload();

                      $scope.entity = {};
                      toaster.pop({
                        type: 'success',
                        title: acao +' com sucesso!'
                      });

                      window.location.reload();

                    },function errorCallBack(response){
                        toaster.pop({
                          type: 'error',
                          title: 'Falha ao executar ação'
                        });
                    });
                  }
              };
              
              self.reloadListaCategorias = function() {


                var ultimaCategoriaAberta = JSON.parse(window.localStorage.getItem("ultima_categoria_aberta"));

                if ($rootScope.listaCategorias.length == 0 && (!$rootScope.categorias || $rootScope.categorias.length == 0) 
                                                           && (!$rootScope.subcategorias || $rootScope.subcategorias.length == 0) 
                                                           && (!$rootScope.secoes || $rootScope.secoes.length == 0)) {
                  self.categorias = AtendimentoCategorias.entities.filter(function(obj){ return (obj.tipo === 1); });
                  self.subcategorias = AtendimentoCategorias.entities.filter(function(obj){ return (obj.tipo === 2); });
                  self.secao = AtendimentoCategorias.entities.filter(function(obj){ return (obj.tipo === 3); });
                }
                
                self.categorias.map(function(categoria){
                  var subcategorias = [];
                  
                  self.subcategorias.map(function(subcategoria){
                    var secoes = [];

                    self.secao.map(function(secao) {
                      if(secao.categoriapai.categoria === subcategoria.categoria){

                        secoes.push({
                          titulo: secao.titulo,
                          tipo: secao.tipo, 
                          status: secao.status,
                          descricao: secao.descricao,
                          tipoordenacao :secao.tipoordenacao,
                          categoriapai: secao.categoriapai,
                          id: secao.categoria,
                          fechada: self.categoriasToggle.indexOf(secao.categoria) == -1 ,
                          nodes: []
                        });
                      }
                  });
                  
                    secoes.map(function(secao){
                      if(!secao.fechada) {
                        self.toggle(secao, false);
                      }
                    }); 
                    
                    if(subcategoria.categoriapai.categoria === categoria.categoria){
                      subcategorias.push({
                        titulo: subcategoria.titulo,
                        tipo: subcategoria.tipo, 
                        status: subcategoria.status,
                        id: subcategoria.categoria,
                        descricao: subcategoria.descricao,
                        categoriapai: subcategoria.categoriapai,
                        fechada: self.categoriasToggle.indexOf(subcategoria.categoria) == -1 ,
                        nodes: secoes,
                      });
                    }
                  });
                  
                  self.listaCategorias.push({
                    titulo: categoria.titulo,
                    tipo: categoria.tipo,
                    status: categoria.status,
                    id: categoria.categoria,
                    descricao: categoria.descricao,
                    categoriapai: categoria.categoriapai,
                    fechada: self.categoriasToggle.indexOf(categoria.categoria) == -1 ,
                    nodes: subcategorias,
                  });

                });

                var continua = true;

                if (ultimaCategoriaAberta) {
                  self.listaCategorias.every(function(cat) {

                    if (cat.id == ultimaCategoriaAberta.categoria) {
                      continua = false;

                      cat.fechada = ultimaCategoriaAberta.fechada;
                      cat.isCollapsed = ultimaCategoriaAberta.fechada;
                     
                      window.localStorage.removeItem("ultima_categoria_aberta");
                    }

                    cat.nodes.every(function(sub) {

                      if (sub.id == ultimaCategoriaAberta.categoria) {
                        continua = false;

                        sub.fechada = ultimaCategoriaAberta.fechada;
                        sub.isCollapsed = ultimaCategoriaAberta.fechada;
                       
                        window.localStorage.removeItem("ultima_categoria_aberta");
                      }

                      var sec = sub.nodes.find(function(sec) {
                        return sec.id == ultimaCategoriaAberta.categoria;
                      });

                      if (sec) {
                        sub.isCollapsed = ultimaCategoriaAberta.fechada;
                        sub.fechada = ultimaCategoriaAberta.fechada;

                        continua = false;

                        window.localStorage.removeItem("ultima_categoria_aberta");
                      }

                      return continua;
                    });

                    if (!continua) {
                      cat.isCollapsed = false;
                      cat.fechada = false;
                    }

                    return continua;
                  });
                }

                $rootScope.listaCategorias = self.listaCategorias;
                $rootScope.categorias = self.categorias;
                $rootScope.subcategorias = self.subcategorias;
                $rootScope.secoes = self.secao;
            };

                
            $scope.$on("atendimento_categorias_deleted", function (event) {
                self.reload();
            });
            
            $scope.$on("atendimento_categorias_list_finished", function(event) {
              self.reloadListaCategorias();
            });
              
            if (!$rootScope.listaCategorias) {
              self.load();
            }

            self.reloadListaCategorias();
              
            }])
        .controller(
            'ArtigoModalCtrl',['$rootScope', '$uibModalInstance','AtendimentoAdminArtigos','ArtigoId','toaster','Categorias','$state', '$http', 'nsjRouting',
            function($rootScope ,$uibModalInstance, ArtigosAdminService, ArtigoId, toaster, Categorias, $state, $http, nsjRouting)
        {
            var self = this;
            self.artigo = null;
            self.listaCategorias = [];
            // Não faz mais a requisição por esse load global.
            // self.todasCategorias = Categorias.load();

            // Passa a fazer da seguinte forma:
            // Faz uma requisição para buscar somente os títulos das categorias.
            // Isso diminui o custo da requisição e otimiza o tempo de resposta, porque não faz a soma dos quantitativos etc.
            $http.get(nsjRouting.generate('atendimento_cliente_categorias_index', { buscarApenasCategorias: true }, false))
            .then(function(response) {                
                self.categorias = response.data.filter(function(cat){ return cat.tipo === 1; });
                self.subcategorias = response.data.filter(function(cat){ return cat.tipo === 2; });
                self.secoes = response.data.filter(function(cat){ return cat.tipo === 3; });

                self.categorias.map(function(cat) {
                  cat.subcategorias = [];
                  self.subcategorias.map(function(sub){
                    if(sub.categoriapai.categoria === cat.categoria) {
                        sub.secoes = [];
                        self.secoes.map(function(sec) {
                            if(sec.categoriapai.categoria === sub.categoria){
                                sub.secoes.push(sec);
                            }
                        });
                        cat.subcategorias.push(sub);
                    }
                  });
                  self.listaCategorias.push(cat);
                });  
            });
            
            ArtigosAdminService.get(ArtigoId).then(function(res) {
                self.artigo = res;
            })

            self.salvar = function(){

                var secao = self.selected.subcategorias.selected.secoes.selected.categoria;
                self.artigo.secao.categoria = secao;

                ArtigosAdminService._save(self.artigo)
                    .then(function(res){
                        toaster.pop({
                            type: 'success',
                            title: 'Artigo atualizado com sucesso'
                        });
                        $uibModalInstance.close("Ok");
                        $rootScope.$broadcast("artigo_movido");                        
                    })
                    .catch(function(erro){
                        toaster.pop({
                            type: 'error',
                            title: 'Não foi possível atualizar o artigo!'
                        });
                    })
            }
                
            self.cancel = function(){
                $uibModalInstance.dismiss();
            } 
            
        }])
        .controller('AtendimentoCategoriasFormController', [
            '$scope', '$stateParams', '$state', 'AtendimentoCategorias', 'entity',
            function ($scope, $stateParams, $state, entityService, entity) {

                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};


                self.submit = function () {
                    self.submitted = true;
                    if (self.form.$valid && !self.entity.submitting) {
                        entityService.save(self.entity);
                    }
                };
                
                self.isBusy = function () {
                    return entityService.busy;
                };

                self.onSubmitSuccess = $scope.$on("atendimento_categorias_submitted", function (event, args) {
                    $state.go('atendimento_categorias', self.constructors);
                });

                self.onSubmitError = $scope.$on("atendimento_categorias_submit_error", function (event, args) {
                    if (args.response.status == 409) {
                        if (confirm(args.response.data.message)) {
                            self.entity['lastupdate'] = args.response.data.entity['lastupdate'];
                            entityService.save(self.entity);
                        }
                    } else {
                        if (typeof (args.response.data.erro) !== 'undefined') {
                            alert(args.response.data.erro);
                        } else {
                            var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                            alert("Ocorreu um erro ao tentar " + acao + ".");
                        }
                    }
                });
                self.delete = function (force) {
                    entityService.delete($stateParams['categoria'], force);
                };

                self.onDeleteSuccess = $scope.$on("atendimento_categorias_deleted", function (event, args) {
                    $state.go('atendimento_categorias', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("atendimento_categorias_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else if (typeof (args.response.data.message) !== 'undefined') {
                        alert(args.response.data.message);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }
            }]);

