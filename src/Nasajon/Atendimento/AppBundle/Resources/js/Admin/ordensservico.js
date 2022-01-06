angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['ServicosOrdensServico', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['ordemservico']) {
                                    return entityService.get($stateParams['ordemservico']);
                                } else {
                                    return {};
                                }
                            }
                        }]

                    , 'context': ['entity', 'ServicosOrdensServicoOperacao', function (entity, contextService) {
                            return contextService.get(entity.operacao.operacao);
                        }]
                };

                $stateProvider
                        .state('servicos_ordensservico', {
                            url: "/ordensservico?operacao?projeto?responsavel?situacao?rascunho",
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_ordensservico_template'),
                            controller: 'Servicos\OrdensServicoListController',
                            controllerAs: 'srvcs_rdns_srvc_lst_cntrllr'
                        })

                        .state('servicos_ordensservico_show', {
                            parent: 'servicos_ordensservico',
                            url: "/:ordemservico",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_ordensservico_template_show'),
                            controller: 'ServicosOrdensServicoFormController',
                            controllerAs: 'srvcs_rdns_srvc_frm_cntrllr'
                        })
                        .state('servicos_ordensservico_edit', {
                            parent: 'servicos_ordensservico',
                            url: "/:ordemservico/edit",
                            params: {
                                entity: null
                            },
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_ordensservico_template_form'),
                            controller: 'Servicos\OrdensServicoFormController',
                            controllerAs: 'srvcs_rdns_srvc_frm_cntrllr'
                        });
            }
        ])
        .controller('ServicosOrdensServicoFormController', [
            '$scope', '$stateParams', '$state', 'ServicosOrdensServico', 'entity', 'context', 'debounce', 'NsClientes', 'nsjRouting', '$http', '$uibModal', 'toaster',
            function ($scope, $stateParams, $state, entityService, entity, context, debounce, NsClientes, nsjRouting, $http, $uibModal, toaster) {

                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                self.autosavingstatus = -1;
                self.context = context;

                self.autosave = debounce(function () {
                    self.autosavingstatus = 0;

                    if (!self.entity.submitting) {
                        entityService.save(self.entity, true);
                    }
                }, 1000);


                self.situacao_maquina_chamado = [
                    'Parada',
                    'Em vias de parar',
                    'Funcionando'
                ];
                self.situacao_maquina_chegada = [
                    'Parada',
                    'Em vias de parar',
                    'Funcionando'
                ];
                self.situacao_maquina_saida = [
                    'Liberada',
                    'Liberada com restrições',
                    'Não Liberada'
                ];

                self.situacao = [
                    'Preliminar',
                    'Agendada',
                    'Encerrada',
                    'Faturada sem Cobrança',
                    'Faturada',
                    'Cancelada'
                ];

                self.submit = function () {
                    self.submitted = true;
                    if (self.form.$valid && !self.entity.submitting) {
                        self.entity['rascunho'] = false;
                        entityService.save(self.entity);
                    }
                };

                self.delete = function (force) {
                    entityService.delete($stateParams['ordemservico'], force);
                };

                self.changeCliente = function () {
                    self.entity.contrato = null;
                    self.entity.pessoamunicipio = null;
                    self.entity.enderecocliente = null;
                    self.entity.ativo = null;
                    self.loadCliente();

                    self.autosave();
                };

                self.loadCliente = function () {
                    if (self.entity.cliente) {
                        NsClientes.get(self.entity.cliente.cliente).then(function (data) {
                            self.cliente = data;
                        });
                    }
                };

                self.onDeleteSuccess = $scope.$on("servicos_ordensservico_deleted", function (event, args) {
                    $state.go('servicos_ordensservico', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("servicos_ordensservico_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                self.onSubmitSuccess = $scope.$on("servicos_ordensservico_submitted", function (event, args) {
                    if (args.autosave) {
                        self.autosavingstatus = 1;
                    } else {
                        $state.go('servicos_ordensservico_show', self.constructors);
                    }
                });

                self.onSubmitError = $scope.$on("servicos_ordensservico_submit_error", function (event, args) {
                    if (args.autosave) {
                        self.autosavingstatus = 2;
                    }
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

                self.saveVisita = debounce(function (visitaindex) {
                    var method, url;
                    method = "PUT";
                    url = nsjRouting.generate('servicos_visita_put', {'ordemservico': self.entity.ordemservico, 'id': self.entity.visitas[visitaindex].ordemservicovisita}, false);
                    self.entity.visitas[visitaindex]['submitting'] = true;

                    return $http({
                        method: method,
                        url: url,
                        data: self.entity.visitas[visitaindex]
                    })
                            .finally(function (response) {
                                self.entity.visitas[visitaindex]['submitting'] = false;
                            });
                }, 1000);

                self.openDeslocamentoExtra = function ($index) {
                    self.modalDeslocamentoExtra = $uibModal.open({
                        animation: false,
                        templateUrl: nsjRouting.generate('servicos_ordensservico_template_form_deslocamentoextra', {}, false),
                        scope: $scope,
                        controller: function ($scope, visitaindex, debounce) {
                            $scope.visitaindex = visitaindex;
                        },
                        resolve: {
                            visitaindex: function () {
                                return $index;
                            },
                            debounce: function (debounce) {
                                return debounce
                            }
                        }
                    });
                };

                self.openDadosAdicionais = function ($index) {
                    self.modalDadosAdicionais = $uibModal.open({
                        animation: false,
                        templateUrl: nsjRouting.generate('servicos_ordensservico_template_form_dadosadicionais', {}, false),
                        scope: $scope,
                        controller: function ($scope, visitaindex) {
                            $scope.visitaindex = visitaindex;
                        },
                        resolve: {
                            visitaindex: function () {
                                return $index;
                            }
                        }
                    });
                };



                self.bindEndereco = function (option) {
                    return option.logradouro + ' - ' + option.bairro;
                };

                self.bindAtivos = function (option) {
                    return option.servico_descricao;
                };

                self.bindPessoaMunicipio = function (option) {
                    return option.nome;
                };

                self.bindContrato = function (option) {
                    return option.descricao;
                };


                self.encerrar = function () {
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('servicos_ordensservico_encerrar', {'id': self.entity.ordemservico}, false),
                        data: {
                            ordemservico: self.entity.ordemservico ? self.entity.ordemservico : null
                        },
                        headers: {
                            "ETag": self.entity['lastupdate']
                        }
                    }).then(function (response) {
                        toaster.pop({
                            type: 'success',
                            title: 'Encerrado com sucesso.'
                        });
                        $state.reload();
                    }).catch(function (response) {
                        if (response.status == 409) {
                            if (confirm(response.data.message)) {
                                self.entity['lastupdate'] = response.data.entity['lastupdate'];
                                self.save();
                            }
                        } else {
                            toaster.pop({
                                type: 'error',
                                title: response.data.erro || response.data.message
                            });
                        }
                    });
                };

                self.addVisita = function (oblistctrl, type) {
                    if (type == 1 && oblistctrl.entities.length > 0) {
                        return false;
                    }
                    oblistctrl.add();
                };

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.openVisita = function ($index) {
                    self.modalVisita = $uibModal.open({
                        animation: false,
                        templateUrl: nsjRouting.generate('servicos_ordensservico_template_form_visita', {}, false),
                        scope: $scope,
                        controller: function ($scope, visitaindex, debounce) {
                            $scope.visitaindex = visitaindex;
                        },
                        resolve: {
                            visitaindex: function () {
                                return $index;
                            },
                            debounce: function (debounce) {
                                return debounce
                            }
                        }
                    });
                };

                self.loadChecklists = function ($index) {
                    if (self.entity.servicostecnicos[$index].servicotecnico.servicotecnico) {
                        $http({
                            method: "GET",
                            url: nsjRouting.generate('servicos_servicotecnico_checklist_index', {'servicotecnico': self.entity.servicostecnicos[$index].servicotecnico.servicotecnico}, false),
                            data: {}
                        }).then(function (response) {
                            var checklist = [];
                            for (var y in response.data) {
                                checklist.push({
                                    'descricao': response.data[y].descricao,
                                    'servicotecnicochecklist': response.data[y].servicotecnicochecklist
                                });
                            }
                            self.entity.servicostecnicos[$index].checklists = checklist;
                        });
                    }
                };

                self.visitaCheck = function ($type, $index) {
                    var tecnicos = self.entity.visitas[$index].tecnicos;
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('servicos_visita_check', {'ordemservico': self.entity.visitas[$index].ordemservico, 'id': self.entity.visitas[$index].ordemservicovisita}, false),
                        data: {
                            'type': $type
                        }
                    }).then(function (response) {
                        self.entity.visitas[$index] = response.data;
                        self.entity.visitas[$index].tecnicos = tecnicos;
                    });
                };


                self.saveTecnico = function ($pindex, $index) {
                    var method, url;
                    if (self.entity.visitas[$pindex].tecnicos[$index].ordemservicovisitatecnico) {
                        method = "PUT";
                        url = nsjRouting.generate('servicos_visita_tecnico_put', {
                            'id': self.entity.visitas[$pindex].tecnicos[$index].ordemservicovisitatecnico,
                            'ordemservicovisita': self.entity.visitas[$pindex].ordemservicovisita
                        }, false);
                    } else {
                        method = "POST";
                        url = nsjRouting.generate('servicos_visita_tecnico_create', {'ordemservicovisita': self.entity.visitas[$pindex].ordemservicovisita}, false);
                    }
                    autosave = false;
                    self.entity.visitas[$pindex].tecnicos[$index].submitting = true;
                    return $http({
                        method: method,
                        url: url,
                        data: self.entity.visitas[$pindex].tecnicos[$index]
                    })
                            .finally(function (response) {
                                self.entity.visitas[$pindex].tecnicos[$index].submitting = false;
                            });
                };

                self.addTecnico = function ($index) {
                    self.entity.visitas[$index].tecnicos.push({
                        'visitaIndex': $index
                    });
                };

                self.removeTecnico = function ($pindex, $index) {

                    if (self.entity.visitas[$pindex].tecnicos[$index].ordemservicovisitatecnico) {
                        if (confirm("Tem certeza que deseja deletar?")) {
                            $http
                                    .delete(nsjRouting.generate('servicos_visita_tecnico_delete', angular.extend(self.constructors,
                                            {'id': self.entity.visitas[$pindex].tecnicos[$index].ordemservicovisitatecnico,
                                                'ordemservicovisita': self.entity.visitas[$pindex].ordemservicovisita}), false))
                                    .then(function (response) {
                                        self.entity.visitas[$pindex].tecnicos.splice($index, 1);
                                    })
                                    .catch(function (response) {

                                    });
                        }
                    }
                };

                self.loadTecnicos = function ($index) {
                    $http({
                        method: "GET",
                        url: nsjRouting.generate('servicos_visita_tecnico_index', {'ordemservicovisita': self.entity.visitas[$index].ordemservicovisita}, false),
                        data: {}
                    }).then(function (response) {
                        var tecnicos = [];
                        for (var y in response.data) {
                            tecnicos.push({
                                'ordemservicovisitatecnico': response.data[y].ordemservicovisitatecnico,
                                'tecnico': response.data[y].tecnico,
                                'visitaIndex': $index
                            });
                        }
                        self.entity.visitas[$index].tecnicos = tecnicos;
                    });
                };


                self.loadCliente();
            }]);
