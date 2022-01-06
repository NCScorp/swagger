angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                $stateProvider.state('home', {
                    url: "/home",
                    templateUrl: nsjRoutingProvider.$get().generate('atendimento_home'),
                    controller: 'DashboardCtrl',
                    controllerAs: 'controller'
                });
            }])

        .controller("DashboardCtrl", ['$scope', 'nsjRouting', '$http', 'UsuarioLogado', 'PageTitle', '$sce', '$uibModal',
            function ($scope, nsjRouting, $http, UsuarioLogado, PageTitle, $sce, $uibModal) {

                var self = this;
                self.busy = true;
                self.UsuarioLogado = UsuarioLogado;
                // Seta o título e a descrição pelo globals
                self.titulo = nsj.globals.getInstance().get('PORTAL_TITULO');
                self.descricao = nsj.globals.getInstance().get('PORTAL_DESCRICAO');
                self.placeholderBuscaArtigos = nsj.globals.getInstance().get('PLACEHOLDER_BUSCA_ARTIGOS_CLIENTE');
                self.botaoBaseConhecimento = nsj.globals.getInstance().get('TEXTO_BOTAO_BASE_CONHECIMENTO_CLIENTE');
                
                self.dynamicPopover = {
                    templateUrl: nsjRouting.generate('atendimento_cliente_solicitacoes_template_widget'),
                    title: 'Solicitar Atendimento'
                };

                self.isBusy = function () {
                    return self.busy;
                };
                self.updateTitle = function () {
                    PageTitle.reset();
                };
                self.updateTitle();

                
                $http.get(nsjRouting.generate('atendimento_cliente_solicitacoes_dashboard', {}, false)).then(function (response) {
                    self.chamados = response.data.chamados.map(function (chamado) {
                        chamado.atendimento_resumo = $sce.trustAsHtml(chamado.atendimento_resumo);
                        chamado.ultima_resposta_resumo = $sce.trustAsHtml(chamado.ultima_resposta_resumo);
                        return chamado;
                    });
                });
                
                $http.get(nsjRouting.generate('atendimento_cliente_categorias_index', { "status": 1, 'tipo': 1 } , false)).then(function (response) {
                    self.categorias = response.data;
                     self.busy = false;
                });

                self.open = function (size, parentSelector) {
                    var modalInstance = $uibModal.open({
                        templateUrl: nsjRouting.generate('template_vinculo_modal', {}, false),
                        controller: 'ModalInstanceCtrl',
                        controllerAs: 'self',
                        windowClass: 'modal-form',
                        size: size,
                        backdrop: true,
                        resolve: {}
                    });
                    
                    modalInstance.result.then(function (formData) {
                        self.modalFormData = formData;
                    });
                };

            }]);