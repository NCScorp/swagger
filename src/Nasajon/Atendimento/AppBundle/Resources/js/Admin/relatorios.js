angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'clientes': ['NsClientes', '$stateParams', function (NsClientes, $stateParams) {
                            return NsClientes.load();
                    }]
                };

                $stateProvider
                        .state('atendimento_admin_relatorios', {
                            abstract: true,
                            url: "/relatorios",
                            templateUrl: nsjRoutingProvider.$get().generate('template_relatorios_index')
                        })
                        .state('atendimento_admin_relatorios_performance', {
                            parent: 'atendimento_admin_relatorios',
                            url: "/performance",
                            templateUrl: nsjRoutingProvider.$get().generate('template_relatorios_performance'),
                            controller: 'AdminRelatoriosPerformanceController',
                            controllerAs: 'dmn_rltrs_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_admin_relatorios_resposta', {
                            parent: 'atendimento_admin_relatorios',
                            url: "/resposta",
                            templateUrl: nsjRoutingProvider.$get().generate('template_relatorios_resposta'),
                            controller: 'AdminRelatoriosRespostaController',
                            controllerAs: 'dmn_rltrs_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_admin_relatorios_baseconhecimento', {
                            parent: 'atendimento_admin_relatorios',
                            url: "/baseconhecimento",
                            templateUrl: nsjRoutingProvider.$get().generate('template_relatorios_baseconhecimento'),
                            controller: 'AdminRelatoriosBaseConhecimentoController',
                            controllerAs: 'dmn_rltrs_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_admin_relatorios_backlog', {
                            parent: 'atendimento_admin_relatorios',
                            url: "/backlog",
                            templateUrl: nsjRoutingProvider.$get().generate('template_relatorios_backlog'),
                            controller: 'AdminRelatoriosBacklogController',
                            controllerAs: 'dmn_rltrs_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_admin_relatorios_termos', {
                            parent: 'atendimento_admin_relatorios',
                            url: "/termos",
                            templateUrl: nsjRoutingProvider.$get().generate('template_relatorios_termos'),
                            controller: 'AdminRelatoriosTermosController',
                            controllerAs: 'dmn_rltrs_cntrllr',
                            resolve: resolve
                        });
            }])
        .controller('AdminRelatoriosBaseConhecimentoController', ['$http', '$q', '$scope', 'nsjRouting', '$uibModal', 'PageTitle', 'Contas', 'AtendimentoCategorias', 'debounce', 'UsuarioComEquipe',
            function ($http, $q, $scope, nsjRouting, $uibModal, PageTitle, Contas, AtendimentoCategorias, debounce, UsuarioComEquipe) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                PageTitle.setTitle("Base de Conhecimento");
                self.total = {"artigos": "-", "gostaram": "-", "naoGostaram": "-", "respostasChamados": "-", "respostasImediatas": "-" };
                self.filtros = [];
                self.tableData = [];
                self.busy = false;
                self.finished = false;
                self.loadingMore = false;
                self.opcoesSecao = [];
                self.opcoesSubCategoria = [];
                self.opcoesCategoria = [];
                self.opcoesContas = [];
                self.listaAvaliacoes = [];

                self.opcoesFiltro = {
                    'categoria': {
                        'label': 'Categoria',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesCategoria
                            },
                            'is_not_equal': {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesCategoria
                            }
                        }
                    },
                    'subcategoria': {
                        'label': 'Sub Categoria',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesSubCategoria
                            },
                            'is_not_equal': {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesSubCategoria
                            }
                        }
                    },
                    'secao': {
                        'label': 'Seção',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesSecao
                            },
                            'is_not_equal': {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesSecao
                            }
                        }
                    },
                    'criador': {
                        label: 'Criador',
                        options: {
                            is_equal: {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesContas
                            },
                            is_not_equal: {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesContas
                            }
                        }
                    },
                    'criado_por_resposta': {
                        'label': 'Criados por resposta',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': [{'label': 'Sim', 'value': true}, {'label': 'Não', 'value': false}]
                            }
                        }
                    },
                    'titulo_artigo': {
                        'label': 'Título do Artigo',
                        'options': {
                            'includes': {
                                'label': 'Inclui',
                                'type': 'text'
                            }
                        }
                    },
                    'data_publicacao': {
                        'label':'Data de publicação',
                        'options': {
                            is_equal: {
                                'label': 'É igual a',
                                'type':'datepicker'
                            },
                            is_between: {
                                'label': 'Está entre',
                                'type':'datepickerDuplo'
                            },
                            is_greater:{
                                label : 'É maior que',
                                type : 'datepicker'
                            },
                            is_smaller: {
                                label : 'É menor que',
                                type : 'datepicker'
                            },
                            is_null: {
                                label : 'Não publicado',
                                type : ''
                            }
                        }
                    },
                    'data_criacao': {
                        'label':'Data de criação',
                        'options': {
                            is_equal: {
                                'label': 'É igual a',
                                'type':'datepicker'
                            },
                            is_between: {
                                'label': 'Está entre',
                                'type':'datepickerDuplo'
                            },
                            is_greater:{
                                label : 'É maior que',
                                type : 'datepicker'
                            },
                            is_smaller: {
                                label : 'É menor que',
                                type : 'datepicker'
                            }
                        }
                    },
                    data_atualizacao: {
                        label:'Data de atualização',
                        options: {
                            'is_equal': {
                                'label': 'É igual a',
                                'type':'datepicker'
                            },
                            'is_between': {
                                'label': 'Está entre',
                                'type':'datepickerDuplo'
                            },
                            is_greater : {
                                label : 'É maior que',
                                type : 'datepicker'
                            },
                            is_smaller: {
                                label : 'É menor que',
                                type : 'datepicker'
                            }
                        }
                    }
                };
                
                self.colunas = {
                    'titulo': true,
                    'categoria': true,
                    'subcategoria': true,
                    'secao': true,
                    'criador': true,
                    'dataCriacao': true,
                    'dataAtualizacao': true,
                    'dataPublicacao': true,
                    'gostaram': true,
                    'naoGostaram': true,
                    'respostasChamados': true,
                    'criadoAtravesResposta': true,
                    'respostaImediata': true,
                    'comentarios' : true,
                    'totalComentarios' : true
                };
                self.mostrarTodasColunas = function () {
                    self.colunas.titulo = true;
                    self.colunas.categoria = true;
                    self.colunas.subcategoria = true;
                    self.colunas.secao = true;
                    self.colunas.criador = true;
                    self.colunas.dataCriacao = true;
                    self.colunas.dataAtualizacao = true;
                    self.colunas.gostaram = true;
                    self.colunas.naoGostaram = true;
                    self.colunas.respostasChamados = true;
                    self.colunas.criadoAtravesResposta = true;
                    self.colunas.respostaImediata = true;
                    self.colunas.comentarios = true;
                    self.colunas.totalComentarios = true;
                };

                self.montaOpcoesCategorias = function (categorias) {
                    for (var i in categorias) {
                        
                        switch(categorias[i].tipo) {
                            case 1: // categoria
                                self.opcoesCategoria.push({
                                'label': categorias[i].titulo,
                                'value': categorias[i].categoria
                                });
                                break;
                            case 2: // sub categoria
                                self.opcoesSubCategoria.push({
                                'label': categorias[i].titulo,
                                'value': categorias[i].categoria
                                });
                                break;
                            case 3: // secao
                                self.opcoesSecao.push({
                                'label': categorias[i].titulo,
                                'value': categorias[i].categoria
                                });
                          }
                    }

                    $http.get(nsjRouting.generate('atendimento_admin_contas_index', {}, false)).then(function (response) {
                        
                        var contas = response.data;

                        for (var i in contas) {
                            self.opcoesContas.push({
                                'label': contas[i].nome,
                                'value': contas[i].email
                            });
                        }
                    });

                    self.loadData();
                    self.loadTotalizadores();
                };

                self.addFiltros = function () {
                    self.filtros.push({'campo': self.novoFiltro, 'operador': Object.keys(self.opcoesFiltro[self.novoFiltro].options)[0]});
                    self.novoFiltro = null;
                };

                self.removeFiltro = function (index) {
                    self.filtros.splice(index, 1);
                    self.reloadData();
                };

                self.validaFiltroDate = function (filtro) {
                    filtro.msgErros = [];
                    filtro.valor = moment(filtro.dtInicio).format('YYYY-MM-DD');
                    if (filtro.operador == 'is_between') {
                        if (filtro.dtInicio && filtro.dtFim) {
                            filtro.valor2 = moment(filtro.dtFim).format('YYYY-MM-DD');
                            if (filtro.dtInicio > filtro.dtFim) {
                                var msgErro = 'A data de início não pode ser maior que a data fim.';
                                filtro.msgErros.push(msgErro);
                            };

                            if (Math.abs(moment(filtro.dtInicio).diff(moment(filtro.dtFim), 'days')) > 30) {
                                var msgErro = 'A diferença máxima entre as datas deve ser 30 dias.';
                                filtro.msgErros.push(msgErro);
                            };
                        };
                    }
                    if (filtro.msgErros.length == 0) {
                        self.reloadData();
                    }
                };
                
                self.cancelarRequisicoesPendentes = function () {
                    if ($http.pendingRequests.length > 0) {
                        $http.pendingRequests.map(function (request) {
                            request.cancel.resolve("Cancelada pelo usuário");
                        });
                    }
                };

                self.loadTotalizadores = function () {
                    var filtros = {
                        'condicoes': self.filtros
                    };

                    var canceler = $q.defer();

                    var requestTotalizadores = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_baseconhecimento_gerar_totalizadores', filtros, false),
                        timeout: canceler.promise,
                        cancel: canceler
                    };

                    $http(requestTotalizadores).then(function (response) {
                        self.total.artigos = response.data.artigos;
                        self.total.gostaram = response.data.gostaram;
                        self.total.naoGostaram = response.data.naogostaram;
                        self.total.respostasChamados = response.data.respostaschamados;
                        self.total.respostasImediatas = response.data.respostasimediatas;
                    });
                };
                
                self.organizaFiltros = function(filtros){
                    var f = [];
                    filtros.forEach(function(el, i, a){
                        if(el.campo === 'cliente' && el.valor){
                        f.push({ campo: el.campo, operador: el.operador, valor: el.valor.cliente });
                        }else{
                        f.push(el);
                        }
                    });
                    return f;
                };

                self.loadData = function () {
                    if (!self.busy && !self.finished) {
                        self.busy = true;

                        var filtros = {
                            'condicoes': self.organizaFiltros(self.filtros)
                        };

                        var canceler = $q.defer();

                        var requestData = {
                            method: 'GET',
                            url: nsjRouting.generate('atendimento_admin_relatorios_baseconhecimento_gerar_relatorio', filtros, false),
                            timeout: canceler.promise,
                            cancel: canceler
                        };

                        $http(requestData)
                                .then(function (response) {
                                    if (response.data.length === 0) {
                                        self.busy = false;
                                    } else {
                                        if (response.data.length > 0) {
                                            for (var i = 0; i < response.data.length; i++) {
                                                self.tableData.push(response.data[i]);
                                            }
                                        }

                                        self.loaded = true;
                                    }
                                })
                                .finally(function () {
                                    self.busy = false;
                                    self.loadingUsuario = false;
                                });
                    }
                };

                self.reloadData = debounce(function () {
                    // Verifica se há um filtro para a data_publicacao e is_null
                    // Caso sim, seta o valor para false
                    if(self.form_filtros_relatorios['add-filtro']) {
                        if (self.form_filtros_relatorios['add-filtro'].$viewValue == 'data_publicacao' && self.form_filtros_relatorios['add-condicao'].$viewValue == 'is_null') {
                            var indice = self.filtros.indexOf(self.filtros.find(function(fltr) {
                                return fltr.campo == 'data_publicacao' && fltr.operador == 'is_null';
                            }));

                            self.filtros[indice].valor = false;
                        }
                    }

                    if (self.form_filtros_relatorios.$valid && self.usuarioComEquipe) {
                        this.cancelarRequisicoesPendentes();
                        self.tableData = [];
                        self.loadingUsuario = true;
                        self.loadTotalizadores();
                        self.loadData();
                    }
                }, 1000);

                self.alterarFiltro = function (filtro) {
                    if (filtro.campo !== undefined) {
                        var index = self.filtros.indexOf(filtro);
                        self.filtros[index] = {'campo': filtro.campo,'operador': Object.keys(self.opcoesFiltro[filtro.campo].options)[0]};
                    }
                    self.reloadData();
                };

                if (AtendimentoCategorias.loaded) {
                    self.montaOpcoesCategorias(AtendimentoCategorias.load());
                } else {
                    $scope.$on('atendimento_categorias_list_finished', function (event, data) {
                        self.montaOpcoesCategorias(data);
                    });
                    AtendimentoCategorias.load();
                }

                self.abrirModalAvaliacoes = function (entity) {
                    var request = {
                        "method": "GET",
                        "url": nsjRouting.generate('atendimento_admin_artigosuteis_get_info_artigo_util', {'artigo': entity.artigoid}, false)
                    };

                    $http(request).then(function (response) {
                        self.listaAvaliacoes = response.data;
                    });


                    self.modalInstance = $uibModal.open({
                        animation: false,
                        templateUrl: nsjRouting.generate('template_relatorios_baseconhecimento_avaliacao_modal', {}, false),
                        scope: $scope
                    });

                    self.fecharModal = function () {
                        self.listaAvaliacoes = [];
                        self.modalInstance.close();
                    };

                    self.modalInstance.result.then(function () {
                    }, function () {
                        self.listaAvaliacoes = [];
                    });
                };

            }])
        .controller('AdminRelatoriosRespostaController', ['$http', '$q', '$scope', 'nsjRouting', 'ServicosAtendimentosfilas', 'ServicosAtendimentoscamposcustomizados', 'debounce', 'AtendimentoEquipes', 'UsuarioComEquipe', '$uibModal', 'AtendimentoRepresentantetecnico', 'PageTitle',
            function ($http, $q, $scope, nsjRouting, ServicosAtendimentosfilas, ServicosAtendimentoscamposcustomizados, debounce, AtendimentoEquipes, UsuarioComEquipe, $uibModal, AtendimentoRepresentantetecnico, PageTitle) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.dtValida = true;
                self.dtInicio = moment().add(-30, 'day').toDate();
                self.dtFim = moment().toDate();
                self.filtros = [];
                self.loadingUsuario = false;
                self.opcoesUsuario = [];
                self.opcoesFila = [];
                self.opcoesEquipe = [];
                self.campocustomizadoAgrupamento = null;
                self.tableData = [];
                self.camposcustomizados = [];
                self.total = {"chamados": "-", "respostas": "-", "clientes": "-", "comentarios" : "-", "comentariosERespostas": "-"};
                self.opcoesCanal = [{'label': 'Manual', 'value': 'manual'}, {'label': 'Email', 'value': 'email'}, {'label': 'Portal', 'value': 'portal'}];
                self.opcoesRepresentanteTec = [];
                self.busy = false;
                self.resumo = null;
                self.finished = false;
                self.loadingMore = false;
                self.after = null;
                self.chamado = 0;
                self.respostaCompleta = null;
                var cancel = $q.defer();
                PageTitle.setTitle("Analítico de Respostas");
         
                self.opcoesFiltro = {
                    'cliente_resposta': {
                        'label': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select'
                            },
                            'is_not_equal': {
                                'label': 'Diferente de',
                                'type': 'select'
                            }
                        }
                    },
                    'equipe': {
                        label: 'Equipe',
                        options: {
                            is_equal: {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesEquipe
                            },
                            is_not_equal: {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesEquipe
                            }
                        }
                    },
                    'representante_tecnico': {
                        'label': 'Representante Técnico',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesRepresentanteTec
                            },
                            'is_not_equal': {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesRepresentanteTec
                            }
                        }
                    },
                    'conteudo_resposta': {
                        'label': 'Resposta',
                        'options': {
                            'includes': {
                                'label': 'Inclui',
                                'type': 'text'
                            }
                        }
                    },
                    'conteudo_resumo': {
                        'label': 'Assunto',
                        'options': {
                            'includes': {
                                'label': 'Inclui',
                                'type': 'text'
                            }
                        }
                    },
                    'usuariofollowup': {
                        'label': 'Usuário',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesUsuario
                            },
                            'is_not_equal': {
                                'label': 'Diferente de',
                                'type': 'select',
                                'options': self.opcoesUsuario
                            }
                        }
                    }
                };
                
                self.colunas = {
                    'nome': true,
                    'chamado': true,
                    'data': true,
                    'cliente': true,
                    'resposta': true,
                    'comentarios' : true,
                    'totalComentarios' : true
                };

                self.mostrarTodasColunas = function () {
                    self.colunas.nome = true;
                    self.colunas.chamado = true;
                    self.colunas.data = true;
                    self.colunas.cliente = true;
                    self.colunas.resposta = true;
                    self.colunas.comentarios = true;
                    self.colunas.totalComentarios = true;
                };

                self.addFiltros = function () {
                    self.filtros.push({'campo': self.novoFiltro});
                    self.novoFiltro = null;
                };

                self.removeFiltro = function (index) {
                    self.filtros.splice(index, 1);
                    self.reloadData();
                };

                self.fecharModal = function () {
                    self.respostaCompleta = null;
                    self.modalInstance.close();
                };

                self.montaOpcoesCamposCustomizados = function (camposcustomizados) {
                    self.camposcustomizados = camposcustomizados;

                    for (var i in self.camposcustomizados) {
                        if (self.camposcustomizados[i].tipo === 'CB') {
                            var opcoescamposcustomizados = [];

                            for (var j in self.camposcustomizados[i].opcoes) {
                                opcoescamposcustomizados.push({
                                    value: self.camposcustomizados[i].opcoes[j],
                                    label: self.camposcustomizados[i].opcoes[j]
                                });
                            }

                            self.opcoesFiltro[self.camposcustomizados[i]['atendimentocampocustomizado']] = {
                                label: self.camposcustomizados[i]['label'],
                                options: {
                                    is_equal: {
                                        label: 'É igual a',
                                        type: 'select',
                                        options: opcoescamposcustomizados
                                    },
                                    is_not_equal: {
                                        label: 'Diferente de',
                                        type: 'select',
                                        options: opcoescamposcustomizados
                                    }
                                }
                            };
                        } else {
                            self.opcoesFiltro[self.camposcustomizados[i]['atendimentocampocustomizado']] = {
                                label: self.camposcustomizados[i]['label'],
                                options: {
                                    is_set: {
                                        label: 'Existe',
                                        type: false
                                    },
                                    is_not_set: {
                                        label: 'Não existe',
                                        type: false
                                    },
                                    includes: {
                                        label: 'Inclui',
                                        type: 'text'
                                    }
                                }
                            };
                        }
                    }

                    if (camposcustomizados.length > 0) {
                        self.campocustomizadoAgrupamento = camposcustomizados[0]['atendimentocampocustomizado'];
                    }

                    self.loadData();
                    self.loadTotalizadores();
                };

                self.montaOpcoesAtendimentosfilas = function (atendimentosfilas) {
                    for (var i in atendimentosfilas) {
                        self.opcoesFila.push({
                            'label': atendimentosfilas[i].nome,
                            'value': atendimentosfilas[i].atendimentofila
                        });
                    }
                };

                self.montaOpcoesEquipes = function (equipes) {
                    for (var i in equipes) {
                        self.opcoesEquipe.push({
                            'label': equipes[i].nome,
                            'value': equipes[i].equipe
                        });
                    }
                };

                self.montaOpcoesRepresentanteTec = function (representantes) {
                    for (var i in representantes) {
                        self.opcoesRepresentanteTec.push({
                            'label': representantes[i]['nome'],
                            'value': representantes[i]['representantetecnico']
                        });
                    }
                };

                self.validaFiltroDate = function () {
                    if (self.dtInicio && self.dtFim) {
                        if (Math.abs(moment(self.dtInicio).diff(moment(self.dtFim), 'days')) > 30) {
                            self.dtValida = false;
                            return false;
                        } else {
                            self.dtValida = true;
                            return true;
                        }
                    } else {
                        return false;
                    }
                };

                self.cancelarRequisicoesPendentes = function () {

                    var totais = $http.pendingRequests.filter(function(o) { return o.tipo === 'total' });

                    var dados = $http.pendingRequests.filter(function(o) { return o.tipo === 'dados' });

                    var cancelerAux = null;

                    for (var i = 0; i < totais.length; i++) {
                        if ((cancelerAux = totais[i].cancel)) {
                            cancelerAux.resolve();
                        }
                    }

                    for (var i = 0; i < dados.length; i++) {
                        if ((cancelerAux = dados[i].cancel)) {
                            cancelerAux.resolve();
                        }
                    }

                };
                
                self.organizaFiltros = function(filtros){
                  var f = [];
                  filtros.forEach(function(el, i, a){
                    if(el.campo === 'cliente_resposta' && el.valor.cliente){
                      f.push({ campo: el.campo, operador: el.operador, valor: el.valor.cliente });
                    }
                    else 
                    if(el.campo === 'conteudo_resumo'){
                        f.push(el);
                        self.resumo = el.valor;
                      }
                      else{
                      f.push(el);
                    }
                  });
                  return f;
                };

                self.zeraFiltros = function(filtro) {
                    filtro.operador = null;
                    filtro.valor = null;
                };

                self.loadTotalizadores = function () {

                    var filtros = {
                        'datainicial': moment(self.dtInicio).format('DD/MM/YYYY'),
                        'datafinal': moment(self.dtFim).format('DD/MM/YYYY'),
                        'campocustomizado': self.campocustomizadoAgrupamento,
                        'condicoes': self.organizaFiltros(self.filtros),
                        'created_at': self.after,
                        'resumo': self.resumo
                    };

                    var cancelar = $q.defer();

                    var requestTotalizadores = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_resposta_gerar_totalizadores', filtros, false),
                        timeout: cancelar.promise,
                        cancel: cancelar,
                        'tipo': 'total'
                    };

                    $http(requestTotalizadores).then(function (response) {
                        if (response.data.length > 0) {
                            var totalChamados = 0;
                            var totalRespostas = 0;
                            var totalClientes = 0;
                            var totalComentarios = 0;
                            var totalRespostasComentarios = 0;

                            for (var i = 0; i < response.data.length; i++) {
                                totalChamados += parseInt(response.data[i].qtd_chamados);

                                if (response.data[i].qtd_respostas != null || response.data[i].qtd_respostas != undefined) {
                                    totalRespostas += parseInt(response.data[i].qtd_respostas);
                                }

                                totalComentarios += parseInt(response.data[i].qtd_comentarios);
                                totalRespostasComentarios += parseInt(response.data[i].qtd_respostas_comentarios);

                                totalClientes += parseInt(response.data[i].qtd_clientes);

                            }

                            self.total.chamados = totalChamados;
                            self.total.respostas = totalRespostas;
                            self.total.clientes = totalClientes;

                            self.total.comentarios = totalComentarios;
                            self.total.comentariosERespostas = totalRespostasComentarios;
                        } else {
                            self.total.chamados = "-";
                            self.total.respostas = "-";
                            self.total.clientes = "-";
                            self.total.comentariosERespostas = "-";
                        }
                    });
                };
                
                
                
                function obterDados() {


                    var filtros = {
                        'datainicial': moment(self.dtInicio).format('DD/MM/YYYY'),
                        'datafinal': moment(self.dtFim).format('DD/MM/YYYY'),
                        'campocustomizado': self.campocustomizadoAgrupamento,
                        'condicoes': self.organizaFiltros(self.filtros),
                        'created_at': self.after,
                        'resumo': self.resumo
                    };
                    
                    var cancelar = $q.defer();;
                    var requestData = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_resposta_gerar_relatorio', filtros, false),
                        timeout: cancelar.promise,
                        cancel: cancelar,
                        'tipo': 'dados'
                    };

                    $http(requestData).then(function (response) {

                        if (response.data.length == 0) {
                            self.after = null;
                            self.finished = true;
                            self.busy = false;

                        } else if (response.data.length > 0) {

                            // Mapeia todos os followups e seta o contador de comentários
                            // O contador de comentários é a quantidade de followups com tipo 1 referentes ao mesmo atendimento
                            response.data.map(function(follow) {

                                follow.contadorComentarios = 
                                response.data.filter(function(up) {
                                    return follow.atendimento == up.atendimento && up.tipo == 1;
                                }).length;

                            });
                            
                            for (var i = 0; i < response.data.length; i++) {
                            
                                self.tableData.push(response.data[i]);
                            }

                            self.after = self.tableData[self.tableData.length - 1]['created_at'];
                        }

                        if (response.data.length < 50 || self.total.respostas == self.tableData.length) {
                            self.finished = true;
                        }

                        if (response.data.length == 50) {
                            self.finished = false;
                        }
                        self.loaded = true;
                    }).finally(function () {
                        self.busy = false;
                        self.loadingUsuario = ($http.pendingRequests.filter(function(o) { return o.tipo === 'dados' }).length > 0);
                    });
                }

                self.loadMore = function () {
                    if (!self.busy && !self.finished) {
                        self.busy = true;                        
                        obterDados();
                    }
                };
                
                self.loadData = function () {
                    if (!self.busy && !self.finished) {
                        self.busy = true;
                        self.loadingUsuario = true;
                        obterDados();
                    }
                };
                
                self.reloadData = debounce(function () {
                    if (self.form_filtros_relatorios.$valid && self.validaFiltroDate() && self.usuarioComEquipe) {
                        this.cancelarRequisicoesPendentes();
                        self.finished = false;
                        self.tableData = [];
                        self.after = null;
                        self.loadingUsuario = true;
                        self.busy = true;
                        self.loadTotalizadores();
                        obterDados();

                    }
                }, 1000);

                self.visualizarResposta = function (data) {
                    self.chamado = data.numeroprotocolo;

                    var request = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_followups_get', {'atendimento': data.atendimento, 'id': data.followup}, false)
                    };

                    $http(request)
                    .then(function (response) {
                        self.respostaCompleta = response.data.historico;

                        self.modalInstance = $uibModal.open({
                            templateUrl: 'respostaModal',
                            scope: $scope
                        });
    
                        self.modalInstance.result.then(function () {
                        }, function () {
                            self.respostaCompleta = null;
                        });
                    });
                }

                if (ServicosAtendimentosfilas.loaded) {
                    self.montaOpcoesAtendimentosfilas(ServicosAtendimentosfilas.load());
                } else {
                    $scope.$on('servicos_atendimentosfilas_list_finished', function (event, data) {
                        self.montaOpcoesAtendimentosfilas(data);
                    });
                    ServicosAtendimentosfilas.load();
                }

                if (ServicosAtendimentoscamposcustomizados.loaded) {
                    self.montaOpcoesCamposCustomizados(ServicosAtendimentoscamposcustomizados.load());
                } else {
                    $scope.$on('servicos_atendimentoscamposcustomizados_list_finished', function (event, data) {

                        self.montaOpcoesCamposCustomizados(data);
                    });
                    ServicosAtendimentoscamposcustomizados.load();
                }

                if (AtendimentoEquipes.loaded) {
                    self.montaOpcoesEquipes(AtendimentoEquipes.load());
                } else {
                    $scope.$on('atendimento_equipes_list_finished', function (event, data) {
                        self.montaOpcoesEquipes(data);
                    });
                    AtendimentoEquipes.load();
                }

                if (AtendimentoRepresentantetecnico.loaded) {
                    self.montaOpcoesRepresentanteTec(AtendimentoRepresentantetecnico.load());
                } else {
                    $scope.$on('atendimento_representantetecnico_list_finished', function (event, data) {
                        self.montaOpcoesRepresentanteTec(data);
                    });
                    AtendimentoRepresentantetecnico.load();
                }

                $http.get(nsjRouting.generate('atendimento_admin_contas_index', {}, false)).then(function (response) {
                    for (var i in response.data) {
                        self.opcoesUsuario.push({
                            'label': response.data[i].nome,
                            'value': response.data[i].email
                        });
                    }
                });
            }])
        .controller('AdminRelatoriosPerformanceController', ['$http', '$q', '$scope', 'nsjRouting', 'ServicosAtendimentosfilas', 'ServicosAtendimentoscamposcustomizados', 'debounce', 'AtendimentoEquipes', 'UsuarioComEquipe', 'PageTitle', '$window', 'clientes',
            function ($http, $q, $scope, nsjRouting, ServicosAtendimentosfilas, ServicosAtendimentoscamposcustomizados, debounce, AtendimentoEquipes, UsuarioComEquipe, PageTitle, $window, clientes) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.dtValida = true;
                self.dtInicio = moment().add(-30, 'day').toDate();
                self.dtFim = moment().toDate();
                self.filtros = [];
                self.opcoesUsuario = [];
                self.opcoesFila = [];
                self.opcoesCliente = [];
                self.opcoesEquipe = [];
                self.campocustomizadoAgrupamento = null;
                self.loadingUsuario = false;
                self.loadingCliente = false;
                self.loadingFila = false;
                self.loadingCampocustomizado = false;
                self.qtgrafico = 10;
                PageTitle.setTitle("Análise de Chamados e Respostas");
                self.activeTab = 'usuarios';
                self.tableData = {
                    'usuarios': [],
                    'filas': [],
                    'cientes': [],
                    'camposcustomizados': []
                };
                self.chart = [];
                self.camposcustomizados = [];
                self.generatedColors = [];
                self.total = {
                    "chamados": "-",
                    "respostas": "-",
                    "mediaderespostas": "-",
                    "resolvidoprimeirocontato": "-"
                };
                self.opcoesCanal = [
                    {
                        'label': 'Manual',
                        'value': 'manual'
                    },
                    {
                        'label': 'Email',
                        'value': 'email'
                    },
                    {
                        'label': 'Portal',
                        'value': 'portal'
                    }
                ];

                self.opcaoSituacao = [
                    {
                        'label': 'Aberto',
                        'value': 0
                    },
                    {
                        'label': 'Fechado',
                        'value': 1
                    }
                ];
                
                self.colunas = {
                    'nome': true,
                    'chamados': true,
                    'percent': true,
                    'respostaOutroUsuario': true,
                    'respostaMediaChamado': true,
                    'resolucaoPrimeiroContatoCount': true,
                    'resolucaoPrimeiroContatoPercent': true,
                    'respostas': true,
                    'trocasAtribuicao': true,
                    'comentarios': true,
                    'totalComentarios': true
                };

                self.mostrarTodasColunas = function () {
                    self.colunas.nome = true;
                    self.colunas.chamados = true;
                    self.colunas.percent = true;
                    self.colunas.respostaOutroUsuario = true;
                    self.colunas.respostaMediaChamado = true;
                    self.colunas.resolucaoPrimeiroContatoCount = true;
                    self.colunas.resolucaoPrimeiroContatoPercent = true;
                    self.colunas.respostas = true;
                    self.colunas.trocasAtribuicao = true;
                };

                self.montaGrafico = function (id, data) {
                    var i = Math.abs(moment(self.dtInicio).diff(moment(self.dtFim), 'days'));
                    var categories = [];
                    var series = [];
                    var qtmax = self.qtgrafico > data.length ? data.length : self.qtgrafico;
                    for (var j = 0; j < qtmax; j++) {
                        var name = '';
                        var key = '';
                        var color = self.randomColor();
                        switch (id) {
                            case 'grafico_usuario':
                                name = data[j].usuario.replace(/>/g, "&gt;").replace(/</g, "&lt;");
                                key = data[j].usuario;
                                data[j].color = color;
                                data[j].checked = true;
                                break;
                            case 'grafico_fila':
                                name = data[j].fila.replace(/>/g, "&gt;").replace(/</g, "&lt;");
                                key = data[j].fila;
                                data[j].color = color;
                                data[j].checked = true;
                                break;
                            case 'grafico_cliente':
                                name = data[j].nome.replace(/>/g, "&gt;").replace(/</g, "&lt;");
                                key = data[j].cliente;
                                data[j].color = color;
                                data[j].checked = true;
                                break;
                            case 'grafico_campocustomizado':
                                name = data[j].campocustomizado.replace(/>/g, "&gt;").replace(/</g, "&lt;");
                                key = data[j].campocustomizado;
                                data[j].color = color;
                                data[j].checked = true;
                                break;
                        }
                        series.push({
                            showInLegend: false,
                            name: name,
                            data: data[j].chamados,
                            color: color,
                            id: key
                        });
                    }

                    for (var k = 0; k <= i; k++) {
                        categories[k] = moment(self.dtInicio).add(k, 'd').format('DD/MM');
                    }

                    self.chart[id] = Highcharts.chart(id, {
                        chart: {
                            type: 'area',
                            height: 200
                        },
                        title: {
                            text: ''
                        },
                        yAxis: {
                            title: {
                                text: 'Quantidade de Chamados'
                            }
                        },
                        xAxis: {
                            categories: categories,
                            tickmarkPlacement: 'on',
                            title: {
                                enabled: false
                            }
                        }, 
                        tooltip: {
                            shared: true,
                            valueSuffix: ''
                        },
                        plotOptions: {
                            area: {
                                stacking: 'normal',
                                lineColor: '#666666',
                                lineWidth: 1,
                                marker: {
                                    lineWidth: 1,
                                    lineColor: '#666666'
                                }
                            }
                        },
                        series: series,
                    });
                };
                
                // Função a ser chamada na ação de print
                // Aqui é setado o tamanho do gráfico para um tamanho que fique bom na folha de impressão
                var beforePrint = function () {
                    Object.keys(self.chart).forEach(function(id) {
                        self.chart[id].hasUserSize = true;
                        self.chart[id].oldhasUserSize = self.chart[id].hasUserSize;
                        self.chart[id].resetParams = [null, self.chart[id].chartHeight, false];
                        self.chart[id].setSize(900, 200, false);
                    });
                };

                // Função a ser chamada após a ação de print
                // Aqui é o gráfico é setado para voltar ao seu tamanho correto (pré ação de print)
                var afterPrint = function () {
                    Object.keys(self.chart).forEach(function(id) {
                        self.chart[id].setSize.apply(self.chart[id], self.chart[id].resetParams);
                        self.chart[id].hasUserSize = self.chart[id].oldhasUserSize;
                        self.chart[id].reflow();
                    });
                };
                
                window.onbeforeprint = beforePrint;
                window.onafterprint = afterPrint;

                self.series = function (gindex, value) {
                    var table = '';
                    var key = '';
                    var name = '';
                    switch (gindex) {
                        case 'grafico_usuario':
                            table = 'usuarios';
                            key = 'usuario';
                            name = 'usuario';
                            break;
                        case 'grafico_fila':
                            table = 'filas';
                            key = 'fila';
                            name = 'fila';
                            break;
                        case 'grafico_cliente':
                            table = 'clientes';
                            key = 'cliente';
                            name = 'nome';
                            break;
                        case 'grafico_campocustomizado':
                            table = 'camposcustomizados';
                            key = 'campocustomizado';
                            name = 'campocustomizado';
                            break;
                    }
                    var serieIndex = false;
                    for (var x in self.chart[gindex].series) {
                        if (self.chart[gindex].series[x].options.id == value) {
                            serieIndex = x;
                        }
                    }
                    if (serieIndex === false) {
                        var color = self.randomColor();
                        var data = '';
                        for (var x in self.tableData[table]) {
                            if (self.tableData[table][x][key] == value) {
                                data = self.tableData[table][x].chamados;
                                name = self.tableData[table][x][name];
                                self.tableData[table][x].color = color;
                            }
                        }

                        self.chart[gindex].addSeries({
                            showInLegend: false,
                            name: name,
                            color: color,
                            data: data,
                            id: value
                        });
                    } else {
                        self.chart[gindex].series[serieIndex].visible ? self.chart[gindex].series[serieIndex].hide() : self.chart[gindex].series[serieIndex].show();
                    }
                };
                self.randomColor = function () {
                    var color = '#' + ('00000' + (Math.random() * 16777216 << 0).toString(16)).substr(-6);
                    var used = false;
                    for (var x in self.generatedColors) {
                        if (self.generatedColors[x] == color) {
                            used = true;
                        }
                    }
                    if (used) {
                        return self.randomColor();
                    } else {
                        self.generatedColors.push(color);
                        return color;
                    }
                };
                self.opcoesFiltro = {
                    cliente: {
                        label: 'Cliente',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesCliente
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesCliente
                            }
                        }
                    },
                    usuario: {
                        label: 'Usuário',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesUsuario
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesUsuario
                            }
                        }
                    },
                    fila: {
                        label: 'Fila',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesFila
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesFila
                            }
                        }
                    },
                    equipe: {
                        label: 'Equipe',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesEquipe
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesEquipe
                            }
                        }
                    },
                    canal: {
                        label: 'Canal',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesCanal
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesCanal
                            }
                        }
                    },
                    situacao: {
                        label: 'Situação',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcaoSituacao
                            },
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcaoSituacao
                            }
                        }
                    }
                };
                

                clientes.map(function(cliente){
                    self.opcoesCliente.push({
                        label: cliente.nome || cliente.nomefantasia,
                        value: cliente.cliente
                    });
                });

                
                self.addFiltros = function () {
                    self.filtros.push({
                        'campo': self.novoFiltro
                    });
                    self.novoFiltro = null;
                };

                self.removeFiltro = function (index) {
                    self.filtros.splice(index, 1);
                    self.loadData();
                };

                self.triggerResize = debounce(function (activeTab) {
                    self.activeTab = activeTab;
                    window.dispatchEvent(new Event('resize'));
                }, 100);

                self.exportarParaCsv = function () {
                    var filtros = {
                        'datainicial': moment(self.dtInicio).format('DD/MM/YYYY'),
                        'datafinal': moment(self.dtFim).format('DD/MM/YYYY'),
                        'campocustomizado': self.campocustomizadoAgrupamento,
                        'condicoes': self.organizaFiltros(self.filtros),
                        'relatorio': self.activeTab
                    };

                    $window.open(nsjRouting.generate('atendimento_admin_relatorios_performance_exporta_csv', filtros, false), 'Análise de Chamados e Respostas');
                };

                self.montaOpcoesCamposCustomizados = function (camposcustomizados) {
                    self.camposcustomizados = camposcustomizados;

                    for (var i in self.camposcustomizados) {
                        if (self.camposcustomizados[i].tipo === 'CB') {
                            var opcoescamposcustomizados = [];
                            for (var j in self.camposcustomizados[i].opcoes) {
                                opcoescamposcustomizados.push({
                                    'value': self.camposcustomizados[i].opcoes[j],
                                    'label': self.camposcustomizados[i].opcoes[j]
                                });
                            }
                            self.opcoesFiltro[self.camposcustomizados[i]['atendimentocampocustomizado']] = {
                                'label': self.camposcustomizados[i]['label'],
                                'options': {
                                    'is_equal': {
                                        'label': 'É igual a',
                                        'type': 'select',
                                        'options': opcoescamposcustomizados
                                    },
                                    'is_not_equal': {
                                        'label': 'Diferente de',
                                        'type': 'select',
                                        'options': opcoescamposcustomizados
                                    }
                                }
                            };
                        } else {
                            self.opcoesFiltro[self.camposcustomizados[i]['atendimentocampocustomizado']] = {
                                'label': self.camposcustomizados[i]['label'],
                                'options': {
                                    'is_set': {
                                        label: 'Existe',
                                        type: false
                                    },
                                    'is_not_set': {
                                        label: 'Não existe',
                                        type: false
                                    }
                                }
                            };
                        }
                    }

                    if (camposcustomizados.length > 0) {
                        self.campocustomizadoAgrupamento = camposcustomizados[0]['atendimentocampocustomizado'];
                    }

                    self.loadData();
                };
                self.montaOpcoesAtendimentosfilas = function (atendimentosfilas) {
                    for (var i in atendimentosfilas) {
                        self.opcoesFila.push({
                            'label': atendimentosfilas[i].nome,
                            'value': atendimentosfilas[i].atendimentofila
                        });
                    }
                };

                self.todasEquipes = [];

                self.montaOpcoesEquipes = function (equipes) {
                    for (var i in equipes) {
                        self.opcoesEquipe.push({
                            'label': equipes[i].nome,
                            'value': equipes[i].equipe
                        });
                    }

                    self.todasEquipes = self.opcoesEquipe;
                };

                self.validaFiltroDate = function () {
                    if (self.dtInicio && self.dtFim) {
                        if (Math.abs(moment(self.dtInicio).diff(moment(self.dtFim), 'days')) > 30) {
                            self.dtValida = false;
                            return false;
                        } else {
                            self.dtValida = true;
                            return true;
                        }
                    } else {
                        return false;
                    }
                };

                self.cancelarRequisicoesPendentes = function () {
                    $http.pendingRequests.forEach(function (request) {
                        if (request.cancel) {
                            request.cancel.resolve();
                        }
                    });
                };

                self.organizaFiltros = function(filtros){
                    var f = [];
                    filtros.forEach(function(el, i, a){
                        if(el.campo === 'cliente' && el.valor){
                            f.push({ campo: el.campo, operador: el.operador, valor: el.valor.cliente });
                        } else {
                            if (el.operador && el.valor) {
                                f.push(el);
                            }
                        }
                    });
                    return f;
                };

                self.loadData = function () {

                    this.cancelarRequisicoesPendentes();

                    self.loadingUsuario = true;
                    self.loadingCliente = true;
                    self.loadingCampocustomizado = true;
                    self.loadingFila = true;
                    var filtros = {
                        'datainicial': moment(self.dtInicio).format('DD/MM/YYYY'),
                        'datafinal': moment(self.dtFim).format('DD/MM/YYYY'),
                        'campocustomizado': self.campocustomizadoAgrupamento,
                        'condicoes': self.organizaFiltros(self.filtros)
                    }

                    var cancel = $q.defer();
                    var request = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_performance_usuario', filtros, false),
                        timeout: cancel.promise,
                        cancel: cancel
                    };
                    $http(request).then(function (response) {
                        self.tableData['usuarios'] = response.data;
                        self.loadingUsuario = false;
                        self.montaGrafico('grafico_usuario', response.data);

                        if (response.data.length > 0) {
                            var totalChamados = 0;
                            var totalResp = 0;
                            var totalPriCont = 0;
                            var mediaderespostas = 0;
                            var totalRespostas = 0;
                            for (var i = 0; i < response.data.length; i++) {
                                totalChamados += parseInt(response.data[i].qtdchamados);
                                totalResp += parseFloat(response.data[i].resposta_media_por_chamado);
                                totalPriCont += parseInt(response.data[i].resolvidoprimeirocontato);
                                totalRespostas += parseInt(response.data[i].respostas);
                            }

                            mediaderespostas = totalRespostas / totalChamados;
                            if (mediaderespostas % 1 !== 0) {
                                mediaderespostas = mediaderespostas.toFixed(2);
                            }

                            self.total.chamados = totalChamados;
                            self.total.respostas = totalRespostas;
                            self.total.mediaderespostas = mediaderespostas;
                            self.total.resolvidoprimeirocontato = totalPriCont;
                        } else {
                            self.total.chamados = "-";
                            self.total.respostas = "-";
                            self.total.mediaderespostas = "-";
                            self.total.resolvidoprimeirocontato = "-";
                        }

                    });

                    var request = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_performance_cliente', filtros, false),
                        timeout: cancel.promise,
                        cancel: cancel
                    };
                    $http(request).then(function (response) {
                        self.tableData['clientes'] = response.data;
                        self.loadingCliente = false;
                        self.montaGrafico('grafico_cliente', response.data);
                    });

                    var request = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_performance_campocustomizado', filtros, false),
                        timeout: cancel.promise,
                        cancel: cancel
                    };
                    $http(request).then(function (response) {
                        self.tableData['campocustomizado'] = response.data;
                        self.loadingCampocustomizado = false;
                        self.montaGrafico('grafico_campocustomizado', response.data);
                    });

                    var request = {
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_admin_relatorios_performance_fila', filtros, false),
                        timeout: cancel.promise,
                        cancel: cancel
                    };
                    $http(request).then(function (response) {
                        self.tableData['fila'] = response.data;
                        self.loadingFila = false;
                        self.montaGrafico('grafico_fila', response.data);
                    });

                };

                // Filtra os usuários que irão aparecer, de acordo com a equipe
                self.filtrarOpcoesUsuario = function() {

                    // Filtra os usuários que satisfazem a condição
                    // Equipe - Igual - Nome da Equipe
                    var usuariosFiltradosEquals = self.equipesUsuarios.filter(function(usuario) {

                        return self.filtros.filter(function(filtro) {
                            return filtro.campo == 'equipe' && filtro.operador == 'is_equal'
                        })
                        .find(function(equipe) {
                            var equipe = self.todasEquipes.find(function(eqp) {
                                return eqp.value == equipe.valor;
                            });

                            return equipe && equipe.label == usuario.equipe
                        });
                    });

                    // Filtra os usuários que satisfazem a condição
                    // Equipe - Diferente de - Nome da Equipe
                    var usuariosFiltradosNotEquals = self.equipesUsuarios.filter(function(usuario) {
                        return self.filtros.filter(function(filtro) {
                            return filtro.campo == 'equipe' && filtro.operador == 'is_not_equal'
                        })
                        .find(function(equipe) {
                            var equipe = self.todasEquipes.find(function(eqp) {
                                return eqp.value == equipe.valor;
                            });

                            return equipe && equipe.label == usuario.equipe
                        });
                    });

                    // Dos usuários que batem com a condição de pertencer e não pertencer às equipes selecionadas,
                    // seleciona, de todos os usuários, os que satisfazem essa condição.
                    self.opcoesUsuario = self.todosUsuarios.filter(function(usuario) {
                        var eq = usuariosFiltradosEquals.find(function(usr) {
                            return usr.usuario == usuario.value;
                        });
                        
                        var nEq = usuariosFiltradosNotEquals.find(function(usrNot) {
                            return usrNot.usuario == usuario.value;
                        });

                        return (eq && !nEq) || (usuariosFiltradosNotEquals.length > 0 && !eq && !nEq);
                    });

                    if (usuariosFiltradosEquals.length == 0 && usuariosFiltradosNotEquals.length == 0 && self.opcoesUsuario.length == 0) {
                        self.opcoesUsuario = self.todosUsuarios;
                    }
                    
                    // Seta os usuários filtrados
                    self.opcoesFiltro.usuario.options.is_equal.options = self.opcoesUsuario;
                    self.opcoesFiltro.usuario.options.is_not_equal.options = self.opcoesUsuario;
                }

                self.reloadData = debounce(function () {
                    self.filtrarOpcoesUsuario();

                    if (self.form_filtros_relatorios.$valid && self.validaFiltroDate() && self.usuarioComEquipe) {
                        self.loadData();
                    }
                }, 100);
                if (ServicosAtendimentosfilas.loaded) {
                    self.montaOpcoesAtendimentosfilas(ServicosAtendimentosfilas.load());
                } else {
                    $scope.$on('servicos_atendimentosfilas_list_finished', function (event, data) {
                        self.montaOpcoesAtendimentosfilas(data);
                    });
                    ServicosAtendimentosfilas.load();
                }

                if (ServicosAtendimentoscamposcustomizados.loaded) {
                    self.montaOpcoesCamposCustomizados(ServicosAtendimentoscamposcustomizados.load());
                } else {
                    $scope.$on('servicos_atendimentoscamposcustomizados_list_finished', function (event, data) {
                        self.montaOpcoesCamposCustomizados(data);
                    });
                    ServicosAtendimentoscamposcustomizados.load();
                }

                if (AtendimentoEquipes.loaded) {
                    self.montaOpcoesEquipes(AtendimentoEquipes.load());
                } else {
                    $scope.$on('atendimento_equipes_list_finished', function (event, data) {
                        self.montaOpcoesEquipes(data);
                    });
                    AtendimentoEquipes.load();
                }

                self.todosUsuarios = [];

                $http.get(nsjRouting.generate('atendimento_admin_contas_index', {}, false)).then(function (response) {
                    for (var i in response.data) {
                        self.opcoesUsuario.push({
                            'label': response.data[i].nome,
                            'value': response.data[i].email
                        });
                    }

                    self.todosUsuarios = self.opcoesUsuario;
                });

                self.equipesUsuarios = [];

                $http.get(nsjRouting.generate('atendimento_equipes_list_usuarios_alocados_equipe', {}, false))
                .then(function(response) {
                    self.equipesUsuarios = response.data;
                });
            }])
        .controller('AdminRelatoriosBacklogController', ['$http', '$scope', 'nsjRouting', 'ServicosAtendimentosfilas', 'ServicosAtendimentoscamposcustomizados', 'debounce', 'AtendimentoEquipes', 'UsuarioComEquipe', 'PageTitle', '$window',
            function ($http, $scope, nsjRouting, ServicosAtendimentosfilas, ServicosAtendimentoscamposcustomizados, debounce, AtendimentoEquipes, UsuarioComEquipe, PageTitle, $window) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.filtros = [];
                self.opcoesUsuario = [];
                self.opcoesFila = [];
                self.opcoesCliente = [];
                self.opcoesEquipe = [];
                self.total = {
                    total: 0, 
                    adiados: 0,
                    sem_resposta: 0,
                    media_tempo_aberto: 0, 
                    respondido_pelo_atendente: 0,
                    respondido_pelo_cliente: 0
                };
                self.campocustomizadoAgrupamento = null;
                self.loading = true;
                PageTitle.setTitle("Chamados Abertos");
                self.colunas = {
                    atribuido: true,
                    abertos: true,
                    respondidoultimousuario: true,
                    respondidoultimocliente: true,
                    semresposta: true,
                    mediatempoaberto: true
                };
                self.tableData = {
                    usuarios: [],
                    filas: [],
                    clientes: [],
                    camposcustomizados: []
                };
                self.camposcustomizados = [];
                self.opcaoSituacao = [
                    {
                        label: 'Aberto',
                        value: 0
                    },
                    {
                        label: 'Fechado',
                        value: 1
                    }
                ];

                self.opcoesFiltro = {
                    usuario: {
                        label: 'Usuário',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesUsuario
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesUsuario
                            }
                        }
                    },
                    fila: {
                        label: 'Fila',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesFila
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesFila
                            }
                        }
                    },
                    equipe: {
                        label: 'Equipe',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcoesEquipe
                            },
                            is_not_equal: {
                                label: 'Diferente de',
                                type: 'select',
                                options: self.opcoesEquipe
                            }
                        }
                    },
                    situacao: {
                        label: 'Situação',
                        options: {
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcaoSituacao
                            },
                            is_equal: {
                                label: 'É igual a',
                                type: 'select',
                                options: self.opcaoSituacao
                            }
                        }
                    }
                };
                self.addFiltros = function () {
                    self.filtros.push({
                        campo: self.novoFiltro
                    });
                    self.novoFiltro = null;
                };
                self.removeFiltro = function (index) {
                    self.filtros.splice(index, 1);
                    self.loadData();
                };
                self.montaOpcoesCamposCustomizados = function (camposcustomizados) {
                    self.camposcustomizados = camposcustomizados;
                    for (var i in self.camposcustomizados) {
                        if (self.camposcustomizados[i].tipo === 'CB') {
                            var opcoescamposcustomizados = [];
                            for (var j in self.camposcustomizados[i].opcoes) {
                                opcoescamposcustomizados.push({
                                    value: self.camposcustomizados[i].opcoes[j],
                                    label: self.camposcustomizados[i].opcoes[j]
                                });
                            }
                            self.opcoesFiltro[self.camposcustomizados[i]['atendimentocampocustomizado']] = {
                                label: self.camposcustomizados[i]['label'],
                                options: {
                                    is_equal: {
                                        label: 'É igual a',
                                        type: 'select',
                                        options: opcoescamposcustomizados
                                    },
                                    is_not_equal: {
                                        label: 'Diferente de',
                                        type: 'select',
                                        options: opcoescamposcustomizados
                                    }
                                }
                            };
                        } else {
                            self.opcoesFiltro[self.camposcustomizados[i]['atendimentocampocustomizado']] = {
                                label: self.camposcustomizados[i]['label'],
                                options: {
                                    is_set: {
                                        label: 'Existe',
                                        type: false
                                    },
                                    is_not_set: {
                                        label: 'Não existe',
                                        type: false
                                    },
                                    includes: {
                                        label: 'Inclui',
                                        type: 'text'
                                    }
                                }
                            };
                        }
                    }

                    if (camposcustomizados.length > 0) {
                        self.campocustomizadoAgrupamento = camposcustomizados[0]['atendimentocampocustomizado'];
                    }

                    self.loadData();
                };
                
                self.mostrarTodasColunas = function () {
                    self.colunas.atribuido = true;
                    self.colunas.abertos = true;
                    self.colunas.respondidoultimousuario = true;
                    self.colunas.respondidoultimocliente = true;
                    self.colunas.semresposta = true;
                    self.colunas.mediatempoaberto = true;
                };
                self.montaOpcoesEquipes = function (equipes) {
                    for (var i in equipes) {
                        self.opcoesEquipe.push({
                            label: equipes[i].nome,
                            value: equipes[i].equipe
                        });
                    }
                };
                self.montaOpcoesAtendimentosfilas = function (atendimentosfilas) {
                    for (var i in atendimentosfilas) {
                        self.opcoesFila.push({
                            label: atendimentosfilas[i].nome,
                            value: atendimentosfilas[i].atendimentofila
                        });
                    }
                };
                self.validaFiltroDate = function () {
                    if (self.dtInicio && self.dtFim) {
                        if (Math.abs(moment(self.dtInicio).diff(moment(self.dtFim), 'days')) > 30) {
                            self.dtValida = false;
                            return false;
                        } else {
                            self.dtValida = true;
                            return true;
                        }
                    } else {
                        return false;
                    }
                };
                self.tempoMedioMask = function (tempo) {

                    if (!tempo) {
                        return null;
                    }

                    var dias = Math.floor(tempo / 24);
                    var horas = tempo - (24 * dias);
                    if (dias > 0) {
                        return dias + "d " + horas + "h";
                    } else {
                        return  horas + "h";
                    }

                };
                self.organizaFiltros = function(filtros){
                    var f = [];
                    filtros.forEach(function(el, i, a){
                        if(el.campo === 'cliente' && el.valor){
                        f.push({ campo: el.campo, operador: el.operador, valor: el.valor.cliente });
                        }else{
                        f.push(el);
                        }
                    });
                    return f;
                };

                // Variável auxiliar para atualizar os dados quando finalizar todas as requisições pendentes.
                self.numeroRequisicoes = 0;

                // Variáveis auxiliares para setar os valores do objeto apenas quando finalizar todas as requisições
                self.resultadoAtribuicao = null;
                self.resultadoTotalizadores = null;

                self.total = {
                    total: null,
                    respondido_pelo_atendente: null,
                    respondido_pelo_cliente: null,
                    sem_resposta: null,
                    media_tempo_aberto: null
                }

                // Atualiza os valores do objeto apenas quando as requisições finalizarem
                self.atualizarAtribuicaoTotalizadores = function() {
                    if (self.numeroRequisicoes <= 0) {
                        self.tableData['atribuidoa'] = self.resultadoAtribuicao;
                        self.total = self.resultadoTotalizadores;

                        self.loading = false;
                    }
                }

                self.loadData = function () {

                    // Soma duas requisições, considerando os totalizadores e as atribuições de usuários
                    self.numeroRequisicoes += 2;

                    self.total = {
                        total: null,
                        respondido_pelo_atendente: null,
                        respondido_pelo_cliente: null,
                        sem_resposta: null,
                        media_tempo_aberto: null
                    }

                    self.loading = true;
                    var filtros = {
                        datainicial: moment(self.dtInicio).format('DD/MM/YYYY'),
                        datafinal: moment(self.dtFim).format('DD/MM/YYYY'),
                        campocustomizado: self.campocustomizadoAgrupamento,
                        condicoes: self.organizaFiltros(self.filtros)
                    };

                    $http.get(nsjRouting.generate('atendimento_admin_relatorios_backlog_atribuicao', filtros, false)).then(function (response) {

                        // Atribui a resposta à variável auxiliar do resultado das atribuições
                        self.resultadoAtribuicao = response.data;
                        self.numeroRequisicoes--;

                        self.atualizarAtribuicaoTotalizadores();
                    });
                    self.loadDataTotalizadores();
                };
                
                self.loadDataTotalizadores = function () {
                    self.loading = true;

                    var filtros = {
                        datainicial: moment(self.dtInicio).format('DD/MM/YYYY'),
                        datafinal: moment(self.dtFim).format('DD/MM/YYYY'),
                        campocustomizado: self.campocustomizadoAgrupamento,
                        condicoes: self.organizaFiltros(self.filtros)
                    };

                    $http.get(nsjRouting.generate(
                        'atendimento_admin_relatorios_backlog_atribuicao_totalizadores', 
                        filtros, 
                        false
                    )).then(function (response) {
                        // Atribui a resposta à variável auxiliar do resultado dos totalizadores
                        self.resultadoTotalizadores = {
                            adiados: response.data.adiados ? response.data.adiados : 0,
                            media_tempo_aberto: response.data.media_tempo_aberto ? response.data.media_tempo_aberto : '0',
                            respondido_pelo_atendente: response.data.respondido_pelo_atendente ? response.data.respondido_pelo_atendente : 0,
                            respondido_pelo_cliente: response.data.respondido_pelo_cliente ? response.data.respondido_pelo_cliente : 0,
                            sem_resposta: response.data.sem_resposta ? response.data.sem_resposta : 0,
                            total: response.data.total ? response.data.total : 0
                        }
                        
                        self.numeroRequisicoes--;
                        
                        self.atualizarAtribuicaoTotalizadores();
                    });
                };

                self.exportarParaCsv = function () {
                    var filtros = {
                        datainicial: moment(self.dtInicio).format('DD/MM/YYYY'),
                        datafinal: moment(self.dtFim).format('DD/MM/YYYY'),
                        campocustomizado: self.campocustomizadoAgrupamento,
                        condicoes: self.filtros
                    };

                    $window.open(nsjRouting.generate('atendimento_admin_relatorios_backlog_exporta_csv', filtros, false), 'Chamados Abertos');
                };

                self.reloadData = debounce(function () {
                    if (self.form_filtros_relatorios.$valid && self.usuarioComEquipe) {
                        self.loadData();
                    }
                }, 100);
                if (ServicosAtendimentosfilas.loaded) {
                    self.montaOpcoesAtendimentosfilas(ServicosAtendimentosfilas.load());
                } else {
                    $scope.$on('servicos_atendimentosfilas_list_finished', function (event, data) {
                        self.montaOpcoesAtendimentosfilas(data);
                    });
                    ServicosAtendimentosfilas.load();
                }

                if (ServicosAtendimentoscamposcustomizados.loaded) {
                    self.montaOpcoesCamposCustomizados(ServicosAtendimentoscamposcustomizados.load());
                } else {
                    $scope.$on('servicos_atendimentoscamposcustomizados_list_finished', function (event, data) {
                        self.montaOpcoesCamposCustomizados(data);
                    });
                    ServicosAtendimentoscamposcustomizados.load();
                }
                if (AtendimentoEquipes.loaded) {
                    self.montaOpcoesEquipes(AtendimentoEquipes.load());
                } else {
                    $scope.$on('atendimento_equipes_list_finished', function (event, data) {
                        self.montaOpcoesEquipes(data);
                    });
                    AtendimentoEquipes.load();
                }
                $http.get(nsjRouting.generate('atendimento_admin_contas_index', {}, false)).then(function (response) {
                    for (var i in response.data) {
                        self.opcoesUsuario.push({
                            label: response.data[i].nome,
                            value: response.data[i].email
                        });
                    }
                });
            }])
            .controller('AdminRelatoriosTermosController', ['$http', '$q', '$scope', 'nsjRouting', '$uibModal', 'PageTitle', 'debounce', '$window',
            function ($http, $q, $scope, nsjRouting, $uibModal, PageTitle, debounce, $window) {
                var self = this;
                PageTitle.setTitle("Termos");
                self.loading = true;
                
                self.colunas = {
                    nome: true,
                    conta: true,
                    created_at: true
                };

                self.exportarParaCsv = function () {
                    var filtros = {};
                    $window.open(nsjRouting.generate('atendimento_admin_relatorios_termos_exporta_csv', filtros, false), 'Termos');
                };

                self.mostrarTodasColunas = function () {
                    self.colunas.nome = true;
                    self.colunas.conta = true;
                    self.colunas.created_at = true;
                };

                self.loadData = function () {
                    self.loading = true;
                    var filtros = {};

                    $http.get(nsjRouting.generate('atendimento_admin_relatorios_termos_termos', filtros, false)).then(function (response) {
                        self.tableData = response.data;
                        self.loading = false;
                    });
                };

                self.loadData();

            }]);

