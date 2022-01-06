angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
                    'entity': ['AtendimentoClienteUsuarios', '$stateParams', function (entityService, $stateParams) {
                            return {};
                        }]
                };
            }])
        .controller('ClienteUsuariosListController', ['$http', '$scope', '$rootScope', '$stateParams', '$state', 'nsjRouting', 'UsuarioLogado', 'AtendimentoClienteUsuarios', 'PageTitle', 'toaster',
            function ($http, $scope, $rootScope, $stateParams, $state, nsjRouting, UsuarioLogado, AtendimentoClienteUsuarios, PageTitle, toaster) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = AtendimentoClienteUsuarios.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.templateUrl = nsjRouting.generate('atendimento_cliente_usuarios_template');
                self.search = AtendimentoClienteUsuarios.search;

                self.updateTitle = function () {
                    PageTitle.setTitle('Usuários');
                };
                self.updateTitle();

                self.usuarioLogado = UsuarioLogado;
                if (!$stateParams['cliente']) {
                    var nextCliente, clientes = self.usuarioLogado.getClientes();
                    for (var i in clientes) {
                        if (clientes[i].funcao == 'A') {
                            nextCliente = clientes[i].cliente_id;
                            break;
                        }
                    }
                    $state.go('atendimento_cliente_usuarios', {
                        'cliente': nextCliente,
                        'funcao': null
                    });
                    return;
                }

                self.constructors = AtendimentoClienteUsuarios.constructors = [];
                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && i != 'q') {
                        AtendimentoClienteUsuarios.constructors[i] = $stateParams[i];
                    }
                }
                self.entities = AtendimentoClienteUsuarios.reload();


                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return AtendimentoClienteUsuarios.busy;
                };

                self.reload = AtendimentoClienteUsuarios.reload;

                self.nextPage = AtendimentoClienteUsuarios.load;

                $scope.$on("atendimento_cliente_usuarios_deleted", function (event) {
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao excluir Usuário!'
                    });

                    self.reload();
                });

                $scope.$on("atendimento_cliente_usuarios_submitted", function (event) {
                    self.reload();
                });



                self.delete = function (usuario) {
                    if (confirm('Tem certeza que deseja deletar?')) {
                        $http
                                .delete(nsjRouting.generate('atendimento_cliente_usuarios_delete', {'cliente': $stateParams['cliente'], 'id': usuario.clientefuncao}, false))
                                .then(function (response) {
                                    $rootScope.$broadcast("atendimento_cliente_usuarios_deleted", {
                                        entity: usuario,
                                        response: response
                                    });
                                })
                                .catch(function (response) {
                                    $rootScope.$broadcast("atendimento_cliente_usuarios_delete_error", {
                                        entity: usuario,
                                        response: response
                                    });
                                });
                    }
                };

                self.alteraUsuario = function (usuario, dado, tipo) {
                    var route = "atendimento_cliente_usuarios_put";
                    var method = "PUT";
                    switch (tipo) {
                        case "funcao":
                            usuario.funcao = dado;
                            break;
                        case "notificacao":
                            usuario.notificar = dado;
                            break;
                        case "pendente":
                            method = "POST";
                            route = "atendimento_cliente_usuarios_aprovar";
                            usuario.pendente = dado;
                            break;
                    }
                    
                    $http({
                        method: method,
                        url: nsjRouting.generate(route, {'cliente': $stateParams['cliente'], 'id': usuario.clientefuncao}, false),
                        data: usuario
                    }).then(function (response) {
                        $rootScope.$broadcast("atendimento_cliente_usuarios_submitted", {
                            entity: usuario,
                            response: response
                        });
                    }).catch(function (response) {
                        $rootScope.$broadcast("atendimento_cliente_usuarios_submit_error", {
                            entity: usuario,
                            response: response
                        });
                    });
                };
                self.nextPage();
            }]);

