angular.module('mda')
        .controller('AtendimentoAdminFollowupsFormController', ['$http', '$rootScope', '$scope', '$stateParams', '$state', 'nsjRouting', 'toaster' ,'$uibModal',
            function ($http, $rootScope, $scope, $stateParams, $state, nsjRouting, toaster, $uibModal) {
                var self = this;
                self.form = null;
                self.ckeditor = null;                
                self.submitting = false;
                self.submitted = false;
                self.entity = {
                    tipo: 0
                };
                self.qtdAnexos = 0;
                self.uploadAnexos = false;
                self.fecharAtendimento = 0;
                self.formSolicitacoesValid = false;
                self.constructors = {};
                for (var i in $stateParams) {
                    if (typeof ($stateParams[i]) != 'function') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }
                
                self.toggleTipo = function ($tipo) {
                    if (!self.submitting) {
                        if (self.entity.tipo == 0 && $tipo != 0) {
                            self.entity.tipo = 1;
                        } else if (self.entity.tipo == 1 && $tipo != 1) {
                            self.entity.tipo = 0;
                        }
                    }
                };

                self.clear = function () {
                    self.entity.historico = "";
                    self.entity.anexos = [];
                    self.submitted = false;
                };

                self.historicoVazio = function () {
                    return (!self.entity.historico && self.submitted);
                };

                self.onSubmitSuccess = function (response) {
                    delete self.entity.artigo;
                    var successMsg = ["Resposta enviada com sucesso.", "Comentário enviado com sucesso."];
                    $rootScope.$broadcast("atendimento_admin_followups_submitted", {
                        entity: self.entity,
                        response: response
                    });
                    toaster.pop({
                        type: 'success',
                        title: successMsg[self.entity.tipo]
                    });
                    self.clear();
                };

                self.onSubmitError = function (response) {
                    if (response.status === 401) {
                        alert(response.data.message);
                    } else {
                        $rootScope.$broadcast("atendimento_admin_followups_error", {
                            entity: self.entity,
                            response: response
                        });
                    }
                };

                $scope.$on("uploadAnexos", function (event, args) {
                    if (args) {
                        self.qtdAnexos++;
                        self.uploadAnexos = true;
                    } else {
                        self.qtdAnexos--

                        //Se todos os anexos já estiverem carregados liberar o submit
                        if (self.qtdAnexos == 0) {
                            self.uploadAnexos = false;
                        }
                    }
                });
  
                self.abrirSugestoesArtigos = function() {
                    $rootScope.modalSugestoesArtigos = {};
                    $rootScope.ckeditor = self.ckeditor;
                    $rootScope.modalSugestoesArtigos.modalInstance = $uibModal.open({
                        templateUrl: nsjRouting.generate('template_sugerir_artigos_modal', {}, false),
                        controller: 'ModalSugestoesArtigosController',
                        controllerAs: 'sugestoesArtigosModal',
                    });
                };

                self.toggled = function (open) {
                    if (open) {
                        document.getElementById('txtRespostaAutomatica').focus();
                    } else {
                        self.resetarListaArtigos();
                    }
                };

                self.submit = function ($event, fecharAtendimento, form) {
                    if (!self.uploadAnexos) {
                        $event.preventDefault();
                        if (form) {
                            self.form = form;
                        }
                       
                        if(self.entity.copiaoculta){
                          self.entity.copiaoculta = self.entity.copiaoculta.map(function (el) {
                           return el.text.trim();
                          });
                        }
                        
                        self.constructors['fechar_atendimento'] = fecharAtendimento;
                        self.submitted = true;
                        var i = 0;
                        
                        if (self.form.$$parentForm.$error.required) {
                            self.form.$$parentForm.$error.required.forEach(function (field) {                            
                                if (field.$name !== 'respostaController.form' && field.$name !== 'comentarioForm') {
                                    i++;
                                }
                            });
                        }
                        
                        self.formSolicitacoesValid = i > 0 && self.form.$valid ? false : true;
                        
                        if (self.form.$invalid) {
                            if (self.form.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar");
                            }
                        } else if (self.form.$valid && !self.submitting && (self.form.$$parentForm.$valid || self.formSolicitacoesValid)) {
                            var method = "POST";
                            var url = nsjRouting.generate('atendimento_admin_followups_create', self.constructors, false);
                            self.submitting = true;

                            $http({
                                method: method,
                                url: url,
                                data: self.entity
                            }).then(function (response) {
                                self.onSubmitSuccess(response);
                            }).catch(function (response) {
                                self.onSubmitError(response);
                            }).finally(function (response) {
                                self.submitting = false;
                                delete self.entity.copiaoculta;
                            });
                        } else {
                            $rootScope.$broadcast('atendimento_admin_solicitacoes_form_invalid');
                        }
                    } else {
                        alert("Aguarde o upload dos arquivos anexados para continuar.");
                    }

                };
                
                $scope.$on('atendimento_admin_solicitacoes_form_valid', function (event) {
                    self.formSolicitacoesValid = true;
                });
                
            }])
        .controller('ModalSugestoesArtigosController', ['$uibModalInstance', '$rootScope', '$http', 'nsjRouting', 'AtendimentoAdminArtigos','toaster', function ($uibModalInstance, $rootScope,$http, nsjRouting, AtendimentoAdminArtigos,toaster) {
                var self = this;
                self.escolheuArtigo = false;
                self.busy = false;

                self.isBusy = function () {
                    return self.busy;
                };
                
                self.buscarArtigos = function (text) {
                    self.busy = true;
                    var filter = {};
                    filter.key = text;
                    
                    if(filter.key == ''){
                        
                        self.busy = false;
                        self.resetarListaArtigos();
                        
                        
                        return;
                    };

                    filter.filterfield = 'all';
                    AtendimentoAdminArtigos._load({ 'status': true }, moment().add(1, 'days').format("YYYY-MM-DD HH:mm:ss"), filter).then(function (data) {
                        self.artigos = data;
                        self.busy = false;
                    });
                };
                
                self.inserirLinkArtigo = function (artigo) {
                    var request = {
                        "method": "GET",
                        "url": nsjRouting.generate('atendimento_admin_artigos_get', {'id': artigo.artigo}, false)
                    };
                    
                    $http(request).then(function (response) {
                        var tag = "<a href=\"" + response.data.linkPublico + "\"> clique aqui </a>";
                        if ($rootScope.ckeditor.getData()) {
                            $rootScope.ckeditor.insertHtml(tag);
                        }else{
                            $rootScope.ckeditor.setData(tag);
                        }
                        $rootScope.ckeditor = undefined;
                        self.resetarListaArtigos();
                    });

                    self.fechar();
                };

                self.inserirArtigo = function (artigo) {
                    var request = {
                        "method": "GET",
                        "url": nsjRouting.generate('atendimento_admin_artigos_get', {'id': artigo.artigo}, false)
                    };
                    $http(request).then(function (response) {
                        $rootScope.ckeditor.setData(response.data.conteudo);
                        $rootScope.ckeditor = undefined;
                        self.resetarListaArtigos();
                    });
                    
                    self.fechar();
                };
                
                self.resetarListaArtigos = function () {
                    self.artigos = [];
                    self.searchArtigo = null;
                };
                
                self.fechar = function () {
                    $rootScope.modalSugestoesArtigos.modalInstance.dismiss();
                    $rootScope.modalSugestoesArtigos = undefined;
                };
            }])
        .controller('AtendimentoAdminFollowupsFormComentarioController', ['$http', '$rootScope', '$scope', '$stateParams', '$state', 'nsjRouting', 'toaster',
            function ($http, $rootScope, $scope, $stateParams, $state, nsjRouting, toaster) {
                var self = this;
                self.form = null;
                self.submitting = false;
                self.submitted = false;
                self.qtdAnexos = 0;
                self.uploadAnexos = false;
                self.entity = {
                    tipo: 1
                };

                self.fecharAtendimento = 0;
                self.constructors = {};
                for (var i in $stateParams) {
                    if (typeof ($stateParams[i]) != 'function') {
                        self.constructors[i] = $stateParams[i] ? $stateParams[i] : '';
                    }
                }

                self.toggleTipo = function ($tipo) {
                    if (!self.submitting) {
                        if (self.entity.tipo == 0 && $tipo != 0) {
                            self.entity.tipo = 1;
                        } else if (self.entity.tipo == 1 && $tipo != 1) {
                            self.entity.tipo = 0;
                        }
                    }
                };

                self.clear = function () {
                    self.entity.historico = "";
                    self.entity.anexos = [];
                    self.submitted = false;
                };

                self.historicoVazio = function () {
                    return (!self.entity.historico && self.submitted);
                };

                self.onSubmitSuccess = function (response) {
                    var successMsg = ["Resposta enviada com sucesso.", "Comentário enviado com sucesso."];
                    $rootScope.$broadcast("atendimento_admin_followups_submitted", {
                        entity: self.entity,
                        response: response
                    });
                    toaster.pop({
                        type: 'success',
                        title: successMsg[self.entity.tipo]
                    });
                    self.clear();
                };

                self.onSubmitError = function (response) {
                    if (response.status === 401) {
                        alert(response.data.message);
                    } else {
                        $rootScope.$broadcast("atendimento_admin_followups_error", {
                            entity: self.entity,
                            response: response
                        });
                    }
                };

                $scope.$on("uploadAnexos", function (event, args) {
                    if (args) {
                        self.qtdAnexos++;
                        self.uploadAnexos = true;
                    } else {
                        self.qtdAnexos--

                        //Se todos os anexos já estiverem carregados liberar o submit
                        if (self.qtdAnexos == 0) {
                            self.uploadAnexos = false;
                        }
                    }
                });

                self.submit = function ($event, fecharAtendimento, form) {
                    if (!self.uploadAnexos) {
                        $event.preventDefault();
                        if (form) {
                            self.form = form;
                        }
                       
                        self.constructors['fechar_atendimento'] = fecharAtendimento;
                        self.submitted = true;
                        if (self.form.$invalid) {
                            if (self.form.$error.isUploading) {
                                alert("Aguarde o upload da imagem antes de continuar");
                            }
                        } else if (self.form.$valid && !self.submitting) {
                            var method = "POST";
                            var url = nsjRouting.generate('atendimento_admin_followups_create', self.constructors, false);
                            self.submitting = true;

                            $http({
                                method: method,
                                url: url,
                                data: self.entity
                            }).then(function (response) {
                                self.onSubmitSuccess(response);
                            }).catch(function (response) {
                                self.onSubmitError(response);
                            }).finally(function (response) {
                                self.submitting = false;
                            });
                        }
                    }
                    else {
                        alert("Aguarde o upload dos arquivos anexados para continuar.");
                    }

                };
            }])
