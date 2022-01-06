angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['AtendimentoAdminFlagcliente', '$stateParams', function (entityService, $stateParams) {
                        if ($stateParams['entity']) {
                            return $stateParams['entity'];
                        } else {
                            if ($stateParams['flagcliente']) {
                                return entityService.get($stateParams['flagcliente']);
                            } else {
                                return {};
                            }
                        }
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
                    }],
                    'servicosCatalogo': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                        return $q(function (resolve, reject) {
                            $http({
                                method: 'GET',
                                url: nsjRouting.generate('atendimento_servicoscatalogo_index', {}, false)
                            }).then(function (response) {
                                resolve(response.data);
                            }).catch(function (response) {
                                reject(response);
                            });
                        });
                    }],
                    'classificadores': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                        return $q(function (resolve, reject) {
                            $http({
                                method: 'GET',
                                url: nsjRouting.generate('ns_classificadores_index', {}, false)
                            }).then(function (response) {
                                resolve(response.data);
                            }).catch(function (response) {
                                reject(response);
                            });
                        });
                    }],
                };
                $stateProvider
                        .state('atendimento_admin_flagcliente', {
                            url: "/avisos",
                            parent: 'atendimento_admin_configuracoes',
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_flagcliente_template'),
                            controller: 'Atendimento\Admin\FlagclienteListController',
                            controllerAs: 'tndmnt_dmn_flgclnt_lst_cntrllr'
                        })

                        .state('atendimento_admin_flagcliente_new', {
                            parent: 'atendimento_admin_flagcliente',
                            url: "/novo",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_flagcliente_template_form'),
                            controllerAs: 'tndmnt_dmn_flgclnt_frm_cntrllr',
                            controller: 'AtendimentoAdminFlagclienteFormController'
                        })
                        .state('atendimento_admin_flagcliente_edit', {
                            parent: 'atendimento_admin_flagcliente',
                            url: "/:flagcliente/alterar",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_flagcliente_template_form'),
                            controller: 'Atendimento\Admin\FlagclienteFormController',
                            controllerAs: 'tndmnt_dmn_flgclnt_frm_cntrllr'
                        });
            }
        ])
        .controller('AtendimentoAdminFlagclienteFormController', [
            '$scope', '$stateParams', '$state', 'AtendimentoAdminFlagcliente', 'entity', 'vendedor', 'servicosCatalogo', 'repTecnico','repComercial', 'classificadores', 'toaster',
            function ($scope, $stateParams, $state, entityService, entity, vendedor, servicosCatalogo, repTecnico, repComercial, classificadores, toaster) {
                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                self.opcoesVendedor = [];
                self.opcoesServicosCatalogo = [];
                self.opcoesRepresentanteTec = [];
                self.opcoesRepresentanteCom = [];
                self.condicoesSempreAtivas = ["status_suporte", "estado", "email", "email_cobranca", "classificador", "representantetecnico" , "representante", "fidelidade", "bloqueado", "cliente_ofertas", "vendedor"];
                self.operacoesSempreAtivas = ["is_set", "is_not_set"];
                self.opcoesClassificadores = [];
                self.operadores = [
                      {operador: 'is_equal', label: 'É igual a', type: 'select'},
                      {operador: 'is_not_equal', label: 'Diferente de', type: 'select'},
                      {operador: 'is_set', label: 'Existe', type: false},
                      {operador: 'is_not_set', label: 'Não existe', type: false},
                      {operador: 'includes', label: 'Inclui', type: 'text'},
                      {operador: 'does_not_include', label: 'Não inclui', type: 'text'},
                      {operador: 'regex', label: 'Comparação (Expressão Regular)', type: 'text'}
                  ];
                self.isBusy = function () {
                    return entityService.busy;
                };
              
                self.submit = function () {
                    self.submitted = true;

                    if (self.form.$valid && !self.entity.submitting) {
                        if (!self.entity.icone) {
                            toaster.error('É necessário selecionar um ícone para o aviso!');
    
                            return;
                        }
    
                        if (!self.entity.cor) {
                            toaster.error('É necessário selecionar uma cor para o ícone do aviso!');
    
                            return;
                        }
    
                        if (!self.entity.corfundo) {
                            toaster.error('É necessário selecionar uma cor de fundo para o aviso!');
    
                            return;
                        }

                        if (self.entity.condicoes_in !== undefined) {
                            self.entity.condicoes_in = self.entity.condicoes_in.filter(function (array) {
                                return array.desativado === false || array.desativado === undefined;
                            });
                        }

                        if (self.entity.condicoes_ex !== undefined) {
                            self.entity.condicoes_ex = self.entity.condicoes_ex.filter(function (array) {
                                return array.desativado === false || array.desativado === undefined;
                            });
                        }

                        entityService.save(self.entity);
                    }
                };
                self.onSubmitSuccess = $scope.$on("atendimento_admin_flagcliente_submitted", function (event, args) {
                    var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao ' + acao + ' Aviso de Cliente!'
                    });
                    $state.go('atendimento_admin_flagcliente', self.constructors);
                });
                self.onSubmitError = $scope.$on("atendimento_admin_flagcliente_submit_error", function (event, args) {
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
                    entityService.delete($stateParams['flagcliente'], force);
                };

                self.onDeleteSuccess = $scope.$on("atendimento_admin_flagcliente_deleted", function (event, args) {
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao excluir Aviso de Cliente!'
                    });

                    $state.go('atendimento_admin_flagcliente', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("atendimento_admin_flagcliente_delete_error", function (event, args) {
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
                
                self.montaOpcoesClassificadores = function (classificadores) {
                    for (var i in classificadores) {
                        self.opcoesClassificadores.push({
                            'label': classificadores[i]['etiqueta'],
                            'value': classificadores[i]['classificador']
                        });
                    }  
                };

                self.montaOpcoesServicosCatalogo = function (servicoscatalogo) {
                    for (var i in servicoscatalogo) {
                        self.opcoesServicosCatalogo.push({
                            'label': servicoscatalogo[i]['descricao'],
                            'value': servicoscatalogo[i]['servicocatalogo']
                        });
                    }
                    
                    trataRegistroDesativado(self.opcoesServicosCatalogo, "cliente_ofertas");
                };

                self.montaOpcoesVendedor = function (vendedores) {
                    for (var i in vendedores) {
                        self.opcoesVendedor.push({
                            'label': vendedores[i]['nome'],
                            'value': vendedores[i]['vendedor']
                        });
                    }
                    
                    trataRegistroDesativado(self.opcoesVendedor, "vendedor");
                };
                
                self.montaOpcoesRepresentanteTec = function (representantes) {
                    for (var i in representantes) {
                        self.opcoesRepresentanteTec.push({
                            'label': representantes[i]['nome'],
                            'value': representantes[i]['representantetecnico']
                        });
                    }
                    
                    trataRegistroDesativado(self.opcoesRepresentanteTec, "representantetecnico");
                };

                self.montaOpcoesRepresentanteCom = function (representantes) {
                    for (var i in representantes) {
                        self.opcoesRepresentanteCom.push({
                            'label': representantes[i]['nome'],
                            'value': representantes[i]['representantecomercial']
                        });
                    }
                    
                    trataRegistroDesativado(self.opcoesRepresentanteCom, "representante");
                };

                self.selecionaCampo = function ($item) {
                    $item.operador = Object.keys(self.optionscondicoes[$item.campo].options)[0];
                    self.selecionaOperador($item);
                };
                
                self.selecionaOperador = function ($item) {
                    if ($item.operador == 'is_equal' || $item.operador == 'is_not_equal') {
                        if (self.optionscondicoes[$item.campo].options[$item.operador].options) {
                            if(self.optionscondicoes[$item.campo].options[$item.operador].options.length > 0) {
                                $item.valor = self.optionscondicoes[$item.campo].options[$item.operador].options[0]['value'];
                            }
                        } else {
                            $item.valor = null;
                        }
                    }
                };
                
                self.addCondicaoIn = function () {
                    if (!Array.isArray(self.entity.condicoes_in)) {
                        self.entity.condicoes_in = [];
                    }
                    self.entity.condicoes_in.push({
                        campo: 'bloqueado',
                        operador: 'is_equal',
                        valor: '0'
                    });
                };
                self.removeCondicaoIn = function (index) {

                    self.entity.condicoes_in.splice(index, 1);
                };
                self.addCondicaoEx = function () {
                    if (!Array.isArray(self.entity.condicoes_ex)) {
                        self.entity.condicoes_ex = [];
                    }
                    self.entity.condicoes_ex.push({
                        campo: 'bloqueado',
                        operador: 'is_equal',
                        valor: '0'
                    });
                };
                self.removeCondicaoEx = function (index) {
                    self.entity.condicoes_ex.splice(index, 1);
                };
                self.buscaOperador = function (operador) {
                    for (var i in self.operadores) {
                        if (operador == self.operadores[i].operador) {
                            return self.operadores[i];
                        }
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
                self.opcoesEstado = [
                    {'value': 'AC', 'label': 'AC'}, {'value': 'AL', 'label': 'AL'}, {'value': 'AP', 'label': 'AP'}, {'value': 'AM', 'label': 'AM'}, {'value': 'BA', 'label': 'BA'}, {'value': 'CE', 'label': 'CE'}, {'value': 'DF', 'label': 'DF'}, {'value': 'ES', 'label': 'ES'}, {'value': 'GO', 'label': 'GO'}, {'value': 'MA', 'label': 'MA'}, {'value': 'MT', 'label': 'MT'}, {'value': 'MS', 'label': 'MS'}, {'value': 'MG', 'label': 'MG'}, {'value': 'PA', 'label': 'PA'}, {'value': 'PB', 'label': 'PB'}, {'value': 'PR', 'label': 'PR'}, {'value': 'PE', 'label': 'PE'}, {'value': 'PI', 'label': 'PI'}, {'value': 'RJ', 'label': 'RJ'}, {'value': 'RN', 'label': 'RN'}, {'value': 'RS', 'label': 'RS'}, {'value': 'RO', 'label': 'RO'}, {'value': 'RR', 'label': 'RR'}, {'value': 'SC', 'label': 'SC'}, {'value': 'SE', 'label': 'SE'}, {'value': 'SP', 'label': 'SP'}, {'value': 'TO', 'label': 'TO'}
                ];
                
                self.opcoesStatusSuporte = [{'value': 'Ativo', 'label': 'Ativo'}, {'value': 'Bloqueado', 'label': 'Bloqueado'}];

                self.optionscondicoes = {
                    'bloqueado': {
                        'label': 'Bloqueado',
                        'category': 'Cliente',
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
                        'category': 'Cliente',
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
                    'fidelidade': {
                        'label': 'Fidelidade',
                        'category': 'Cliente',
                        'options': {
                            'is_greater': {
                                'label': 'É maior que',
                                'type': 'select',
                                'options': self._geraOpcoesFidelidade()
                            }
                        }
                    },
                    'representante': {
                        'label': 'Representante Comercial',
                        'category': 'Cliente',
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
                        'category': 'Cliente',
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
                    'cliente_ofertas': {
                        'label': 'Serviço contratado',
                        'category': 'Cliente',
                        'options': {
                            'matches': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesServicosCatalogo
                            },
                            'not_matches': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesServicosCatalogo
                            }
                        }
                    },
                    'email': {
                        'label': 'Endereço de E-mail',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'text'
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                'type': 'text'
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
                    'email_cobranca': {
                        'label': 'Email de contato',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'text'
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                'type': 'text'
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
                        'category': 'Cliente',
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
                    'status_suporte': {
                        'label': 'Status do suporte',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesStatusSuporte
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesStatusSuporte
                            }
                        }
                    },
                    'classificador': {
                        'label': 'Classificadores',
                        'category': 'Classificadores',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesClassificadores
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesClassificadores
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
                    }
                };

                function filtraOpcoesDesativadas(entidade, arrayOpcoes, param) {
                    var filter = arrayOpcoes.filter(function (arrayFilter) {
                        if (param !== "Customizados") {
                            return arrayFilter.value === entidade.valor && entidade.campo === param;
                        } else {
                            return entidade.campo === arrayFilter.atendimentocampocustomizado && arrayFilter.opcoes.indexOf(entidade.valor) !== -1;
                        }
                    }).pop();

                    switch (entidade.desativado) {
                        case true:
                            entidade.desativado = filter === undefined && self.condicoesSempreAtivas.indexOf(entidade.campo) === -1 && self.operacoesSempreAtivas.indexOf(entidade.operador) === -1 ? true : false;
                            break;
                    }
                }

                function trataRegistroDesativado(arrayOpcoes, param) {
                    if (self.entity.condicoes_in !== undefined) {
                        self.entity.condicoes_in = self.entity.condicoes_in.map(function (array) {
                            filtraOpcoesDesativadas(array, arrayOpcoes, param);
                            return array;
                        });
                    }

                    if (self.entity.condicoes_ex !== undefined) {
                        self.entity.condicoes_ex = self.entity.condicoes_ex.map(function (array) {
                            filtraOpcoesDesativadas(array, arrayOpcoes, param);
                            return array;
                        });
                    }
                }
                
                self.montaOpcoesServicosCatalogo(servicosCatalogo);
                self.montaOpcoesVendedor(vendedor);
                self.montaOpcoesRepresentanteTec(repTecnico);
                self.montaOpcoesRepresentanteCom(repComercial);
                self.montaOpcoesClassificadores(classificadores);
            }]);