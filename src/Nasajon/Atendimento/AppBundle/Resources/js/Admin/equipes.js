angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['$q', '$http', 'nsjRouting', '$stateParams', function ($q, $http, nsjRouting, $stateParams) {
                            if ($stateParams['equipe']) {
                                return $q(function (resolve, reject) {
                                    $http({
                                        method: 'GET',
                                        url: nsjRouting.generate('atendimento_equipes_get', {'id': $stateParams['equipe']}, false)
                                    }).then(function (response) {
                                        resolve(response.data);
                                    }).catch(function (response) {
                                        reject(response);
                                    });
                                });
                            } else {
                                return {};
                            }
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
                        }],
                    'repTecnico': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_representantetecnico_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'repComercial': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_representantecomercial_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'vendedor': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_vendedor_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }]
                };

                $stateProvider
                        .state('atendimento_equipes', {
                            url: "/equipes",
                            parent: 'atendimento_admin_configuracoes',
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_equipes_template'),
                            controller: 'AtendimentoEquipesListController',
                            controllerAs: 'tndmnt_qps_lst_cntrllr'
                        })
                        .state('atendimento_equipes_new', {
                            parent: 'atendimento_equipes',
                            url: "/novo",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_equipes_template_form'),
                            controllerAs: 'tndmnt_qps_frm_cntrllr',
                            controller: 'AtendimentoEquipesFormController'
                        })
                        .state('atendimento_equipes_edit', {
                            parent: 'atendimento_equipes',
                            url: "/:equipe/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_equipes_template_form'),
                            controller: 'AtendimentoEquipesFormController',
                            controllerAs: 'tndmnt_qps_frm_cntrllr'
                        });
            }
        ])
        .controller('AtendimentoEquipesFormController', [
            '$scope', '$stateParams', '$state', 'AtendimentoEquipes', 'entity', 'vendedor', 'repTecnico', 
            'repComercial', 'contas', 'usuariosAlocadosEquipe','toaster',
            function ($scope, $stateParams, $state, entityService, entity, vendedor, repTecnico, repComercial, 
                contas, usuariosAlocadosEquipe,toaster) {
                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                self.contas = [];
                self.opcoesUsuarios = [];
                self.opcoesGerentes = [];
                self.opcoesVendedor = [];
                self.opcoesRepresentanteTec = [];
                self.opcoesRepresentanteCom = [];
                
                for (var i in contas) {
                    self.contas.push(contas[i]);
                }
                
                self.listUsuariosAlocadosEquipe = usuariosAlocadosEquipe;

                if (self.entity.todosclientes == undefined) {
                    self.entity.todosclientes = 1;
                }
                
                self.isBusy = function () {
                    return entityService.busy;
                };

                self.submit = function () {

                    self.submitted = true;
                    if (self.form.$valid && !self.entity.submitting) {
                        entityService.save(self.entity);
                    }
                };

                self.addUsuario = function (tipo) {
                    if ((tipo === 'A' && !self.gerente_selecionado) || (tipo === 'U' && !self.usuario_selecionado)) {
                        alert("Por favor, selecione as opções nos campos para adicionar um usuário.");
                    } else {
                        if (!Array.isArray(self.entity.usuarios)) {
                            self.entity.usuarios = [];
                        }
                        
                        var usuarioSelecionado = null;
                        
                        if (tipo === 'A') {
                            usuarioSelecionado = self.gerente_selecionado;
                            self.gerente_selecionado = null;
                        } else {
                            usuarioSelecionado = self.usuario_selecionado;
                            self.usuario_selecionado = null;
                        }
                        
                        self.entity.usuarios.push({ 'usuario': usuarioSelecionado, 'usuariotipo': tipo });
                        self.listUsuariosAlocadosEquipe.push({ 'usuario': usuarioSelecionado, 'usuariotipo': tipo });
                        
                        self.preencheListaUsuarios();
                    }

                };

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
                    self.opcoesGerentes = [];
                    
                    var usuariosComEquipe = self.listUsuariosAlocadosEquipe;

                    for (var i in self.contas) {
                        var temEquipe = false;
                        var ehUsuario = false;

                        for (var j in usuariosComEquipe) {
                            if (usuariosComEquipe[j].usuario === self.contas[i].email) {
                                temEquipe = true;
                                if (!ehUsuario)
                                    ehUsuario = (usuariosComEquipe[j].usuariotipo === "U") ? true : false;
                                break;
                            }
                        }
                        
                        //Preenche o array de usuários removendo TODOS os usuários que estão em QUALQUER equipe
                        if (temEquipe === false && self.contas[i].nome) {
                            self.opcoesUsuarios.push({
                                'nome': self.contas[i].nome.concat(" (", self.contas[i].email, ")"),
                                'email': self.contas[i].email
                            });
                        }
                        
                        //Preenche o array de gerente caso a conta não seja do tipo "U" (usuario) em outra equipe.
                        if (!ehUsuario && self.contas[i].nome) {
                            self.opcoesGerentes.push({
                                'nome': self.contas[i].nome.concat(" (", self.contas[i].email, ")"),
                                'email': self.contas[i].email
                            });
                        }
                    }
                    
                    //Remove do array de gerentes os usuários que estão na equipe a ser cadastrada/atualizada.                    
                    if (self.entity.usuarios !== undefined) {
                        var usuariosEquipe = [];
                        self.entity.usuarios.forEach(function (usuario) {
                            usuariosEquipe.push(usuario.usuario);
                        });
                        
                        self.opcoesGerentes = self.opcoesGerentes.filter(function (item) {
                            return usuariosEquipe.indexOf(item.email) < 0;
                        });
                    }
                };

                self.removeUsuario = function (index, usuario) {
                    self.entity.usuarios.splice(index, 1);

                    for (var i in self.listUsuariosAlocadosEquipe) {

                        if (self.listUsuariosAlocadosEquipe[i].usuario === usuario) {
                            self.listUsuariosAlocadosEquipe.splice(i, 1);
                        }
                    }

                    self.preencheListaUsuarios();
                };

                self.onSubmitSuccess = $scope.$on("atendimento_equipes_submitted", function (event, args) {
                    var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao ' + acao + ' equipe!'
                    });
                    $state.go('atendimento_equipes', self.constructors);
                });

                self.onSubmitError = $scope.$on("atendimento_equipes_submit_error", function (event, args) {
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
                    entityService.delete($stateParams['equipe'], force);
                };

                self.onDeleteSuccess = $scope.$on("atendimento_equipes_deleted", function (event, args) {
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao excluir equipe!'
                    });
                    $state.go('atendimento_equipes', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("atendimento_equipes_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                self.addClientesregrasIn = function (tipo) {
                    if (!Array.isArray(self.entity.clientesregrasin)) {
                        self.entity.clientesregrasin = [];
                    }
                    self.entity.clientesregrasin.push({
                        campo: 'bloqueado',
                        operador: 'is_equal',
                        valor: '0',
                        tipo: 0
                    });
                };
                self.addClientesregrasEx = function () {
                    if (!Array.isArray(self.entity.clientesregrasex)) {
                        self.entity.clientesregrasex = [];
                    }
                    self.entity.clientesregrasex.push({
                        campo: 'bloqueado',
                        operador: 'is_equal',
                        valor: '0',
                        tipo: 1
                    });
                };

                self.removeClientesregras = function (index, tipo) {
                    if (tipo === 1) {
                        self.entity.clientesregrasex.splice(index, 1);
                    }else if(tipo === 0){
                        self.entity.clientesregrasin.splice(index, 1);
                    }
                };

                self._geraOpcoesFidelidade = function () {
                    var opcoes = [];
                    for (var i = 1; i <= 15; i++) {
                        opcoes.push({
                            'label': i + ' anos',
                            'value': (i * 365) + ""
                        });
                    }
                    return opcoes;
                };

                self.selecionaCampo = function ($item) {
                    $item.operador = Object.keys(self.optionscondicoes[$item.campo].options)[0];
                    self.selecionaOperador($item);
                };

                self.selecionaOperador = function ($item) {
                    if (($item.operador == 'is_equal' || $item.operador == 'is_not_equal') && self.optionscondicoes[$item.campo].options[$item.operador].options.length > 0) {
                        $item.valor = self.optionscondicoes[$item.campo].options[$item.operador].options[0]['value'];
                    } else {
                        $item.valor = null;
                    }
                };

                self.montaOpcoesVendedor = function (vendedores) {
                    for (var i in vendedores) {
                        self.opcoesVendedor.push({
                            'label': vendedores[i]['nome'],
                            'value': vendedores[i]['vendedor']
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

                self.montaOpcoesRepresentanteCom = function (representantes) {
                    for (var i in representantes) {
                        self.opcoesRepresentanteCom.push({
                            'label': representantes[i]['nome'],
                            'value': representantes[i]['representantecomercial']
                        });
                    }
                };


                self.opcoesEstado = [
                    {'value': 'AC', 'label': 'AC'}, {'value': 'AL', 'label': 'AL'}, {'value': 'AP', 'label': 'AP'}, {'value': 'AM', 'label': 'AM'}, {'value': 'BA', 'label': 'BA'}, {'value': 'CE', 'label': 'CE'}, {'value': 'DF', 'label': 'DF'}, {'value': 'ES', 'label': 'ES'}, {'value': 'GO', 'label': 'GO'}, {'value': 'MA', 'label': 'MA'}, {'value': 'MT', 'label': 'MT'}, {'value': 'MS', 'label': 'MS'}, {'value': 'MG', 'label': 'MG'}, {'value': 'PA', 'label': 'PA'}, {'value': 'PB', 'label': 'PB'}, {'value': 'PR', 'label': 'PR'}, {'value': 'PE', 'label': 'PE'}, {'value': 'PI', 'label': 'PI'}, {'value': 'RJ', 'label': 'RJ'}, {'value': 'RN', 'label': 'RN'}, {'value': 'RS', 'label': 'RS'}, {'value': 'RO', 'label': 'RO'}, {'value': 'RR', 'label': 'RR'}, {'value': 'SC', 'label': 'SC'}, {'value': 'SE', 'label': 'SE'}, {'value': 'SP', 'label': 'SP'}, {'value': 'TO', 'label': 'TO'}
                ];

                self.optionscondicoes = {
                    'bloqueado': {
                        'label': 'Bloqueado',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': [
                                    {'value': '0', 'label': 'Não'},
                                    {'value': '1', 'label': 'Sim'},
                                ]
                            }
                        }
                    },
                    'estado': {
                        'label': 'Estado',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesEstado
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesEstado
                            },
                            'is_set': {
                                label: 'Existe',
                                type: false
                            },
                            'is_not_set': {
                                label: 'Não existe',
                                type: false
                            }

                        }
                    },
                    'vendedor': {
                        'label': 'Vendedor',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesVendedor
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesVendedor
                            },
                            'is_set': {
                                label: 'Existe',
                                type: false
                            },
                            'is_not_set': {
                                label: 'Não existe',
                                type: false
                            }
                        }
                    },
                    'representante': {
                        'label': 'Representante Comercial',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesRepresentanteCom
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesRepresentanteCom
                            },
                            'is_set': {
                                label: 'Existe',
                                type: false
                            },
                            'is_not_set': {
                                label: 'Não existe',
                                type: false
                            }
                        }
                    },
                    'representantetecnico': {
                        'label': 'Representante Técnico',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesRepresentanteTec
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesRepresentanteTec
                            },
                            'is_set': {
                                label: 'Existe',
                                type: false
                            },
                            'is_not_set': {
                                label: 'Não existe',
                                type: false
                            }
                        }
                    },
                    'fidelidade': {
                        'label': 'Fidelidade',
                        'options': {
                            'is_greater': {
                                'label': 'É maior que',
                                'type': 'select',
                                'options': self._geraOpcoesFidelidade()
                            }
                        }
                    }
                };
                
                self.montaOpcoesVendedor(vendedor);
                self.montaOpcoesRepresentanteTec(repTecnico);
                self.montaOpcoesRepresentanteCom(repComercial);

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }
                
                self.preencheListaUsuarios();

            }]);
