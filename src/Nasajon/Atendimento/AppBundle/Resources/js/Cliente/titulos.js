angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['AtendimentoClienteTitulos', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['id']) {
                                    return entityService.get($stateParams['id']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };
                
            }])
        .controller('AtendimentoClienteTitulosListController', ['UsuarioLogado', '$rootScope', '$scope', '$state', '$http', '$stateParams', 'nsjRouting', 'AtendimentoClienteTitulos', 'PageTitle',
            function (UsuarioLogado, $rootScope, $scope, $state, $http, $stateParams, nsjRouting, AtendimentoClienteTitulos, PageTitle) {
                var self = this;
                self.after = '';
                self.busy = false;
                self.finished = false;
                self.titulosDescricao = nsj.globals.getInstance().get('TITULOS_DESCRICAO');
                self.filter = AtendimentoClienteTitulos.filter;
                self.filter['key'] = $stateParams['q'] ? $stateParams['q'] : '';
                self.filter['filterfield'] = 'all';
                self.search = AtendimentoClienteTitulos.search;
                self.reload = AtendimentoClienteTitulos.reload;
                self.nextPage = AtendimentoClienteTitulos.load;
                self.loadMore = AtendimentoClienteTitulos.loadMore;
                self.service = AtendimentoClienteTitulos;
                self.constructors = AtendimentoClienteTitulos.constructors = [];
                self.usuarioLogado = UsuarioLogado;
                var clientes = UsuarioLogado.getClientes();
                self.clientes = [];
                self.exibirBoleto = nsj.globals.getInstance().get('TITULOS_EXIBIR_PARA_CLIENTE_BOLETO') == 1 ? true : false;
                self.exibirNotaFiscal = nsj.globals.getInstance().get('TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL') == 1 ? true : false;


                for (var i in $stateParams) {
                    if (typeof $stateParams[i] != 'undefined' && typeof $stateParams[i] != 'function' && i != 'q') {
                        AtendimentoClienteTitulos.constructors[i] = $stateParams[i];
                    }
                }

                clientes.map(function (c) {
                    if (!c.cliente_bloqueado && c.funcao == 'A') {
                        self.clientes.push({
                            'value': c['cliente_id'],
                            'label': c['cliente_nome']
                        });
                    }
                });

                self.clienteSelecionado = self.constructors.cliente ? {'value': self.constructors.cliente} : angular.copy(self.clientes).pop();

                self.updateTitle = function () {
                    PageTitle.setTitle('Boletos e Faturas');
                };
                self.updateTitle();

                self.entities = AtendimentoClienteTitulos.reload();


                self.generateRoute = function (route, params) {
                    return nsjRouting.generate(route, params);
                };

                self.isBusy = function () {
                    return AtendimentoClienteTitulos.busy;
                };

                self.changeCliente = function (cliente) {
                    $state.go('atendimento_cliente_titulos', {cliente: cliente});
                };
                
                self.copiarLinhaDigitavel = function () {
                    var clipboard = new Clipboard('#btnLinhaDigitavel');
                    clipboard.on('success', function (e) {
                        e.clearSelection();
                    });

                    clipboard.on('error', function (e) {
                        alert('Não foi possível executar a cópia da linha digitável. Por favor, tente novamente.');
                    });
                };
            }]);
