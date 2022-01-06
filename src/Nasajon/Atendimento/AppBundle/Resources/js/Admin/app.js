angular.module('Atendimento', ['ui.router', 'ui.bootstrap', 'filters', 'anexos', 'historicos', 'cliente', 'angularMoment',
    'UsuarioLogado', 'convertToNumber', 'dateInput', 'toggle', 'nsj404', 'angular-auto-save-form', 'ngCkeditor',
    'mdaUiSelect', 'objectList', 'ngclipboard', 'ui.mask', 'ngRedux', 'nsjRedux', 'autocomplete', 'ngDraggable',
    'nsjMenu', 'nsjHeader', 'appLoad', 'mda', 'xeditable', 'treeGrid', 'toaster', 'ngSanitize', 'cfp.hotkeys', 'tableSort', 'dynamic', 'imgbox', 'moment-picker',
    'angularScreenfull', 'ngTagsInput', 'ui.tree','ui.select', 'validacaoCaractere'
])
        .config(['$urlRouterProvider', '$ngReduxProvider', 'rootReducerAdminProvider', '$locationProvider', '$qProvider', function ($urlRouterProvider, $ngReduxProvider, rootReducerAdminProvider, $locationProvider, $qProvider) {

                $locationProvider.html5Mode(true);

                $ngReduxProvider.createStoreWith(rootReducerAdminProvider.$get(), ['promiseMiddleware']);

                $urlRouterProvider.otherwise('/chamados');

                //Se for um usuário sem equipe, não tem acesso a chamados.
                //Redirecionar direto para artigos.
                if (nsj.globals.getInstance().get('usuario')['provisao'].toLowerCase() == 'usuario' &&
                    nsj.globals.getInstance().get('usuario')['equipes'].length == 0) {
                    $urlRouterProvider.otherwise('/artigos');
                }

                $qProvider.errorOnUnhandledRejections(false);
            }])
        .constant('angularMomentConfig', {
            timezone: nsj.globals.getInstance().get('TIMEZONE')
        })
        .factory('AtendimentosAbertos', ['$timeout', '$http', 'nsjRouting', '$rootScope', function ($timeout, $http, nsjRouting, $rootScope) {
                var self = this;
                self.abertos = [];

                self.buscaAbertos = function () {
                    $http
                            .get(nsjRouting.generate('atendimento_admin_solicitacoes_abertos', {}, false))
                            .then(function (response) {
                                self.abertos = response.data;
                                $rootScope.$broadcast("atendimentos_abertos_reload", self.abertos);
                            })
                            .finally(function () {
                                self.letsWatch();
                            });
                };



                self.letsWatch = function () {
                    self.watcher = $timeout(function () {
                        self.buscaAbertos();
                    }, 60000);
                };

                self.stopWatch = function () {
                    if (angular.isDefined(self.watcher)) {
                        $timeout.cancel(self.watcher);
                        self.watcher = undefined;
                    }
                };

                self.buscaAbertos();

                return self;
            }])
        .factory('AgendamentosPendentes', ['$timeout', '$http', 'nsjRouting', '$rootScope', function ($timeout, $http, nsjRouting, $rootScope) {
                var self = this;
                self.count = 0;

                self.buscarPendentes = function () {
                    $http.get(nsjRouting.generate('crm_proximoscontatos_pendentes', {}, false)).then(function (response) {
                        self.count = response.data.pendentes;
                    }).finally(function () {
                        $rootScope.$broadcast("crm_contatospendentes_reload", self.count);
                    });
                };
                
                self.buscarPendentes();

                return self;
            }])
        .factory('PageTitle', ['$window', '$rootScope', function ($window, $rootScope) {
                var originalTitle = $window.document.title;
                $rootScope.$on("$stateChangeStart", function (event, toState, toParams, fromState, fromParams) {
                    if (toState['name'] !== fromState['name']) {
                        $window.document.title = originalTitle;
                    }
                });
                return {
                    reset: function () {
                        $window.document.title = originalTitle;
                    },
                    setTitle: function (newTitle) {
                        $window.document.title = newTitle + " - " + originalTitle;
                    }
                };
            }])
        .factory('UsuarioComEquipe', ['$state', function ($state) {
                var self = {
                    load: function () {
                        var usuarioLogado = nsj.globals.getInstance().get('usuario');

                        if (usuarioLogado.provisao != 'admin' && usuarioLogado.equipes.length == 0) {
                            $state.go('atendimento_admin_artigos');
                        } else {
                            return true;
                        }
                        
                    }
                };

                return self;
            }])
        .factory('Contas', ['$http', 'nsjRouting', '$q', function ($http, nsjRouting, $q) {
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
            }])
        .controller("DashboardCtrl", [function () {
                var self = this;

            }])
        .controller("MenuCtrl", ["AtendimentosAbertos", "AgendamentosPendentes", "$scope", '$window', '$http', 'nsjRouting', function (AtendimentosAbertos, AgendamentosPendentes, $scope, $window, $http, nsjRouting) {
                var self = this;
                self.showButton = false;
                self.gerenciaEquipe = false;
                self.exibe_cliente = nsj.globals.getInstance().get('EXIBE_CLIENTE');

                if (nsj.globals.getInstance().get('usuario')['equipes'].length === 1) {
                    nsj.globals.getInstance().get('usuario')['equipes'].forEach(function (equipe) {
                        self.equipe = equipe.equipe;
                    });
                }

                if (nsj.globals.getInstance().get('usuario')['provisao'].toLowerCase() == 'admin' ||
                        nsj.globals.getInstance().get('usuario')['equipes'].length > 0) { //nsj.globals.getInstance().get('USUARIO_SEM_EQUIPE_PODE_ACESSAR_CHAMADOS_CLIENTES') != 0)
                    self.showButton = true;
                }

                nsj.globals.getInstance().get('usuario')['equipes'].forEach(function (equipe) {
                    if (equipe.usuariotipo === 'A') {
                        self.gerenciaEquipe = true;
                    }
                });

                self.abertos = AtendimentosAbertos.abertos;
                $scope.$on("atendimentos_abertos_reload", function (event, abertos) {
                    self.abertos = abertos;
                });
                
                self.meusContatosPendentes = AgendamentosPendentes.count;
                $scope.$on("crm_contatospendentes_reload", function (event, count) {
                    self.meusContatosPendentes = count;
                });

                self.usuarioDisponivelQuemDetermina = nsj.globals.getInstance().get('USUARIOSDISP_QUEM_DETERMINA');

                self.isAdmin = nsj.globals.getInstance().get('usuario')['provisao'].toLowerCase() == 'admin';

                self.tituloUsuarioDisponivelQuemDetermina = (self.usuarioDisponivelQuemDetermina == 1 || self.isAdmin)
                                                            ? ""
                                                            : "Somente administradores podem alterar a disponibilidade";
 
                self.usuarioDisponivel = !nsj.globals.getInstance().get('usuario')['indisponivel'];

                self.alterarDisponibilidade = function (status) {
                    status = status ? 1 : 0;
                    $http({
                        method: "PUT",
                        url: nsjRouting.generate('atendimento_usuariosdisponibilidades_alterar', false, false),
                        data: {
                            usuario: nsj.globals.getInstance().get('usuario')['email'],
                            status: status
                        }
                    }).then(function (response) {
                        $window.location.reload();
                    }).catch(function (response) {
                        alert(response.data.erro);
                    });
                };
            }])
        .run(run);

run.$inject = ['$rootScope', '$location', '$window', '$transitions'];
function run($rootScope, $location, $window, $transitions) {
    $transitions.onSuccess({}, function () {
        $window.ga('send', 'pageview', $location.url());
        $window.onbeforeunload = undefined;
    });
}
