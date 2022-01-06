angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                var resolve = {
                    'entity': ['AtendimentoClienteArquivos', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['arquivo']) {
                                    return entityService.get($stateParams['arquivo']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };
            }])
        .controller('AtendimentoClienteArquivosListController', [
            '$rootScope', '$scope', '$state', '$http', '$stateParams', '$uibModal', 'nsjRouting', 'AtendimentoClienteArquivos', function ($rootScope, $scope, $state, $http, $stateParams, $uibModal, nsjRouting, AtendimentoClienteArquivos) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.downloadsDescricao = nsj.globals.getInstance().get('DOWNLOADS_DESCRICAO');
                self.filter = AtendimentoClienteArquivos.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = AtendimentoClienteArquivos.search;
                self.reload = AtendimentoClienteArquivos.reload;
                self.nextPage = AtendimentoClienteArquivos.load;
                self.loadMore = AtendimentoClienteArquivos.loadMore;
                self.service = AtendimentoClienteArquivos;
                self.constructors = AtendimentoClienteArquivos.constructors = [];
                self.modalInstance = null;
                self.arquivoAberto = {};

                AtendimentoClienteArquivos.load = function() {
                    if (!AtendimentoClienteArquivos.busy && !AtendimentoClienteArquivos.finished && AtendimentoClienteArquivos.to_load > 0 ) {
                        AtendimentoClienteArquivos.busy = true;
                        var success = true;

                        AtendimentoClienteArquivos._load(AtendimentoClienteArquivos.constructors, AtendimentoClienteArquivos.after, AtendimentoClienteArquivos.filter)
                        .then(function (data) {
                            if (data.length > 0) {
                                for (var i = 0; i < data.length; i++) {
                                    AtendimentoClienteArquivos.entities.push(data[i]);
                                }

                                AtendimentoClienteArquivos.after = AtendimentoClienteArquivos.entities[AtendimentoClienteArquivos.entities.length - 1]['ordem'];
                            }
                            
                            if(data.length < 20){
                                AtendimentoClienteArquivos.finished = true;
                                $rootScope.$broadcast("atendimento_cliente_arquivos_list_finished", AtendimentoClienteArquivos.entities);
                            }
                            
                            AtendimentoClienteArquivos.loaded = true;
                            AtendimentoClienteArquivos.to_load--;
        
                        })
                        .catch(function(response) {
                            if (response.status == 403) {
                                success = false;
                            }
                        })
                        .finally(function(){
                            AtendimentoClienteArquivos.busy = false;

                            if (success) {
                                AtendimentoClienteArquivos.load();
                            }
                        });
                    }
        
                    return AtendimentoClienteArquivos.entities;
                };

                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        AtendimentoClienteArquivos.constructors[i] = $stateParams[i];
                    }
                }

                self.entities = AtendimentoClienteArquivos.reload();

                self.openmodal = function (entidade) {
                    self.arquivoAberto = entidade;
                    self.modalInstance = $uibModal.open({
                        animation: false,
                        templateUrl: nsjRouting.generate('template_arquivos_modal', {}, false),
                        scope: $scope
                    });
                };

                self.fecharModal = function () {
                    self.conteudo = null;
                    self.modalInstance.close();
                }


                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return AtendimentoClienteArquivos.busy;
                };

                $scope.$on("atendimento_cliente_arquivos_deleted", function (event) {
                    self.reload();
                });
            }]);