angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                $stateProvider
                        .state('atendimento_cliente_artigos', {
                            url: "/artigos?categoria?q?tags",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_artigos_template'),
                            controller: 'AtendimentoClienteArtigosListController',
                            controllerAs: 'tndmnt_clnt_rtgs_lst_cntrllr'
                        })
                        .state('atendimento_cliente_artigos_show', {
                            url: "/artigos/:artigo",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_artigos_template_show'),
                            controller: 'AtendimentoClienteArtigosShowController',
                            controllerAs: 'tndmnt_clnt_rtgs_frm_cntrllr'
                        });
            }])
        .factory('AtendimentoClienteArtigosUteis', ['$http', 'nsjRouting',
            function ($http, nsjRouting) {
                return {
                    cadastrar: function (artigo, util, justificativa) {
                        var request = {
                            "method": "POST",
                            "url": nsjRouting.generate('atendimento_cliente_artigosuteis_create', {"artigo": artigo}, false),
                            "data": {"util": util, "justificativa": justificativa}
                        };
                        $http(request);
                    }
                };
            }])
        .factory('Categorias', ['$http', 'nsjRouting', 'PageTitle', 
            function ($http, nsjRouting, PageTitle) {
              return {
                load: function(tipo, data){
                  var params ;
                  var request = {
                      "method": "GET",
                      "url": nsjRouting.generate('atendimento_cliente_categorias_index', { "status": 1  } , false)
                  };
                  
                  return $http(request);
                },
                busy: true
              };
            }])
        .factory('ListaArtigos', ['$http', 'nsjRouting',
          function($http, nsjRouting){
            return {
              load: function(parametros){
                var request = {
                  "method": 'GET',
                  "url": nsjRouting.generate('atendimento_cliente_categorias_artigos', parametros, false)
                };
                return $http(request);
              }
            };
        }])
        .controller('AtendimentoClienteArtigosListController', [
            '$scope', '$stateParams', 'AtendimentoClienteArtigos', 'Categorias', 'ListaArtigos', 'PageTitle', '$http','debounce', '$state',
            function ($scope, $stateParams, AtendimentoClienteArtigos, Categorias, ListaArtigos, PageTitle, $http, debounce, $state) {
                var self = this;
                self.busy = false;
                self.artigoDescricao = nsj.globals.getInstance().get('ARTIGO_DESCRICAO');
                self.filter = AtendimentoClienteArtigos.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.categorias = [];
                self.tags = $stateParams['tags'];
                self.subCategorias = [];
                self.secoes = [];
                self.todasCategorias = Categorias.load(null);
                self.listaSubCategorias = [];
                self.entities = $stateParams['q'] ? AtendimentoClienteArtigos.search('key') : [];
                self.artigos = [];
                self.listaUltimosArtigosAtualizados = {};
                self.placeholderBuscaArtigos = nsj.globals.getInstance().get('PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE');
                PageTitle.setTitle("Base de Conhecimento");
                
                self.search = AtendimentoClienteArtigos.search;
                self.reload = AtendimentoClienteArtigos.reload;
                self.nextPage = AtendimentoClienteArtigos.load;
                self.loadMore = AtendimentoClienteArtigos.loadMore;
                self.service = AtendimentoClienteArtigos;
                self.constructors = AtendimentoClienteArtigos.constructors = [];
                
                
                self.categoriaSelecionada = {};
                AtendimentoClienteArtigos.constructors = {};
                for(var i in $stateParams){
                  if(typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q' && i != 'artigo'){
                      AtendimentoClienteArtigos.constructors[i] = $stateParams[i];
                  }
                }
                self.pesquisaArtigos = debounce(function () {
                    $state.go('atendimento_cliente_artigos', { q :  self.filter.key });
                }, 1000);
                
                self.carregaArtigos = function (secao,indexSecao){
                    if (secao.artigos) return;
                    var request = {categoriapai: secao.categoria, tipoCategoria: 3, tipoordenacao: secao.tipoordenacao};
                    ListaArtigos.load(request).then(function(response){
                        self.listaSubCategorias[secao.indexSubCategoria].secoes[indexSecao].artigos = response.data;
                    });
                };
                
                self.todasCategorias.then(function(response){
                  self.categorias = response.data.filter(function(cat){ return cat.tipo === 1;  });
                  self.subcategorias = response.data.filter(function(cat){ return cat.tipo === 2;  });
                  self.secoes = response.data.filter(function(cat){ return cat.tipo === 3;  });
                  
                  if($stateParams['categoria']){
                  self.categoriaSelecionada.id = $stateParams['categoria'];
                  self.busy = true;
                  self.artigos = ListaArtigos.load({ 'categoriapai': self.categoriaSelecionada.id}).then(function(response){
                    self.subcategorias.map(function(sub){
                      if(sub.categoriapai.categoria === self.categoriaSelecionada.id){
                        var secoes = [];
                        self.secoes.map(function(sec){
                          var artigos = [];
                          if(sec.categoriapai.categoria === sub.categoria){
                            response.data.map(function(artigo){
                              if(sec.categoria === artigo.secao)
                                  artigos.push(artigo);
                            });
                            secoes.push(sec);
                          }
                        });
                        sub.secoes = secoes;
                        self.listaSubCategorias.push(sub);
                      }
                    });
                    
                    self.busy = false;
                  }).catch(function(){
                    //não foi possivel carregar os artigos
                  });
                    
                    self.categorias.map(function(cat){
                      if(cat.categoria === self.categoriaSelecionada.id){
                        self.categoriaSelecionada.titulo = cat.titulo;
                      }
                    });
                  };
                  
                  Categorias.busy = false;
                  
                });
                
                if($stateParams.tags){
                    self.entities = AtendimentoClienteArtigos.reload();
                    self.tags = $stateParams.tags;
                }

                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };
                self.isBusy = function(){
                    return Categorias.busy || AtendimentoClienteArtigos.busy || self.busy;
                };

                AtendimentoClienteArtigos._load({categoria: $stateParams['categoria'] ? $stateParams['categoria'] : null, campoordenacao: 'updated_at', tipoordenacao: 'DESC', limite: '6'}, {}, {}).then(function(response){ 
                  self.listaUltimosArtigosAtualizados = response;
                });
                
            }])
        .controller('AtendimentoClienteArtigosShowController', [
            'nsjRouting', '$scope', '$stateParams', '$sce', 'PageTitle', '$ngRedux', 'artigosClienteActions', 'AtendimentoClienteArtigosUteis', 'UsuarioLogado',
            function (nsjRouting, $scope, $stateParams, $sce, PageTitle, $ngRedux, artigosClienteActions, AtendimentoClienteArtigosUteis, UsuarioLogado) {

                var self = this;
                self.gosteiClass = "default";
                self.naoGosteiClass = "default";
                self.submitted = false;
                self.constructors = {};
                self.conteudoTratado = "";
                self.justificativa = "";
                self.jaVotou = true;
                self.isAnonimous = UsuarioLogado.isAnonimous;

                var unsubscribe = $ngRedux.connect(mapStateToThis, artigosClienteActions)(self);
                $scope.$on('$destroy', unsubscribe);

                window.updateMeOnError = function(image) {
                  if (!image) {
                    return;
                  }

                  image.classList.add("image-style-error");
                }

                function mapStateToThis(state) {

                    if (state.artigos.artigo.titulo !== undefined) {
                        PageTitle.setTitle('Artigo - ' + state.artigos.artigo.titulo);
                    }
                    
                    if (state.artigos.artigo.artigoutil !== undefined && state.artigos.lastaction === 'MOSTRA_ARTIGO_COMPLETO') {
                        var votoUsuarioLogado = [];
                        votoUsuarioLogado = state.artigos.artigo.artigoutil.filter(function (au) {
                            var created_by = JSON.parse(au.created_by);
                            return created_by.email === nsj.globals.getInstance().get('usuario')['email'];
                        });
                        
                        self.jaVotou = votoUsuarioLogado.length > 0 ? true : false;
                        
                        var imgExists = state.artigos.artigo.conteudo.indexOf('<img');
                        if (imgExists > 0) {
                            state.artigos.artigo.conteudo = state.artigos.artigo.conteudo.replace(/(<img("[^"]*"|[^\/">])*)\/>/gi, "$1 imgbox onerror=\"updateMeOnError(this);\" />");
                        }
                    }
                    
                    return {
                        entity: state.artigos.artigo,
                        conteudoTratado: (state.artigos.artigo && state.artigos.artigo.conteudo) ? $sce.trustAsHtml(state.artigos.artigo.conteudo) : '',
                        busy: state.artigos.busyShow
                    };
                }
                ;

                if ($stateParams['artigo']) {
                    self.mostra($stateParams['artigo']);
                }

                self.votar = function (flag) {
                    self.voto = flag;
                    self.gosteiClass = self.voto ? "btn-success" : "btn-default";
                    self.naoGosteiClass = self.voto ? "btn-default" : "btn-danger";
                };

                self.enviarVoto = function () {
                    AtendimentoClienteArtigosUteis.cadastrar(self.entity.artigo, self.voto, self.justificativa);
                    self.finalizouPesquisa = true;
                };

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.isBusy = function () {
                    return self.busy;
                };

                self.getTipoVideo = function (url) {
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
                  var div = document.getElementById("render-video");
                  while(div.hasChildNodes()){ div.removeChild(div.firstChild )  } 
    
                  var ifrm = document.createElement("iframe");
    
                  if ( tipo == "youtube" ) {
                    var url_embutida = "https://www.youtube.com/embed/" + self.getVideoIDyoutube(url);
                    ifrm.setAttribute("src", url_embutida);
                    ifrm.setAttribute("allow", "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture");
                    ifrm.setAttribute("allowfullscreen", ""); 
                    ifrm.setAttribute("width", "50%");
                    ifrm.setAttribute("height", "515px");
                    ifrm.setAttribute("frameborder", "0");
                    document.getElementById("render-video").appendChild(ifrm);   
    
    
                  } else if ( tipo == "vimeo" ) {
                    var url_embutida = "https://player.vimeo.com/video/" + self.getVideoIDvimeo(url);
                    ifrm.setAttribute("width", "100%");
                    ifrm.setAttribute("height", "415px");
                    ifrm.setAttribute("src", url_embutida);
                    ifrm.setAttribute("frameborder", "0");
                    ifrm.setAttribute("allow", "autoplay; fullscreen");
                    ifrm.setAttribute("allowfullscreen", ""); 
                    document.getElementById("render-video").appendChild(ifrm);    
    
                  } 
    
                }
    
                self.renderizarVideo =  function () {
                  var tipoVideo = self.getTipoVideo(self.entity.conteudo);      
                  self.montaFrameVideo(tipoVideo, self.entity.conteudo);  
                };

            }]);          