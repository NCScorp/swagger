angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
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
                    'entity': ['ServicosAtendimentosregras', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['atendimentoregra']) {
                                    return entityService.get($stateParams['atendimentoregra']);
                                } else {
                                    return {};
                                }
                            }
                        }],
                    'contas': ['Contas', function (Contas) {
                            return Contas;
                        }],
                    'filas': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('servicos_atendimentosfilas_index', {}, false)
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
                    'camposcustomizados': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('servicos_atendimentoscamposcustomizados_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }],
                    'enderecosEmails': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                            return $q(function (resolve, reject) {
                                $http({
                                    method: 'GET',
                                    url: nsjRouting.generate('atendimento_admin_enderecosemails_index', {}, false)
                                }).then(function (response) {
                                    resolve(response.data);
                                }).catch(function (response) {
                                    reject(response);
                                });
                            });
                        }]
                };
                $stateProvider
                        .state('servicos_atendimentosregras', {
                            parent: 'atendimento_admin_configuracoes',
                            url: "/atendimentosregras",
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentosregras_template'),
                            controller: 'ServicosAtendimentosregrasListController',
                            controllerAs: 'srvcs_tndmntsrgrs_lst_cntrllr'
                        })

                        .state('servicos_atendimentosregras_new', {
                            parent: 'servicos_atendimentosregras',
                            url: "/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentosregras_template_form'),
                            controller: 'AtendimentosregrasFormController',
                            controllerAs: 'srvcs_tndmntsrgrs_frm_cntrllr'
                        })
                        .state('servicos_atendimentosregras_edit', {
                            parent: 'servicos_atendimentosregras',
                            url: "/:atendimentoregra/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_atendimentosregras_template_form'),
                            controller: 'AtendimentosregrasFormController',
                            controllerAs: 'srvcs_tndmntsrgrs_frm_cntrllr'
                        });
            }])
        .controller('AtendimentosregrasFormController', [
            '$scope', '$stateParams', '$state', 'nsjRouting', '$http', 'toaster', 'ServicosAtendimentosregras', 'classificadores' ,'entity', 'contas', 'filas', 'repTecnico', 'repComercial', 'camposcustomizados', 'enderecosEmails',
            function ($scope, $stateParams, $state, nsjRouting, $http, toaster, entityService, classificadores, entity, contas, filas, repTecnico, repComercial, camposcustomizados, enderecosEmails) {
                var self = this;

                self.submitted = false;
                self.entity = entity;
                self.optionscondicoes = [];
                self.optionsacoes = [];
                self.optionsatribuira = [];
                self.camposcustomizados = [];
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
                self.opcoesStatusSuporte = [{'value': 'Ativo', 'label': 'Ativo'}, {'value': 'Bloqueado', 'label': 'Bloqueado'}];
                self.condicoesSempreAtivas = ["situacao", "sintoma", "email", "canalemail", "status_suporte"];
                self.operacoesSempreAtivas = ["is_set", "is_not_set"];
                self.acoesSempreAtivas = ["fechar_atendimento", "definir_spam", "atribuir", "enviar_resposta_automatica"];
                self.opcoesRepresentanteTec = [];
                self.opcoesRepresentanteCom = [];

                self.optionscondicoes = {
                    'situacao': {
                        'label': 'Situação',
                        'category': 'Chamado',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': [
                                    {'value': '0', 'label': 'Aberto'}
                                ]
                            }
                        }
                    },
                    'sintoma': {
                        'label': 'Descrição',
                        'category': 'Chamado',
                        'options': {
                            'includes': {
                                'label': 'Inclui',
                                'type': 'text'
                            },
                            'does_not_include': {
                                'label': 'Não inclui',
                                'type': 'text'
                            },
                            'regex': {
                                'label': 'Comparação (Expressão Regular)',
                                'type': 'text'
                            }
                        }
                    },
                    'email': {
                        'label': 'Email do contato',
                        'category': 'Cliente',
                        'options': {
                            'includes': {
                                'label': 'Inclui',
                                'type': 'text'
                            },
                            'does_not_include': {
                                'label': 'Não inclui',
                                'type': 'text'
                            },
                            'regex': {
                                'label': 'Comparação (Expressão Regular)',
                                'type': 'text'
                            }
                        }
                    },
                    'canalemail': {
                        'label': 'Endereço de E-mail',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': []
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
                    'status_suporte': {
                        'label': 'Status do suporte',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesStatusSuporte
                            }
                        }
                    }
                };
                self.isBusy = function () {
                    return entityService.busy;
                };
                self.optionsacoes = {
                    'fechar_atendimento': {
                        'label': 'Fechar Atendimento',
                        'type': 'none'
                    },
                    'definir_spam': {
                        'label': 'Definir como SPAM',
                        'type': 'none'
                    },
                    'atribuir': {
                        'label': 'Atribuir a',
                        'type': 'select',
                        'options': self.optionsatribuira
                    },
                    'enviar_resposta_automatica': {
                        'label': 'Enviar resposta automática',
                        'type': 'textarea'
                    },
                };
                self.constructors = {};
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

                    self.adicionaClassificadoresNasCondicoes();

                    trataRegistroDesativado(self.opcoesClassificadores, "classificadores");
                };

                self.adicionaClassificadoresNasCondicoes = function () {
                    for (var i in self.opcoesClassificadores) {
                        self.optionscondicoes[self.opcoesClassificadores[i].value] = {
                            'label': self.opcoesClassificadores[i].label,
                            'category': 'Classificadores',
                            'options': {}
                        };

                        var operadores = ['includes', 'does_not_include', 'regex'];
                        for (var y in operadores) {
                            var operador = self.buscaOperador(operadores[y]);
                            self.optionscondicoes[self.opcoesClassificadores[i].value]['options'][operadores[y]] = {
                                'label': operador.label,
                                'type': operador.type
                            };
                        }
                    }
                };

                self.submit = function () {
                    self.submitted = true;
                    if (self.form.$valid && !self.entity.submitting) {

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

                        if (self.entity.acoes !== undefined) {
                            self.entity.acoes = self.entity.acoes.filter(function (array) {
                            return array.desativado === false || array.desativado === undefined;
                        });
                        }

                        entityService.save(self.entity);
                    }
                };

                self.onSubmitSuccess = $scope.$on("servicos_atendimentosregras_submitted", function (event, args) {
                    var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao ' + acao + ' Regra!'
                    });
                    $state.go('servicos_atendimentosregras', self.constructors);
                });

                self.onSubmitError = $scope.$on("servicos_atendimentosregras_submit_error", function (event, args) {
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
                    entityService.delete($stateParams['atendimentoregra'], force);
                };

                self.onDeleteSuccess = $scope.$on("servicos_atendimentosregras_deleted", function (event, args) {
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao excluir Regra!'
                    });

                    $state.go('servicos_atendimentosregras', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("servicos_atendimentosregras_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });

                self.preencheOptionsContas = function () {
                    for (var i in contas) {
                        self.optionsatribuira.push({'label': contas[i].nome + ' (' + contas[i].email + ')', 'value': contas[i].email, 'grupo': 'Usuários'});
                    }
                }();

                self.preencheOptionsFilas = function () {
                    for (var i in filas) {
                        self.optionsatribuira.push({'label': filas[i].nome, 'value': filas[i].atendimentofila, 'grupo': 'Filas'});
                    }
                }();

                function filtraOpcoesDesativadas(entidade, arrayOpcoes, param) {
                    var filter = arrayOpcoes.filter(function (arrayFilter) {
                        if(entidade.desativado)
                            entidade.desativado = filter === undefined  ? true : false;
                        if (param === "representantetecnico" || param === "representante") {
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

                    if (self.entity.acoes !== undefined && param === "Ações") {
                        self.entity.acoes = self.entity.acoes.map(function (array) {
                            filtraOpcoesDesativadas(array, arrayOpcoes, param);
                            return array;
                        });
                    }
                }
                
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
                
                self.buscaOperador = function (operador) {
                    for (var i in self.operadores) {
                        if (operador == self.operadores[i].operador) {
                            return self.operadores[i];
                        }
                    }
                };

                self.adicionaCanalemailNasCondicoesEAcoes = function () {
                    for (var i in enderecosEmails) {
                        self.optionscondicoes.canalemail.options.is_equal.options.push({
                            'value': enderecosEmails[i].enderecoemail,
                            'label': enderecosEmails[i].email
                        });
                    }
                }();

                self.adicionaCamposCustomizadosNasCondicoesEAcoes = function () {
                    self.camposcustomizados = camposcustomizados;
                    trataRegistroDesativado(self.camposcustomizados, "Customizados");

                    for (var i in self.camposcustomizados) {

                        self.optionscondicoes[self.camposcustomizados[i].atendimentocampocustomizado] = {
                            'label': self.camposcustomizados[i].label,
                            'category': 'Campo Customizado',
                            'options': {}
                        };

                        self.optionsacoes[self.camposcustomizados[i].atendimentocampocustomizado] = {
                            'label': self.camposcustomizados[i].label,
                            'options': {}
                        };

                        if (self.camposcustomizados[i].tipo == 'CB') {

                            var operadores = ['is_set', 'is_not_set', 'is_equal', 'is_not_equal'];

                            for (var y in operadores) {
                                var operador = self.buscaOperador(operadores[y]);
                                var opcoescondicoes = [];
                                for (var z in self.camposcustomizados[i].opcoes) {
                                    opcoescondicoes.push({'value': self.camposcustomizados[i].opcoes[z], 'label': self.camposcustomizados[i].opcoes[z]});
                                }
                                self.optionscondicoes[self.camposcustomizados[i].atendimentocampocustomizado]['options'][operadores[y]] = {
                                    'label': operador.label,
                                    'type': operador.type,
                                    'options': opcoescondicoes
                                };
                            }

                            var opcoesacoes = [];
                            for (var z in self.camposcustomizados[i].opcoes) {
                                opcoesacoes.push({'value': self.camposcustomizados[i].opcoes[z], 'label': self.camposcustomizados[i].opcoes[z]});
                            }

                            self.optionsacoes[self.camposcustomizados[i].atendimentocampocustomizado] = {
                                'label': self.camposcustomizados[i].label,
                                'type': 'select',
                                'options': opcoesacoes
                            };

                        } else if (self.camposcustomizados[i].tipo == 'TE') {
                            var operadores = ['includes', 'does_not_include', 'regex'];
                            for (var y in operadores) {
                                var operador = self.buscaOperador(operadores[y]);
                                self.optionscondicoes[self.camposcustomizados[i].atendimentocampocustomizado]['options'][operadores[y]] = {
                                    'label': operador.label,
                                    'type': operador.type
                                };
                            }
                            self.optionsacoes[self.camposcustomizados[i].atendimentocampocustomizado] = {
                                'label': self.camposcustomizados[i].label,
                                'type': 'text'
                            };
                        }

                        trataRegistroDesativado(self.camposcustomizados, "Ações");
                    }
                }();

                self.addCondicaoIn = function () {
                    if (!Array.isArray(self.entity.condicoes_in)) {
                        self.entity.condicoes_in = [];
                    }
                    self.entity.condicoes_in.push({
                        campo: 'situacao',
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
                        campo: 'situacao',
                        operador: 'is_equal',
                        valor: '0'
                    });
                };
                self.removeCondicaoEx = function (index) {
                    self.entity.condicoes_ex.splice(index, 1);
                };
                self.addAcao = function () {

                    if (!Array.isArray(self.entity.acoes)) {
                        self.entity.acoes = [];
                    }
                    self.entity.acoes.push({
                        acao: 'fechar_atendimento',
                        valor: null
                    });

                };
                self.removeAcao = function (index) {

                    self.entity.acoes.splice(index, 1);
                };

                self.selecionaCampo = function ($item) {
                    
                    if (self.optionscondicoes[$item.campo].category === 'Campo Customizado') {
                        $item.tipoentidade = 2;
                    }else if (self.optionscondicoes[$item.campo].category === 'Classificadores') {
                        $item.tipoentidade = 1;
                    }else{
                        $item.tipoentidade = 0;
                    }

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

                self.selecionaAcao = function ($item) {
                    if (self.optionsacoes[$item.acao].type == 'select') {
                        $item.valor = self.optionsacoes[$item.acao].options[0].value;
                    } else {
                        $item.valor = null;
                    }
                };
                
                self.montaOpcoesRepresentanteTec(repTecnico);
                self.montaOpcoesRepresentanteCom(repComercial);
                self.montaOpcoesClassificadores(classificadores);

            }])
        .controller('ServicosAtendimentosregrasListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'ServicosAtendimentosregras', function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, ServicosAtendimentosregras) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = ServicosAtendimentosregras.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = ServicosAtendimentosregras.search;
                self.reload = ServicosAtendimentosregras.reload;
                self.nextPage = ServicosAtendimentosregras.load;
                self.loadMore = ServicosAtendimentosregras.loadMore;
                self.service = ServicosAtendimentosregras;
                self.constructors = ServicosAtendimentosregras.constructors = [];

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        ServicosAtendimentosregras.constructors[i] = $stateParams[i];
                    }
                }

                self.entities = ServicosAtendimentosregras.reload();

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
                        url: nsjRouting.generate('servicos_atendimentosregras_reordenar', {'id': entity.atendimentoregra, 'ordem': posicaonova}, false),
                    });

                    return entity;
                };

                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return ServicosAtendimentosregras.busy;
                };
                self.new = function (context) {
                    var self = this;
                    self.entity = {
                    };
                    ServicosAtendimentosregras._save(self.entity)
                            .then(function (response) {
                                $state.go('servicos_atendimentosregras_edit', {'atendimentoregra': response['data']['atendimentoregra'], 'entity': response['data']});
                            });
                };


                $scope.$on("servicos_atendimentosregras_deleted", function (event) {
                    self.reload();
                });

                $rootScope.$on("servicos_atendimentosregras_submitted", function (event, args) {
                    if (!args.autosave) {
                        self.reload();
                    }
                });

                self.nextPage();

            }]);