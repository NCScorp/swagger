angular
        .module('Atendimento', [
          'infinite-scroll', 'ngMessages',
          'ui.router', 'ui.bootstrap', 'nsjRouting', 'filters', 'mgo-angular-wizard', 'angularMoment', 'tableSort',
          'UsuarioLogado', 'convertToNumber', 'dateInput', 'toggle', 'nsj404', 'ngCkeditor', 'mdaUiSelect',
          'ngRedux', 'nsjRedux', 'nsjMenu', 'nsjHeader', 'appLoad', 'ui.mask', 'toaster', 'anexos', 'mda',
          'ngDraggable', 'ngSanitize', 'ui.select', 'ngAnimate', 'dynamic', 'imgbox'
        ])
        .config(['$urlRouterProvider', '$ngReduxProvider', 'rootReducerClienteProvider', '$locationProvider', function ($urlRouterProvider, $ngReduxProvider, rootReducerClienteProvider, $locationProvider) {

            $locationProvider.html5Mode(true);

            $ngReduxProvider.createStoreWith(rootReducerClienteProvider.$get(), ['promiseMiddleware']);

            $urlRouterProvider.otherwise('/home');
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
        .constant('angularMomentConfig', {
          timezone: nsj.globals.getInstance().get('TIMEZONE')
        })
        .controller("MenuCtrl", ['UsuarioLogado', 'nsjRouting', function (UsuarioLogado, nsjRouting) {
            var self = this;
            self.clientes = UsuarioLogado.getClientes();
            self.podeCriarChamado = UsuarioLogado.podeCriarChamado;
            self.usuarioAdministrador = false;
            self.isAnonimous = UsuarioLogado.isAnonimous;
            self.possuiClienteAtivo = false;
            for (var i in nsj.globals.getInstance().get('usuario').clientes) {
              if (!nsj.globals.getInstance().get('usuario').clientes[i].cliente_bloqueado) {
                self.possuiClienteAtivo = true;
              }
            }

            self.exibirNotaFiscal = nsj.globals.getInstance().get('TITULOS_EXIBIR_PARA_CLIENTE_NOTA_FISCAL') == '1' ? true : false;
            self.exibirBoleto = nsj.globals.getInstance().get('TITULOS_EXIBIR_PARA_CLIENTE_BOLETO') == '1' ? true : false;

            self.disponibilizarArquivos = nsj.globals.getInstance().get('DISPONIBILIZAR_ARQUIVOS') == '1' ? true : false;
            
            
            for (var i in self.clientes) {
              if (self.clientes[i].funcao == 'A') {
                self.clienteAdmin = self.clientes[i];
                self.usuarioAdministrador = true;
                break;
              }
            }

            self.verificaProfile = function(e) {
              if(self.isAnonimous) {
                e.preventDefault();
				        window.location = nsjRouting.generate('lightsaml_sp.login', {}, false);
              }

              return;
            }
          }
        ])
        .run(run);

run.$inject = ['$location', '$window', '$transitions', 'PageTitle', '$uibModal', 'nsjRouting', '$http', '$rootScope', 'toaster'];
function run($location, $window, $transitions, PageTitle, $uibModal, nsjRouting, $http, $rootScope, toaster) {
  $transitions.onSuccess({}, function (a) {
    if (a._targetState._identifier == 'atendimento_cliente_arquivos') {
      PageTitle.setTitle('Downloads');
    }
    
    if (nsj.globals.getInstance().get('TERMO_HABILITADO') == '1' && nsj.globals.getInstance().get('usuario')['TERMO_ACEITO'] == 0) {
      $http.get(nsjRouting.generate('atendimento_cliente_termos_texto', {}, false)).then(function (response) {
          $rootScope.termo_texto = response.data;
          $rootScope.aceitarTermo = function(){
            $http.post(nsjRouting.generate('atendimento_cliente_termos_aceitar', {}, false)).then(function (response) {
              var nglobal = nsj.globals.getInstance().a;
              nglobal.usuario.TERMO_ACEITO = 1;
              nsj.globals.set(nglobal);
              $rootScope.modalInstance.close();
              toaster.pop({
                  type: 'success',
                  title: 'Termo aceito'
              });
              
            },function(response){
              toaster.pop({
                  type: 'error',
                  title: 'Falha ao aceitar termo'
              });
            });
          };
          $rootScope.modalInstance = $uibModal.open({
          templateUrl: nsjRouting.generate('template_termo_modal', {}, false),
          scope: $rootScope,
          size: 'lg',
          windowClass: 'modalTermo',
          keyboard: false,
          backdrop: 'static'
        });
      },function(){
          toaster.pop({
              type: 'error',
              title: 'Falha ao carregar termo'
          });
      })
      ;

    }

    $window.ga('send', 'pageview', $location.url());
  });
  // Envia tempo de carregamento da página para o Google Analytics
  if (window.performance) {
    ga('send', 'timing', 'JS Dependencies', 'load', Math.round(performance.now()));
  }
}
