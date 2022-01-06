angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['CrmProximoscontatos', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['proximocontato']) {
                                    return entityService.get($stateParams['proximocontato']);
                                } else {
                                    return {};
                                }
                            }
                        }],
                    'contasResolve': ['Contas', function (Contas) {
                            return Contas;
                        }]
                };

                $stateProvider
                        .state('crm_proximoscontatos', {
                            url: "/proximoscontatos?data?situacao?ematraso?order",
                            templateUrl: nsjRoutingProvider.$get().generate('crm_proximoscontatos_template'),
                            controller: 'CrmProximoscontatosListController',
                            controllerAs: 'crm_prxmscntts_lst_cntrllr'
                        })

                        .state('crm_proximoscontatos_new', {
                            url: "/proximoscontatos/new",
                            params: {
                                cliente: null,
                                nome: null,
                                atendimento: null
                            },
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('crm_proximoscontatos_template_form'),
                            controllerAs: 'crm_prxmscntts_frm_cntrllr',
                            controller: 'CrmProximoscontatosFormController',
                            parent: 'crm_proximoscontatos'
                        })
                        .state('crm_proximoscontatos_show', {
                            url: "/:proximocontato",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('crm_proximoscontatos_template_show'),
                            controller: 'CrmProximoscontatosFormController',
                            controllerAs: 'crm_prxmscntts_frm_cntrllr',
                            parent: 'crm_proximoscontatos'
                        })
                        .state('crm_proximoscontatos_edit', {
                            url: "/:proximocontato/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('crm_proximoscontatos_template_form'),
                            controller: 'CrmProximoscontatosFormController',
                            controllerAs: 'crm_prxmscntts_frm_cntrllr',
                            parent: 'crm_proximoscontatos'
                        })
                        .state('crm_proximoscontatos_duplicar', {
                            url: "/proximoscontatos/:proximocontato/duplicar",
                            params: {
                                duplicar: true
                            },
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('crm_proximoscontatos_template_form'),
                            controller: 'CrmProximoscontatosFormController',
                            controllerAs: 'crm_prxmscntts_frm_cntrllr',
                            parent: 'crm_proximoscontatos'
                        })
                        ;
            }])
        .controller('CrmProximoscontatosListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'CrmProximoscontatos', 'UsuarioLogado', 'PageTitle', 'UsuarioComEquipe', 'AgendamentosPendentes',
            function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, CrmProximoscontatos, UsuarioLogado, PageTitle, UsuarioComEquipe, AgendamentosPendentes) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = CrmProximoscontatos.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = CrmProximoscontatos.search;
                self.reload = CrmProximoscontatos.reload;
                self.nextPage = CrmProximoscontatos.load;
                self.loadMore = CrmProximoscontatos.loadMore;
                self.service = CrmProximoscontatos;
                self.constructors = CrmProximoscontatos.constructors = [];
                self.usuarioLogado = UsuarioLogado.getUsuario();
                PageTitle.setTitle("Agenda de Contatos");

                self.responsavelAtivo = 'meus';
                self.menuSituacaoAtivo = 'todos';

                self.dataFilter = new Date();
                self.constructors.data = formatarData(self.dataFilter);
                self.constructors.responsavel_web = self.usuarioLogado.email;
                self.search(self.constructors);
                AgendamentosPendentes.buscarPendentes();

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q' && i != 'cliente') {
                        CrmProximoscontatos.constructors[i] = $stateParams[i];
                    }
                }

                self.filtrarPorData = function () {
                    if (self.dataFilter != null && self.dataFilter != undefined) {
                        self.constructors.data = formatarData(self.dataFilter);
                    } else {
                        delete self.constructors.data;
                    }

                    self.search(self.constructors);
                }

                self.removerFiltroResponsavelWeb = function (ativo) {
                    delete self.constructors.responsavel_web;
                    self.search(self.constructors);
                    self.responsavelAtivo = ativo;
                }

                self.filtrarMeusContatos = function (ativo) {
                    self.constructors.responsavel_web = self.usuarioLogado.email;
                    self.search(self.constructors);
                    self.responsavelAtivo = ativo;
                }

                self.filtrarSituacao = function (situacao, ematraso, ativo) {

                    if (situacao != null) {
                        self.constructors.ematraso = ematraso;
                        self.constructors.situacao = situacao;
                    } else {
                        delete self.constructors.ematraso;
                        delete self.constructors.situacao;
                    }
                    self.menuSituacaoAtivo = ativo;
                    self.search(self.constructors);
                }

                self.ordenarLista = function (order) {
                    self.constructors.order = order;
                    self.search(self.constructors);
                }

                function formatarData(data) {
                    var dia = data.getDate();
                    if (dia.toString().length == 1)
                        dia = "0" + dia;
                    var mes = data.getMonth() + 1;
                    if (mes.toString().length == 1)
                        mes = "0" + mes;
                    var ano = data.getFullYear();
                    return ano + "-" + mes + "-" + dia;
                }

                if (self.usuarioComEquipe) {
                    self.entities = CrmProximoscontatos.reload();
                }

                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return CrmProximoscontatos.busy;
                };
                self.new = function (context) {
                    var self = this;
                    self.entity = {
                    };
                    CrmProximoscontatos._save(self.entity)
                            .then(function (response) {
                                $state.go('crm_proximoscontatos_edit', {'proximocontato': response['data']['proximocontato'], 'entity': response['data']});
                            });
                };


                $scope.$on("crm_proximoscontatos_deleted", function (event) {
                    self.reload();
                });
                
                if (self.constructors.atendimento !== null && self.constructors.atendimento !== undefined) {
                    self.constructors.atendimento = self.constructors.atendimento.atendimento;
                }

                $rootScope.$on("crm_proximoscontatos_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });

            }])
        .controller('CrmProximoscontatosFormController', [
            '$scope', '$stateParams', '$state', 'CrmProximoscontatos', 'entity', 'UsuarioLogado', 'contasResolve', 'nsjRouting', 'toaster', '$http', 'AgendamentosPendentes',
            function ($scope, $stateParams, $state, entityService, entity, UsuarioLogado, contasResolve, nsjRouting, toaster, $http, AgendamentosPendentes) {

                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                self.usuarioLogado = UsuarioLogado.getUsuario();
                self.optionusuariologado = [{'nome': 'Atribuir a mim: ' + self.usuarioLogado.nome, 'email': self.usuarioLogado.email}];
                self.atribuidoa = self.optionusuariologado[0].email;
                self.contas = contasResolve.filter(function (conta) {
                    return conta.email !== self.usuarioLogado.email;
                });
                self.entity.data = new Date(self.entity.data + ' 00:00:00');
                self.hideCliente = false;
                self.cliente = {};
                self.loadingCliente = false;
                self.permitirCadastroSemCliente = parseInt(nsj.globals.getInstance().get('CADASTRAR_CONTATO_SEM_CLIENTE'));
                
                if (self.entity.hora != undefined || self.entity.hora != null) {
                    self.entity.hora = self.entity.hora.substring(0, 5);
                }

                if (self.entity.responsavel_web) {
                    self.exibeAtribuido = self.entity.responsavel_web;
                } else {
                    self.exibeAtribuido = self.optionusuariologado[0].nome;
                }

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
                    }
                };

                self.concluir = function () {
                    var data = {}
                    data['proximocontato'] = self.entity['proximocontato'];
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('crm_proximoscontatos_concluir', {'id': self.entity['proximocontato']}),
                        data: data
                    }).then(function (response) {
                        var mensagem = 'Agendamento concluído com sucesso';
                        toaster.pop({
                            type: 'success',
                            title: mensagem
                        });
                        $state.reload();
                    }).catch(function (response) {
                        alert(response.data.erro);
                    });
                };
                self.reabrir = function () {
                    var data = {}
                    data['proximocontato'] = self.entity['proximocontato'];
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('crm_proximoscontatos_reabrir', {'id': self.entity['proximocontato']}),
                        data: data
                    }).then(function (response) {
                        var mensagem = 'Agendamento reaberto com sucesso';
                        toaster.pop({
                            type: 'success',
                            title: mensagem
                        });
                        $state.reload();
                    }).catch(function (response) {
                        alert(response.data.erro);
                    });
                };
                self.cancelar = function () {
                    var data = {};
                    data['proximocontato'] = self.entity['proximocontato'];
                    $http({
                        method: "POST",
                        url: nsjRouting.generate('crm_proximoscontatos_cancelar', {'id': self.entity['proximocontato']}),
                        data: data
                    }).then(function (response) {
                        var mensagem = 'Agendamento cancelado com sucesso';
                        toaster.pop({
                            type: 'success',
                            title: mensagem
                        });
                        $state.reload();
                    }).catch(function (response) {
                        alert(response.data.erro);
                    });
                };

                self.duplicarAgendamento = function () {
                    $state.go('atendimento_admin_solicitacoes_duplicar');
                };

                if ($stateParams['duplicar']) {
                    delete self.entity.proximocontato;
                    delete self.entity.data;
                    delete self.entity.hora;
                }

                self.submit = function () {
                    self.entity.responsavel_web = self.entity.responsavel_web === undefined ? self.optionusuariologado[0].email : self.entity.responsavel_web;

                    if (self.entity.responsavel_web === null) {
                        self.form.$valid = false;
                        toaster.pop({ type: 'danger', title: "Por favor, preencha o campo responsável." });
                    }

                    self.submitted = true;
                    if (self.form.$valid && !self.entity.submitting) {

                        if (!self.validateDate(self.entity.data)) {
                            self.form.$valid = false;
                            toaster.pop({ type: 'danger', title: "Por favor, insira uma data maior ou igual a 1900!" });
                            return false;
                        }

                        var dataContato = new Date(self.entity.data);
                        var splitedTime = self.entity.hora.split(":");
                        dataContato.setHours(splitedTime[0]);
                        dataContato.setMinutes(splitedTime[1]);

                        if (dataContato < new Date()) {
                            if (confirm("A data e hora informada está no passado. Deseja criar o agendamento mesmo assim?")) {
                                entityService.save(self.entity);
                            }
                        } else {
                            entityService.save(self.entity);
                        }
                    }
                };

                self.onSubmitSuccess = $scope.$on("crm_proximoscontatos_submitted", function (event, args) {
                    if (self.constructors.atendimento !== undefined && self.constructors.atendimento !== null) {
                        self.constructors.atendimento = self.constructors.atendimento.atendimento;
                    }
                    
                    $state.go('crm_proximoscontatos', self.constructors);
                    AgendamentosPendentes.buscarPendentes();
                });

                self.onSubmitError = $scope.$on("crm_proximoscontatos_submit_error", function (event, args) {
                    if (args.response.status == 409) {
                        if (confirm(args.response.data.message)) {
                            self.entity[''] = args.response.data.entity[''];
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
                    entityService.delete($stateParams['proximocontato'], force);
                };

                self.onDeleteSuccess = $scope.$on("crm_proximoscontatos_deleted", function (event, args) {
                    $state.go('crm_proximoscontatos', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("crm_proximoscontatos_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                if ($stateParams['cliente']) {
                    self.entity.cliente = {
                        cliente: $stateParams['cliente'],
                        nome: $stateParams['nome']
                    };
                }
                
                if ($stateParams['atendimento']) {
                    self.entity.atendimento = $stateParams['atendimento'];
                }

                self.validateDate = function(date) {					
                    if ( date.getTime() !== date.getTime())
                        return false;
                    
                    return date.getFullYear() >= 1900;
                }
            }]);