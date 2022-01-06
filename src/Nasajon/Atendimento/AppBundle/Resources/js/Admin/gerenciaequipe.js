angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'equipe': ['$q', '$http', 'nsjRouting', '$stateParams', function ($q, $http, nsjRouting, $stateParams) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_admin_gerenciaequipe_get_equipe_by_usuario', {'id': $stateParams['equipe']}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'listaIndisponiveis': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_usuariosdisponibilidades_lista_indisponiveis', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'contas': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_admin_contas_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'usuariosAlocadosEquipe': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_equipes_list_usuarios_alocados_equipe', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }]
                };

                $stateProvider
                        .state('atendimento_admin_gerenciaequipe', {
                            url: "/gerenciaequipe",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_gerenciaequipe_template'),
                            controller: 'AtendimentoAdminGerenciaEquipeListCtrl',
                            controllerAs: 'tndmnt_dmn_grncqp_lst_cntrllr'
                        })
                        .state('atendimento_admin_gerenciaequipe_show', {
                            parent: 'atendimento_admin_gerenciaequipe',
                            url: "/:equipe",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_gerenciaequipe_template_show'),
                            controller: 'AtendimentoAdminGerenciaEquipeShowCtrl',
                            controllerAs: 'tndmnt_dmn_grncqp_shw_cntrllr',
                            resolve: resolve
                        });
            }])

        .controller('AtendimentoAdminGerenciaEquipeListCtrl', ['$state', function ($state) {
                var self = this;
                self.entities = nsj.globals.getInstance().get('usuario')['equipes'];

                if (self.entities.length === 0) {
                    $state.go('atendimento_admin_solicitacoes', {});
                }

            }])
        .controller('AtendimentoAdminGerenciaEquipeShowCtrl', ['nsjRouting', '$http', 'toaster', 'PageTitle', 'equipe', 'listaIndisponiveis', 'contas', 'usuariosAlocadosEquipe', '$stateParams',
            function (nsjRouting, $http, toaster, PageTitle, equipe, listaIndisponiveis, contas, usuariosAlocadosEquipe, $stateParams) {
                var self = this;
                self.submitted = false;
                self.entity = {};
                self.constructors = {};
                self.contas = [];
                self.opcoesUsuarios = [];
                self.usuariosIndisponiveis = [];
                PageTitle.setTitle("Gerenciamento de Equipes");

                self.load = function () {
                    self.entity = equipe;
                    self.usuariosIndisponiveis = listaIndisponiveis;

                    for (var i in self.entity.usuarios) {
                        self.entity.usuarios[i]['status'] = true;
                        for (var j in self.usuariosIndisponiveis) {
                            if (self.entity.usuarios[i]['usuario'] == self.usuariosIndisponiveis[j]) {
                                self.entity.usuarios[i]['status'] = false;
                            }
                        }
                    }

                    for (var i in contas) {
                        self.contas.push(contas[i]);
                    }

                    self.listUsuariosAlocadosEquipe = usuariosAlocadosEquipe;
                    self.preencheListaUsuarios();
                }

                self.addUsuario = function (tipo) {
                    if (!self.usuario_selecionado) {
                        alert("Por favor, selecione as opções nos campos para adicionar um usuário.");
                    } else {
                        if (!Array.isArray(self.entity.usuarios)) {
                            self.entity.usuarios = [];
                        }
                        
                        var usuarioSelecionado = self.usuario_selecionado;
                        self.usuario_selecionado = null;
                        
                        var status = self.getDisponibilidade(usuarioSelecionado);
                        
                        var usuario = {
                            'usuario': usuarioSelecionado,
                            'usuariotipo': tipo,
                            'status': status
                        };


                        if (self.salvarUsuario(usuario)) {
                            self.entity.usuarios.push(usuario);
                            self.listUsuariosAlocadosEquipe.push(usuario);
                            self.preencheListaUsuarios();
                            toaster.pop({
                                type: 'success',
                                title: 'Usuário adicionado com sucesso.'
                            });
                        } else {
                            alert("Erro ao adicionar usuário");
                        }
                    }

                };

                self.salvarUsuario = function (usuario) {
                    return $http({
                        method: "POST",
                        url: nsjRouting.generate('atendimento_admin_gerenciaequipe_adiciona', {id: self.entity.equipe}, false),
                        data: usuario
                    }).then(function (response) {
                        return true;
                    }).catch(function (response) {
                        return false;
                    });
                };

                self.getDisponibilidade = function (email) {
                    for (var i in self.usuariosIndisponiveis) {
                        if (email === self.usuariosIndisponiveis[i]) {
                            return false;
                        }
                    }
                    return true;
                }

                self.getNomeUsuario = function (email) {
                    for (var i in self.contas) {
                        if (email === self.contas[i].email) {
                            return self.contas[i].nome;
                        }
                    }
                }
                self.getTipoUsuario = function (tipo) {
                    if (tipo === 'U') {
                        return "Usuário";
                    } else {
                        return "Gerente";
                    }

                }

                self.preencheListaUsuarios = function () {
                    self.opcoesUsuarios = [];
                    
                    var usuariosComEquipe = self.listUsuariosAlocadosEquipe;

                    for (var i in self.contas) {
                        var temEquipe = false;

                        for (var j in usuariosComEquipe) {
                            if (usuariosComEquipe[j].usuario === self.contas[i].email) {
                                temEquipe = true;
                            }
                        }
                        
                        //Preenche o array de usuários removendo TODOS os usuários que estão em QUALQUER equipe
                        if (temEquipe === false) {
                            self.opcoesUsuarios.push({
                                'nome': self.contas[i].nome.concat(" (", self.contas[i].email, ")"),
                                'email': self.contas[i].email
                            });
                        }
                    }
                };

                self.removeUsuario = function (index, usuario) {

                    $http.delete(nsjRouting.generate('atendimento_admin_gerenciaequipe_delete', {id: self.entity.equipe, 'usuario': usuario}, false))
                            .then(function (response) {
                                self.entity.usuarios.splice(index, 1);
                                for (var i in self.listUsuariosAlocadosEquipe) {

                                    if (self.listUsuariosAlocadosEquipe[i].usuario === usuario) {
                                        self.listUsuariosAlocadosEquipe.splice(i, 1);
                                    }
                                }

                                self.preencheListaUsuarios();
                                toaster.pop({
                                    type: 'success',
                                    title: 'Usuário removido com sucesso.'
                                });

                            });

                };





                self.setStatus = function (usuario) {
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_usuariosdisponibilidades_alterar', false, false),
                        data: {
                            usuario: usuario.usuario,
                            status: usuario.status
                        }
                    }).then(function (response) {
                        toaster.pop({
                            type: 'success',
                            title: 'Disponibilidade do usuário alterada com sucesso!'
                        });

                        if (usuario.status === false) {
                            self.usuariosIndisponiveis.push(usuario.usuario);
                        } else {

                            for (var i in self.usuariosIndisponiveis) {
                                if (self.usuariosIndisponiveis[i] === usuario.usuario) {
                                    self.usuariosIndisponiveis.splice(i, 1);
                                }
                            }
                        }

                    }).catch(function (response) {
                        alert(response.data.erro);
                    });
                };

                self.load();

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

            }]);