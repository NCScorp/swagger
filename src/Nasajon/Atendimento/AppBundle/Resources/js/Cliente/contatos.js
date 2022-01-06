angular.module('mda')
    .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
        var resolve = {
            'entity': ['AtendimentoClienteContatos', '$stateParams', function (entityService, $stateParams) {
                if ($stateParams['entity']) {
                    return $stateParams['entity'];
                } else {
                    if ($stateParams['cliente']) {
                        return entityService.get($stateParams['cliente']);
                    } else {
                        return {};
                    }
                }
            }]
        };

        $stateProvider
            .state('atendimento_cliente_contatos', {
                    url: "/contatos/",
                    resolve: resolve,
                    templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_contatos_template_show'),
                    controller: 'AtendimentoClienteContatosShowController',
                    controllerAs: 'tndmnt_clnt_cntts_frm_cntrllr'
            })
            .state('atendimento_cliente_contatos_new', {
                    url: "/contatos/new?pessoa",
                    resolve: resolve,
                    templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_contatos_template_form'),
                    controllerAs: 'tndmnt_clnt_cntts_frm_cntrllr',
                    controller: 'AtendimentoClienteContatosFormController'
            })
            .state('atendimento_cliente_contatos_show', {
                url: "/contatos/",
                resolve: resolve,
                templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_contatos_template_show'),
                controller: 'AtendimentoClienteContatosShowController',
                controllerAs: 'tndmnt_clnt_cntts_frm_cntrllr'
            })
            .state('atendimento_cliente_contatos_edit', {
                    url: "/contatos/:id/edit",
                    resolve: resolve,
                    templateUrl: nsjRoutingProvider.$get().generate('atendimento_cliente_contatos_template_form'),
                    controller: 'AtendimentoClienteContatosFormController',
                    controllerAs: 'tndmnt_clnt_cntts_frm_cntrllr'
                });
    }])    
    .controller('AtendimentoClienteContatosShowController', [
        '$rootScope', '$scope', '$stateParams', '$state', 'AtendimentoClienteContatos', 'entity', 'toaster', '$http', 'nsjRouting', '$rootScope', '$uibModal',
        function ($rootScope, $scope, $stateParams, $state, entityService, entity, toaster, $http, nsjRouting, $rootScope, $uibModal) {
    
            var self = this;
            self.submitted = false;
            self.entity = entity;
            self.constructors = {};

            self.isDeleting = false;

            self.clientes = entityService.reload();

            self.setClienteSelecionado = function(entity) {
                $rootScope.clienteSelecionado = entity;
            }

            $scope.$on('atendimento_cliente_contatos_list_finished', function(response, entities) {
                if (!$rootScope.clienteSelecionado) {
                    self.entity = entities[0];
                } else {
                    self.entity = entities.find(function(cliente) {
                        return cliente.id == $rootScope.clienteSelecionado.id;
                    });
                }
            });

            self.adicionarContato = function() {
                $state.go('atendimento_cliente_contatos_new', {pessoa: self.entity.id});
            }

            self.editarContato = function(contato) {
                $state.go('atendimento_cliente_contatos_edit', { id: contato.id });
            }

            self.excluirContato = function(contato) {

                $scope.contato = contato;

                var modalInstance = $uibModal.open({
                   
                    size: '',
                    backdrop: 'static',
                    keyboard: false,
                    template: '' + 
                    '<div class="headline-sub clearfix headline-mobile">' + 
                    '    <h3 class="">Excluir contato</h3>' + 
                    '</div>' + 
                    '<div class="modal-body">' + 
                    '    <div class="row">' + 
                    '        <div class="col-sm-12">' + 
                    "            Tem certeza que deseja excluir o contato <strong>'{{ctrl.contato.nome}}'?" + 
                    '        </div>' + 
                    '    </div>' + 
                    '</div>' + 

                    '<div class="modal-footer">' + 
                    '    <button class="btn btn-default" ng-click="ctrl.close()">Cancelar</button>' + 
                    '    <button class="btn btn-primary" ng-click="ctrl.excluir()">Excluir</button>' + 
                    '</div>'
                    ,
                    controllerAs: 'ctrl',
                    controller: ['AtendimentoClienteContatos', function(entityService) {
                        this.contato = modalInstance.contato;

                        this.excluir = function() {                            
                            this.close(true);
                        }

                        this.close = modalInstance.close;
                    }]
                });

                modalInstance.contato = contato;

                modalInstance.result.then(function (response) {
                    if (!response) {
                        return;
                    }

                    self.isDeleting = true;

                    entityService.delete(contato.id);
                });
            }

            $scope.$on('atendimento_cliente_contatos_deleted', function(params, response) {
                toaster.success('O contato foi excluído com sucesso!');

                entityService.reload();

                self.isDeleting = false;
            });

            $scope.$on('atendimento_cliente_contatos_delete_error', function(params, response) {
                toaster.error('Não foi possível excluir o contato "' + $scope.contato.nome + '"!');

                self.isDeleting = false;
            });

            // Sobrescreve a função de deletar apenas para exibir a mensagem de confirmação mais elegante.
            entityService.delete = function($identifier, force) {
                if (typeof($identifier) == 'object') {
                    $identifier = $identifier['id'];
                }

                if ($identifier) {
                    $http
                    .delete(nsjRouting.generate('atendimento_cliente_contatos_delete', angular.extend(self.constructors,{'id': $identifier }), true))
                    .then(function (response) {
                        $rootScope.$broadcast("atendimento_cliente_contatos_deleted", {
                            entity: self.entity,
                            response : response
                        });
                    })
                    .catch(function (response) {
                        $rootScope.$broadcast("atendimento_cliente_contatos_delete_error", {
                            entity: self.entity,
                            response : response
                        });
                    });
                }
            }

            self.submit = function () {
                self.submitted = true;
                if (self.form.$valid && !self.entity.$$__submitting) {
                    entityService.save(self.entity);
                } else {
                    toaster.pop({
                        type: 'error',
                        title: 'Alguns campos do formulário apresentam erros'
                    });
                }
            };
    
            self.onSubmitSuccess = $scope.$on("atendimento_cliente_contatos_submitted", function (event, args) {
                var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao ' + acao + ' Contatos!'
                });
                $state.go('atendimento_cliente_contatos', self.constructors);
            });
    
            self.onSubmitError = $scope.$on("atendimento_cliente_contatos_submit_error", function (event, args) {
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
                toaster.pop({
                    type: 'success',
                    title: 'Sucesso ao excluir Contatos!'
                });
                entityService.delete($stateParams['id'], force);
            };
    
            self.onDeleteSuccess = $scope.$on("atendimento_cliente_contatos_deleted", function (event, args) {
                $state.go('atendimento_cliente_contatos', angular.extend(self.constructors));
            });
    
            for (var i in $stateParams) {
                if (i != 'entity') {
                    self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                }
            }
        }])
    .controller('AtendimentoClienteContatosFormController', [
            '$scope', '$stateParams', '$state', 'AtendimentoClienteContatos', 'entity', 'toaster',
            function ($scope, $stateParams, $state, entityService, entity, toaster) {
        
                var self = this;
                self.submitted = false;
                self.entity = entity;
                self.constructors = {};
                self.idPessoa = $stateParams['pessoa'];

                self.isSaving = false;

                self.clienteSelecionado = { };

                self.addNovoEmail = function() {
                    if (!self.entity.contatosemails) {
                        self.entity.contatosemails = [];
                    }

                    self.entity.contatosemails.push({
                        id: '',
                        email: '',
                    });
                }

                self.addNovoTelefone = function() {
                    if (!self.entity.telefones) {
                        self.entity.telefones = [];
                    }

                    self.entity.telefones.push({
                        id: '',
                        ddi: '',
                        ddd: '',
                        telefone: '',
                        ramal: '',
                        tptelefone: '',
                    });
                }

                if ($stateParams.id) {
                    entityService.get($stateParams.id);
                } else {
                    self.addNovoEmail();
                    self.addNovoTelefone();
                }

                self.podeSalvar = function() {
                    var podeSalvar = true;

                    if (!self.entity.nome || !self.entity.cargo) {
                        podeSalvar = false;
                    }

                    if (self.entity.telefones) {
                        self.entity.telefones.forEach(function(telefone) {
                            if (!telefone.ddd || !telefone.telefone || telefone.telefone.length < 8) {
                                podeSalvar = false;
                            }
                        });
                    }

                    if (self.entity.contatosemails) {
                        self.entity.contatosemails.forEach(function(contatoEmail) {
                            if (!contatoEmail.email || contatoEmail.email.length < 8 || !self.validarEmail(contatoEmail.email)) {
                                podeSalvar = false;
                            }
                        });
                    } else {
                        podeSalvar = false;
                    }

                    return podeSalvar;
                }

                self.isKeyBackDel = function(event) {
                    // Backspace, Tab, Delete, Left e Right
                    if (event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 46 || event.keyCode == 37 || event.keyCode == 39) {
                        return true;
                    }

                    return false;
                }

                self.formatarValor = function(object, tipo) {
                    if (tipo == 'phone') {
                        var lastIsTrace = object.telefone.substring(object.telefone.length - 1, object.telefone.length) == "-";
                        var tel = object.telefone.replace('-', '');

                        if (tel.length < 9) {
                            if (tel.length > 3 && !lastIsTrace) {
                                object.telefone = tel.substring(0, 4) + '-' + tel.substring(4, 8);
                            } else {
                                object.telefone = tel;
                            }
                        
                        } else {
                            object.telefone = tel.substring(0, 5) + '-' + tel.substring(5, 9);                            
                        }
                    }
                }

                self.validarEmail = function(email) {
                    return email.match( /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
                }

                self.validaTipoEmail = function(contatoEmail) {
                    if (contatoEmail.tipo == 1) {
                        contatoEmail.recebe_boleto = false;
                        return;
                    }

                    contatoEmail.principal = false;
                }

                self.validarInput = function(event, type, object) {

                    var isKeyBackDel = self.isKeyBackDel(event);

                    if (type == 'int') {
                        if (!event.key.match(/\d/gi) && !isKeyBackDel) {
                            event.preventDefault();
                        }
                    }
                }

                self.cancelar = function() {
                    $state.go('atendimento_cliente_contatos_show');
                }

                self.salvar = function() {
                    if (self.idPessoa) {
                        self.entity.cliente = self.idPessoa;
                    }

                    if (self.entity.principal) {
                        var countEmailPrincipal = self.entity.contatosemails.filter(function(contato) {
                            return contato.tipo == 1;
                        });

                        if (!countEmailPrincipal.length) {
                            toaster.error('É necessário selecionar pelo menos um e-mail como principal.');

                            return;
                        }
                    }

                    self.isSaving = true;

                    entityService.save(self.entity, true);
                }

                $scope.$on('atendimento_cliente_contatos_loaded', function(paramns, entidade) {
                    self.entity = entidade;

                    if (!self.entity.contatosemails.length) {
                        self.addNovoEmail();
                    }

                    if (!self.entity.telefones.length) {
                        self.addNovoTelefone();
                    }

                    self.entity.telefones.map(function (telefone) {
                        telefone.tptelefone = (parseInt(telefone.tptelefone) >= 0) ? telefone.tptelefone.toString() : '';
                    });
                });

                self.submit = function () {
                    self.submitted = true;
                    if (self.form.$valid && !self.entity.$$__submitting) {
                        entityService.save(self.entity);
                    } else {
                        toaster.pop({
                            type: 'error',
                            title: 'Alguns campos do formulário apresentam erros'
                        });
                    }
                };
        
                self.onSubmitSuccess = $scope.$on("atendimento_cliente_contatos_submitted", function (event, args) {
                    var acao = ((args.response.config.method == "PUT") ? "atualizar" : "inserir");
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao ' + acao + ' Contatos!'
                    });
                    $state.go('atendimento_cliente_contatos', self.constructors);
                });
        
                self.onSubmitError = $scope.$on("atendimento_cliente_contatos_submit_error", function (event, args) {
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

                    self.isSaving = false;
                });
                self.delete = function (force) {
                    toaster.pop({
                        type: 'success',
                        title: 'Sucesso ao excluir Contatos!'
                    });
                    entityService.delete($stateParams['id'], force);
                };
        
                self.onDeleteSuccess = $scope.$on("atendimento_cliente_contatos_deleted", function (event, args) {
                    $state.go('atendimento_cliente_contatos', angular.extend(self.constructors));
                });
        
                self.onDeleteError = $scope.$on("atendimento_cliente_contatos_delete_error", function (event, args) {
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
            }]);
