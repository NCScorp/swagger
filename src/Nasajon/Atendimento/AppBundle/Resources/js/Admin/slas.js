angular.module('mda')
    .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
        var resolve = {
            'entity': ['AtendimentoAdminSlas', '$stateParams', function (entityService, $stateParams) {
                if ($stateParams['entity']) {
                    return $stateParams['entity'];
                } else {
                    if ($stateParams['sla']) {
                        return entityService.get($stateParams['sla']);
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
            }]
        };

        $stateProvider
            .state('atendimento_admin_slas', {
                url: "/slas",
                templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_slas_template'),
                controller: 'AtendimentoAdminSlasListController',
                controllerAs: 'tndmnt_dmn_sls_lst_cntrllr'
            })
            .state('atendimento_admin_slas_new', {
                url: "/slas/new",
                resolve: resolve,
                templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_slas_template_form'),
                controller: 'AtendimentoAdminSlasFormController',
                controllerAs: 'tndmnt_dmn_sls_frm_cntrllr'
            }).state('atendimento_admin_slas_edit', {
                url: "/slas/:sla/edit",
                resolve: resolve,
                templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_slas_template_form'),
                controller: 'Atendimento\Admin\SlasFormController',
                controllerAs: 'tndmnt_dmn_sls_frm_cntrllr'
            })
            ;
    }])
      .controller('AtendimentoAdminSlasListController', [
        '$rootScope', '$scope','$state', '$http', '$stateParams', 'nsjRouting', 'AtendimentoAdminSlas',             function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, AtendimentoAdminSlas) {
        var self = this;
        self.after = '';
        self.busy = false;
        self.finished = false;
        self.filter = AtendimentoAdminSlas.filter;
        self.filter['key'] =  $stateParams['q'] ? $stateParams['q'] : '';
        self.filter['filterfield'] = 'all';
        self.search = AtendimentoAdminSlas.search;
        self.reload = AtendimentoAdminSlas.reload;
        self.nextPage = AtendimentoAdminSlas.load;
        self.loadMore = AtendimentoAdminSlas.loadMore;
        self.service = AtendimentoAdminSlas;
        self.constructors = AtendimentoAdminSlas.constructors = [];
        
        for(var i in $stateParams){
            if(typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q' && i != 'sla'){
                AtendimentoAdminSlas.constructors[i] = $stateParams[i];
            }
        }
        self.entities = AtendimentoAdminSlas.reload();


        self.generateRoute = function (route, params) {
            return nsjRouting.generate(route, params);
        };

        self.isBusy = function(){
            return AtendimentoAdminSlas.busy;
        };            
        self.new = function (context) {
          var self = this;
          self.entity = {};
          AtendimentoAdminSlas._save(self.entity)
            .then(function (response) {
              $state.go('atendimento_admin_slas_edit', { 'sla' : response['data']['sla'], 'entity' : response['data']});
            });
        };
        
        self.montaFraseSlaCondicao = function (condicao) {
            var frase = '';
            
            switch (condicao.campo) {
                case 'email': frase += 'E-mail de contato '; break;
                case 'datacriacao': frase += 'Data de criação ';break;
                case 'situacao':  frase += 'Situação ';break;
                case 'sintoma': frase += 'Descrição ';break;
                case 'canalemail': frase += 'Endereço de E-mail ';break;
                case 'representante': frase += 'Representante Comercial ';break;
                case 'representantetecnico': frase += 'Representante Técnico '; break;
                case 'status_suporte': frase += 'Status do suporte '; break;
                default: frase += (condicao.camponome || condicao.campo) + ' ';
            }
            
            switch (condicao.operador) {
                case 'includes': 
                    frase += 'inclui '; 
                    break;
                case 'is_equal': 
                    frase += 'é igual a '; 
                    break;
                case 'is_not_equal': 
                    frase += 'é diferente de '; 
                    break;
                case 'does_not_include': 
                    frase += 'não inclui '; 
                    break;
                case 'regex': 
                    frase += 'comparação (Expressão Regular) '; 
                    break;
                case 'is_set': 
                    frase += 'existe '; 
                    break;
                case 'is_not_set': 
                    frase += 'não existe '; 
                    break;
                case 'is_before': 
                    frase += 'é anterior a '; 
                    break;
                case 'is_after':
                    frase += 'é após a ';
                    break;
                default: frase += condicao.operador + ' ';
            }
            
            return frase;
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
                url: nsjRouting.generate('atendimento_admin_slas_reordenar', {'id': entity.sla, 'ordem': posicaonova}, false)
            });

            return entity;
        };


        $scope.$on("atendimento_admin_slas_deleted", function (event) {
            self.reload();
        });


      self.nextPage();

    }])
    .controller('AtendimentoAdminSlasFormController', [
        '$scope', '$stateParams', '$state', 'AtendimentoAdminSlas', 'camposcustomizados', 'entity', 'toaster','classificadores', 
        function ($scope, $stateParams, $state, entityService, camposcustomizados, entity, toaster, classificadores) {

            var self = this;
            self.submitted = false;
            self.entity = entity;
            self.constructors = {};
            self.optionscondicoes = [];
            self.optionsacoes = [];
            self.optionsatribuira = [];
            self.camposcustomizados = [];
            self.opcoesRepresentanteTec = [];
            self.opcoesRepresentanteCom = [];
            self.opcoesClassificadores = [];
            self.operadores = [
                { operador: 'is_equal', label: 'É igual a', type: 'select' },
                { operador: 'is_not_equal', label: 'Diferente de', type: 'select' },
                { operador: 'is_set', label: 'Existe', type: false },
                { operador: 'is_not_set', label: 'Não existe', type: false },
                { operador: 'includes', label: 'Inclui', type: 'text' },
                { operador: 'does_not_include', label: 'Não inclui', type: 'text' },
                { operador: 'regex', label: 'Comparação (Expressão Regular)', type: 'text' }
            ];
            self.opcoesStatusSuporte = [{ 'value': 'Ativo', 'label': 'Ativo' }, { 'value': 'Bloqueado', 'label': 'Bloqueado' }];
            self.condicoesSempreAtivas = ["datacriacao", "situacao", "sintoma", "email", "canalemail", "status_suporte"];
            self.operacoesSempreAtivas = ["is_set", "is_not_set"];


            self.optionscondicoes = {
                'datacriacao': {
                    'label': 'Data Criação',
                    'category': 'Chamado',
                    'options': {
                        'is_before': {
                            'label': 'É anterior a',
                            'type': 'datetime'
                        },
                        'is_after': {
                            'label': 'É após a',
                            'type': 'datetime'
                        }
                    }
                },
                'situacao': {
                    'label': 'Situação',
                    'category': 'Chamado',
                    'options': {
                        'is_equal': {
                            'label': 'É igual a',
                            'type': 'select',
                            'options': [
                                { 'value': '0', 'label': 'Aberto' }
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
                }, 'representante': {
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
                }, 'representantetecnico': {
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

            self.tempossla = [{
                id: 'tempo_primeiro',
                ativoId: 'tempo_primeiro_ativo',
                name: 'Primeira Resposta',
                description: 'Tempo para a primeira resposta'
            }, {
                id: 'tempo_proximo',
                ativoId: 'tempo_proximo_ativo',
                name: 'Próximas Resposta',
                description: 'Tempo para entre as demais respostas'
            }, {
                id: 'tempo_resolucao',
                ativoId: 'tempo_resolucao_ativo',
                name: 'Tempo de resolução',
                description: 'Tempo para a resolução do chamado'
            }];


            self.tempossla.map(function (temposla) {
                if (self.entity[temposla.ativoId] == undefined) { self.entity[temposla.ativoId] = false; }
            });
            
            self.selecionaCampo = function ($item) {
                var categoria = self.optionscondicoes[$item.campo].category;
                
                if (categoria === "Classificadores") {
                    $item.tipoentidade = 1;
                } else if (categoria === "Campo Customizado") {
                    $item.tipoentidade = 2;
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


            self.submit = function () {
                
                self.submitted = true;
                if (self.form.$valid && !self.entity.$$__submitting) {

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
                } else {
                    toaster.pop({
                        type: 'error',
                        title: 'Alguns campos do formulário apresentam erros'
                    });
                }
            };

            self.onSubmitSuccess = $scope.$on("atendimento_admin_slas_submitted", function (event, args) {
                var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao ' + acao + ' Sla!'
                });
                $state.go('atendimento_admin_slas', self.constructors);
            });

            self.onSubmitError = $scope.$on("atendimento_admin_slas_submit_error", function (event, args) {
                if (args.response.status == 409) {
                    if (confirm(args.response.data.message)) {
                        self.entity[''] = args.response.data.entity[''];
                        entityService.save(self.entity);
                    }
                } else {
                    if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                        toaster.pop(
                            {
                                type: 'error',
                                title: args.response.data.message
                            });
                    } else {
                        var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                        toaster.pop(
                            {
                                type: 'error',
                                title: "Ocorreu um erro ao tentar " + acao + "."
                            });
                    }
                }
            });
            self.delete = function (force) {
                entityService.delete($stateParams['sla'], force);
            };

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

            self.buscaOperador = function (operador) {
                for (var i in self.operadores) {
                    if (operador == self.operadores[i].operador) {
                        return self.operadores[i];
                    }
                }
            };

            self.adicionaCamposCustomizadosNasCondicoes = function () {
                self.camposcustomizados = camposcustomizados;
                // trataRegistroDesativado(self.camposcustomizados, "Customizados");

                for (var i in self.camposcustomizados) {

                    self.optionscondicoes[self.camposcustomizados[i].atendimentocampocustomizado] = {
                        'label': self.camposcustomizados[i].label,
                        'category': 'Campo Customizado',
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

                    } else if (self.camposcustomizados[i].tipo == 'TE') {
                        var operadores = ['includes', 'does_not_include', 'regex'];
                        for (var y in operadores) {
                            var operador = self.buscaOperador(operadores[y]);
                            self.optionscondicoes[self.camposcustomizados[i].atendimentocampocustomizado]['options'][operadores[y]] = {
                                'label': operador.label,
                                'type': operador.type
                            };
                        }
                    }

                    // trataRegistroDesativado(self.camposcustomizados, "Customizados");

                }
            }();

            self.onDeleteSuccess = $scope.$on("atendimento_admin_slas_deleted", function (event, args) {
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao excluir Sla!'
                });
                $state.go('atendimento_admin_slas', angular.extend(self.constructors));
            });

            self.onDeleteError = $scope.$on("atendimento_admin_slas_delete_error", function (event, args) {
                if (typeof (args.response.data.message) !== 'undefined' && args.response.data.message) {
                    toaster.pop(
                        {
                            type: 'error',
                            title: args.response.data.message
                        });
                } else {
                    toaster.pop(
                        {
                            type: 'error',
                            title: "Ocorreu um erro ao tentar excluir."
                        });
                }
            });

            for (var i in $stateParams) {
                if (i != 'entity') {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }
            }

            self.montaOpcoesClassificadores(classificadores);

        }]);