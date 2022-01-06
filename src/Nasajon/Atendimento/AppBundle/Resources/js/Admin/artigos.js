angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
            $stateProvider
                    .state('atendimento_admin_artigos', {
                      url: "/artigos",
                      templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_artigos_template'),
                      controller: 'AtendimentoAdminArtigosListController',
                      controllerAs: 'tndmnt_dmn_rtgs_lst_cntrllr'
                    })
                    .state('atendimento_admin_artigos_list', {
                        parent: 'atendimento_admin_artigos',
                        url: "/?status?tags?categoria?subcategoria?secao?busca",
                        templateUrl: nsjRoutingProvider.$get().generate('template_artigos_list'),
                        controller: 'AtendimentoAdminArtigosListController',
                        controllerAs: 'tndmnt_dmn_rtgs_lst_cntrllr'
                    })
                    .state('atendimento_admin_artigos_new', {
                      parent: 'atendimento_admin_artigos',
                      url: "/new?secao",
                      templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_artigos_template_form'),
                      controller: 'Atendimento\Admin\ArtigosFormController',
                      controllerAs: 'tndmnt_dmn_rtgs_frm_cntrllr'
                    })
                    .state('atendimento_admin_artigos_show', {
                      parent: 'atendimento_admin_artigos',
                      url: "/:artigo",
                      templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_artigos_template_show'),
                      controller: 'Atendimento\Admin\ArtigosFormController',
                      controllerAs: 'tndmnt_dmn_rtgs_frm_cntrllr'
                    })
                    .state('atendimento_admin_artigo_estatisticas', {
                      parent: 'atendimento_admin_artigos',
                      url: "/:artigo/estatisticas",
                      templateUrl: nsjRoutingProvider.$get().generate('template_estatisticasartigo_modal'),
                      controller: 'Atendimento\Admin\ArtigosEstatisticasController',
                      controllerAs: 'estatisticas'
                    })
                    .state('atendimento_admin_artigos_edit', {
                      parent: 'atendimento_admin_artigos',
                      url: "/:artigo/edit",
                      templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_artigos_template_form'),
                      controller: 'Atendimento\Admin\ArtigosFormController',
                      controllerAs: 'tndmnt_dmn_rtgs_frm_cntrllr'
                    });
          }])
        .factory('AtendimentoAdminArtigosOver', ['$http', 'nsjRouting', '$rootScope', function ($http, nsjRouting, $rootScope) {
            var self = {
              delete: function (constructors, identifier) {
                return $http
                        .delete(nsjRouting.generate('atendimento_admin_artigos_delete', angular.extend(constructors, {'id': identifier}), false))
                        .then(function (response){
                            $rootScope.$broadcast("atendimento_admin_artigos_deleted", {
                                response : response
                            });
                        });
              },
              setStatus: function (artigo, status) {
                status = (typeof(status) === 'undefined') ? artigo.status : !!status ;
                return $http({
                  method: "PUT",
                  url: nsjRouting.generate('atendimento_admin_artigos_put_status', {id: artigo.artigo}, false),
                  data: {
                    status: status
                  }
                }).then(function(response){
                  if(response.statusText === 'OK') {
                    $rootScope.$broadcast("atendimento_admin_artigos_publish_change", {
                      status : status
                    });
                  }
                });
              }
            };

            return self;
          }])
        .factory('Categorias', ['$http', 'nsjRouting', 'PageTitle', '$stateParams',
            function ($http, nsjRouting, PageTitle, $stateParams) {
              return {
                load: function(){
                  var params = { };

                  // Passa a realizar a busca, coerentemente com o tipo de filtro a ser realizado.

                  // Busca todas as categorias
                  if (!$stateParams.categoria && !$stateParams.subcategoria && !$stateParams.secao) {
                    params.tipo = 1;

                  // Busca todas as subcategorias
                  } else if ($stateParams.categoria) {
                    params.tipo = 2;
                    params.categoriapai = $stateParams.categoria;

                  // Busca todas as seções
                  } else if ($stateParams.subcategoria) {
                    params.tipo = 3;
                    params.categoriapai = $stateParams.subcategoria;
                  }

                  var request = {
                      "method": "GET",
                      "url": nsjRouting.generate('atendimento_cliente_categorias_index', params, false)
                  };
                  
                  return $http(request);
                }
              };
            }])
        .controller('AtendimentoAdminArtigosListController', [
          '$location','$scope', '$stateParams', 'nsjRouting', 'AtendimentoAdminArtigos', 
          'Categorias',  '$ngRedux', 'artigosAdminActions', 'PageTitle', 'hotkeys', '$state', '$http', '$rootScope',
          function ($location, $scope, $stateParams, nsjRouting, AtendimentoAdminArtigos, 
            Categorias, $ngRedux, artigosAdminActions, PageTitle, hotkeys, $state, $http, $rootScope) {
            var self = this;
            self.busy = false;
            self.finished = false;
            self.filter = AtendimentoAdminArtigos.filter;
            self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
            self.filter['filterfield'] = 'all';
            // Não faz mais a requisição por esse load global.
            // self.todasCategorias = Categorias.load();
            /*
              Motivação:
              Essa funcionalidade (todos esses scripts) rodam sobre uma estrutura de Entidade Pai -> Filho por conta dos states consumirem o mesmo parent.
              Sendo assim, para cada requisição, ou para cada vez que esse controller é criado, são realizadas duas requisições,
              uma para o parent e outra para o child, o que sempre ocasiona uma "duplicação" na requisição e uma lentidão muito grande.

              E embora tenham o mesmo parent, cada instância desse controller é única, ou seja, não é possível simplesmente criar
              um objeto e aproveitá-lo na próxima requisição.

              Para simplificar esse aproveitamento, passou-se a armazenar o resultado da requisição no $rootScope.
            */            
            self.categorias= [];
            self.tags = $stateParams['tags'];
            self.listaCategorias = [];
            self.listaCategoriasNomes = [];
            PageTitle.setTitle("Base de Conhecimento");
            self.menuOrdenacaoAtiva = 'desc';
            self.menuStatusAtivo = 'todos';
            self.atendimentoAdminArtigos = AtendimentoAdminArtigos;
            self.entity = undefined;
            self.isLoadingArticles = false;
            self.colunas = {};
            self.artigos = [];            

            if ($location.path() === "/artigos") {
                $state.go('atendimento_admin_artigos_list');
            };

            self.carregaArtigos = function(secao) {
                self.isLoadingArticles = true;
                self.busy = true;
                self.atendimentoAdminArtigos.busy = true;

                self.atendimentoAdminArtigos.busy = true;

                // Monta o filtro para fazer a requisição caso venha com o $stateParams de busca.
                var filtro = $stateParams['busca'] ? { filterfield: 'all', key: $stateParams['busca'] } : self.filter;

                AtendimentoAdminArtigos._load({ secao: secao.categoria, orderfield: secao.tipoordenacao }, false, filtro).then(function(response){
                    self.entity.artigos = response;

                    // Seta os artigos que são exibidos caso o usuário faça uma busca.
                    self.artigos = self.artigos.length ? self.artigos : response;
                }).finally(function() {
                  self.busy = false;
                  self.atendimentoAdminArtigos.busy = false;
                  self.isLoadingArticles = false;

                });
            };

            self.loadEntity = function() {
              if ($stateParams.categoria) {
                  self.entity = self.listaCategorias.filter(function(cat,idx){
                      var result = cat.categoria === $stateParams.categoria;
                      if (result) {
                          cat.isCollapsed = 0;
                      }
                      return result;
                  })[0];
                  return;
              }
              if ($stateParams.subcategoria) {
                  var subC = self.subcategorias.filter(function(sub){return sub.categoria === $stateParams.subcategoria})[0];
                  if (subC) {
                      var catPai = self.listaCategorias.filter(function(cat){return cat.categoria === subC.categoriapai.categoria})[0];
                      self.entity = catPai.subcategorias.filter(function(sub){return sub.categoria === $stateParams.subcategoria})[0];
                  }
                  return;
              }
              if ($stateParams.secao) {
                  self.entity = self.secoes.filter(function(sec){return sec.categoria === $stateParams.secao})[0];
                  self.carregaArtigos(self.entity);
                  return;
              }              
              if(!$stateParams.secao && !$stateParams.categoria && !$stateParams.subcategoria && $stateParams.busca) {
                self.filter.key = $stateParams.busca;
                $http.get(nsjRouting.generate('atendimento_admin_artigos_index', { filter: self.filter }, false))
                  .then(function(response) {
                    self.artigos = response.data;
                    self.atendimentoAdminArtigos.busy = false;
                  });
              }
              
            };

            self.todasCategorias = { };
            self.todasCategorias.busy = true;

            // Função criada para carregar (com uma requisição separada) somente as seções,
            // pois essa requisição traz o somatório da quantidade de artigos publicados, não publicados etc.
            self.carregarSubcategorias = function() {

              // Caso esteja sendo filtrado pela subcategoria
              if ($stateParams.subcategoria) {

                // Faz uma requisição para buscar as seções
                $http.get(nsjRouting.generate('atendimento_cliente_categorias_index', { tipo: 3, categoriapai: $stateParams.subcategoria }, false))
                .then(function(response) {

                  // Flag para ser usada no every
                  var continua = true;

                  // Utiliza o every ao invés do forEach para ter uma condição de break.
                  self.listaCategorias.every(function(cat) {
                    cat.subcategorias.every(function(sub) {
                      if (sub.categoria == $stateParams.subcategoria) {
                        sub.secoes = response.data;

                        continua = false;
                        return false;
                      }

                      return true;
                    });

                    return continua;
                  });

                }).catch(function(){
                  // Caso nao carregue as categorias
                }).finally(function() {
                    if (AtendimentoAdminArtigos.constructors.tags || AtendimentoAdminArtigos.constructors.busca) {
                        AtendimentoAdminArtigos.busy = true;
                        AtendimentoAdminArtigos._load(AtendimentoAdminArtigos.constructors, 
                                                      AtendimentoAdminArtigos.after, 
                                                      AtendimentoAdminArtigos.filter)
                        .then(function(response){
                            AtendimentoAdminArtigos.busy = false;
                            self.artigos = response
                        });
                    }
                });
              }

              self.todasCategorias.busy = false;
            }

            // Se o $rootScope já tiver armazenados as subcategorias e seções, faz o reaproveitamento, para não precisar recarregar.
            if ($rootScope.categorias || $rootScope.subcategorias || $rootScope.secoes) {
              self.categorias = $rootScope.categorias;
              self.subcategorias = $rootScope.subcategorias;
              self.secoes = $rootScope.secoes;
            }

            // Se o $rootScope já tiver armazenado a lista de Categorias, faz o reaproveitamento, para não precisar recarregar.
            if ($rootScope.listaCategorias) {

              $rootScope.listaCategorias.forEach(function(cat, i) {
                if (cat.nodes) {
                  cat.nodes.forEach(function(sub, ii) {
                    if (sub.nodes) {

                      sub.nodes.forEach(function(secao, iii) {
                        secao.categoria = secao.categoria || secao.id;
                        secao.ordem = iii;
                        secao.isCollapsed = (secao.isCollapsed != undefined) ? secao.isCollapsed : true;
                        
                        delete secao.fechada;
                      });

                      sub.categoria = sub.categoria || sub.id;
                      sub.ordem = ii;

                      delete sub.fechada;

                      sub.secoes = sub.nodes;
                      delete sub.nodes;

                      sub.exibir = true;
                      sub.isCollapsed = (sub.isCollapsed != undefined) ? sub.isCollapsed : true;
                      sub.qtdfilhos = sub.secoes.length;
                    }
                  });

                  cat.ordem = i;

                  cat.categoria = cat.categoria || cat.id;

                  delete cat.fechada;

                  cat.subcategorias = cat.nodes;
                  delete cat.nodes;

                  cat.exibir = true;
                  cat.isCollapsed = (cat.isCollapsed != undefined) ? cat.isCollapsed : true;
                  cat.qtdfilhos = cat.subcategorias.length;
                }
              });

              self.listaCategorias = self.listaCategoriasNomes = $rootScope.listaCategorias;

              self.carregarSubcategorias();

              self.loadEntity();

            } else { // Senão, faz a requisição para buscar todas as categorias.
            
              $http.get(nsjRouting.generate('atendimento_cliente_categorias_index', { buscarApenasCategorias: true }, false))
              .then(function(response) {

                // Subdivide os resultados em categorias, subcategorias e seções, para renderizar na index.
                self.todasCategorias.busy = false;
                self.categorias = response.data.filter(function(cat){ return cat.tipo === 1;  });
                self.subcategorias = response.data.filter(function(cat){ return cat.tipo === 2;  });
                self.secoes = response.data.filter(function(cat){ return cat.tipo === 3;  });

                // Faz um map para agrupar as respectivas categorias, subcategorias e seções.
                self.categorias.map(function(cat){
                  var subcategorias = [];
                    
                  self.subcategorias.map(function(sub){
                    if(sub.categoriapai.categoria === cat.categoria){
                      sub.secoes = [];
                      self.secoes.map(function(sec) {
                        if(sec.categoriapai.categoria === sub.categoria){
                            sub.secoes.push(sec);
                        }
                      });
                        sub.exibir = true;
                        sub.isCollapsed = false;
                        sub.qtdfilhos = sub.secoes.length;
                        subcategorias.push(sub);
                    }
                  });

                  cat.exibir = true;
                  cat.subcategorias = subcategorias;
                  cat.isCollapsed = false;
                  cat.qtdfilhos = cat.subcategorias.length;
                  self.listaCategoriasNomes.push(cat);
                });  

                // Seta para o $rootScope global, as categorias, subcategorias e seções carregadas, para reaproveitamento,
                // para que não seja necessário realizar outra requisição.
                $rootScope.listaCategorias = self.listaCategorias = self.listaCategoriasNomes;
                $rootScope.categorias = self.categorias;
                $rootScope.subcategorias = self.subcategorias;
                $rootScope.secoes = self.secoes;

                self.loadEntity();
            
                self.carregarSubcategorias();
              });
            }

            self.videoValido = true;

            self.colunas = {
                publicado:true,
                subcategoria:true,
                ordem:true,
                qtdSecoes:true,
                qtdSubCategorias:true,
                qtdArtigos:true,
                qtdArtigosPublicados: true,
                qtdArtigosNaoPublicados: true,
                titulo:true,
                created_at:true,
                gostaram:true,
                naogostaram:true,
                mostrarTodasColunas: function(){
                    for (key in self.colunas) {
                        typeof self.colunas[key] !== "function" ? self.colunas[key] = true : true;
                    }
                }
            };

            hotkeys.bindTo($scope).add({
              "combo": "ctrl+alt+c",
              "description": "Criar Artigo",
              "callback": function () {
                $state.go('atendimento_admin_artigos_new', {tags: null, categorias: null});
              }
            });

            var unsubscribe = $ngRedux.connect(mapStateToThis, artigosAdminActions)(self);
            $scope.$on('$destroy', unsubscribe);

            function mapStateToThis(state) {
              /*
               Utiliza tratamento do retorno para disparar a consulta 
               que busca demais artigos, após garantir que fixou os 
               primeiros no topo
               */
              // if (state.artigos.lastaction === "CARREGA_ARTIGOS_TOPO_COMPLETO") {
              //   self.carrega();
              // }
              if (state.artigos.lastaction === "CARREGA_ARTIGOS_COMPLETO" || state.artigos.lastaction === "CARREGA_MAIS_ARTIGOS_COMPLETO") {
                AtendimentoAdminArtigos.after = state.artigos.after;
              }

                AtendimentoAdminArtigos.constructors = {};

              for (var i in $stateParams) {
                if (typeof $stateParams[i] !== 'undefined' && typeof $stateParams[i] !== 'function' && i !== 'q') {
                  AtendimentoAdminArtigos.constructors[i] = $stateParams[i];
                }
              }

              return {
                lastaction: state.artigos.lastaction,
                after: state.artigos.after,
                busy: state.artigos.busyList,
                finished: state.artigos.finished,
                entities: state.artigos.artigos
              };
            }

            self.nextPage = function () {
              if (!self.finished)
                self.carregaMais();
            };

            self.entities = [];
            
            self.generateRoute = function (route, params) {
              return nsjRouting.generate(route, params);
            };

            self.isBusy = function () {
              return self.busy;
            };

            self.buscar = function() {                
                $state.go('.',{busca: self.filter.key})                
                self.busca('key');
            }

            self.filterOrder = function (order) {
              self.busy = true;
              if (order === 'desc') {
                AtendimentoAdminArtigos.constructors.order = 'desc';
                AtendimentoAdminArtigos.constructors.orderfield = 'created_at';
              } else if (order === 'asc') {
                AtendimentoAdminArtigos.constructors.order = 'asc';
                AtendimentoAdminArtigos.constructors.orderfield = 'created_at';
              }

              self.menuOrdenacaoAtiva = order;
              self.busca();
              self.busy = false;
            };

            self.filterStatus = function (status) {
              self.busy = true;

              switch (status) {
                case 'todos':
                  delete AtendimentoAdminArtigos.constructors.status ;
                  break;
                case 'publicados':
                  AtendimentoAdminArtigos.constructors.status = '1';
                  break;
                case 'rascunho':
                  AtendimentoAdminArtigos.constructors.status = '0';
                  break;
              }

              self.menuStatusAtivo = status;
              self.busca();
              self.busy = false;
            };
            
            $scope.$on('atendimento_admin_artigos_list_finished',function(event,data){
                // self.atualizaListaArtigos(data);
                self.artigos = data;
            });

            if ($stateParams.busca) {
                self.filter.key = $stateParams.busca;
                var inputSerarch = document.getElementById("txtBuscaArtigos");
                if (inputSerarch) inputSerarch.focus(); //Solução do bug tarefa: 28258.
                self.atendimentoAdminArtigos.busy = true;
            }
           
            $scope.$on('atendimento_admin_artigos_submitted',function(event,data){
                self.busca('key');
            }); 
            
            $scope.$on('atendimento_admin_artigos_deleted',function(event,data){
                self.busca('key');//Atualiza a lista
            });

            self.getCategoriaPai = function(categoria,key){
                var result;
                if(self.subcategorias){
                  self.subcategorias.map(function(cat){
                      if (categoria && categoria.categoria === cat.categoria){
                          if (key === 'titulo') {
                              result = cat.categoriapai.titulo;
                          }else {
                              result = cat.categoriapai.categoria;
                          } 
                      }
                  });
                }
                return result;
            };

            self.selecionaCategoria = function(cat,idx){
                self.entity = cat;
                cat.isCollapsed = false;
                if (cat.tipo === 1) {
                    $state.go('atendimento_admin_artigos_list',{categoria:cat.categoria},{inherit:false})
                    self.atendimentoAdminArtigos.constructors = {categoria:cat.categoria};
                } else if (cat.tipo === 2) {
                    $state.go('atendimento_admin_artigos_list',{subcategoria:cat.categoria},{inherit:false})
                    self.atendimentoAdminArtigos.constructors = {subcategoria:cat.categoria};
                } else if (cat.tipo === 3) {
                    self.carregaArtigos(cat);
                    $state.go('atendimento_admin_artigos_list',{secao:cat.categoria},{inherit:false})
                    self.atendimentoAdminArtigos.constructors = {secao:cat.categoria};
                } else {
                    $state.go('atendimento_admin_artigos_list',{})
                }
            };

            self.limparTags = function(){
                delete self.artigos
                $state.go('.',{tags:null});
            }
          }])
        .controller('AtendimentoAdminArtigosFormController', [
          'nsjRouting', '$scope', '$stateParams', '$state', '$sce', 'toaster', '$http', '$q', 'debounce', '$uibModal',
          '$ngRedux', 'artigosAdminActions', 'hotkeys', 'AtendimentoAdminArtigos','$rootScope',
          function (nsjRouting, $scope, $stateParams, $state, $sce, toaster, $http, $q, debounce, $uibModal, $ngRedux, 
                    artigosAdminActions, hotkeys, ArtigosService, $rootScope) {
            var self = this;
            self.submitted = false;
            self.entity = {};
            self.constructors = {};
            self.conteudoTratado = "";
            self.suggestedTags = [];
            self.suggestedTagsMaxSize = 10;
            self.tagObrigatorio = nsj.globals.getInstance().get('ARTIGO_TAG_OBRIGATORIO');
            $scope.tags = [];
            self.tagInvalida = 0;

            $scope.addedTag = function(tag)
            {
              self.tagInvalida = 0;
            }

            $scope.invalidTag = function(tag)
            {
              self.tagInvalida  = tag.text.length < 3 ? 1 : 2; 
            }
            
            hotkeys.bindTo($scope).add({
              "combo": "ctrl+alt+p",
              "description": "Publicar/Despublicar Artigo",
              "callback": function () {
                if (self.entity.artigo) {
                  self.entity.status = self.entity.status === true ? false : true;
                  self.setStatus(self.entity);
                } else {
                  alert('O artigo precisa estar cadastrado para efetuar esta atualização.');
                }
              }
            }).add({
              "combo": "alt+s",
              "description": "Salvar",
              "allowIn": ['INPUT', 'SELECT', 'TEXTAREA'],
              "callback": function (event) {
                event.preventDefault();
                self.submit(event, 0);
              }
            }).add({
              "combo": "alt+p",
              "description": "Salvar e Publicar",
              "allowIn": ['INPUT', 'SELECT', 'TEXTAREA'],
              "callback": function (event) {
                event.preventDefault();
                self.submit(event, 1);
              }
            });


            var unsubscribe = $ngRedux.connect(mapStateToThis, artigosAdminActions)(self);
            $scope.$on('$destroy', unsubscribe);



            $scope.$on('atendimento_admin_artigos_publish_change', function(event, data){
              self.entity.status = data.status;
            });

            self.addSuggestedTags = function (tag) {
              if (self.entity.tags == null) {
                self.entity.tags = [];
              }
              self.entity.tags.push({'text': tag});
            };

            self.suggestTag = debounce(function () {
              var artigoTituloConteudo = self.entity.conteudo +" "+ self.entity.titulo;

              if (self.cancelRequest) {
                self.cancelRequest.resolve();
              }
              
              self.cancelRequest = $q.defer();

              self.suggestedTags = [];
              var tags = [];
              if (!artigoTituloConteudo) {
                return false;
              }
              var content = (artigoTituloConteudo.replace(/<(?:.|\n)*?>/gm, ' ')).split(" ");
              content.map(function (item) {
//              Limpa conteudo HTML
                var doc = new DOMParser().parseFromString(item, "text/html");
                doc = (doc.documentElement.textContent).replace(/[^A-zÀ-ú0-9\-]/g, '');
                if (doc.length > 2) {
                  if (!tags.some(function (el) {
                    return el === doc;
                  })) {
                    tags.push(doc);
                  }
                }
              });

              $http.post(nsjRouting.generate('atendimento_admin_artigos_tags'), {'tags': tags}, {timeout: self.cancelRequest.promise})
                      .then(function (response) {
                        response.data.map(function (item) {
                          self.suggestedTags.push(item.texto);
                        });
                      })
                      .catch(function () {

                      })
                      .finally(function () {
                        self.busy = false;
                      });
            }, 1000);

            self.filterSuggestedTag = function (con) {
              if (self.entity.tags == null) {
                return true;
              }
              return !self.entity.tags.some(function (tag) {
                if (tag.text == con) {
                  return true;
                } else {
                  return false;
                }

              });
            };

            self.abreModalArtigo = function() { 
              var modalInstance =  $uibModal.open({
                  templateUrl: nsjRouting.generate('template_artigo_modal', {}, false),
                  controller: "ArtigoModalCtrl",
                  controllerAs: 'tndmnt_rtg_mdl_ctrl',
                  size: '',
                  resolve: {
                      ArtigoId: function () {
                          return self.entity.artigo;
                      }
                  }
              });
            };

            function mapStateToThis(state) {
              var artigo = state.artigos.artigo;
              
              if (artigo.tipoexibicao == 2) {
                artigo.tipoexibicao = 1;

                var youtube = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;

                var vimeo = /^(http:\/\/|https:\/\/)?(?:www.)?(vimeo).com\/(\d{9})|(http:\/\/|https:\/\/)player.vimeo.com\/video\/(\d{9})/;
                
                var matchYotube = artigo.conteudo.match(youtube);
                var matchVimeo = artigo.conteudo.match(vimeo);

                var src = '';
                var videoId = '';

                if (matchYotube) {
                  videoId = matchYotube[1];

                  src = 'https://www.youtube.com/embed/' + videoId;

                  artigo.conteudo = '<p><iframe allow="autoplay; fullscreen;" allowfullscreen="" frameborder="0" height="360" src="' + src + '"width="640"></iframe></p>';

                } else if (matchVimeo) {
                  videoId = matchVimeo[3];

                  src = 'https://player.vimeo.com/video/' + videoId;

                  artigo.conteudo = '<p><iframe allow="autoplay; fullscreen;" allowfullscreen="" frameborder="0" height="360" src="' + src + '"width="640"></iframe></p>';
                }

                state.artigos.artigo = artigo;
              }

              if (state.artigos.lastaction === "MOSTRA_ARTIGO_COMPLETO") {
                var imgExists = state.artigos.artigo.conteudo.indexOf('<img');
                if (imgExists > 0) {
                  state.artigos.artigo.conteudo = state.artigos.artigo.conteudo.replace(/(<img("[^"]*"|[^\/">])*)\/>/gi, "$1 imgbox />");
                }
              }

              return {
                entity: state.artigos.artigo,
                conteudoTratado: (state.artigos.artigo && state.artigos.artigo.conteudo) ? $sce.trustAsHtml(state.artigos.artigo.conteudo) : '',
                busy: state.artigos.busyShow
              };
            }

            if ($stateParams['artigo']) {
              self.mostra($stateParams['artigo']);
              self.entity.status = self.entity.status || false;
              self.entity.criado_por_resposta = self.entity.criado_por_resposta || false;
            } else {
              self.entity = {};
              self.entity.status = false;
            }

            self.isBusy = function () {
              return self.busy;
            };

            self.submit = function ($event, requisicao) {
              $event.preventDefault();
              self.submitted = true;

              if (self.entity.tipoexibicao == 2 && !self.videoValido) {
                self.form.$valid = false;
              }

              if (self.form.$valid && !self.entity.submitting) {
                self.entity.status = requisicao === 0 ? self.entity.status : true;

                self.entity.fixarnotopo = false; //Removido do form 
                
                if (self.entity.tags) {
                  self.entity.tags = self.entity.tags.map(function (el) {
                    return el.text.trim();
                  });
                } else {
                  self.entity.tags = [];
                }

                if (self.entity.artigo === null || self.entity.artigo === undefined) {
                  self.entity.criado_por_resposta = false;
                  self.entity.secao = {}; 
                  self.entity.secao.categoria = $stateParams.secao;

                  ArtigosService._save(self.entity)
                    .then(function(res){
                        toaster.pop({
                            type: 'success',
                            title: 'Artigo criado com sucesso'
                        });
                        $state.go('atendimento_admin_artigos_show',{artigo: res.data.artigo});                        
                    })
                    .catch(function(erro){
                        toaster.pop({
                            type: 'error',
                            title: 'Não foi possível criar o artigo!'
                        });
                    })
                } else { //PUT
                  ArtigosService._save(self.entity)
                    .then(function(res){
                        toaster.pop({
                            type: 'success',
                            title: 'Artigo atualizado com sucesso'
                        });
                        $state.go('atendimento_admin_artigos_show',{artigo: res.data.artigo});
                    })
                    .catch(function(erro){
                        toaster.pop({
                            type: 'error',
                            title: 'Não foi possível atualizar o artigo!'
                        });
                    })
                }
              } else {
                if (self.form.$error.isUploading) {
                  alert("Aguarde o upload da imagem antes de continuar.");
                }
              }
            };

            self.delete = function (force) {
              self.deleta($stateParams['artigo'], force);
              $state.go('atendimento_admin_artigos', angular.extend(self.constructors));
            };

            self.confirmacaoCopia = function () {
              toaster.pop({
                type: 'success',
                title: 'Link copiado para área de transferência'
              });
            };

            self.abrirEstatisticas = function () {
              $state.go('atendimento_admin_artigo_estatisticas', {artigo:$stateParams['artigo']});
            };

            (self.iniciaTipoExibicao = function () {
              var tipo = self.entity.tipoexibicao != undefined ? self.entity.tipoexibicao : 1;
              self.entity.tipoexibicao = tipo; 
            })();
            
            self.notificacaoVideoInvalido = function () {
              toaster.pop({
                type: 'error',
                title: 'Link utilizado não suportado!'
              });
            };
            
            self.getTipoVideo = function (url) {
              if ( !url ) {
                return "Vazio";
              }

              var regexpYoutube = /^(http(s)?:\/\/)?((w){3}.)?youtu(be|.be)?(\.com)?\/.+/;
              var regexpVimeo = /(http|https)?:\/\/(www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|)(\d+)(?:|\/\?)/;  

              var ehYoutube = regexpYoutube.test(url); 
              if ( ehYoutube ) {
                return "youtube";    
              } else {
                  var ehVimeo = regexpVimeo.test(url); 
                  if ( ehVimeo ) {
                    return "vimeo";    
                  } else {
                      return "nao-suportado";
                  }
              }
            };

            self.getVideoIDyoutube = function (url) {
              var r, rx = /^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/;
              r = url.match(rx);
              return r[1];
            }

            self.getVideoIDvimeo = function (url) {
              var r, rx = /https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
              r = url.match(rx);
              return r[3];
            }

            self.montaFrameVideo = function (tipo, url) {

              self.videoValido = false;

              var div = document.getElementById("render-video");
                while(div.hasChildNodes()){ div.removeChild(div.firstChild )  } 

              var ifrm = document.createElement("iframe");

              if ( tipo == "youtube" ) {
                var url_embutida = "https://www.youtube.com/embed/" + self.getVideoIDyoutube(url);
                ifrm.setAttribute("src", url_embutida);
                ifrm.setAttribute("allow", "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture");
                ifrm.setAttribute("allowfullscreen", ""); 
                ifrm.setAttribute("width", "100%");
                ifrm.setAttribute("height", "415px");
                ifrm.setAttribute("frameborder", "0");
                document.getElementById("render-video").appendChild(ifrm);   

                self.videoValido = true;

              } else if ( tipo == "vimeo" ) {
                var url_embutida = "https://player.vimeo.com/video/" + self.getVideoIDvimeo(url);
                ifrm.setAttribute("width", "100%");
                ifrm.setAttribute("height", "415px");
                ifrm.setAttribute("src", url_embutida);
                ifrm.setAttribute("frameborder", "0");
                ifrm.setAttribute("allow", "autoplay; fullscreen");
                ifrm.setAttribute("allowfullscreen", ""); 
                document.getElementById("render-video").appendChild(ifrm);

                self.videoValido = true;

              } else {
                
                self.videoValido = true;

                if ( tipo == "nao-suportado" ) {

                  self.videoValido = false;

                  self.notificacaoVideoInvalido();  
                }

              }

            }

            self.renderizarVideo =  debounce(function () {
                var tipoVideo = self.getTipoVideo(self.entity.conteudo);      
                self.montaFrameVideo(tipoVideo, self.entity.conteudo);  
            },1000);


          }])
        .controller('AtendimentoAdminArtigosEstatisticasController', ['$scope', '$state', '$http', 'nsjRouting', '$stateParams', 'nsjRouting', function ($scope, $state, $http, nsjRouting, $stateParams, nsjRouting) {
            var self = this;
            self.busy = false;
            self.gostaram = [];
            self.naoGostaram = [];
            self.entity = {};
            self.total = {gostaram: "-", naoGostaram: "-", respostasChamados: "-", respostasImediatas: "-"};

            $http.get(nsjRouting.generate('atendimento_admin_estatisticasartigo_gerar_relatorio', {'artigo': $stateParams['artigo']}, false)).then(function (response) {
              self.gostaram = response.data.filter(function (item) {
                return item.util === true && item.comentario !== '';
              });

              self.naoGostaram = response.data.filter(function (item) {
                return item.util === false && item.comentario !== '';
              });

              $http.get(nsjRouting.generate('atendimento_admin_estatisticasartigo_gerar_totalizadores', {'artigo': $stateParams['artigo']}, false)).then(function (response) {
                self.total.gostaram = response.data.gostaram;
                self.total.naoGostaram = response.data.naogostaram;
                self.total.respostasChamados = response.data.respostaschamados;
                self.total.respostasImediatas = response.data.respostasimediatas;
              }).finally(function () {
                self.busy = false;
              });
            });

            self.isBusy = function () {
              return self.busy;
            };

            self.voltar = function () {
              $state.go('atendimento_admin_artigos_show', {'artigo': $stateParams['artigo']});
            };

          }])
          .controller(
            'ArtigoModalCtrl',['$uibModalInstance','AtendimentoAdminArtigos','ArtigoId','toaster','Categorias','$state',
            function($uibModalInstance, ArtigosAdminService, ArtigoId, toaster, Categorias, $state)
        {
            var self = this;
            self.artigo = null;
            self.listaCategorias = [];
            self.todasCategorias = Categorias.load();
            
            ArtigosAdminService.get(ArtigoId).then(function(res) {
                self.artigo = res;
                self.loadCategorias();
            })

            self.loadCategorias = function(){
                self.todasCategorias.then(function(response) {                
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
                })
            }


            self.salvar = function(){

                var secao = self.selected.subcategorias.selected.secoes.selected.categoria;
                self.artigo.secao.categoria = secao;

                ArtigosAdminService._save(self.artigo)
                    .then(function(res){
                        toaster.pop({
                            type: 'success',
                            title: 'Artigo movido com sucesso'
                        });
                        $uibModalInstance.close("Ok");
                        self.cancel();
                    })
                    .catch(function(erro){
                        toaster.pop({
                            type: 'error',
                            title: 'Não foi possível mover o artigo!'
                        });
                    })
            }
                
            self.cancel = function(){
                $uibModalInstance.dismiss();
            } 
            
        }])
        ;
