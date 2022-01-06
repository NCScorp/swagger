angular.module('mda')
        .config(['$stateProvider', '$urlRouterProvider', 'nsjRoutingProvider', 'UsuarioLogadoProvider', function ($stateProvider, $urlRouterProvider, nsjRoutingProvider, UsuarioLogadoProvider) {

                var resolve = {
                    'camposcustomizados': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                        return $q(function (resolve, reject) {
                            $http({
                                method: 'GET',
                                url: nsjRouting.generate('atendimento_cliente_servicos_clientescamposcustomizados_index', {}, false)
                            }).then(function (response) {
                                resolve(response.data);
                            }).catch(function (response) {
                                reject(response);
                            });
                        });
                    }],
                };

                $stateProvider
                        .state('atendimento_cliente_solicitacoes', {
                            url: "/chamados?situacao?cliente?minhas?created_at?adiado?camposcustomizados?atribuidoa?created_at_ini?created_at_fim",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_solicitacoes_template'),
                            controller: 'ClienteSolicitacoesListController',
                            controllerAs: 'tndmnt_clnt_slctcs_lst_cntrllr',
                            resolve: resolve
                        })
                        .state('atendimento_cliente_solicitacoes_new', {
                            parent: 'atendimento_cliente_solicitacoes',
                            url: "/novo",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_solicitacoes_template_form'),
                            controller: 'ClienteSolicitacoesFormCtrl',
                            controllerAs: 'tndmnt_clnt_slctcs_frm_cntrllr'
                        })
                        .state('404', {
                            parent: 'atendimento_cliente_solicitacoes',
                            url: "/404",
                            params: {
                                atendimento: null
                            },
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_solicitacoes_notfound'),
                            controller: 'notFoundController',
                            controllerAs: 'tndmnt_clnt_slctcs_lst_cntrllr'
                        })
                        .state('atendimento_cliente_solicitacoes_show', {
                            parent: 'atendimento_cliente_solicitacoes',
                            url: "/:atendimento",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_solicitacoes_template_show'),
                            controller: 'ClienteSolicitacoesShowCtrl',
                            controllerAs: 'tndmnt_clnt_slctcs_frm_cntrllr'
                        });
            }])


        .controller("ClienteSolicitacoesShowCtrl", ['$rootScope', '$scope', '$state', '$stateParams', '$http', '$sce', 'nsjRouting', 'PageTitle', '$uibModal',
            function ($rootScope, $scope, $state, $stateParams, $http, $sce, nsjRouting, PageTitle, $uibModal) {

                var self = this;
                self.busy = true;
                self.entity = {};
                self.constructors = {};
                self.habilitarFollowup = false;
                for (var i in $stateParams) {
                    self.constructors[i] = $stateParams[i];
                }
                self.habilitarAssunto = nsj.globals.getInstance().get('HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS');
                self.updateTitle = function () {
                    var title = 'Chamado ';
                    if (Object.getOwnPropertyNames(self.entity).length !== 0) {
                        title += '#' + self.entity.numeroprotocolo;
                    }
                    PageTitle.setTitle(title);
                };

                self.isBusy = function () {
                    return self.busy;
                };

                self.getNumeroChamado = function(id){
                    var numeroprotocolo = '';
                    self.entity.chamadosmesclados.forEach(function(chamado){
                       if(chamado.atendimento == id){
                            numeroprotocolo = chamado.numeroprotocolo;
                       }
                    });
                    return numeroprotocolo;
                };

                self.load = function () {
                    $http.get(nsjRouting.generate('atendimento_cliente_solicitacoes_get', angular.extend(self.constructors, {'id': $stateParams.atendimento}), false))
                            .then(function (response) {
                                self.entity = response.data;
                                self.habilitarFollowup = response.data.bloqueado;

                                if (self.entity.sintoma) {
                                    var imgExistsSintoma = self.entity.sintoma.indexOf('<img');
                                    if (imgExistsSintoma > 0) {
                                        self.entity.sintoma = self.entity.sintoma.replace(/(<img("[^"]*"|[^\/">])*)\/>/gi, "$1 imgbox />");
                                    }

                                    self.entity.sintoma = $sce.trustAsHtml(self.entity.sintoma);
                                }

                                var url_avaliacao_original = nsj.globals.getInstance().get('usuario')['URL_AVALIACAO_RESPOSTA_CHAMADO'];

                                if (url_avaliacao_original != "" && url_avaliacao_original != null) {
                                    for (var i in self.entity.followups) {

                                        if (self.entity.followups[i].historico) {
                                            var imgExistsFollowup = self.entity.followups[i].historico.indexOf('<img');
                                            if (imgExistsFollowup > 0) {
                                                self.entity.followups[i].historico = self.entity.followups[i].historico.replace(/(<img("[^"]*"|[^\/">])*)\/>/gi, "$1 imgbox />");
                                            }

                                            self.entity.followups[i].historico = $sce.trustAsHtml(self.entity.followups[i].historico);
                                        }

                                        if (self.entity.followups[i].criador == false) {
                                            url_avaliacao = url_avaliacao_original.replace('[[chamado]]', self.entity.numeroprotocolo);
                                            if (self.entity.cliente !== null) {
                                                url_avaliacao = url_avaliacao.replace('[[cliente]]', self.entity.cliente.codigo);
                                                url_avaliacao = url_avaliacao.replace('[[razao_social]]', self.entity.cliente.nome?self.entity.cliente.nome:'');
                                                url_avaliacao = url_avaliacao.replace('[[vendedor]]', self.entity.vendedor?self.entity.vendedor:'');
                                                url_avaliacao = url_avaliacao.replace('[[email_cliente]]', self.entity.email ? self.entity.email : '');
                                            }
                                            url_avaliacao = url_avaliacao.replace('[[nome_atendente]]', self.entity.followups[i].created_by.nome);
                                            url_avaliacao = url_avaliacao.replace('[[email_atendente]]', self.entity.followups[i].created_by.email);
                                            self.entity.followups[i].url_avaliacao = url_avaliacao;
                                        } else {
                                            self.entity.followups[i].url_avaliacao = null;
                                        }
                                    }
                                }
                                $rootScope.$broadcast("atendimento_cliente_solicitacoes_loaded", self.entity);

                            })
                            .catch(function (response) {
                                if (response.status == "404" || response.status == "403") {
                                    $state.go('404', {'atendimento': $stateParams['atendimento']}, {location: false});
                                }
                            })
                            .finally(function () {
                                self.updateTitle();
                                self.busy = false;
                            });

                };

                $rootScope.$on('atendimento_cliente_followups_submitted', function (event, toState, toParams, fromState, fromParams, options) {
                    self.load();
                });
                $rootScope.$on('atendimento_cliente_solicitacoes_loaded', function (event, entity) {
                    if(self.entity.mesclado){
                        self.modalAvisoMeslcado = $uibModal.open({
                            animation: false,
                            templateUrl: nsjRouting.generate('template_mesclado_cliente_aviso_modal', {}, false),
                            scope: $scope
                        });
                    }
                });

                self.load();

            }])
        .controller('ClienteFollowupsFormController', ['$rootScope', '$scope', '$http', '$stateParams', '$state', 'nsjRouting', 'toaster',
            function ($rootScope, $scope, $http, $stateParams, $state, nsjRouting, toaster) {

                var self = this;
                self.submitted = false;
                self.submitting = false;
                self.qtdAnexos = 0;
                self.uploadAnexos = false;
                self.entity = {};
                self.constructors = {};
                for (var i in $stateParams) {
                    if (typeof ($stateParams[i]) != 'function') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.clear = function () {
                    self.entity.historico = '';
                    self.form.$setPristine();
                    self.submitted = false;
                };

                self.historicoVazio = function () {
                    return (!self.entity.historico && self.submitted);
                };


                self.onSubmitSuccess = function (response) {
                    $rootScope.$broadcast("atendimento_cliente_followups_submitted");
                    self.entity.anexos = [];
                    self.submitted = false;
                    toaster.pop({
                        type: 'success',
                        title: 'Salvo com sucesso.'
                    });
                    self.clear();
                };

                self.onSubmitError = function (response) {
                    //alert(response.data.erro);
                    toaster.pop({
                        type: 'error',
                        title: response.data.erro || response.data.message
                    });
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

                self.submit = function (form, event) {
                    if (!self.uploadAnexos) {
                        self.submitted = true;

                        if (form) {
                            self.form = form;
                        }

                        if (form.$invalid) {
                            if (form.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar");
                            }
                        } else if (form.$valid && !self.submitting) {
                            self.submitting = true;
                            var method = "POST";
                            var url = nsjRouting.generate('atendimento_cliente_followups_create', angular.extend(self.constructors), false);
                            $http({
                                method: method,
                                url: url,
                                data: self.entity
                            }).then(function (response) {
                                self.onSubmitSuccess(response);
                            }).catch(function (response) {
                                self.onSubmitError(response);
                            }).finally(function (response) {
                                self.submitting = false;
                            });
                        }
                    } else {
                        alert("Aguarde o upload dos arquivos anexados para continuar.");
                    }

                };


            }])

        .controller('ClienteSolicitacoesFormCtrl', ['UsuarioLogado', '$stateParams', '$state', 'AtendimentoClienteSolicitacoes', '$http', 'nsjRouting', '$scope', 'toaster', 'PageTitle', '$sce',
            function (UsuarioLogado, $stateParams, $state, AtendimentoClienteSolicitacoes, $http, nsjRouting, $scope, toaster, PageTitle, $sce) {

                var self = this;
                self.submitted = false;
                self.submitting = false;
                self.entity = {};
                self.constructors = {};
                self.usuarioLogado = UsuarioLogado;
                self.criarComo = [];
                self.qtdAnexos = 0;
                self.uploadAnexos = false;
                self.habilitarAssunto = nsj.globals.getInstance().get('HABILITAR_CAMPO_ASSUNTO_NOS_CHAMADOS');
                self.tituloAssunto = nsj.globals.getInstance().get('TITULO_ASSUNTO');
                self.placeholderAssunto = nsj.globals.getInstance().get('PLACEHOLDER_ASSUNTO');
                self.sintomaLabel = nsj.globals.getInstance().get('CHAMADO_SINTOMA_LABEL');
                self.sintomaObs = nsj.globals.getInstance().get('CHAMADO_SINTOMA_OBSERVACAO');
                self.entity.cliente = {};
                self.artigos = [];
                self.artigoEscolhido = {};
                self.selecionouArtigo = false;
                for (var i in $stateParams) {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }

                self.scrollToTop = function() {
                    var divArtigo = document.getElementsByClassName('article-area')[0];

                    if (divArtigo) {
                        divArtigo.style.scrollBehavior = 'smooth';
                        divArtigo.scrollTop = 0;
                    }
                }

                self.updateTitle = function () {
                    var title = 'Novo Chamado ';
                    PageTitle.setTitle(title);
                };
                self.updateTitle();
                self.camposcustomizados = nsj.globals.getInstance().get('usuario')['camposcustomizados'];

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

                self.abrirArtigo = function (artigo) {
                    var request = {
                        'method': 'GET',
                        'url': nsjRouting.generate('atendimento_cliente_artigos_get', angular.extend({}, {'id': artigo.artigo}), false)
                    };

                    $http(request).then(function (response) {
                        self.artigoEscolhido = response.data;
                        self.artigoEscolhido.conteudo = $sce.trustAsHtml(self.artigoEscolhido.conteudo);
                        self.selecionouArtigo = true;
                    });
                };

                self.submit = function () {
                    if (!self.uploadAnexos) {
                        self.submitted = true;
                        if (self.form.$invalid) {
                            if (self.form.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar");
                            }
                        } else if (self.form.$valid && !AtendimentoClienteSolicitacoes.submitting && !self.submitting) {
                            self.submitting = true;

                            var campos = '';

                            self.camposcustomizados.forEach(function (campo) {
                                 if(campo.habilitar_busca && campo.exibidoformcontato){
                                   campos += self.entity.camposcustomizados[campo.atendimentocampocustomizado] ? self.entity.camposcustomizados[campo.atendimentocampocustomizado] + ' ' : '' ;
                                 }else {
                                   campos += '';
                                 }
                            });

                            if(self.entity.resumo){
                              campos += self.entity.resumo;
                            }

                            var request = {
                                'method': 'GET',
                                'url': nsjRouting.generate('atendimento_cliente_artigos_index', angular.extend({}, {'offset': null, 'filter': {'key': campos , 'filterfield': 'all'}}), false)
                            };

                            $http(request).then(function (response) {
                                self.artigos = response.data;
                                self.submitting = false;

                                if (self.artigos.length === 0) {
                                    self.criarChamado();
                                }
                            });
                        }
                    } else {
                        alert("Aguarde o upload dos arquivos anexados para continuar.");
                    }
                };

                self.criarChamado = function () {
                    if (!self.uploadAnexos) {
                        self.submitted = true;
                        if (self.form.$invalid) {
                            if (self.form.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar");
                            }
                        } else if (self.form.$valid && !AtendimentoClienteSolicitacoes.submitting && !self.submitting) {
                            self.submitting = true;
                            AtendimentoClienteSolicitacoes.save(self.entity);
                        }
                    } else {
                        alert("Aguarde o upload dos arquivos anexados para continuar.");
                    }
                };

                self.artigoRespondeuDuvida = function () {
                    if (!self.submitting) {
                        self.submitting = true;

                        var request = {
                            'method': 'PUT',
                            'url': nsjRouting.generate('atendimento_cliente_artigos_incrementar_qtde_evitou_criacao_chamado', {'id': self.artigoEscolhido.artigo}, false)
                        };

                        $http(request).then(function (response) {
                            self.submitting = false;
                            $state.go('atendimento_cliente_solicitacoes', {});
                        });
                    }
                };

                self.voltar = function () {

                    if (self.selecionouArtigo) {
                        self.artigoEscolhido = {};
                        self.selecionouArtigo = false;
                    } else {
                        self.artigos = [];
                    }

                };

                self.onSubmitSuccess = $scope.$on("atendimento_cliente_solicitacoes_submitted", function (event, args) {
                    $state.go('atendimento_cliente_solicitacoes', angular.extend(self.constructors));
                });

                self.onSubmitError = $scope.$on("atendimento_cliente_solicitacoes_submit_error", function (event, args) {
                    if (args.response.status == 409) {
                        if (confirm(args.response.data.message)) {
                            self.entity[''] = args.response.data.entity[''];
                            AtendimentoClienteSolicitacoes.save(self.entity);
                        }
                    } else {
                        if (typeof (args.response.data.erro) !== 'undefined') {
                            alert(args.response.data.erro);
                        } else {
                            var acao = ((args.response.config.method = "PUT") ? "atualizar" : "inserir");
                            alert(("Ocorreu um erro ao tentar " + acao + "."));
                        }
                    }
                    self.submitted = false;
                });

                if (!UsuarioLogado.podeCriarChamado) {
                    $state.go('home');
                }


                if (UsuarioLogado.usuarioSemClienteCriarChamado) {
                    self.criarComo.push({
                        'value': '',
                        'label': 'Minha conta'
                    });

                    //Quando o self.entity.cliente.cliente é nulo e no ng-model do select está definido o mesmo ele gera um espaço em branco
                    //Então foi definido aqui que ao ser nulo ele receberá o valor do criarComo[0].
                    self.entity.cliente = {cliente: self.criarComo[0].value};
                }

                var clientes = UsuarioLogado.getClientes();
                var qtdeClientes = 0;
                for (var i in clientes) {
                    if (clientes[i].cliente_bloqueado == false) {
                        self.criarComo.push({
                            'value': clientes[i]['cliente_id'],
                            'label': clientes[i]['cliente_nome']
                        });
                        qtdeClientes++;
                    }

                }

                if (qtdeClientes > 0) {
                    self.entity.cliente.cliente = $stateParams['cliente'] ? $stateParams['cliente'] : self.criarComo[0]['value'];
                } else if (qtdeClientes <= 0 && nsj.globals.get('usuario')['USUARIO_SEM_CLIENTE_CRIAR_CHAMADO'] != 1) {
                    toaster.pop({type: 'error', title: 'Você não tem as permissões necessárias para criar chamados.'});
                }
            }])
        .controller('ClienteSolicitacoesListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'AtendimentoClienteSolicitacoes', 'UsuarioLogado', 'PageTitle', '$sce', 'camposcustomizados', 'debounce', 'toaster',
            function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, AtendimentoClienteSolicitacoes, UsuarioLogado, PageTitle, $sce, camposcustomizados, debounce, toaster) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.contas = [];
                self.chamadosDescricao = nsj.globals.getInstance().get('CHAMADOS_DESCRICAO');
                self.usuarioLogado = UsuarioLogado.getUsuario();
                self.filtroAtribuicao = {'value': 'atribuidoa', 'label': 'Criado por'};
                self.filter = AtendimentoClienteSolicitacoes.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = AtendimentoClienteSolicitacoes.search;
                self.reload = AtendimentoClienteSolicitacoes.reload;
                self.nextPage = AtendimentoClienteSolicitacoes.load;
                self.loadMore = AtendimentoClienteSolicitacoes.loadMore;
                self.service = AtendimentoClienteSolicitacoes;
                self.constructors = AtendimentoClienteSolicitacoes.constructors = [];
                self.menuSituacaoAtivo = 'todos';
                self.menuOrdenacaoAtiva = 'desc';
                self.filtrosPossiveis = ["camposcustomizados", "created_at_ini", "created_at_fim", "atribuidoa"];
                self.camposcustomizados = camposcustomizados;
                self.criarComo = [];
                self.usuarios = [];
                self.optionusuariologado = [];
                var obj = [];
                obj.atribuidoa = "todos"
                self.filtros = {
                    'camposcustomizados': [],
                    'created_at_ini': [],
                    'created_at_fim': [],
                    'atribuidoa': [],
                };

                self.listaFiltros = [
                    {'value': 'created_at', 'label': 'Data de Criação'},
                    {'value': 'atribuidoa', 'label': 'Criado por'}
                ];
                self.camposcustomizados.map(function (campo) {
                    self.listaFiltros.push({'value': campo.atendimentocampocustomizado, 'label': campo.label});
                    self.filtrosPossiveis.push(campo.atendimentocampocustomizado);
                    self.filtros[campo.atendimentocampocustomizado] = [];
                });

                var clientes_ = UsuarioLogado.getClientes();

                function filtroClientes(filtro)
                {
                    self.usuarios = [];
                    self.contas = [];
                var qtdeClientes = 0;
                for (var i in clientes_) {
                    if (clientes_[i].cliente_bloqueado == false) {
                        self.criarComo.push({
                            'value': clientes_[i]['cliente_id'],
                            'label': clientes_[i]['cliente_nome']
                        });
                        qtdeClientes++;
                            if(filtro != 'meus'){
                            $http.get(nsjRouting.generate('ns_clientes_usuarios_grupos', {'id': clientes_[i]['cliente_id']}, false)).then(function (response) {
                            var id = response.data.cliente;
                            self.usuarios.push([]);
                            if(filtro == 'todos')
                            {
                            self.usuarios[i] = JSON.parse(JSON.stringify(response.data));
                            self.usuarios[i].anotacaoHTML = $sce.trustAsHtml
                            }
                            else {
                                if(id == filtro)
                                {
                                self.usuarios[i] = JSON.parse(JSON.stringify(response.data));
                                self.usuarios[i].anotacaoHTML = $sce.trustAsHtml
                                }
                            }
                            var repeat = false;
                            if(self.usuarios[i] != undefined){
                            self.usuarios[i].forEach(function (usuario) {
                                self.contas.forEach(function (conta) {
                                    if(usuario.conta == conta)
                                    {
                                        repeat = true;
                                    }
                                });
                                if(repeat == false)
                                {
                              self.contas.push(usuario.conta);
                                }
                              else
                              {
                                  repeat = false;
                              }
                            });
                        }
                        });
                    }
                    }

                }
            }

                filtroClientes('todos');

                function formatarData(dateString) {
                    var data = new Date(dateString);

                    if (data == 'Invalid Date') {
                        return null;
                    }

                    var dia = data.getDate();
                    if (dia.toString().length == 1)
                        dia = "0" + dia;
                    var mes = data.getMonth() + 1;
                    if (mes.toString().length == 1)
                        mes = "0" + mes;
                    var ano = data.getFullYear();
                    return ano + "-" + mes + "-" + dia;
                }

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
                        'camposcustomizados': camposcustomizados,
                        'created_at_ini': self.constructors.created_at_ini,
                        'created_at_fim': self.constructors.created_at_fim,
                        'situacao': self.constructors.situacao,
                        'order': self.constructors.order,
                        'atribuidoa' : self.constructors.atribuidoa
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

                self.removeFiltroCreated_at = function (row) {
                    self.filtros.created_at_ini.splice(self.filtros.created_at_ini);
                    self.filtros.created_at_fim.splice(self.filtros.created_at_fim);
                    self.reloadData();
                };

                self.limparFiltroRespostas = function() {
                    if (self.filtros.respostas && self.filtros.respostas[0] != undefined) {
                        if (self.filtros.respostas[0].opt == 0) {
                            self.constructors.ultima_resposta_admin = null;
                        }else{
                            self.constructors.qtd_respostas = null;
                        }
                    }
                }

                self.checaAtribuicao = function ()
                {
                    if(self.filtros.atribuidoa[0] !== undefined && self.filtros.atribuidoa[0].opt == null){
                        self.filtros.atribuidoa.splice(self.filtros.atribuidoa);
                    }
                    self.reloadData();
                }

                function isEmail(str) {
                    var regexEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    return regexEmail.test(str);
                }

                function isGuid(str) {
                    var regexGuid = /^(\{){0,1}[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}(\}){0,1}$/gi;
                    return regexGuid.test(str);
                }

                self.agruparPorTipo = function (item) {
                    return item ? 'Usuário' : 'Vazio';
                };

                self.reloadData = debounce(function () {
                    self.constructors.camposcustomizados = [];
                    self.constructors.responsavel_web = [];
                    self.limparFiltroRespostas();

                    self.filtrosPossiveis.map(function (filtro) {
                        self.constructors[filtro] = [];

                        self.filtros[filtro].map(function (array) {
                            switch (filtro) {
                                case 'created_at_ini':
                                    self.constructors[filtro].push(formatarData(array.opt));
                                    break;
                                case 'created_at_fim':
                                    self.constructors[filtro].push(formatarData(array.opt));
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
                                case 'atribuidoa':
                                    if (array.opt !== undefined) {
                                        self.constructors.responsavel_web.push(array.opt);
                                        self.constructors[filtro].push(array.opt);
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
                }, 500);

                function resetFilters() {
                    self.filtrosPossiveis.map(function (filtro) {
                        self.filtros[filtro] = [];
                    });
                }

                function adicionaFiltro(filtro) {
                    if (Array.isArray($stateParams[filtro])) {
                        $stateParams[filtro].map(function (item) {
                         if (filtro === 'atribuidoa' && !isGuid($stateParams[filtro])) {
                                self.filtros[filtro].push({'campo': filtro, 'opt': item});
                            } else {
                                self.filtros[filtro].push({'campo': filtro, 'opt': item});
                            }
                        });
                    } else {
                        if (filtro === 'atribuidoa' && !isGuid($stateParams[filtro])) {
                            self.filtros[filtro].push({'campo': filtro, 'opt': $stateParams[filtro]});
                        } else {

                            if ($stateParams[filtro].match(/^\d\d\d\d-\d\d-\d\d$/gi)) {
                                self.filtros[filtro].push({'campo': filtro, 'opt': new Date($stateParams[filtro] + ' 12:00:00')});

                            } else {
                                self.filtros[filtro].push({'campo': filtro, 'opt': $stateParams[filtro]});
                            }

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
                            case 'atribuidoa':
                            case 'created_at_ini':
                            case 'created_at_fim':
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

                self.campoParaBusca = '';

                self.loadMore = function() {
                    self.contador = 1;
                    self._recarregar(self.constructors, self.after, self.filter, false);
                }

                self.recarregar = function() {

                    var filter = { key: self.filter.key, filterfield: 'all' };

                    self.contador = 1;
                    self.constructors.offset = '';
                    self._recarregar(self.constructors, '', filter, true);
                }

                self._recarregar = function(constructors, offset, filter, clearEntities) {

                    $http.get(nsjRouting.generate('atendimento_cliente_solicitacoes_index',
                            angular.extend(constructors, { 'offset': offset, 'filter': filter }), true))
                    .then(function(response) {

                        if (clearEntities) {
                            self.entities = [];
                        }

                        for (var i = 0; i < response.data.length; i++) {
                            self.entities.push(response.data[i]);
                        }

                        offset = response.data[response.data.length - 1]['data_ultima_resposta'];
                        self.after = offset;

                        if (self.contador >= 0) {
                            self.contador--;

                            self._recarregar(constructors, offset, filter, false);
                        }
                    });
                }

                self.search = function() {
                    self.filter.key = self.campoParaBusca.replace(/[Ç]/gi, 'c')
                                                         .replace(/[ÁÀÂÃÄ]/gi, 'a')
                                                         .replace(/[ÉÈÊẼË]/gi, 'e')
                                                         .replace(/[ÍÌÎĨÏ]/gi, 'i')
                                                         .replace(/[ÓÒÔÕÖ]/gi, 'o')
                                                         .replace(/[ÚÙÛŨÜ]/gi, 'u');

                    self.reloadData();
                    // self.reload();
                }

                self.filtrarSituacao = function (filtro) {
                    if (obj.atribuidoa != 'spam' && self.constructors.situacao == 2){
                        //Reseta a situação caso o usuário esteja saindo da fila SPAM
                        self.constructors.situacao = 0;
                        self.menuSituacaoAtivo = 'abertos';
                    }
                    obj.atribuidoa === 'todos' || obj.atribuidoa === 'spam' ? delete self.constructors.responsavel_web : self.constructors.responsavel_web = obj.atribuidoa;
                    switch (filtro) {
                        case 'todos':
                            delete self.constructors.minhas;
                            delete self.constructors.cliente;
                            filtroClientes(filtro);
                            break;
                        case 'meus':
                            self.constructors.minhas = true;
                            filtroClientes(filtro);
                            break;
                        default:
                            delete self.constructors.minhas;
                            self.constructors.cliente = filtro;
                            filtroClientes(filtro);
                            break;
                    }
                    self.updateTitle(obj.atribuidoa, obj.adiado);
                    resetFilters();
                    self.menuSituacaoAtivo = filtro;
                    self.reload()
                };

                self.filterOrder = function (order) {
                    if (order === 'desc') {
                        self.constructors.order = 'desc';
                    } else {
                        self.constructors.order = order;
                    }

                    self.menuOrdenacaoAtiva = order;
                    self.reload();
                };

                self.updateTitle = function () {
                    PageTitle.setTitle('Chamados');
                };
                self.updateTitle();

                self.usuarioLogado = UsuarioLogado;
                self.podeCriarChamado = false;

                var clientesDisponiveis = 0;
                for (var i = 0; i < nsj.globals.get('usuario').clientes.length; i++) {
                    if (!nsj.globals.get('usuario').clientes[i].cliente_bloqueado) {
                        clientesDisponiveis++;
                    }
                }

                if (self.usuarioLogado.usuarioSemClienteCriarChamado || clientesDisponiveis > 0) {
                    self.podeCriarChamado = true;
                }

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        if (i == 'atribuidoa') {
                            self.constructors['responsavel_web'] = $stateParams[i];
                        }
                        else
                        {
                        AtendimentoClienteSolicitacoes.constructors[i] = $stateParams[i];
                        }
                    }
                }

                self.entities = AtendimentoClienteSolicitacoes.reload();

                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return AtendimentoClienteSolicitacoes.busy;
                };
                self.new = function (context) {
                    var self = this;
                    self.entity = {
                    };
                    AtendimentoClienteSolicitacoes._save(self.entity)
                            .then(function (response) {
                                $state.go('atendimento_cliente_solicitacoes_edit', {'atendimento': response['data']['atendimento'], 'entity': response['data']});
                            });
                };


                $scope.$on("atendimento_cliente_solicitacoes_deleted", function (event) {
                    self.reload();
                });

                $rootScope.$on("atendimento_cliente_solicitacoes_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });

                self.nextPage();
            }]);