angular.module('mda')
        .run(['editableOptions', function (editableOptions) {
                editableOptions.theme = 'bs3';
        }])
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
            $stateProvider.state('ns_clientes', {
                url: "/clientes",
                templateUrl: nsjRoutingProvider.$get().generate('ns_clientes_template'),
                controller: 'NsClientesListController',
                controllerAs: 'ns_clnts_lst_cntrllr'
            })
            .state('ns_clientes_show', {
                parent: 'ns_clientes',
                url: "/:cliente",
                templateUrl: nsjRoutingProvider.$get().generate('ns_clientes_template_show'),
                controller: 'ClientesShowController',
                controllerAs: 'ns_clnts_frm_cntrllr'
            });
        }])
        .controller('NsClientesListController', ['$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'NsClientes', 'PageTitle', 'UsuarioComEquipe',
            function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, NsClientes, PageTitle, UsuarioComEquipe) {
                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = NsClientes.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.submitting = NsClientes.submitting;
                self.search = NsClientes.search;
                self.reload = NsClientes.reload;
                self.nextPage = NsClientes.load;
                self.loadMore = NsClientes.loadMore;
                self.service = NsClientes;
                self.constructors = NsClientes.constructors = [];
                PageTitle.setTitle("Clientes");

                NsClientes.load = function() {
                    if (!NsClientes.busy && !NsClientes.finished && NsClientes.to_load > 0 ){
                        NsClientes.busy = true;
                        var success = true;

                        NsClientes._load(NsClientes.constructors, NsClientes.after, NsClientes.filter)
                        .then(function (data) {
                            if (data.length > 0) {
                                for (var i = 0; i < data.length; i++) {
                                    NsClientes.entities.push(data[i]);
                                }

                                NsClientes.after = NsClientes.entities[NsClientes.entities.length - 1]['codigo'];
                            }
                            
                            if(data.length < 20){
                                NsClientes.finished = true;
                                $rootScope.$broadcast("ns_clientes_list_finished", NsClientes.entities);
                            }
                            
                            NsClientes.loaded = true;
                            NsClientes.to_load--;
                        })
                        .catch(function(response) {
                            if (response.status == 403) {
                                success = false;
                            }
                        })
                        .finally(function(){
                            NsClientes.busy = false;

                            if (success) {
                                NsClientes.load();
                            }
                        });
                    }
        
                    return NsClientes.entities;
                };

                self.campoParaBusca = '';
                
                self.search = function() {

                    self.constructors.offset = NsClientes.constructors.offset = '';
                    self.after = NsClientes.after = '';

                    self.filter.key = self.campoParaBusca.replace(/[Ç]/gi, 'c')
                                                         .replace(/[ÁÀÂÃÄ]/gi, 'a')
                                                         .replace(/[ÉÈÊẼË]/gi, 'e')
                                                         .replace(/[ÍÌÎĨÏ]/gi, 'i')
                                                         .replace(/[ÓÒÔÕÖ]/gi, 'o')
                                                         .replace(/[ÚÙÛŨÜ]/gi, 'u');
                    
                    self.reload();
                }

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        NsClientes.constructors[i] = $stateParams[i];
                    }
                }

                if (self.usuarioComEquipe) {
                    self.entities = NsClientes.reload();
                }


                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return NsClientes.busy;
                };

                $scope.$on("ns_clientes_deleted", function (event) {
                    self.reload();
                });

                $rootScope.$on("ns_clientes_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });



            }])
        .controller('ClientesShowController', ['$sce','$scope', '$stateParams', '$state', 'nsjRouting', 'NsClientes',
            '$filter', 'toaster', '$http', '$uibModal', 'UsuarioComEquipe',
            function ($sce,$scope, $stateParams, $state, nsjRouting, NsClientes, $filter, toaster, $http, $uibModal,
                UsuarioComEquipe) {

                var self = this;
                self.usuarioComEquipe = UsuarioComEquipe.load();
                self.submitted = false;
                self.constructors = {};
                self.solicitacoes = [];
                self.buscandoChamados = false;
                self.tituloBusca = "Veja mais chamados";
                self.situacaoSuporte = nsj.globals.getInstance().get('CLIENTE_SITUACAO_SEM_RESTRICAO_LABEL');
                self.funcoes = [
                    {value: 'A', label: 'Administrador'},
                    {value: 'U', label: 'Usuário'}
                ];

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.load = function () {
                    if (!self.busy) {
                        self.busy = true;
                        $http.get(
                            nsjRouting.generate('ns_clientes_get', { 'id': $stateParams['cliente'] }, false)
                        ).then(function (response) {
                            self.entity = response.data;

                            if (self.entity.anotacao) {
                                self.entity.anotacaoHTML = $sce.trustAsHtml(self.entity.anotacao.replace(/\n/g, "<br>"));
                            }

                            self.entity['ativos'] = self.treeViewData(self.entity['ativos']);
                            self.entity['enderecosnobrasil'] = true;

                            for (var i in self.entity['enderecos']) {
                                if (self.entity['enderecos'][i]['pais'] != 'BRASIL' && self.entity['enderecos'][i]['pais'] != null) {
                                    self.entity['enderecosnobrasil'] = false;
                                }

                                if (self.entity['enderecos'][i]['enderecopadrao'] == 1) {
                                    self.entity['enderecos'][i]['tipo'] = 'Principal';
                                } else {
                                    if (self.entity['enderecos'][i]['tipoendereco'] == 1) {
                                        self.entity['enderecos'][i]['tipo'] = 'Entrega';
                                    } else if (self.entity['enderecos'][i]['tipoendereco'] == 2) {
                                        self.entity['enderecos'][i]['tipo'] = 'Cobrança';
                                    } else {
                                        self.entity['enderecos'][i]['tipo'] = '';
                                    }
                                }
                            }
                        }).finally(function () {
                            self.busy = false;
                        });
                    }
                };

                self.novoChamado = function () {
                    if (self.entity.bloqueado) {
                        toaster.pop({type: 'error', title: 'Cliente bloqueado'});
                    } else {
                        $state.go('atendimento_admin_solicitacoes_new', {cliente: self.entity.cliente, nome: self.entity.nome});
                    }

                }
                self.novoProximoContato = function () {
                    if (self.entity.bloqueado) {
                        toaster.pop({type: 'error', title: 'Cliente bloqueado'});
                    } else {
                        $state.go('crm_proximoscontatos_new', {cliente: self.entity.cliente, nome: self.entity.nome});
                    }

                }
                self.mostraProximoContato = function (proximocontato) {
                    $state.go('crm_proximoscontatos_show', {proximocontato: proximocontato});
                }

                self.onDeleteSuccess = $scope.$on("ns_clientes_deleted", function (event, args) {
                    $state.go('ns_clientes', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("ns_clientes_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                self.treeViewData = function (objServicos) {
                    objServicos = objServicos.map(function (objServico) {
                        objServico['children'] = (objServico['ofertas']) ? objServico['ofertas'].map(function (oferta) {
                            oferta['children'] = oferta['componentes'].map(function (componente) {
                                delete componente['id'];
                                return componente;
                            });
                            delete oferta['componentes'];
                            delete oferta['id'];
                            return oferta;
                        }) : [];
                        delete objServico['ofertas'];
                        delete objServico['id'];
                        return objServico;
                    });
                    return objServicos;
                };

                self.mostraFuncao = function (funcao) {
                    var selected = $filter('filter')(self.funcoes, {value: funcao});
                    return (funcao && selected.length) ? selected[0].label : '';
                };

                self.removeContato = function (index) {
                    self.entity.contatos.splice(index, 1);
                };

                self.addContato = function () {
                    self.entity.contatos.push({
                        nome: '',
                        email: '',
                        isNew: ''
                    });
                };

                self.aprovarUsuaro = function (data, index) {
                    data.pendente = false;
                    return $http({
                        method: "POST",
                        url: nsjRouting.generate('atendimento_cliente_usuarios_aprovar', {cliente: self.entity.cliente, id: self.entity.usuarios[index].clientefuncao}, false),
                        data: data
                    }).then(function (response) {
                        toaster.pop({
                            type: 'success',
                            title: 'Usuário aoprovado com sucesso.'
                        });
                    });
                }

                self.salvarUsuario = function (data, $index) {
                    if (data.isNew) {
                        return $http({
                                    method: "POST",
                                    url: nsjRouting.generate('ns_clientes_usuarios_create', {cliente: self.entity.cliente}, false),
                                    data: data
                                }).then(function (response) {
                                    angular.extend(self.entity.usuarios[$index], response.data);
                                    self.entity.usuarios[$index].isNew = false;
        
                                    toaster.pop({
                                        type: 'success',
                                        title: 'Usuário adicionado com sucesso.'
                                    });
                                });
                    }else{
                        return $http({
                                    method: "PUT",
                                    url: nsjRouting.generate('ns_clientes_usuarios_put', {cliente: self.entity.cliente, id: self.usuario.clientefuncao}, false),
                                    data: data
                                }).then(function (response) {
                                    toaster.pop({
                                        type: 'success',
                                        title: 'Usuário atualizado com sucesso.'
                                    });
                                });
                    }
                };

                self.cancelarAcao = function (index) {
                    if (self.entity.usuarios[index].isNew) {
                        self.entity.usuarios.splice(index, 1);
                    }
                }

                self.removeUsuario = function (index) {
                    if (self.entity.bloqueado) {
                        toaster.pop({type: 'error', title: 'Cliente bloqueado'});
                    } else {
                        $http.delete(nsjRouting.generate('ns_clientes_usuarios_delete', {cliente: self.entity.cliente, id: self.entity.usuarios[index].clientefuncao}, false))
                                .then(function (response) {
                                    self.entity.usuarios.splice(index, 1);
                                    toaster.pop({
                                        type: 'success',
                                        title: 'Usuário removido com sucesso.'
                                    });
                                });
                    }
                };

                self.editarUsuario = function (index) {
                    if (self.entity.bloqueado) {
                        toaster.pop({type: 'error', title: 'Cliente bloqueado'});
                    } else {
                        self.usuario = {
                            clientefuncao: self.entity.usuarios[index].clientefuncao,
                            conta: self.entity.usuarios[index].conta,
                            funcao: self.entity.usuarios[index].funcao,
                            notificar: self.entity.usuarios[index].notificar,
                            pendente: self.entity.usuarios[index].pendente,
                            isNew: false
                        };
                        self.entity.usuarios[index] = self.usuario;
                    }

                };

                self.isBusy = function () {
                    return self.busy;
                };

                self.addUsuario = function () {
                    if (self.entity.bloqueado) {
                        toaster.pop({type: 'error', title: 'Cliente bloqueado'});
                    } else {
                        if ($filter('filter')(self.entity.usuarios, {isNew: true}).length == 0) {
                            self.usuario = {
                                conta: '',
                                funcao: 'U',
                                notificar: false,
                                pendente: false,
                                isNew: true
                            };
                            self.entity.usuarios.unshift(self.usuario);
                        }
                    }

                };

                self.openModal = function () {
                    $scope.modalInstance = $uibModal.open({
                        templateUrl: 'avisosDoClienteModal.tmpl.html',
                        scope: $scope
                    });
                };
                self.openDetalhesSuporte = function () {
                    $scope.cliente = self.entity;
                    $scope.modalInstance = $uibModal.open({
                        templateUrl: nsjRouting.generate('template_cliente_modal_suporte', {}, false),
                        scope: $scope
                    });
                };

                $scope.close = function () {
                    $scope.modalInstance.dismiss();
                };

                self.carregaMaisChamados = function () {
                    if (!self.buscandoChamados) {
                        self.tituloBusca = "Carregando...";
                        self.buscandoChamados = true;
                        $http.get(nsjRouting.generate('atendimento_admin_solicitacoes_index', angular.extend(self.constructors, {'orderfield': 'numeroprotocolo', 'offset': self.entity.solicitacoes[self.entity.solicitacoes.length - 1].numeroprotocolo}), false)).then(function (response) {
                            for (var x in response.data) {
                                self.entity.solicitacoes.push(response.data[x]);
                            }
                        }).finally(function () {
                            self.buscandoChamados = false;
                            self.tituloBusca = "Veja mais chamados";
                        });
                    }
                }

                self.load();

            }]);