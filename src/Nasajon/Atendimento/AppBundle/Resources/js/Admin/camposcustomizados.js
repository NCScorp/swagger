angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
                    'entity': ['ServicosAtendimentoscamposcustomizados', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['atendimentocampocustomizado']) {
                                    return entityService.get($stateParams['atendimentocampocustomizado']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };
                $stateProvider
                        .state('servicos_atendimentoscamposcustomizados', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "/camposcustomizados",
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentoscamposcustomizados_template'),
                            controller: 'ServicosAtendimentoscamposcustomizadosListController',
                            controllerAs: 'srvcs_tndmntscmpscstmzds_lst_cntrllr'
                        })

                        .state('servicos_atendimentoscamposcustomizados_new', {
                            parent: 'servicos_atendimentoscamposcustomizados',
                            url: "/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentoscamposcustomizados_template_form'),
                            controller: 'CamposcustomizadosFormController',
                            controllerAs: 'srvcs_tndmntscmpscstmzds_frm_cntrllr'
                        })
                        .state('servicos_atendimentoscamposcustomizados_edit', {
                            parent: 'servicos_atendimentoscamposcustomizados',
                            url: "/:atendimentocampocustomizado/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentoscamposcustomizados_template_form'),
                            controller: 'CamposcustomizadosFormController',
                            controllerAs: 'srvcs_tndmntscmpscstmzds_frm_cntrllr'
                        });
            }])
        .controller('CamposcustomizadosFormController', ['$scope', '$stateParams', '$state', 'nsjRouting', 'ServicosAtendimentoscamposcustomizados', 'entity', '$http', 'toaster',
            function ($scope, $stateParams, $state, nsjRouting, entityService, entity, $http, toaster) {
                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.submit = function () {
                    self.submitted = true;
                    if (self.form.$valid && !self.entity.submitting) {
                        entityService.save(self.entity);
                    }
                };
                
                self.isBusy = function () {
                    return entityService.busy;
                };

                self.onSubmitSuccess = $scope.$on("servicos_atendimentoscamposcustomizados_submitted", function (event, args) {
                    var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao ' + acao + ' Campo Customizado!'
                    });
                    $state.go('servicos_atendimentoscamposcustomizados', self.constructors);
                });

                self.onSubmitError = $scope.$on("servicos_atendimentoscamposcustomizados_submit_error", function (event, args) {
                    if (args.response.status == 409) {
                        if (confirm(args.response.data.message)) {
                            self.entity[''] = args.response.data.entity[''];
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
                self.delete = function (force) {
                    entityService.delete($stateParams['atendimentocampocustomizado'], force);
                };

                self.onDeleteSuccess = $scope.$on("servicos_atendimentoscamposcustomizados_deleted", function (event, args) {
                    $state.go('servicos_atendimentoscamposcustomizados', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("servicos_atendimentoscamposcustomizados_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                self.removeOpcao = function (index) {
                    self.entity.opcoes.splice(index, 1);
                };

                self.addOpcao = function () {
                    if (!Array.isArray(self.entity.opcoes)) {
                        self.entity.opcoes = [];
                    }
                    self.entity.opcoes.push("");

                };


                self.order = function (posicaonova, entity) {
                    var posicaoantiga = self.entity.opcoes.indexOf(entity);

                    // Reordena  self.entities
                    if (self.entity.opcoes.length) {
                        if (posicaonova < posicaoantiga) {
                            self.entity.opcoes.splice(posicaoantiga, 1);
                            for (var i = self.entity.opcoes.length; i > posicaonova; i--) {
                                self.entity.opcoes[i] = self.entity.opcoes[i - 1];
                            }
                            self.entity.opcoes[posicaonova] = entity;
                        } else {
                            for (var i = posicaoantiga; i < posicaonova; i++) {
                                self.entity.opcoes[i] = self.entity.opcoes[i + 1];
                            }
                            self.entity.opcoes[posicaonova] = entity;
                        }
                    }
                    return entity;
                }

            }])
        .controller('ServicosAtendimentoscamposcustomizadosListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'toaster', 'ServicosAtendimentoscamposcustomizados', function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, toaster, ServicosAtendimentoscamposcustomizados) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = ServicosAtendimentoscamposcustomizados.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = ServicosAtendimentoscamposcustomizados.search;
                self.reload = ServicosAtendimentoscamposcustomizados.reload;
                self.nextPage = ServicosAtendimentoscamposcustomizados.load;
                self.loadMore = ServicosAtendimentoscamposcustomizados.loadMore;
                self.service = ServicosAtendimentoscamposcustomizados;
                self.constructors = ServicosAtendimentoscamposcustomizados.constructors = [];

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        ServicosAtendimentoscamposcustomizados.constructors[i] = $stateParams[i];
                    }
                }

                self.entities = ServicosAtendimentoscamposcustomizados.reload();

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
                        url: nsjRouting.generate('servicos_atendimentoscamposcustomizados_reordenar', {'id': entity.atendimentocampocustomizado, 'ordem': posicaonova}, false),
                    });

                    return entity;
                };

                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return ServicosAtendimentoscamposcustomizados.busy;
                };
                self.new = function (context) {
                    var self = this;
                    self.entity = {};

                    ServicosAtendimentoscamposcustomizados._save(self.entity).then(function (response) {
                        $state.go(
                            'servicos_atendimentoscamposcustomizados_edit', 
                            {
                                'atendimentocampocustomizado': response['data']['atendimentocampocustomizado'], 'entity': response['data']
                            }
                        );
                    });
                };


                $scope.$on("servicos_atendimentoscamposcustomizados_deleted", function (event) {
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao excluir Campo Customizado!'
                    });

                    self.reload();
                });

                $rootScope.$on("servicos_atendimentoscamposcustomizados_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });

                self.nextPage();
            }]);