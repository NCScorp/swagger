angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
                    'entity': ['ServicosAtendimentosfilas', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['atendimentofila']) {
                                    return entityService.get($stateParams['atendimentofila']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };
                $stateProvider
                        .state('servicos_atendimentosfilas', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "/filas",
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentosfilas_template'),
                            controller: 'ServicosAtendimentosfilasListController',
                            controllerAs: 'srvcs_tndmntsfls_lst_cntrllr'
                        })
                        .state('servicos_atendimentosfilas_new', {
                            parent: 'servicos_atendimentosfilas',
                            url: "/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentosfilas_template_form'),
                            controller: 'AtendimentosfilasFormController',
                            controllerAs: 'srvcs_tndmntsfls_frm_cntrllr'
                        })
                        .state('servicos_atendimentosfilas_edit', {
                            parent: 'servicos_atendimentosfilas',
                            url: "/:atendimentofila/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentosfilas_template_form'),
                            controller: 'AtendimentosfilasFormController',
                            controllerAs: 'srvcs_tndmntsfls_frm_cntrllr'
                        });
            }])
        .controller('AtendimentosfilasFormController', ['$scope', '$stateParams', '$state', 'nsjRouting', '$http', '$rootScope', '$uibModal', '$state', 'ServicosAtendimentosfilas', 'entity', 'UsuarioLogado', 'toaster', function ($scope, $stateParams, $state, nsjRouting, $http, $rootScope, $uibModal, $state, ServicosAtendimentosfilas, entity, UsuarioLogado, toaster) {

            var self = this;
            self.submitted = false;
            self.entity = entity;
            self.entity.observadores = self.entity.observadores || [];
            self.constructors = {};
            self.countSolicitacoes = null;
            self.exibeAtribuido = null;
            self.atribuidoa = null;
            self.optionsobservadores = [];
            self.exibeObservador = null;
            self.contas = [];
            self.usuarioLogado = UsuarioLogado.getUsuario();
            self.filas = ServicosAtendimentosfilas.load();

            self.onSubmitSuccess = $scope.$on("servicos_atendimentosfilas_submitted", function (event, args) {
                var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao ' + acao + ' a fila de chamado!'
                });

                $state.go('servicos_atendimentosfilas', self.constructors);
            });
            
            self.isBusy = function () {
                return ServicosAtendimentosfilas.busy;
            };

            self.onSubmitError = $scope.$on("servicos_atendimentosfilas_submit_error", function (event, args) {
                if (args.response.status == 409) {
                    if (confirm(args.response.data.message)) {
                        self.entity['lastupdate'] = args.response.data.entity['lastupdate'];
                        entityService.save(self.entity);
                    }
                } else {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        var acao = ((args.response.config.method = "PUT") ? "atualizar" : "inserir");
                        alert("Ocorreu um erro ao tentar " + acao + ".");
                    }
                }
            });

            self.onDeleteSuccess = $scope.$on("servicos_atendimentosfilas_deleted", function (event, args) {
                $state.go('servicos_atendimentosfilas', angular.extend(self.constructors));
            });

            self.onDeleteError = $scope.$on("servicos_atendimentosfilas_delete_error", function (event, args) {
                if (typeof (args.response.data.erro) !== 'undefined') {
                    alert(args.response.data.erro);
                } else {
                    alert("Ocorreu um erro ao tentar excluir.");
                }
            });


            $http.get(nsjRouting.generate('atendimento_admin_contas_index', {}, false)).then(function (response) {
                for (var i in response.data) {
                    self.contas.push(response.data[i]);
                }
                self.preencheOptionsObservadores();
            });

            self.optionscontas = function () {
                var acontas = [];
                acontas.push({'nome': 'Atribuir a mim: ' + self.usuarioLogado.nome, 'email': self.usuarioLogado.email});
                for (var i in self.contas) {
                    if (self.contas[i].email != self.usuarioLogado.email) {
                        acontas.push({'nome': self.contas[i].nome, 'email': self.contas[i].email});
                    }
                }
                return acontas;
            }();

            self.getOptionsObservadores = function () {
                return self.optionsobservadores;
            };

            self.preencheOptionsObservadores = function () {
                self.optionsobservadores = [];
                for (var i in self.contas) {
                    var adicionar = true;
                    for (var y in self.entity.observadores) {
                        if (self.contas[i].email == self.entity.observadores[y].usuario) {
                            adicionar = false;
                        }
                    }
                    if (adicionar) {
                        self.optionsobservadores.push({
                            'nome': self.contas[i].nome,
                            'email': self.contas[i].email
                        });
                    }
                }
            };


            self.bindOption = function (option) {
                if (option.atendimentofila != $stateParams['atendimentofila']) {
                    return option.email ? (option.nome + ' (' + option.email + ')') : option.nome
                }
            };

            self.bindObservador = function (option) {
                return option.email ? (option.nome + ' (' + option.email + ')') : option.nome
            };

            self.addObservador = function () {
                
                if (self.usuario) {
                    if (!Array.isArray(self.entity.observadores)) {
                        self.entity.observadores = [];
                    }
                    self.entity.observadores.push({
                        'usuario': self.usuario.email
                    });
                    delete self.usuario;
                    self.preencheOptionsObservadores();
                }else{
                    toaster.pop({
                        type: 'error',
                        title: 'Selecione um usuário antes de tentar adicionar.'
                    });
    }
            };
            
            self.removeObservador = function (index) {
                self.entity.observadores.splice(index, 1);
                self.preencheOptionsObservadores();
            };

            self.agruparPorTipo = function (item) {
                return item.email ? 'Usuário' : 'Fila';
            };

            self.atribuir = function ($item) {
                if (!$item) {
                    self.exibeAtribuido = null;
                    self.atribuidoa = null;
                } else if ($item.email) {
                    for (var i in self.contas) {
                        if (self.contas[i].email == $item.email) {
                            self.exibeAtribuido = self.contas[i].nome + ' (' + self.contas[i].email + ')';
                            self.atribuidoa = self.contas[i].email;
                        }
                    }
                } else if ($item.atendimentofila) {
                    for (var i in self.filas) {
                        if (self.filas[i].atendimentofila == $item.atendimentofila) {
                            self.exibeAtribuido = self.filas[i].nome;
                            self.atribuidoa = self.filas[i].atendimentofila;
                        }
                    }
                }
            };

            self.delete = function () {

                self.loadingDelete = true;
                $http.delete(nsjRouting.generate('servicos_atendimentosfilas_delete', {'id': $stateParams['atendimentofila']}, false))
                        .then(function (response) {
                            $rootScope.$broadcast("servicos_atendimentosfilas_submitted", self.entity);
                            toaster.pop({
                                type: 'success',
                                title: response.data
                            });

                        })
                        .catch(function (response) {
                            if (!isNaN(response.data)) {
                                self.count_solicitacoes = response.data;
                                self.openmodal();
                            } else {

                                toaster.pop({
                                    type: 'error',
                                    title: response.data
                                });
                            }
                        })
                        .finally(function () {
                            self.loadingDelete = false;
                        });

            };

            self.openmodal = function (size) {

                self.exibeAtribuido = null;
                self.atribuidoa = null;

                var modalInstance = $uibModal.open({
                    animation: false,
                    templateUrl: nsjRouting.generate('servicos_atendimentosfilas_template_form_delete', {}, false),
                    scope: $scope
                });

            };

            self.save = function () {

                $http.delete(nsjRouting.generate('servicos_atendimentosfilas_delete', {'id': $stateParams['atendimentofila'], 'reatribuidoa': self.atribuidoa ? self.atribuidoa : ''}, false))
                        .then(function (response) {
                            $rootScope.$broadcast("servicos_atendimentosfilas_submitted", self.entity);
                            toaster.pop({
                                type: 'success',
                                title: response.data
                            });
                        })
                        .finally(function () {
                            self.loadingDelete = false;
                        });
            }

            self.submit = function () {
                self.submitted = true;
                if (self.form.$valid && !ServicosAtendimentosfilas.submitting) {
                    self.entity['rascunho'] = false;
                    ServicosAtendimentosfilas.save(self.entity);
                }
            };

            for (var i in $stateParams) {
                if (i != 'entity') {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }
            }


        }])
        .controller('ServicosAtendimentosfilasListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'ServicosAtendimentosfilas', function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, ServicosAtendimentosfilas) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = ServicosAtendimentosfilas.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = ServicosAtendimentosfilas.search;
                self.reload = ServicosAtendimentosfilas.reload;
                self.nextPage = ServicosAtendimentosfilas.load;
                self.loadMore = ServicosAtendimentosfilas.loadMore;
                self.service = ServicosAtendimentosfilas;
                self.constructors = ServicosAtendimentosfilas.constructors = [];

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        ServicosAtendimentosfilas.constructors[i] = $stateParams[i];
                    }
                }

                self.entities = ServicosAtendimentosfilas.reload();


                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return ServicosAtendimentosfilas.busy;
                };
                self.new = function (context) {
                    var self = this;
                    self.entity = {
                    };
                    ServicosAtendimentosfilas._save(self.entity)
                            .then(function (response) {
                                $state.go('servicos_atendimentosfilas_edit', {'atendimentofila': response['data']['atendimentofila'], 'entity': response['data']});
                            });
                };

                self.order = function (posicaonova, entity) {

                    var posicaoantiga = self.entities.indexOf(entity);

                    // Reordena  self.entities
                    if (self.entities.length) {
                        if (posicaonova < posicaoantiga) {
                            self.entities.splice(posicaoantiga, 1);
                            for (var i = self.entities.length; i > posicaonova; i--) {
                                self.entities[i] = self.entities[i - 1];
                            }
                            self.entities[posicaonova] = entity;
                        } else {
                            for (var i = posicaoantiga; i < posicaonova; i++) {
                                self.entities[i] = self.entities[i + 1];
                            }
                            self.entities[posicaonova] = entity;
                        }
                    }

                    $http({
                        method: "POST",
                        url: nsjRouting.generate('servicos_atendimentosfilas_reordenar', {'id': entity.atendimentofila, 'ordem': posicaonova}, false)
                    });

                    return entity;
                };


                $scope.$on("servicos_atendimentosfilas_deleted", function (event) {
                    self.reload();
                });

                $rootScope.$on("servicos_atendimentosfilas_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });

                self.nextPage();

            }]);