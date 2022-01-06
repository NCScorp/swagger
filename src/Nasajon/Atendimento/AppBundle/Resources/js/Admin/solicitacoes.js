angular.module('mda')
        .config(['$stateProvider', '$urlRouterProvider', 'nsjRoutingProvider', function ($stateProvider, $urlRouterProvider, nsjRoutingProvider) {

                var resolve = {
                    'cliente': ['NsClientes', '$stateParams', function (NsClientes, $stateParams) {
                            if ($stateParams['cliente']) {
                                return NsClientes.get($stateParams['cliente']);
                            }
                        }],
                    'camposcustomizados': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('servicos_atendimentoscamposcustomizados_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'filas': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('servicos_atendimentosfilas_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'contasResolve': ['Contas', function (Contas) {
                            return Contas;
                        }],
                    'usuariosAlocadosEquipe': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_equipes_list_usuarios_alocados_equipe', {}, false)
                                }).then(function (response) {
                                    var arrEmails = response.data.map(function (usuario) {
                                        return usuario.usuario;
                                    });
                                    resolve(arrEmails);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }]
                };

                $stateProvider
                        .state('atendimento_admin_solicitacoes', {
                            url: "/chamados?situacao?atribuidoa?cliente?canal?visivelparacliente?camposcustomizados?order?adiado?created_at_ini?created_at_fim",
                            reloadOnSearch: false,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_solicitacoes_template'),
                            controller: 'AdminSolicitacoesCtrl',
                            controllerAs: 'tndmnt_dmn_slctcs_lst_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_admin_solicitacoes_new', {
                            parent: 'atendimento_admin_solicitacoes',
                            url: "/novo?nome",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_solicitacoes_template_form'),
                            controller: 'AdminSolicitacoesFormController',
                            controllerAs: 'tndmnt_dmn_slctcs_frm_cntrllr',
                            resolve: resolve
                        })
                        .state('404', {
                            parent: 'atendimento_admin_solicitacoes',
                            url: "/404",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_solicitacoes_notfound'),
                            controller: 'notFoundController',
                            controllerAs: 'tndmnt_dmn_slctcs_lst_cntrllr'
                        })
                        .state('atendimento_admin_solicitacoes_show', {
                            parent: 'atendimento_admin_solicitacoes',
                            url: "/:atendimento",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_solicitacoes_template_show'),
                            controller: 'AdminSolicitacoesShowController',
                            controllerAs: 'tndmnt_dmn_slctcs_shw_cntrllr',
                            resolve: resolve
                        });
            }])
        .factory('AtendimentoAdminEmailOriginal', ['$http', 'nsjRouting', function ($http, nsjRouting) {
                return {
                    load: function (id, tipo) {
                        var request = {
                            "method": "GET",
                            "url": nsjRouting.generate('servicos_atendimentosemailsoriginais_get', {"id": id, "tipo": tipo}, false)
                        };
                        return $http(request);
                    }
                };
            }])
        .factory('AtendimentoAdminSolicitacoesOverride', ['$http', 'nsjRouting', '$q', function ($http, nsjRouting, $q) {
                return {
                    load: function (constructors, offset, filter) {
                        var canceler = $q.defer();

                        var request = {
                            "method": "GET",
                            "url": nsjRouting.generate('atendimento_admin_solicitacoes_index', angular.extend(constructors, {'offset': offset, 'filter': filter}), false),
                            "timeout": canceler.promise,
                            "cancel": canceler
                        };

                        return $q(function (resolve, reject) {
                            $http(request).then(function (response) {
                                resolve(response.data);
                            }).catch(function (response) {
                                reject(response);
                            });
                        });
                    }
                };
            }])
        .controller('AdminSolicitacoesCtrl', ['$scope', '$rootScope', '$http', '$stateParams', 'nsjRouting', 'ServicosAtendimentosfilas', 'PageTitle', 'AtendimentosAbertos', 'UsuarioComEquipe', 'contasResolve', 'UsuarioLogado', '$state', 'AtendimentoAdminSolicitacoesOverride', 'debounce', 'cliente', 'camposcustomizados', 'hotkeys', '$uibModal', 'toaster', 'filas', '$sce',
            function ($scope, $rootScope, $http, $stateParams, nsjRouting, ServicosAtendimentosfilas, PageTitle, AtendimentosAbertos, UsuarioComEquipe, contasResolve, UsuarioLogado, $state, AtendimentoAdminSolicitacoesOverride, debounce, cliente, camposcustomizados, hotkeys, $uibModal, toaster, filas, $sce) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.entities = [];
                self.contas = contasResolve;
                self.after = '';
                self.to_load = 3;
                self.busy = false;
                self.finished = false;
                self.mim = false;
                self.todos = false;
                self.ninguem = false;
                self.spam = false;
                self.adiados = false;
                self.filaAtiva = "";
                self.auxElement = null;
                self.constructors = {};
                self.filtroAtribuicao = {'value': 'atribuidoa', 'label': 'Atribuição'};
                self.menuOrdenacaoAtiva = 'asc';
                self.menuSituacaoAtivo = 'abertos';
                self.filas = filas;
                self.usuarioLogado = UsuarioLogado.getUsuario();
                self.filtrosPossiveis = ["atribuidoa", "cliente" , "canal", "visivelparacliente", "camposcustomizados", "respostas", "adiado", "created_at_ini", "created_at_fim"];
                self.optionusuariologado = [{'nome': 'Atribuidos a mim: ' + self.usuarioLogado.nome, 'email': self.usuarioLogado.email}];
                self.canais = ["email", "manual", "portal"];
                self.novidades = 0;
                self.abertos = AtendimentosAbertos.abertos;
                self.abertosAtuais = 0;
                self.camposcustomizados = camposcustomizados;
                self.now = moment();
                self.orderField = 'data_ultima_resposta';
                self.mesclar = null;
                self.chamadosDescricao = nsj.globals.getInstance().get('CHAMADOS_DESCRICAO');
                
                
                hotkeys.bindTo($scope).add({
                    "combo": "ctrl+alt+c",
                    "description": "Criar chamado",
                    "callback": function () {
                        $state.go('atendimento_admin_solicitacoes_new', {
                            'situacao': undefined,
                            'order': undefined,
                            'atribuidoa': undefined,
                            'responsavel_web': undefined,
                            'cliente': undefined,
                            'canal': undefined,
                            'camposcustomizados': undefined,
                            'visivelparacliente': undefined,
                            'created_at_ini': undefined,
                            'created_at_fim' : undefined
                        });
                    }
                }).add({
                    "combo": "ctrl+alt+a",
                    "description": "Abrir chamado",
                    "callback": function () {
                        $scope.modalInstance = $uibModal.open({
                            templateUrl: nsjRouting.generate('template_abre_chamado_modal', {}, false),
                            scope: $scope,
                            size: 'sm'
                        });
                    }
                });
                
                self.fecharModalAbreChamado = function () {
                    $scope.modalInstance.dismiss();
                };
                
//                findByProtocolo: function (protocolo) {
//                        var request = { "method": "POST", "url":  };
//                        return $http(request);
//                    }
                

                self.mesclarChamados = function(){
                    var chamados = self.entities.filter(function(v){ return v.selectedToAction === true; });
                    if(chamados.length > 0){
                        modalMesclar = $uibModal.open({
                            templateUrl: nsjRouting.generate('template_mesclar_modal', {}, false),
                            controller: "MesclarModalCtrl",
                            controllerAs: 'mesclarModal',
                            size: '',
                            resolve: {
                                chamados: function () {
                                    return chamados;
                                }
                            }
                        });
                    }else{
                        toaster.pop({type: 'warnning', title: 'Selecione os chamados que deseja mesclar.'});
                    }
                };

                self.abrirChamadoProtocolo = function (protocolo) {
                    $http.post(nsjRouting.generate('atendimento_admin_solicitacoes_get_atendimento_by_protocolo', {numeroprotocolo: protocolo}, false)).then(function (response) {
                        if (response.data === false) {
                            toaster.pop({type: 'error', title: 'O número do protocolo informado não está vinculado a nenhum chamado.'});
                        } else {
                            $state.go('atendimento_admin_solicitacoes_show', {'atendimento': response.data.atendimento});
                            $scope.modalInstance.dismiss();
                        }
                    }).finally(function () {
                        self.numProtocolo = "";
                    });
                };

                self.filter = {
                    key: $stateParams['q'] ? $stateParams['q'] : '',
                    filterfield: 'all'
                };

                self.campoParaBusca = '';

                self.search = function() {
                    self.filter.key = self.campoParaBusca.replace(/[Ç]/gi, 'c')
                                                         .replace(/[ÁÀÂÃÄ]/gi, 'a')
                                                         .replace(/[ÉÈÊẼË]/gi, 'e')
                                                         .replace(/[ÍÌÎĨÏ]/gi, 'i')
                                                         .replace(/[ÓÒÔÕÖ]/gi, 'o')
                                                         .replace(/[ÚÙÛŨÜ]/gi, 'u');
                    
                    self.reload();
                }
                
                self.filtros = {
                    'cliente': [],
                    'atribuidoa': [],
                    'canal': [],
                    'visivelparacliente': [],
                    'camposcustomizados': [],
                    'respostas': [],
                    'adiado':[],
                    'created_at_ini': [],
                    'created_at_fim': []
                };

                self.listaFiltros = [
                    {'value': 'cliente', 'label': 'Cliente'},
                    {'value': 'canal', 'label': 'Canal'},
                    {'value': 'created_at', 'label': 'Data de Criação'},
                    {'value': 'visivelparacliente', 'label': 'Visível para o cliente'},
                    {'value': 'respostas', 'label': 'Respostas'},
                    {'value': 'adiado', 'label':'Adiado'}
                ];

                self.camposcustomizados.map(function (campo) {
                    self.listaFiltros.push({'value': campo.atendimentocampocustomizado, 'label': campo.label});
                    self.filtrosPossiveis.push(campo.atendimentocampocustomizado);
                    self.filtros[campo.atendimentocampocustomizado] = [];
                });

                self.montarUrl = function () {
                    if (self.constructors.camposcustomizados !== undefined) {
                        var camposcustomizados = self.constructors.camposcustomizados.map(function (array) {
                            return '{"' + array.campocustomizado + '": "' + array.opcao + '"}';
                        });
                    }

                    if (self.constructors.responsavel_web !== undefined && Array.isArray(self.constructors.responsavel_web)) {
                        self.constructors.responsavel_web = self.constructors.responsavel_web.map(function (atribuidoa) {
                            return atribuidoa === 'mim' ? self.usuarioLogado.email : atribuidoa;
                        });
                    }
                    $state.go('.', {
                        'atribuidoa': self.constructors.responsavel_web,
                        'camposcustomizados': camposcustomizados,
                        'canal': self.constructors.canal,
                        'cliente': self.constructors.cliente,
                        'visivelparacliente': self.constructors.visivelparacliente,
                        'situacao': self.constructors.situacao,
                        'order': self.constructors.order,
                        'adiado': self.constructors.adiado,
                        'created_at_ini': self.constructors.created_at_ini,
                        'created_at_fim': self.constructors.created_at_fim
                    });
                };

                function verificarFiltroAtribuicao() {
                    if (self.constructors.responsavel_web === 'mim') {
                        self.listaFiltros.indexOf(self.filtroAtribuicao) >= 0 ? self.listaFiltros.splice(self.listaFiltros.indexOf(self.filtroAtribuicao), 1) : 0;
                    } else {
                        self.listaFiltros.indexOf(self.filtroAtribuicao) === -1 ? self.listaFiltros.splice(0, 0, self.filtroAtribuicao) : 0;
                    }
                }

                function removerCliente() {
                    self.auxElement = self.listaFiltros.find(function (array) {
                        return array.value === 'cliente';
                    });

                    self.listaFiltros.splice(self.listaFiltros.indexOf(self.auxElement), 1);
                }

                self.addFiltros = function () {
                    if (self.novoFiltro === 'cliente')
                        removerCliente();

                    if(self.novoFiltro === 'created_at')
                    {
                        self.filtros['created_at_ini'].push({'campo': 'created_at_ini'});
                        self.filtros['created_at_fim'].push({'campo': 'created_at_fim'});
                    }
                    else
                    {
                        self.filtros[self.novoFiltro].push({'campo': self.novoFiltro});
                    }   
                    self.novoFiltro = '';
                };
                
                function isEmail(str) {
                    var regexEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return regexEmail.test(str);
                }

                self.removeFiltro = function (row) {
                    if (row.campo === 'cliente') {
                        self.listaFiltros.splice(2, 0, self.auxElement);
                    }else if (row.campo === 'respostas') {
                        self.constructors.qtd_respostas = null;
                        self.constructors.ultima_resposta_admin = null;
                    }

                    self.filtros[row.campo].splice(self.filtros[row.campo].indexOf(row), 1);
                    self.reloadData();
                };

                self.removeFiltroCreated_at = function (row) {
                    self.filtros.created_at_ini.splice(self.filtros.created_at_ini);
                    self.filtros.created_at_fim.splice(self.filtros.created_at_fim);
                    self.reloadData();
                };

                self.checaFiltrosCreatedAt = function ( ) {
                    if(self.filtros.created_at_ini[0].opt != null && self.filtros.created_at_fim[0].opt != null)
                    {
                        self.filtros.created_at_ini = [self.filtros.created_at_ini[0]];
                        self.filtros.created_at_fim = [self.filtros.created_at_fim[0]];

                        var date_ini = moment(new Date(formatarData(self.filtros.created_at_ini[0].opt)));;
                        var date_fim = moment(new Date(formatarData(self.filtros.created_at_fim[0].opt)));;
                        if (Math.abs(moment(date_ini).diff(moment(date_fim), 'days')) > 30) {
                            toaster.pop({ type: 'error', title: 'A diferença máxima entre as datas deve ser 30 dias.' });
                        }
                        else
                        {
                        self.reloadData();
                    }
                    }
                };
                
                self.limparFiltroRespostas = function() {
                    if (self.filtros.respostas[0] != undefined) {
                        if (self.filtros.respostas[0].opt == 0) {
                            self.constructors.ultima_resposta_admin = null;
                        }else{
                            self.constructors.qtd_respostas = null;
                        }
                    }
                }

                self.reloadData = debounce(function () {
                    self.constructors.camposcustomizados = [];
                    self.constructors.responsavel_web = [];
                    self.limparFiltroRespostas();
                    
                    self.filtrosPossiveis.map(function (filtro) {
                        self.constructors[filtro] = [];
                        self.filtros[filtro].map(function (array) {
                            switch (filtro) {
                                case 'cliente':
                                    self.constructors[filtro] = array.opt.cliente;
                                    break;
                                case 'atribuidoa':
                                    if (array.opt.email !== undefined && isEmail(array.opt.email)) {
                                        self.constructors.responsavel_web.push(array.opt.email);
                                    } else {
                                        self.constructors.responsavel_web.push(array.opt.atendimentofila);
                                        array.opt.email = array.opt.nome;
                                    }
                                    break;
                                case 'created_at_ini':
                                    self.constructors[filtro].push(formatarData(array.opt));
                                    break;
                                case 'created_at_fim':
                                    self.constructors[filtro].push(formatarData(array.opt));
                                    break;
                                case 'visivelparacliente':
                                    self.constructors[filtro].push(array.opt);
                                    break;
                                case 'adiado':
                                    self.constructors[filtro].push(array.opt);
                                    break;
                                case 'canal':
                                    array.opt = array.opt === '' ? 'isnull' : array.opt;
                                    self.constructors[filtro].push(array.opt);
                                    break;
                                case 'respostas':
                                    if(array.opt == 0){
                                      self.constructors.qtd_respostas = 0;
                                    }else if(array.opt == 1){
                                      self.constructors.ultima_resposta_admin = 'false';
                                    }else if(array.opt == 2){
                                      self.constructors.ultima_resposta_admin = 'true';
                                    }
                                    break;
                                default:
                                    var campo = self.camposcustomizados.filter(function (cc) {
                                        return cc.atendimentocampocustomizado == array.campo;
                                    }).pop();

                                    self.constructors.camposcustomizados.push({'campocustomizado': campo.atendimentocampocustomizado, 'label': campo.label, 'opcao': array.opt});
                            }
                        });
                    });

                    self.montarUrl();

                    self.reload();
                }, 500);

                self.agruparPorTipo = function (item) {
                    return item.email ? 'Usuário' : 'Fila';
                };

                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                function montaArrayCamposCustomizados(arr, keys) {
                    var cc = [];

                    keys.forEach(function (guid) {
                        arr.forEach(function (campo) {
                            campo['campocustomizado'] = guid;
                            campo['opcao'] = campo[guid];

                            cc.push(campo);
                        });
                    });

                    return cc;
                }

                if (!$stateParams.order) {
                    $state.go('.',{order:'asc'});
                    self.constructors.order = 'asc';
                }

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && i != 'q') {
                        if (i == 'atribuidoa') {
                            self.constructors['responsavel_web'] = $stateParams[i];
                        } else {
                            if (i === 'camposcustomizados') {
                                var arrJson = [];
                                var keyNames = null;

                                if (Array.isArray($stateParams[i])) {
                                    $stateParams[i].forEach(function (items) {
                                        var json = JSON.parse(items);
                                        arrJson.push(json);
                                        keyNames = Object.keys(json);
                                    });

                                } else {
                                    var json = JSON.parse($stateParams[i]);
                                    arrJson.push(json);
                                    keyNames = Object.keys(json);
                                }

                                var filtroCamposCustomizados = montaArrayCamposCustomizados(arrJson, keyNames);

                                self.constructors[i] = filtroCamposCustomizados;
                            } else {
                                self.constructors[i] = $stateParams[i];
                            }

                        }
                    }
                }

                self.isBusy = function () {
                    return self.busy;
                };
                self.reload = function () {
                    self.to_load = 3;
                    self.after = "";
                    self.finished = false;
                    if (self.entities) {
                        self.entities.length = 0;
                    }
                    self.nextPage();
                };

                self.updateTitle = function (atribuido, adiado) {
                    var title = null;
                    self.mim = false;
                    self.todos = false;
                    self.ninguem = false;
                    self.spam = false;
                    self.adiados = false;
                                        
                    if (adiado === "0" && !$rootScope.atribuindoUsuarioLogado) {
                        self.filaAtiva = "mim";
                        adiado = null;
                    } else if (adiado) {
                        self.filaAtiva = "adiados";
                    } else {
                        self.filaAtiva = atribuido;
                    }

                    if (atribuido === 'mim' && !adiado) {
                        title = 'Meus chamados';
                        self.mim = true;
                    } else if (atribuido === 'mim' && adiado) {
                        title = 'Adiados';
                        self.adiados = true;
                    } else if (atribuido === 'ninguem') {
                        title = 'Sem atribuição';
                        self.ninguem = true;
                    } else if (atribuido === 'spam') {
                        self.filaAtiva = 'spam';
                        title = 'SPAM';
                        self.spam = true;
                    } else if (isGuid(atribuido)) {
                        ServicosAtendimentosfilas.get(atribuido).then(function (response) {
                            title = response.nome;
                            title = self.abertos[atribuido] ? "(" + self.abertos[atribuido] + ") " + title : title;
                            PageTitle.setTitle(title);
                        });
                    } else {
                        title = 'Todos os chamados';
                        self.menuSituacaoAtivo, self.filaAtiva = 'todos';
                        self.todos = true;
                    }
                    
                    if (title) {
                        title = self.abertos[atribuido] ? "(" + self.abertos[atribuido] + ") " + title : title;
                        PageTitle.setTitle(title);
                    }

                    $rootScope.atribuindoUsuarioLogado = false;
                };

                self.filtrarSituacao = function (situacao, ativo) {

                    switch (true) {
                        case (situacao === null):
                            self.constructors.situacao = null;
                            break;
                        case (typeof situacao === 'number'):
                            self.constructors.situacao = situacao;
                            break;
                        default:
                            self.constructors.situacao = 0;
                            break;
                    }

                    self.montarUrl();

                    self.menuSituacaoAtivo = ativo;
                    self.reload();
                };

                self.filterOrder = function (order, orderfield) {
                    self.orderField = orderfield;
                    if (order == 'desc') {
                        self.constructors.order = 'desc';
                    } else if (order == 'asc') {
                        self.constructors.order = 'asc';
                    }

                    self.montarUrl();

                    self.menuOrdenacaoAtiva = order;
                    self.reload();
                };
                
                function stripHtml(html){
                    var temporalDivElement = document.createElement("div");
                    temporalDivElement.innerHTML = html;
                    return temporalDivElement.textContent || temporalDivElement.innerText || "";
                }

                self.nextPage = function () {
                    if (!self.busy && !self.finished && self.to_load > 0) {
                        
                        if(self.constructors.situacao === undefined){
                          self.constructors.situacao = 0;
                        }
                        
                        self.busy = true;
                        $http.get(nsjRouting.generate('atendimento_admin_solicitacoes_index', angular.extend(self.constructors, { 'orderfield': self.orderField ,'offset': self.after, 'filter': self.filter}), false)).then(function (response) {
                            if (response.data.length > 0) {
                                for (var i = 0; i < response.data.length; i++) {
                                    if (self.entities) {
                                        self.entities.push(response.data[i]);
                                    }
                                }

                                self.entities = self.entities ? self.entities.map(function (entity) {
                                    entity.resumo = stripHtml(entity.resumo);
                                    entity.data_adiamento = moment(entity.data_adiamento);
                                    entity.labelEscala = new Date(entity.proximaviolacaosla) > new Date() ? 'Escala' : 'Escalou';
                                    return entity;
                                }) : self.entities;
                                
                                self.after = self.entities ? self.entities[self.entities.length - 1][self.orderField] : self.entities;
                            }
                            if (response.data.length < 20) {
                                self.finished = true;
                                $rootScope.$broadcast("atendimento_admin_solicitacoes_list_finished", self.entities);
                            }
                            self.loaded = true;
                            self.busy = false;
                            self.novidades = 0;
                            self.to_load--;
                            self.nextPage();
                        });
                    }
                };

                self.loadMore = function () {
                    self.to_load = 3;
                    self.nextPage();
                };

                function formatarData(dateString) {
                    var data = new Date(dateString);
                    var dia = data.getDate();
                    if (dia.toString().length == 1)
                        dia = "0" + dia;
                    var mes = data.getMonth() + 1;
                    if (mes.toString().length == 1)
                        mes = "0" + mes;
                    var ano = data.getFullYear();
                    return ano + "-" + mes + "-" + dia;
                }

                self.filtrarAtribuicao = function (obj) {            
                    var categoria = obj.categoria !== undefined ? obj.categoria : "";

                    if (self.filaAtiva !== obj.atribuidoa || (self.filaAtiva === obj.atribuidoa && categoria === 'adiados')) {
                        if (categoria !== self.filaAtiva) {
                            self.constructors.order = obj.order;
                            self.menuOrdenacaoAtiva = obj.order;
                            if (obj.adiado != undefined) {
                                self.constructors.adiado = obj.adiado;
                            }else{
                                delete self.constructors.adiado;
                            }
                            
                            if (obj.situacao != null && obj.situacao != undefined) {
                               self.constructors.situacao = obj.situacao;
                            }
                            if (obj.atribuidoa != 'spam' && self.constructors.situacao == 2){
                                //Reseta a situação caso o usuário esteja saindo da fila SPAM
                                self.constructors.situacao = 0;
                                self.menuSituacaoAtivo = 'abertos';
                            }
                            obj.atribuidoa === 'todos' || obj.atribuidoa === 'spam' ? delete self.constructors.responsavel_web : self.constructors.responsavel_web = obj.atribuidoa;
                            verificarFiltroAtribuicao();
                            delete self.constructors.cliente;
                            delete self.constructors.canal;
                            delete self.constructors.visivelparacliente;
                            delete self.constructors.camposcustomizados;
                            delete self.constructors.created_at_ini;
                            delete self.constructors.created_at_fim;

                            self.updateTitle(obj.atribuidoa, obj.adiado);
                            resetFilters();

                            if ($http.pendingRequests.length > 0) {
                                $http.pendingRequests.map(function (request) {
                                    if (request.cancel) {
                                        request.cancel.reject();
                                    }
                                });
                            }
                            self.entities = [];

                            self.toggleFilter = false;
                            self.busy = true;                          
                            self.finished = false;
                            self.to_load = 3;
                            self.after = '';
                            
                            loadAtribuicao();
                            
                            self.montarUrl();
                        }
                    }
                };
                
                function loadAtribuicao() {                                    
                    
                    AtendimentoAdminSolicitacoesOverride
                        .load(self.constructors, self.after, self.filter)
                        .then(function (data) {
                            self.entities = self.entities ? self.entities.concat(data) : self.entities;
                            
                            //self.after = self.entities ? self.entities[self.entities.length - 1]['data_ultima_resposta'] : undefined;
                            
                            if(self.entities && self.entities[self.entities.length - 1] != undefined){
                                self.after = self.entities[self.entities.length - 1]['data_ultima_resposta'];  
                            }else{
                                self.after = undefined;
                            }
                            
                            if(self.to_load <= 1 || data.length < 20){
                                self.finished = true;

                            }else {
                                self.finished = false;                                
                                self.busy = false;
                                self.to_load--;
                                $rootScope.$broadcast("atendimento_admin_solicitacoes_list_finished", self.entities);
                            }

                        }).finally(function () {
                                                                                                           
                            if(self.finished == true && $http.pendingRequests.length == 0){
                                self.busy = false;
                            }else if(self.finished == false){
                                loadAtribuicao();
                            }

                            self.recarregarContadores();
                            
                        });
                }

                self.recarregarContadores = function() {
                    $http
                    .get(nsjRouting.generate('atendimento_admin_solicitacoes_abertos', {}, false))
                    .then(function (response) {
                        $rootScope.$broadcast("atendimentos_abertos_reload", response.data);
                    });
                };

                function resetFilters() {
                    self.filtrosPossiveis.map(function (filtro) {
                        self.filtros[filtro] = [];
                    });
                }

                $scope.$on("atendimento_admin_solicitacoes_submitted", function (event, args) {
                    if(args.removeSpam == true){
                        self.constructors.situacao = args.situacao;
                        self.constructors.adiado = null;
                    }
                    self.reload();
                    self.updateTitle(self.constructors.responsavel_web, self.constructors.situacao);
                });

                $scope.$on("servicos_atendimentosfilas_list_finished", function () {
                    self.updateTitle(self.constructors.responsavel_web, self.constructors.situacao);
                });

                $scope.$on("atendimentos_abertos_reload", function (event, abertos) {
                    var novidades = abertos[self.constructors.responsavel_web] - self.abertosAtuais;
                    self.novidades = novidades > 0 ? novidades : 0;
                    self.abertos = abertos;
                });
                
                function isGuid(str) {
                    var regexGuid = /^(\{){0,1}[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}(\}){0,1}$/gi;
                    return regexGuid.test(str);
                }

                function adicionaFiltro(filtro) {
                    if (Array.isArray($stateParams[filtro])) {
                        $stateParams[filtro].map(function (item) {
                            if (filtro === 'atribuidoa' && isGuid(item)) {
                                var fila = self.filas.filter(function (fila) {
                                    fila.email = fila.nome;
                                    return fila.atendimentofila === item;
                                }).pop();

                                self.filtros[filtro].push({'campo': filtro, 'opt': fila});
                            } else if (filtro === 'atribuidoa' && !isGuid($stateParams[filtro])) {
                                self.filtros[filtro].push({'campo': filtro, 'opt': {'email': item}});
                            } else {
                                self.filtros[filtro].push({'campo': filtro, 'opt': item});
                            }
                        });
                    } else {
                        if (filtro === 'atribuidoa' && isGuid($stateParams[filtro])) {
                            var fila = self.filas.filter(function (fila) {
                                fila.email = fila.nome;
                                return fila.atendimentofila === $stateParams[filtro];
                            }).pop();
                            
                            self.filtros[filtro].push({'campo': filtro, 'opt': fila});
                        } else if (filtro === 'atribuidoa' && !isGuid($stateParams[filtro])) {
                            self.filtros[filtro].push({'campo': filtro, 'opt': {'email': $stateParams[filtro]}});
                        } else {
                            self.filtros[filtro].push({'campo': filtro, 'opt': $stateParams[filtro]});
                        }
                    }
                }

                self.filtrosPossiveis.map(function (filtro) {

                    if ($stateParams[filtro] === 'mim' || $stateParams[filtro] === 'ninguem') {
                        self.toggleFilter = false;
                    } else {
                        self.toggleFilter = $stateParams[filtro] !== undefined || self.toggleFilter ? true : false;
                    }

                    if ($stateParams[filtro] !== undefined && $stateParams[filtro].length > 0) {

                        switch (filtro) {
                            case 'cliente':
                                self.filtros[filtro].push({'campo': filtro, 'opt': cliente});
                                removerCliente();
                                break;
                            case 'atribuidoa':
                            case 'created_at_ini':
                            case 'created_at_fim':
                            case 'visivelparacliente':
                            case 'canal':
                                adicionaFiltro(filtro);
                                break;
                            default:
                                if (Array.isArray($stateParams[filtro])) {
                                    $stateParams[filtro].map(function (item) {
                                        var json = JSON.parse(item);
                                        var key = Object.keys(json);
                                        var campo = {};

                                        key.forEach(function (k) {
                                            campo['campo'] = k;
                                            campo['opt'] = json[k];
                                            self.filtros[k].push(campo);
                                        });
                                    });
                                } else {
                                    var json = JSON.parse($stateParams[filtro]);
                                    var key = Object.keys(json);
                                    var campo = {};

                                    key.forEach(function (k) {
                                        campo['campo'] = k;
                                        campo['opt'] = json[k];
                                        self.filtros[k].push(campo);
                                    });
                                }
                        }

                    }
                });

                verificarFiltroAtribuicao();
                self.updateTitle(self.constructors.responsavel_web, self.constructors.situacao);
                self.nextPage();
            }])
        .controller('AdminSolicitacoesShowController', ['$http', '$scope', '$rootScope', '$stateParams', '$state', '$sce', 'nsjRouting', 'ServicosAtendimentosfilas', 'contasResolve', 'toaster', 'UsuarioLogado', 'debounce', '$uibModal', 'UsuarioComEquipe', 'AtendimentoAdminEmailOriginal', 'camposcustomizados', '$q', 'hotkeys', 'usuariosAlocadosEquipe',
            function ($http, $scope, $rootScope, $stateParams, $state, $sce, nsjRouting, ServicosAtendimentosfilas, contasResolve, toaster, UsuarioLogado, debounce, $uibModal, UsuarioComEquipe, AtendimentoAdminEmailOriginal, camposcustomizados, $q, hotkeys, usuariosAlocadosEquipe) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.busy = true;
                self.submitted = false;
                self.loadingCliente = false;
                self.usuarioLogado = UsuarioLogado.getUsuario();
                self.atendimentoobservador = false;
                self.filas = ServicosAtendimentosfilas.load();
                self.entity = {};
                self.constructors = {};
                self.cliente = {};
                self.cliente_solicitacoes_qtd = 5;
                self.exibeAtribuido = null;
                self.hideCliente = false;
                self.savingstate = 0;
                self.habilitarFollowup = false;
                self.habilitarAssunto = nsj.globals.getInstance().get('HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS');
                self.exibe_cliente = nsj.globals.getInstance().get('EXIBE_CLIENTE');
                self.habilitarCCO = nsj.globals.getInstance().get('HABILITAR_CCO_CHAMADOS');
                self.camposcustomizados = camposcustomizados;
                self.activeTab = 0;
                self.optionusuariologado = [];
                self.permitirAdiamento = false;
                self.escopoModalEncaminhar = {};
                self.cco = false;
                self.email_contato_invalido = false;
                self.cliente_usuarios = [];
               
                self.toggleCco = function() {
                    if (self.habilitarCCO != '1') { 
                        return;
                    }

                    self.cco = !self.cco;
                };

                // Pegar todas as contas exceto usuário logado e as contas sem equipe
                if(contasResolve && contasResolve.length > 0) {
                    self.contas = contasResolve.filter(function (conta) {
                        return (conta.email !== self.usuarioLogado.email) && (usuariosAlocadosEquipe.indexOf(conta.email) >= 0);
                    });
                }

                //Se usuário logado tiver equipe, então populamos o campo atribuido com usuário logado
                if (usuariosAlocadosEquipe.indexOf(self.usuarioLogado.email) >= 0) {
                    self.optionusuariologado = [{'nome': 'Atribuir a mim: ' + self.usuarioLogado.nome, 'email': self.usuarioLogado.email}];
                }
                
                var days = ['dom','seg','ter','qua','qui','sex','sab'];
                
                hotkeys.bindTo($scope).add({
                    "combo": "ctrl+alt+o",
                    "description": "Observar / Deixar de observar este chamado",
                    "callback": function () {
                        self.saveObservador();
                    }
                }).add({
                    "combo": "ctrl+alt+f",
                    "description": "Fechar / Reabrir chamado",
                    "callback": function () {
                        self.entity.situacao == 1 ? self.open() : self.close();
                    }
                }).add({
                    "combo": "alt+s",
                    "description": "Definir como / Não é SPAM ",
                    "callback": function () {
                        self.entity.situacao == 2 ? self.open(1) : self.isSpam();
                    }
                }).add({
                    "combo": "ctrl+alt+h",
                    "description": "Deixar oculto para cliente",
                    "callback": function () {
                        self.alteraVisibilidade();
                    }
                }).add({
                    "combo": "ctrl+p",
                    "description": "Imprimir chamado",
                    "callback": function () {
                        
                    }
                }).add({
                    "combo": "ctrl+alt+r",
                    "description": "Criar Agendamento",
                    "callback": function () {
                        self.agenda();
                    }
                });
                
                self.now = moment();
                self.hoje = { 'label': days[new Date().getDay()], 'value': new Date() };
                self.amanha = { 'label': days[new Date().getDay() +1], 'value': new Date(new Date().setDate(new Date().getDate() + 1))};
                self.fimSemana = new Date(new Date().setDate(new Date().getDate() + (6 - new Date().getDay())));
                self.proxSemana = new Date(new Date().setDate(new Date().getDate() + (7 - (new Date().getDay() - 1))));
                
                self.hoje.value.setHours(18,00,00,00);
                self.amanha.value.setHours(08,00,00,00);
                self.fimSemana.setHours(08,00,00,00);
                self.proxSemana.setHours(08,00,00,00);
                
                self.hoje.valid = self.hoje.value > new Date() ? true : false;

                self.getNumeroChamado = function(id){
                    var numeroprotocolo = '';
                    self.entity.chamadosmesclados.forEach(function(chamado){
                       if(chamado.atendimento == id){
                            numeroprotocolo = chamado.numeroprotocolo;
                       }
                    });
                    return numeroprotocolo;
                };

                self.exibeHistoricoAdiado = function(obj) {

                    if ( obj != null && obj.adiado == true && Date.parse(obj.data_adiamento) > Date.now()) {
                        return ("Adiado até " + moment(obj.data_adiamento).format('lll'));
                    }else{
                        return "Não adiado";
                    }
                };

                self.openMomentPickerModal = function () {
                    var modalInstance = $uibModal.open({
                        templateUrl: nsjRouting.generate('template_momentpicker_modal', {}, false),
                        controller: 'ModalDatePickerController',
                        controllerAs: 'datePickerModal',
                        windowClass: 'modal-form',
                        size: "sm",
                        backdrop: true,
                        resolve: {}
                    });
                    
                    modalInstance.result.then(function (formData) {
                        self.adiar(new Date(formData));
                    });
                };
                
                self.adiar = function (dataAdiamento) {
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_adiar', {'id': self.entity.atendimento}, false),
                        data: {'adiado': true, 'data_adiamento': dataAdiamento, 'responsavel_web': self.entity.responsavel_web}
                    }).then(function (response) {
                        $state.go('atendimento_admin_solicitacoes_show', self.constructors, {reload: true});
                        toaster.pop({type: 'success', title: 'Chamado adiado com sucesso!'});
                    }).catch(function (response) {
                        alert(response.data.message);
                    });
                };
                
                self.naoAdiar = function () {
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_adiar', {'id': self.entity.atendimento}, false),
                        data: {'adiado': 'false', 'responsavel_web': self.entity.responsavel_web}
                    }).then(function (response) {
                        $state.go('atendimento_admin_solicitacoes_show', self.constructors, {reload: true});
                        toaster.pop({type: 'success', title: 'Adiamento removido com sucesso!'});
                    }).catch(function (response) {
                        alert(response.data.message);
                    });
                };

                self.usuarioPodeInteragirNoChamado = function () {
                    if (nsj.globals.getInstance().get('usuario')['indisponivel'] && nsj.globals.getInstance().get('USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS') == 0) {
                        return false;
                    } else {
                        return true;
                    }
                };

                self.mensagemUsuarioNaoPodeInteragirNoChamado = function () {
                    alert('Usuário logado está indisponível no momento e não pode interagir no chamado.');
                    return false;
                };

                self.Imprimir = function () {
                    window.print();
                };
                
                self.modalEncaminhar = function() {
                  
                  self.escopoModalEncaminhar.atendimento = self.entity.atendimento;
                  self.escopoModalEncaminhar.mensagem = '';
                  self.escopoModalEncaminhar.email = [];
                  self.escopoModalEncaminhar.submitting = false;
                  
                  self.escopoModalEncaminhar.encaminhaEmail = function(){
                    self.escopoModalEncaminhar.email = self.escopoModalEncaminhar.email.map(function (el) {
                      return el.text.trim();
                    });

                    if(self.escopoModalEncaminhar.email.length > 0){
                      self.escopoModalEncaminhar.submitting = true;
                      $http({
                          method: "POST",
                          url: nsjRouting.generate('atendimento_admin_solicitacoes_encaminharhistorico', {
                            'atendimento': $stateParams['atendimento'],
                            'email': self.escopoModalEncaminhar.email,
                            'mensagem': self.escopoModalEncaminhar.mensagem
                          })
                      }).then(function (response) {
                          // Encaminhar a mensagem 
                            toaster.pop({type: 'success', title: 'Chamado encaminhado com sucesso.'});
                            self.escopoModalEncaminhar.submitting = false;
                            self.escopoModalEncaminhar.close();
                      }).catch(function (response) {
                          if (response.status === 401) {
                              alert(response.data.message);
                          } else {
                              toaster.pop({ type: 'error', title: 'Ocorreu um erro ao encaminhar o chamado.' });
                          }
                          self.escopoModalEncaminhar.submitting = false;
                      });
                    }
                    
                      
                  };
                  
                  self.escopoModalEncaminhar.validaEmail = function(e){
                    console.log(e);
                  };
                  
                  self.escopoModalEncaminhar.close = function(){
                    $scope.modalInstance.dismiss();
                  };
                  
                  $scope.modalInstance = $uibModal.open({
                        templateUrl: nsjRouting.generate('template_encaminhar_chamado_modal', {}, false),
                        scope: $scope
                    });
                };

                if (nsj.globals.getInstance().get('USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO') == 0) {

                    $http.get(nsjRouting.generate('atendimento_usuariosdisponibilidades_lista_indisponiveis', {}, false)).then(function (response) {

                        for (var i in response.data) {
                            for (var j in self.contas) {
                                if (self.contas[j]['email'] == response.data[i]) {
                                    self.contas.splice(j, 1);
                                }
                            }
                        }
                    });
                }

                for (var i in $stateParams) {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }

                self.saveObservador = function () {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    ;

                    $http({
                        method: "POST",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_observar', {'atendimento': $stateParams['atendimento']})
                    }).then(function (response) {
                        self.entity.usuario_observando = !self.entity.usuario_observando;

                        if (self.entity.usuario_observando) {
                            toaster.pop({type: 'success', title: 'Você está observando este chamado.'});
                        } else {
                            toaster.pop({type: 'success', title: 'Você não está mais observando este chamado.'});
                        }

                    }).catch(function (response) {
                        if (response.status === 401) {
                            alert(response.data.message);
                        } else {
                            toaster.pop({ type: 'error', title: 'Erro ao salvar.' });
                        }
                    });

                };

                self.openDetalhesSuporte = function (cliente) {
                    $scope.cliente = cliente;
                    $scope.modalInstance = $uibModal.open({
                        templateUrl: nsjRouting.generate('template_cliente_modal_suporte', {}, false),
                        scope: $scope
                    });
                };
                $scope.close = function () {
                    $scope.modalInstance.dismiss();//$scope.modalInstance.close() also works I think
                };

                self.openModal = function (historico) {
                    self.artigo = {};
                    self.artigo.conteudo = historico.valornovo.followup.historico;
                    self.artigo.titulo = '';
                    self.artigo.followup = historico.valornovo.followup.followup;
                    self.tagObrigatorio = nsj.globals.getInstance().get('ARTIGO_TAG_OBRIGATORIO');
                    

                    self.addSuggestedTags = function (tag) {
                        if (self.artigo.tags == null) {
                          self.artigo.tags = [];
                        }
                        self.artigo.tags.push({'text': tag});
                      }
          
                      self.suggestTag = debounce(function () {
                        var artigoTituloConteudo = self.artigo.conteudo +" "+ self.artigo.titulo;
                        if (self.cancelRequest != null) {
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
                        if (self.artigo.tags == null) {
                          return true;
                        }
                        return !self.artigo.tags.some(function (tag) {
                          if (tag.text == con) {
                            return true;
                          } else {
                            return false;
                          }

                        });
                    };
                    
                    self.artigo.loadFollowup = function (followup) {
                        self.followup = [];
                        if (followup) {
                            $http({
                                method: "GET",
                                url: nsjRouting.generate('atendimento_admin_artigos_index', {followup: followup})
                            }).then(function (response) {
                                self.followup = response.data;
                            }).catch(function (response) {
                            });
                        }
                    };

                    self.artigo.submit = debounce(function ($event, salvarTipo) {
                        $event.preventDefault();
                        var method, url;
                        method = "POST";
                        url = nsjRouting.generate('atendimento_admin_artigos_create', {}, false);

                        if (self.formArtigo.$invalid) {
                            angular.forEach(self.formArtigo.$error.required, function (field) {
                                field.$setDirty();
                            });
                            if (self.formArtigo.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar.");
                            }
                            return false;
                        }

                        if (self.artigo.tags) {
                            self.artigo.tags = self.artigo.tags.map(function (el) {
                                return el.text.trim();
                            });
                        } else {
                            self.artigo.tags = [];
                        }

                        self.artigo.submiting = true;
                        self.artigo.conteudo = $sce.valueOf(self.artigo.conteudo);
                        self.artigo.criado_por_resposta = true;
                        self.artigo.status = false;
                        self.artigo.tipoexibicao = 1;

                        $http({
                            method: method,
                            url: url,
                            data: self.artigo
                        })
                                .then(function successCallback(response) {
                                    var artigoId = response.data.artigo;
                                    var mensagem = 'O artigo foi salvo';
                                    if (salvarTipo === 1) {
                                        self.setStatus(artigoId);
                                    }

                                    toaster.pop({
                                        type: 'success',
                                        title: mensagem
                                    });
                                    self.modalArtigo.close();
                                }, function errorCallback(response) {
                                    toaster.pop({
                                        type: 'error',
                                        title: 'Erro ao salvar artigo'
                                    });
                                })
                                .finally(function (response) {
                                    self.artigo.submiting = false;

                                });
                        return;
                    }, 1000);

                    self.modalArtigo = $uibModal.open({
                        animation: false,
                        templateUrl: nsjRouting.generate('atendimento_admin_artigos_template_form_modal', {}, false),
                        scope: $scope,
                        size: 'lg'
                    });

                    self.setStatus = function (artigoId) {
                        var mensagem = '';
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_artigos_put_status', {id: artigoId}, false),
                            data: {
                                status: true
                            }
                        }).then(function successCallback(response) {
                            mensagem = 'Artigo publicado';
                            toaster.pop({
                                type: 'success',
                                title: mensagem
                            });
                        }, function errorCallback(response) {
                            mensagem = 'Houve um problema ao publicar! ';// + response.data.erro;
                            toaster.pop({
                                type: 'error',
                                title: mensagem
                            });
                        });

                    };
                };
                self.agruparPorTipo = function (item) {
                    return item.email ? 'Usuário' : 'Fila';
                };

                self.atribuirUsuarioLogado = function () {

                    $rootScope.atribuindoUsuarioLogado = true;

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    
                    if (self.usuarioLogado.email === self.entity.responsavel_web) {
                        alert('Este chamado já está atribuído a você!');
                        return;
                    }

                    self.atribuir(self.usuarioLogado);
                };

                self.atribuir = function ($item) {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    ;

                    var exibeAtributo = null;
                    var responsavel_web = null;
                    if (!$item) {
                        self.entity.responsavel_web = null;
                    } else if ($item.email) {
                        responsavel_web = $item.email;
                        exibeAtributo = $item.email;
                    } else if ($item.atendimentofila) {
                        responsavel_web = $item.atendimentofila;
                        exibeAtributo = $item.nome;
                    }
                    self.exibeAtribuido = exibeAtributo;
                    self.saveAtribuir(responsavel_web);
                };

                self.save = debounce(function (campocustomizado) {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }

                    self.savingstate = 1;
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_campocustomizado', {'id': self.entity.atendimento}, false),
                        data: self.entity.camposcustomizados,
                        headers: {
                            "ETag": self.entity['lastupdate']
                        }
                    }).then(function (response) {
                        self.savingstate = 2;
                        var obrigatoriosPreenchidos = false;
                        
                        if (response.status === 200) {
                            if (self.required !== undefined) {
                                self.required[campocustomizado] = false;
                            }
                            
                            self.camposcustomizados.forEach(function (cc) {
                                if (cc.obrigatorio) {
                                    obrigatoriosPreenchidos = self.entity.camposcustomizados[cc.atendimentocampocustomizado] !== null && self.entity.camposcustomizados[cc.atendimentocampocustomizado] !== undefined ? true : false;
                                }
                            });
                            
                            if (obrigatoriosPreenchidos) {
                                $rootScope.$broadcast('atendimento_admin_solicitacoes_form_valid');
                            }
                        }
                    }).catch(function (response) {
                        if (response.status === 409) {
                            if (confirm(response.data.message)) {
                                self.entity['lastupdate'] = response.data.entity['lastupdate'];
                                self.save();
                            }
                        } else if (response.status === 401) {
                            alert(response.data.message);
                        } else {
                            self.savingstate = 3;
                            toaster.pop({
                                type: 'error',
                                title: response.data.erro || response.data.message
                            });
                        }
                    });

                }, 1000);

                self.loadCliente = function (cliente) {
                    if (cliente.cliente && !self.loadingCliente && (!self.cliente.cliente || (self.cliente.cliente !== cliente.cliente))) {
                        self.loadingCliente = true;
                        
                        $q(function (resolve, reject) {
                            $http({
                                method: 'GET',
                                url: nsjRouting.generate('ns_clientes_get', {'id': cliente.cliente}, false)
                            }).then(function (response) {
                                if (response.status === 200) {
                                    self.cliente = response.data;
                                    self.carregaUsuariosCliente(self.cliente);
                                    self.loadingCliente = false;
                                    resolve(response.data);
                                }                                
                            }).catch(function (response) {
                                reject(response);
                            });
                        });
                    }
                };

                self.alterarCliente = debounce(function () {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    ;

                    self.savingstate = 1;
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_cliente', {'id': self.entity.atendimento}, false),
                        data: {
                            cliente: self.entity.cliente ? self.entity.cliente : null
                        },
                        headers: {
                            "ETag": self.entity['lastupdate']
                        }
                    }).then(function (response) {
                        self.savingstate = 2;
                        if (typeof self.entity.cliente !== 'undefined') {
                            if (self.cliente.cliente != self.entity.cliente.cliente) {
                                self.loadCliente(self.entity.cliente);
                            }
                        }
                        $rootScope.$broadcast("atendimento_admin_solicitacoes_submitted", self.entity);
                    }).catch(function (response) {
                        if (response.status == 409) {
                            if (confirm(response.data.message)) {
                                self.entity['lastupdate'] = response.data.entity['lastupdate'];
                                self.save();
                            }
                        } else if (response.status === 401) {
                            alert(response.data.message);
                        } else {
                            self.savingstate = 3;
                            toaster.pop({
                                type: 'error',
                                title: response.data.erro || response.data.message
                            });
                        }
                    });

                }, 1000);

                self.recarregarContadores = function() {
                    $http
                    .get(nsjRouting.generate('atendimento_admin_solicitacoes_abertos', {}, false))
                    .then(function (response) {
                        self.abertos = response.data;
                        $rootScope.$broadcast("atendimentos_abertos_reload", self.abertos);
                    });
                };

                self.saveAtribuir = function (responsavel_web) {

                    self.savingstate = 1;
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_atribuir', {'id': self.entity.atendimento}, false),
                        data: {
                            'responsavel_web': responsavel_web,
                            'lastupdate': self.entity['lastupdate']
                        },
                        headers: {
                            "ETag": self.entity['lastupdate']
                        }
                    }).then(function (response) {
                        self.savingstate = 2;
                        $rootScope.$broadcast("atendimento_admin_solicitacoes_submitted", self.entity);

                        self.recarregarContadores();

                    }).catch(function (response) {
                        if (response.status == 409) {
                            if (confirm(response.data.message)) {
                                self.entity['lastupdate'] = response.data.entity['lastupdate'];
                                self.saveAtribuir(responsavel_web);
                            }
                        } else if (response.status === 400 || response.status === 401) {
                            alert(response.data.message);
                        } else {
                            self.savingstate = 3;
                            toaster.pop({
                                type: 'error',
                                title: response.data.erro || response.data.message
                            });
                        }
                    });
                };

                self.saveEmailContato = function () {
                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }

                    if (!self.verificaSeEmailContatoValido()) {
                        return;
                    }

                    self.savingstate = 1;
                    
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_contato', {'id': self.entity.atendimento}, false),
                        data: {
                            'email': self.entity['email'],
                            'lastupdate': self.entity['lastupdate']
                        },
                        headers: {
                            "ETag": self.entity['lastupdate']
                        }
                    }).then(function (response) {
                        self.savingstate = 2;
                        $rootScope.$broadcast("atendimento_admin_solicitacoes_submitted", self.entity);
                    }).catch(function (response) {
                        if (response.status == 409) {
                            if (confirm(response.data.message)) {
                                self.entity['lastupdate'] = response.data.entity['lastupdate'];
                                self.saveAtribuir(responsavel_web);
                            }
                        } else if (response.status === 401) {
                            alert(response.data.message);
                        } else {
                            self.savingstate = 3;
                            toaster.pop({
                                type: 'error',
                                title: response.data.erro || response.data.message
                            });
                        }
                    });
                };

                self.verificaSeEmailContatoValido = function () {
                    var reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    if (reg.test(self.entity.email) == false) {
                        self.email_contato_invalido = true;
                        return false;
                    } else {
                        self.email_contato_invalido = false;
                        return true;
                    }
                };

                self.openEmailModal = function (id, tipo) {
                    AtendimentoAdminEmailOriginal.load(id, tipo).then(function (response) {
                        if (response.data.length > 0) {
                            self.entity.emailOriginal = response.data.pop();
                            self.entity.emailOriginal.created_at = Date.parse(self.entity.emailOriginal.created_at);
                            self.entity.emailOriginal.emailoriginal = JSON.parse(self.entity.emailOriginal.emailoriginal);
                            
                            if (self.entity.emailOriginal.emailoriginal.attachments) {
                                self.entity.emailOriginal.emailoriginal.attachments = JSON.parse(self.entity.emailOriginal.emailoriginal.attachments);
                            }
                            
                            self.modalInstance = $uibModal.open({
                                animation: false,
                                templateUrl: nsjRouting.generate('template_email_modal', {}, false),
                                scope: $scope
                            });
                        } else {
                            toaster.pop({
                                type: 'info',
                                title: "Este histórico não gerou mensagem original."
                            });
                        }
                    });
                };

                function tratarCanal(canal) {
                    switch (canal) {
                        case 'portal':
                            return 'Portal';
                            break;
                        case 'manual':
                            return 'Manual';
                            break;
                        case 'email':
                            return 'E-mail';
                            break;
                    }
                }

                self.isBusy = function () {
                    return self.busy;
                };

                self.load = function () {
                    $http
                            .get(nsjRouting.generate('atendimento_admin_solicitacoes_get', angular.extend(self.constructors, {'id': $stateParams['atendimento']}), false))
                            .then(function (response) {
                                self.entity = response.data;
                                
                                self.habilitarFollowup = response.data.bloqueado;
                                self.activeTab = self.entity.situacao === 0 ? 0 : 1;
                                self.entity.sintoma = $sce.trustAsHtml(self.entity.sintoma);
                                self.entity.resumo = $sce.trustAsHtml(self.entity.resumo);
                                
                                self.entity.canal = tratarCanal(self.entity.canal);
                                self.entity.data_adiamento = moment(self.entity.data_adiamento);
                                self.adiamento = moment(self.entity.data_adiamento).calendar();
                                
                                self.permitirAdiamento = (self.usuarioLogado.email === self.entity.responsavel_web && self.entity.situacao == 0);
                                
                                angular.forEach(self.entity.historico, function (historico) {
                                    if (historico.valornovo.historico) {
                                        var imgExistsHistorico = historico.valornovo.historico.indexOf('<img');
                                        if (imgExistsHistorico > 0) {
                                            historico.valornovo.historico = historico.valornovo.historico.replace(/(<img("[^"]*"|[^\/">])*)\/>/gi, "$1 imgbox />");
                                        }
                                        historico.valornovo.historico = $sce.trustAsHtml(historico.valornovo.historico);
                                    }

                                    if (historico.valornovo.sintoma) {
                                        var imgExistsSintoma = historico.valornovo.sintoma.indexOf('<img');
                                        if (imgExistsSintoma > 0) {
                                            historico.valornovo.sintoma = historico.valornovo.sintoma.replace(/(<img("[^"]*"|[^\/">])*)\/>/gi, "$1 imgbox />");
                                        }
                                        historico.valornovo.sintoma = $sce.trustAsHtml(historico.valornovo.sintoma);
                                    }
                                });

                                if (self.entity.responsavel_web_tipo == 0) {
                                    self.exibeAtribuido = null;
                                } else {
                                    self.exibeAtribuido = self.entity.atribuido_a === undefined || self.entity.atribuido_a === null ? "" : self.entity.atribuido_a.label;
                                }

                                if (self.entity.responsavel_web != self.usuarioLogado && self.entity.situacao == 0) {
                                    hotkeys.bindTo($scope).add({
                                        "combo": "ctrl+alt+m",
                                        "description": "Atribuir esse chamado a você para responder.",
                                        "callback": function () {
                                            self.atribuirUsuarioLogado();
                                        }
                                    });
//                                    tndmnt_dmn_slctcs_shw_cntrllr.atribuirUsuarioLogado(tndmnt_dmn_slctcs_shw_cntrllr.usuarioLogado)
                                }

                                $rootScope.$broadcast("atendimento_admin_solicitacoes_loaded", self.entity.cliente);
                            })
                            .catch(function (response) {
                                if (response.status == "404") {
                                    $state.go('404', {'atendimento': $stateParams['atendimento']}, {location: false});
                                }
                            })
                            .finally(function () {
                                self.busy = false;
                            });
                };
                self.isSpam = function () {
                    if (confirm("Tem certeza que deseja definir o atendimento #" + self.entity.numeroprotocolo + " como SPAM?")) {
                        self.savingstate = 1;
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_solicitacoes_definir_spam', {'id': self.entity.atendimento}, false)
                        }).then(function (response) {
                            $rootScope.$broadcast("atendimento_admin_solicitacoes_submitted", self.entity);
                            self.savingstate = 2;
                        }).catch(function (response) {
                            if (response.status === 401) {
                                alert(response.data.message);
                            } else {
                                toaster.pop({
                                    type: 'error',
                                    title: response.data.erro
                                });                                
                            }
                            
                            self.savingstate = 3;
                        });
                    }
                };
                self.close = function () {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    ;

                    if (confirm("Tem certeza que deseja fechar o atendimento #" + self.entity.numeroprotocolo + " ?")) {
                        self.savingstate = 1;
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_solicitacoes_fechar', {'id': self.entity.atendimento}, false)
                        }).then(function (response) {
                            $rootScope.$broadcast("atendimento_admin_solicitacoes_submitted", self.entity);
                            self.savingstate = 2;
                        }).catch(function (response) {
                            if (response.status === 401) {
                                alert(response.data.message);
                            } else {
                                toaster.pop({
                                    type: 'error',
                                    title: response.data.erro
                                });                                
                            }
                            
                            self.savingstate = 3;
                        });
                    }
                };
                self.open = function (wasSpam) {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    ;

                    var strConfirmMsg;
                    if (wasSpam) {
                        strConfirmMsg = "Tem certeza que o atendimento #" + self.entity.numeroprotocolo + " não é um SPAM?";
                    } else {
                        strConfirmMsg = "Tem certeza que deseja abrir o atendimento #" + self.entity.numeroprotocolo + " ?";
                    }

                    if (confirm(strConfirmMsg)) {
                        self.savingstate = 1;
                        $http({
                            method: "PUT",
                            url: nsjRouting.generate('atendimento_admin_solicitacoes_abrir', {'id': self.entity.atendimento}, false)
                        }).then(function (response) {
                            self.entity.situacao = 0;
                            self.entity.removeSpam = true;
                            $rootScope.$broadcast("atendimento_admin_solicitacoes_submitted", self.entity);
                            self.savingstate = 2;
                        }).catch(function (response) {
                            if (response.status === 401) {
                                alert(response.data.message);
                            } else {
                                toaster.pop({
                                    type: 'error',
                                    title: response.data.erro
                                });                                
                            }
                            
                            self.savingstate = 3;
                        });
                    }
                };

                self.filterSolicitacaoList = function (entity) {
                    if ($stateParams['atendimento'] !== entity.atendimento) {
                        return true;
                    } else {
                        return false;
                    }
                };

                self.podeResponder = function () {
                    return (self.entity.responsavel_web == self.usuarioLogado.email && self.entity.tipo == 0);
                };

                self.verMaisSolicitacoesCliente = function () {
                    if (self.cliente_solicitacoes_qtd < 15) {
                        self.cliente_solicitacoes_qtd += 5;
                    }
                };

                self.atribuidoAMim = function () {
                    return ((self.entity.situacao == 0) && (self.entity.responsavel_web == self.usuarioLogado.email));
                };

                self.assuntoKeyDown = function(e) {
                    // Caso tecle ENTER
                    if (e.keyCode === 13) {
                        if (self.entity.resumo) {
                            self.alterarResumo();

                            e.preventDefault();
                        }
                    }                    
                }

                self.alterarResumo = function () {
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_atendimento_alterar_resumo', {'id': self.entity.atendimento}, true),
                        data: {
                            'atendimento': self.entity.atendimento,
                            'resumo': self.entity.resumo
                        }
                    }).then(function (response) {
                        toaster.pop({
                            type: 'success',
                            title: 'Assunto alterado com sucesso.'
                        });
                        self.load();
                    }).catch(function (response) {
                        if (response.status === 401) {
                            alert(response.data.message);
                        } else if (response.status === 403) {
                            toaster.pop({
                                type: 'error',
                                title: 'Usuário não possui permissão para executar esta ação.'
                            }); 
                        } else {
                            toaster.pop({
                                type: 'error',
                                title: response.data.message
                            });                                
                        }
                    });
                };

                self.alteraVisibilidade = function () {

                    if (!self.usuarioPodeInteragirNoChamado()) {
                        return self.mensagemUsuarioNaoPodeInteragirNoChamado();
                    }
                    ;

                    self.entity.visivelparacliente = !self.entity.visivelparacliente;
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_admin_solicitacoes_alteravisibilidade', {'id': self.entity.atendimento}, false),
                        data: {
                            visivel: self.entity.visivelparacliente
                        }
                    }).then(function (response) {
                        toaster.pop({
                            type: 'success',
                            title: 'Visibilidade alterada com sucesso.'
                        });
                        self.load();
                    }).catch(function (response) {
                        if (response.status === 401) {
                                alert(response.data.message);
                        } else {
                            toaster.pop({
                                type: 'error',
                                title: response.data.erro
                            });                                
                        }
                    });
                };

                self.agenda = function () {
                    if (self.entity.cliente !== undefined && self.entity.cliente !== null) {
                        $state.go('crm_proximoscontatos_new', {cliente: self.entity.cliente.cliente, nome: self.entity.cliente.nome, atendimento: {atendimento: self.entity.atendimento, numeroprotocolo: self.entity.numeroprotocolo}});
                    } else {
                        toaster.pop({type: 'danger', title: 'Não é possível criar um agendamento para um chamado sem cliente.'});
                    }
                };

                $scope.$on('atendimento_admin_solicitacoes_submitted', function (event) {
                    self.load();
                });
                
                $scope.$on('atendimento_admin_followups_submitted', function (event) {
                    self.load();
                });

                $scope.$on('atendimento_admin_solicitacoes_form_invalid', function (event) {
                    self.required = {};

                    if (self.entity.camposcustomizados === null || self.entity.camposcustomizados === undefined) {
                        self.entity.camposcustomizados = {};
                    }

                    self.camposcustomizados.forEach(function (campo) {
                        if ((self.entity.camposcustomizados[campo.atendimentocampocustomizado] === null
                                || self.entity.camposcustomizados[campo.atendimentocampocustomizado] === undefined
                                || self.entity.camposcustomizados[campo.atendimentocampocustomizado] === '') && campo.obrigatorio) {
                            self.required[campo.atendimentocampocustomizado] = true;
                        }
                    });
                    
                    alert("Alguns campos do formulário não foram preenchidos corretamente. Revise as informações e tente adicionar sua resposta novamente.");
                });
                
                $scope.$on('atendimento_admin_solicitacoes_loaded', function (event, cliente) {
                    for (var x in self.entity.camposcustomizados) {
                        if (self.entity.camposcustomizados[x] != null) {
                            self.entity.camposcustomizados[x] = String(self.entity.camposcustomizados[x]);
                        }
                    }
                    if (cliente) {
                        self.loadCliente(cliente);
                    }
                    if(self.entity.mesclado_a){
                        self.modalAvisoMeslcado = $uibModal.open({
                            animation: false,
                            templateUrl: nsjRouting.generate('template_mesclado_aviso_modal', {}, false),
                            scope: $scope
                        });
                    }
                });

                self.load();

                if ($stateParams['cliente'] != null) {
                    self.loadCliente($stateParams['cliente']);
                }

                self.carregaUsuariosCliente = function (cliente) {
                    if (typeof self.entity.cliente === 'object') {
                        $http.get(nsjRouting.generate(
                            'ns_clientes_usuarios_lista_usuarios',
                            { 'cliente': cliente.cliente }, 
                            false
                        )).then(function (response) {
                            self.cliente_usuarios = [];
                            self.cliente_status_suporte = response.data[0].cliente.status_suporte;

                            for (var i in response.data) {
                                self.cliente_usuarios.push(response.data[i].conta);
                            }
                        }).catch(function (response) {
                            self.cliente_usuarios = [];
                        });
                    }
                };
            }])
        .controller('AdminSolicitacoesFormController', ['$http', '$scope', '$rootScope', '$stateParams', '$state', 'nsjRouting', 'NsClientes', 'contasResolve', 'UsuarioLogado', 'ServicosAtendimentosfilas', 'AtendimentoAdminSolicitacoes', 'UsuarioComEquipe', 'camposcustomizados', 'hotkeys', 'usuariosAlocadosEquipe',
            function ($http, $scope, $rootScope, $stateParams, $state, nsjRouting, NsClientes, contasResolve, UsuarioLogado, ServicosAtendimentosfilas, AtendimentoAdminSolicitacoes, UsuarioComEquipe, camposcustomizados, hotkeys, usuariosAlocadosEquipe) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.submitted = false;
                self.submitting = false;
                self.usuarioLogado = UsuarioLogado.getUsuario();
                self.filas = ServicosAtendimentosfilas.load();
                self.entity = {};
                self.entity.tipo = 0;
                self.constructors = {};
                self.entity.visivelparacliente = (nsj.globals.getInstance().get('usuario')['CHAMADOS_VISIVELCLIENTE_PADRAO'] == 1) ? true : false;
                self.habilitarAssunto = nsj.globals.getInstance().get('HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS');
                self.atendente_cria_chamado_sem_cliente = (nsj.globals.getInstance().get('ATENDENTE_CRIA_CHAMADO_SEM_CLIENTE') == 1) ? true : false;
                self.exibe_cliente = nsj.globals.getInstance().get('EXIBE_CLIENTE');
                self.cliente_status_suporte = '';
                self.cliente_usuarios = [];
                self.email_contato_invalido = false;
                self.qtdAnexos = 0;
                self.uploadAnexos = false;
                self.camposcustomizados = camposcustomizados;
                self.required = {};
                self.optionusuariologado = [];

                // Pegar todas as contas exceto usuário logado e as contas sem equipe
                if(contasResolve && contasResolve.length > 0) {
                    self.contas = contasResolve.filter(function (conta) {
                        return (conta.email !== self.usuarioLogado.email) && (usuariosAlocadosEquipe.indexOf(conta.email) >= 0);
                    });
                }

                //Se usuário logado tiver equipe, então populamos o campo atribuido com usuário logado
                if (usuariosAlocadosEquipe.indexOf(self.usuarioLogado.email) >= 0) {
                    self.optionusuariologado = [{'nome': 'Atribuir a mim: ' + self.usuarioLogado.nome, 'email': self.usuarioLogado.email}];
                    self.atribuidoa = self.optionusuariologado[0].email;
                    self.exibeAtribuido = self.optionusuariologado[0].nome;
                    self.entity.responsavel_web = self.optionusuariologado[0].email;
                }
                
                if (nsj.globals.getInstance().get('USUARIOSDISP_USUARIO_PODE_SER_ATRIBUIDO_AO_CHAMADO') == 0) {
                    $http.get(nsjRouting.generate('atendimento_usuariosdisponibilidades_lista_indisponiveis', {}, false)).then(function (response) {

                        for (var i in response.data) {
                            for (var j in self.contas) {
                                if (self.contas[j]['email'] == response.data[i]) {
                                    self.contas.splice(j, 1);
                                }
                            }
                        }
                    });
                }
                
                hotkeys.bindTo($scope).add({
                    "combo": "alt+s",
                    "description": "Salvar",
                    "allowIn": ['INPUT', 'SELECT', 'TEXTAREA'],
                    "callback": function (event) {
                        event.preventDefault();
                        self.submit(self.form);
                    }
                });

                self.usuarioPodeInteragirNoChamado = function () {
                    if (nsj.globals.getInstance().get('usuario')['indisponivel'] && nsj.globals.getInstance().get('USUARIOSDISP_USUARIO_HABILITADO_CHAMADOS') == 0) {
                        return false;
                    } else {
                        return true;
                    }
                };

                if (!self.usuarioPodeInteragirNoChamado()) {
                    alert('Usuário logado está indisponível no momento e não pode interagir no chamado.');
                    $state.go('atendimento_admin_solicitacoes');
                }

                for (var i in $stateParams) {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }

                self.agruparPorTipo = function (item) {
                    return item.email ? 'Usuário' : 'Fila';
                };
                self.atribuir = function ($item) {

                    if (!$item) {
                        self.exibeAtribuido = null;
                        self.entity.responsavel_web = null;
                    } else if ($item.email) {
                        if ($item.nome) {
                            self.exibeAtribuido = $item.nome;
                            self.entity.responsavel_web = $item.email;
                        } else {
                            for (var i in self.contas) {
                                if (self.contas[i].email == $item.email) {
                                    self.exibeAtribuido = self.contas[i].nome + ' (' + self.contas[i].email + ')';
                                    self.entity.responsavel_web = self.contas[i].email;
                                }
                            }
                        }
                    } else if ($item.atendimentofila) {
                        for (var i in self.filas) {
                            if (self.filas[i].atendimentofila == $item.atendimentofila) {
                                self.exibeAtribuido = self.filas[i].nome;
                                self.entity.responsavel_web = self.filas[i].atendimentofila;
                            }
                        }
                    }
                };

                self.refreshClientes = function ($search) {
                    NsClientes.filter.key = $search;
                    NsClientes.filter.filterfield = 'all';
                    self.cliente = NsClientes.load();
                };

                self.alteraTipo = function (tipo) {
                    self.entity.tipo = tipo;
                };

                $scope.$on("uploadAnexos", function (event, args) {
                    if (args) {
                        self.qtdAnexos++;
                        self.uploadAnexos = true;
                    } else {
                        self.qtdAnexos--

                        //Se todos os anexos já estiverem carregados liberar o submit
                        if (self.qtdAnexos == 0) {
                            self.uploadAnexos = false;
                        }
                    }
                });
                
                self.removerAlerta = function (campocustomizado) {
                    if (self.required !== undefined) {                        
                        self.required[campocustomizado.atendimentocampocustomizado] = false;
                    }
                    
                    if (self.entity.camposcustomizados !== undefined) {
                        if (self.entity.camposcustomizados[campocustomizado.atendimentocampocustomizado] === undefined) {
                            self.required[campocustomizado.atendimentocampocustomizado] = true;
                        }
                    }
                };
                
                function validaCamposCustomizados() {
                    if (self.entity.camposcustomizados === null || self.entity.camposcustomizados === undefined) {
                        self.entity.camposcustomizados = {};
                    }

                    self.camposcustomizados.forEach(function (campo) {
                        if ((self.entity.camposcustomizados[campo.atendimentocampocustomizado] === null
                                || self.entity.camposcustomizados[campo.atendimentocampocustomizado] === undefined
                                || self.entity.camposcustomizados[campo.atendimentocampocustomizado] === '') && campo.obrigatorio) {
                            self.required[campo.atendimentocampocustomizado] = true;
                        }
                    });
                }

                self.submit = function (form) {
                    if (!self.uploadAnexos) {
                        self.submitted = true;

                        if (!self.verificaSeEmailContatoValido() || form.$invalid) {
                            if (form.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar");
                            }
                            
                            validaCamposCustomizados();
                            
                            alert("Alguns campos do formulário não foram preenchidos corretamente. Revise as informações e tente criar o chamado novamente.");
                            
                        } else if (self.verificaSeEmailContatoValido() && form.$valid && !AtendimentoAdminSolicitacoes.submitting && !self.submitting) {
                            self.submitting = true;
                            if (typeof self.entity.cliente === 'object') {
                                if (self.verificaSeContatoNovo()) {
                                    if (window.confirm("Deseja adicionar este contato nos usuários do cliente?")) {
                                        self.salvaContatoNovo();
                                    }
                                }
                            }
                            
                            AtendimentoAdminSolicitacoes.save(self.entity);
                        }
                    } else {
                        alert("Aguarde o upload dos arquivos anexados para continuar.");
                    }

                };

                self.verificaSeEmailContatoValido = function () {
                    var reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    if (reg.test(self.entity.email) == false) {
                        self.email_contato_invalido = true;
                        return false;
                    } else {
                        self.email_contato_invalido = false;
                        return true;
                    }
                };

                self.onSubmitSuccess = $scope.$on("atendimento_admin_solicitacoes_submitted", function (event, args) {
                    $state.go('atendimento_admin_solicitacoes_show', {'atendimento': args.response.data['atendimento']});
                });

                self.onSubmitError = $scope.$on("atendimento_admin_solicitacoes_submit_error", function (event, args) {
                    self.submitting = false;
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        var acao = ((args.response.config.method = "PUT") ? "atualizar" : "inserir");
                        alert(("Ocorreu um erro ao tentar " + acao + "."));
                    }
                });

                self.carregaUsuariosCliente = function (cliente) {
                    if (typeof self.entity.cliente === 'object') {
                        $http.get(nsjRouting.generate('ns_clientes_usuarios_lista_usuarios',
                        angular.extend(self.constructors, {'cliente': cliente.cliente}), false))
                        .then(function (response) {
                            self.cliente_usuarios = [];
                            self.cliente_status_suporte = response.data[0].cliente.status_suporte;

                            for (var i in response.data) {
                                self.cliente_usuarios.push(response.data[i].conta);
                            }
                        }).catch(function (response) {
                            self.cliente_usuarios = [];
                        });
                    }

                    return;
                };

                self.verificaSeContatoNovo = function () {
                    for (var i in self.cliente_usuarios) {
                        if (self.cliente_usuarios[i] == self.entity.email) {
                            return false;
                        }
                    }
                    return true;
                };
                self.salvaContatoNovo = function () {
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('ns_clientes_usuarios_adiciona', {}, false),
                        data: {
                            'cliente': self.entity.cliente.cliente,
                            'conta': self.entity.email
                        }
                    });
                };

                if ($stateParams['cliente']) {
                    self.entity.cliente = {
                        cliente: $stateParams['cliente'],
                        nome: $stateParams['nome']
                    };
                    self.carregaUsuariosCliente($stateParams);
                }

            }]).controller('ModalDatePickerController', ['$uibModalInstance', '$http', 'nsjRouting', 'toaster', function ($uibModalInstance, $http, nsjRouting, toaster) {
                var self = this;
                self.minDate = new Date();
                            
                self.fechar = function () {
                    $uibModalInstance.dismiss('cancel');
                };

                self.salvar = function () {
                    if (self.data !== null && self.data !== undefined) {
                        $uibModalInstance.close(self.data);
                    }
                };
            }]).controller('MesclarModalCtrl', ['$uibModalInstance', '$http', 'nsjRouting', 'toaster','chamados', '$state',
            function($uibModalInstance, $http, nsjRouting, toaster, chamados, $state){
                var self = this;
                self.chamados = chamados;
                self.chamadodestino = null;

                self.close = function(){
                    $uibModalInstance.dismiss('cancel');
                };

                self.save = function(){
                    
                    if(self.chamadodestino){
                        var chamados = self.chamados.map(function(i){
                            return i.atendimento;
                        });

                        $http({
                            method: "POST",
                            url: nsjRouting.generate('atendimento_admin_solicitacoes_mesclar', {  }, false),
                            data: { chamados: chamados, chamadoDestino: self.chamadodestino.atendimento }
                        }).then(function (response) {
                            $state.reload();
                            self.close();
                            toaster.pop({type: 'success', title: 'Chamados mesclados com sucesso!'});
                        }).catch(function (response) {
                            toaster.pop({type: 'error', title: 'Não foi possível mesclar os chamados!'});
                        });
                    }
                };

            }]);
