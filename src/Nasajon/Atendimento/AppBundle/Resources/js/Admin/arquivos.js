angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
                    'entity': ['AtendimentoAdminArquivos', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['arquivo']) {
                                    return entityService.get($stateParams['arquivo']);
                                } else {
                                    return {};
                                }
                            }
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
                        .state('atendimento_admin_arquivos', {
                            url: "/arquivos",
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_arquivos_template'),
                            controller: 'AtendimentoAdminArquivosListController',
                            controllerAs: 'tndmnt_dmn_rqvs_lst_cntrllr'
                        })

                        .state('atendimento_admin_arquivos_new', {
                            url: "/arquivos/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_arquivos_template_form'),
                            controllerAs: 'tndmnt_dmn_rqvs_frm_cntrllr',
                            controller: 'AtendimentoAdminArquivosFormCtrl'
                        })
                        .state('atendimento_admin_arquivos_edit', {
                            url: "/arquivos/:arquivo/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('atendimento_admin_arquivos_template_form'),
                            controllerAs: 'tndmnt_dmn_rqvs_frm_cntrllr',
                            controller: 'AtendimentoAdminArquivosFormCtrl'
                        });
            }])
        .controller('AtendimentoAdminArquivosListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'AtendimentoAdminArquivos', 'toaster',
            function ($rootScope, $scope, $state, $http, $stateParams, nsjRouting, AtendimentoAdminArquivos, toaster) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.filter = AtendimentoAdminArquivos.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = AtendimentoAdminArquivos.search;
                self.reload = AtendimentoAdminArquivos.reload;
                self.nextPage = AtendimentoAdminArquivos.load;
                self.loadMore = AtendimentoAdminArquivos.loadMore;
                self.service = AtendimentoAdminArquivos;
                self.constructors = AtendimentoAdminArquivos.constructors = [];

                AtendimentoAdminArquivos.load = function() {
                    if (!AtendimentoAdminArquivos.busy && !AtendimentoAdminArquivos.finished && AtendimentoAdminArquivos.to_load > 0 ){
                        AtendimentoAdminArquivos.busy = true;
                        var success = true;

                        AtendimentoAdminArquivos._load(AtendimentoAdminArquivos.constructors, AtendimentoAdminArquivos.after, AtendimentoAdminArquivos.filter)
                        .then(function (data) {
                            if (data.length > 0) {
                                for (var i = 0; i < data.length; i++) {
                                    AtendimentoAdminArquivos.entities.push(data[i]);
                                }

                                AtendimentoAdminArquivos.after = AtendimentoAdminArquivos.entities[AtendimentoAdminArquivos.entities.length - 1]['ordem'];
                            }
                                                        
                            if (data.length < 20) {
                                AtendimentoAdminArquivos.finished = true;
                                $rootScope.$broadcast("atendimento_admin_arquivos_list_finished", AtendimentoAdminArquivos.entities);
                            
                            }

                            AtendimentoAdminArquivos.loaded = true;
                            AtendimentoAdminArquivos.to_load--;
                        })
                        .catch(function(response) {
                            if (response.status == 403) {
                                success = false;
                            }
                        })
                        .finally(function(){
                            AtendimentoAdminArquivos.busy = false;

                            if (success) {
                                AtendimentoAdminArquivos.load();
                            }
                        });
                    }
        
                    return AtendimentoAdminArquivos.entities;
                };

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        AtendimentoAdminArquivos.constructors[i] = $stateParams[i];
                    }
                }

                self.entities = AtendimentoAdminArquivos.reload();


                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return AtendimentoAdminArquivos.busy;
                };
                self.new = function (context) {
                    var self = this;
                    self.entity = {
                    };
                    AtendimentoAdminArquivos._save(self.entity)
                            .then(function (response) {
                                $state.go('atendimento_admin_arquivos_edit', {'arquivo': response['data']['arquivo'], 'entity': response['data']});
                            });
                };

                self.montaFraseCondicao = function (condicao) {
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
                        case 'classificador': frase += 'Classificador '; break;
                        default: frase += (condicao.camponome || condicao.campo) + ' ';
                    }
                    
                    switch (condicao.operador) {
                        case 'includes': frase += 'inclui '; break;
                        case 'is_equal': frase += 'é igual a '; break;
                        case 'is_not_equal': frase += 'não é igual a '; break;
                        case 'does_not_include': frase += 'não inclui '; break;
                        case 'regex': frase += 'Comparação (Expressão Regular) '; break;
                        case 'is_set': frase += 'Existe '; break
                        case 'is_not_set': frase += 'Não existe '; break;
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
        
                    return entity;
                };

                $scope.$on("atendimento_admin_arquivos_deleted", function (event) {
                    self.reload();
                });

                self.delete = function (arquivo, force) {
                    AtendimentoAdminArquivos.delete(arquivo, force);
                };

                self.onDeleteSuccess = $scope.$on("atendimento_admin_arquivos_deleted", function (event, args) {
                    toaster.pop(
                            {
                                type: 'success',
                                title: 'Arquivo excluído com sucesso!'
                            });
                    $state.go('atendimento_admin_arquivos', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("atendimento_admin_arquivos_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        toaster.pop(
                                {
                                    type: 'error',
                                    title: args.response.data.erro
                                });
                    } else {
                        toaster.pop(
                                {
                                    type: 'error',
                                    title: 'Ocorreu um erro ao tentar excluir.'
                                });
                    }
                });

                self.order = function (posicaonova, entity) {
                    var posicaoantiga = self.entities.indexOf(entity);
                    
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
                        url: nsjRouting.generate('atendimento_admin_arquivos_reordenar', {'id': entity.arquivo, 'ordem': posicaonova}, false),
                    });

                    return entity;
                };

            }])
        .controller('AtendimentoAdminArquivosFormCtrl', [
            '$scope', '$stateParams', '$state', 'AtendimentoAdminArquivos', 'entity', 'servicosCatalogo', 'classificadores', 'toaster', 'repTecnico', 'repComercial', 'camposcustomizados',
            function ($scope, $stateParams, $state, entityService, entity, servicosCatalogo, classificadores, toaster, repTecnico, repComercial, camposcustomizados) {
                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                self.optionscondicoes = [];
                self.contas = [];
                self.optionsusuarios = [];
                self.opcoesServicosCatalogo = [];
                self.opcoesClassificadores = [];
                self.opcoesRepresentanteTecnico = [];
                self.opcoesRepresentanteComercial = [];
                self.camposcustomizados = [];
                self.condicoesSempreAtivas = ["cliente_status_suporte", "cliente_estado", "cliente_email", "cobranca", "cliente_classificador", "cliente_cobranca" , "cliente_representantetecnico" , "cliente_representantecomercial"];
                self.operacoesSempreAtivas = ["is_set", "is_not_set"];
                self.operadores = [
                    { operador: 'is_equal', label: 'É igual a', type: 'select' },
                    { operador: 'is_not_equal', label: 'Diferente de', type: 'select' },
                    { operador: 'is_set', label: 'Existe', type: false },
                    { operador: 'is_not_set', label: 'Não existe', type: false },
                    { operador: 'includes', label: 'Inclui', type: 'text' },
                    { operador: 'does_not_include', label: 'Não inclui', type: 'text' },
                    { operador: 'regex', label: 'Comparação (Expressão Regular)', type: 'text' }
                ];
                self.opcoesEstado = [
                    {'value': 'AC', 'label': 'AC'}, {'value': 'AL', 'label': 'AL'}, {'value': 'AP', 'label': 'AP'}, {'value': 'AM', 'label': 'AM'}, {'value': 'BA', 'label': 'BA'}, {'value': 'CE', 'label': 'CE'}, {'value': 'DF', 'label': 'DF'}, {'value': 'ES', 'label': 'ES'}, {'value': 'GO', 'label': 'GO'}, {'value': 'MA', 'label': 'MA'}, {'value': 'MT', 'label': 'MT'}, {'value': 'MS', 'label': 'MS'}, {'value': 'MG', 'label': 'MG'}, {'value': 'PA', 'label': 'PA'}, {'value': 'PB', 'label': 'PB'}, {'value': 'PR', 'label': 'PR'}, {'value': 'PE', 'label': 'PE'}, {'value': 'PI', 'label': 'PI'}, {'value': 'RJ', 'label': 'RJ'}, {'value': 'RN', 'label': 'RN'}, {'value': 'RS', 'label': 'RS'}, {'value': 'RO', 'label': 'RO'}, {'value': 'RR', 'label': 'RR'}, {'value': 'SC', 'label': 'SC'}, {'value': 'SE', 'label': 'SE'}, {'value': 'SP', 'label': 'SP'}, {'value': 'TO', 'label': 'TO'}
                ];
                self.opcoesStatusSuporte = [{'value': 'Ativo', 'label': 'Ativo'}, {'value': 'Bloqueado', 'label': 'Bloqueado'}];
                self.optionscondicoes = {
                    'cliente_estado': {
                        'label': 'Estado do cliente',
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
                    'cliente_email': {
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
                     'cliente_representantecomercial': {
                        'label': 'Representante Comercial',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesRepresentanteComercial
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesRepresentanteComercial
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
                    }, 'cliente_representantetecnico': {
                        'label': 'Representante Técnico',
                        'category': 'Cliente',
                        'options': {
                            'is_equal': {
                                'label': 'É igual a',
                                'type': 'select',
                                'options': self.opcoesRepresentanteTecnico
                            },
                            'is_not_equal': {
                                label: 'Diferente de',
                                type: 'select',
                                'options': self.opcoesRepresentanteTecnico
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
                    'cliente_cobranca': {
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
                            },
                           'is_set': {
                               label: 'Qualquer um',
                               type: false
                           },
                           'is_not_set': {
                               label: 'Nenhum',
                               type: false
                           }
                        }
                    },
                    'cliente_status_suporte': {
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
                    'cliente_classificador': {
                        'label': 'Classificadores',
                        'category': 'Classificadores',
                        'options': {
                            'is_equal': {
                                'label': 'Inclui',
                                'type': 'select',
                                'options': self.opcoesClassificadores
                            },
                            'is_not_equal': {
                                label: 'Não Inclui',
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
                    },
                    
                };

                self.montaOpcoesClassificadores = function (classificadores) {
                    for (var i in classificadores) {
                        self.opcoesClassificadores.push({
                            'label': classificadores[i]['etiqueta'],
                            'value': classificadores[i]['classificador']
                        });
                    }
                    
                };

                self.montaOpcoesRepTecnico = function (repTecnico) {
                    for (var i in repTecnico) {
                        self.opcoesRepresentanteTecnico.push({
                            'label': repTecnico[i]['nome'],
                            'value': repTecnico[i]['representantetecnico']
                        });
                    }
                    
                    // trataRegistroDesativado(self.opcoesClassificadores, "classificadores");
                };

                self.montaOpcoesRepComercial = function (repComercial) {
                    for (var i in repComercial) {
                        self.opcoesRepresentanteComercial.push({
                            'label': repComercial[i]['nome'],
                            'value': repComercial[i]['representantecomercial']
                        });
                    }
                    
                };
                
                self.montaOpcoesClassificadores(classificadores);
                if (self.entity.tamanho) {
                    self.entity.tamanho = self.entity.tamanho.toFixed(2);
                }

                if (!Array.isArray(self.entity.arquivosregrasin)) {
                    self.entity.arquivosregrasin = [];
                }

                if (!Array.isArray(self.entity.arquivosregrasex)) {
                    self.entity.arquivosregrasex = [];
                }

                if (self.entity.todosclientes == undefined) {
                    self.entity.todosclientes = 1;
                }

                if (self.entity.multiplicador_tamanho) {
                    self.entity.multiplicador_tamanho = self.entity.multiplicador_tamanho.toString();
                }
                
                self.isBusy = function () {
                    return entityService.busy;
                };

                self.submit = function () {
                    self.submitted = true;

                    if (self.form.$valid && !self.entity.submitting) {
                        if (self.entity.todosclientes == 0 && self.entity.arquivosregrasin.length == 0 && self.entity.arquivosregrasex.length == 0) {
                            toaster.pop(
                                    {
                                        type: 'error',
                                        title: 'Nenhuma condição adicionada!',
                                        body: 'Caso todos possam visualizar, favor selecionar esta opção',
                                    });
                        } else {
                            if (self.entity.todosclientes == 1) {
                                self.entity.arquivosregrasex = [];
                                self.entity.arquivosregrasin = [];
                            }

                            self.entity.tamanho = self.entity.tamanho.toString().replace(/,/, ".").replace(/[^0-9.]/g, "");
                            self.entity.tamanho = Math.ceil(1024 * self.entity.tamanho * self.entity.multiplicador_tamanho);


                            self.entity.arquivosregrasin = self.entity.arquivosregrasin.filter(function (array) {
                                return array.desativado === false || array.desativado === undefined;
                            });

                            self.entity.arquivosregrasex = self.entity.arquivosregrasex.filter(function (array) {
                                return array.desativado === false || array.desativado === undefined;
                            });

                            entityService.save(self.entity);
                        }
                    }
                };

                self.onSubmitSuccess = $scope.$on("atendimento_admin_arquivos_submitted", function (event, args) {
                    var acao = ((args.response.config.method == "PUT") ? "alterado" : "inserido");
                    toaster.pop(
                            {
                                type: 'success',
                                title: 'Arquivo ' + acao + ' com sucesso!'
                            });
                    $state.go('atendimento_admin_arquivos', self.constructors);
                });

                self.onSubmitError = $scope.$on("atendimento_admin_arquivos_submit_error", function (event, args) {
                    if (args.response.status == 409) {
                        if (confirm(args.response.data.message)) {
                            self.entity[''] = args.response.data.entity[''];
                            entityService.save(self.entity);
                        }
                    } else {
                        if (typeof (args.response.data.erro) !== 'undefined') {
                            alert(args.response.data.erro);
                        } else {
                            self.entity.tamanho = 1024 / parseInt(self.entity.tamanho) / parseInt(self.entity.multiplicador_tamanho);
                            var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                            alert("Ocorreu um erro ao tentar " + acao + ".");
                        }
                    }
                });
                self.delete = function (force) {
                    entityService.delete($stateParams['arquivo'], force);
                };

                self.onDeleteSuccess = $scope.$on("atendimento_admin_arquivos_deleted", function (event, args) {
                    $state.go('atendimento_admin_arquivos', angular.extend(self.constructors));
                });

                self.onDeleteError = $scope.$on("atendimento_admin_arquivos_delete_error", function (event, args) {
                    if (typeof (args.response.data.erro) !== 'undefined') {
                        alert(args.response.data.erro);
                    } else {
                        alert("Ocorreu um erro ao tentar excluir.");
                    }
                });
            
                self.addregrasIn = function () {
                    // if (!Array.isArray(self.entity.regrasin)) {
                    //     self.entity.regrasin = [];
                    // }
                    self.entity.arquivosregrasin.push({
                        campo: 'cliente_bloqueado',
                        operador: 'is_equal',
                        valor: '0',
                        tipo: 1
                    });
                };
                self.addregrasEx = function () {
                    // if (!Array.isArray(self.entity.regrasex)) {
                    //     self.entity.regrasex = [];
                    // }
                    self.entity.arquivosregrasex.push({
                        campo: 'cliente_bloqueado',
                        operador: 'is_equal',
                        valor: '0',
                        tipo: 0
                    });
                };

                self.removeregrasIn = function (index) {
                    self.entity.arquivosregrasin.splice(index, 1);
                };

                self.removeregrasEx = function (index) {
                    self.entity.arquivosregrasex.splice(index, 1);
                };


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
                    if (($item.operador == 'is_equal' || $item.operador == 'is_not_equal') && self.optionscondicoes[$item.campo].options[$item.operador].options && self.optionscondicoes[$item.campo].options[$item.operador].options.length > 0) {
                        $item.valor = self.optionscondicoes[$item.campo].options[$item.operador].options[0]['value'];
                        if($item.campo == 'cliente_classificador')
                        {
                            $item.valor = '%' + $item.valor + '%';
                        }
                    } else {
                        $item.valor = null;
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
                        case undefined:
                            entidade.desativado = filter === undefined && self.condicoesSempreAtivas.indexOf(entidade.campo) === -1 && self.operacoesSempreAtivas.indexOf(entidade.operador) === -1 ? true : false;
                            break;
                    }
                }

                function trataRegistroDesativado(arrayOpcoes, param) {
                    if (self.entity.arquivosregrasin !== undefined) {
                        self.entity.arquivosregrasin = self.entity.arquivosregrasin.map(function (array) {
                            filtraOpcoesDesativadas(array, arrayOpcoes, param);
                            return array;
                        });
                    }

                    if (self.entity.arquivosregrasex !== undefined) {
                        self.entity.arquivosregrasex = self.entity.arquivosregrasex.map(function (array) {
                            filtraOpcoesDesativadas(array, arrayOpcoes, param);
                            return array;
                        });
                    }
                }
                
                self.buscaOperador = function (operador) {
                    for (var i in self.operadores) {
                        if (operador == self.operadores[i].operador) {
                            return self.operadores[i];
                        }
                    }
                };

                
                self.montaOpcoesServicosCatalogo(servicosCatalogo);

                for (var i in $stateParams) {
                    if (i != 'entity') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.montaOpcoesRepTecnico(repTecnico);
                self.montaOpcoesRepComercial(repComercial);
            }]);
