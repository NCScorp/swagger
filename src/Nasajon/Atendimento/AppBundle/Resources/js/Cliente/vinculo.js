angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
                $stateProvider
                        .state('atendimento_cliente_vinculo', {
                            url: "/vinculo",
                            templateUrl: nsjRoutingProvider.$get().generate('template_vinculo_modal'),
                            controller: 'ModalInstanceCtrl',
                            controllerAs: 'self'
                        });
            }])
        .controller('ModalInstanceCtrl', [
            '$scope', '$uibModalInstance', '$http', 'nsjRouting',
            function ($scope, $uibModalInstance, $http, nsjRouting) {

                var self = this;
                self.cnpj = "";
                self.situacao = -1;

                $scope.$watch(self.cnpj, function () {
                    self.changemask();
                });

                self.changemask = function () {
                    self.cnpjMask = (self.cnpj.length === 11) ? '999.999.999-99?9' : '99.999.999/9999-99';
                };

                self.ok = function () {
                    if (self.form.$valid) {

                        self.situacao = 0;

                        $http({
                            method: "POST",
                            url: nsjRouting.generate('atendimento_cliente_vinculo', {}, false),
                            data: {
                                'cnpj': self.cnpj
                            },
                            transformRequest: function (obj) {
                                var str = [];
                                for (var p in obj)
                                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                                return str.join("&");
                            },
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            }
                        }).then(function (response) {
                            self.emails = response.data['emails'];
                            self.situacao = 1;
                        }).catch(function (response) {

                            if (response.data['error']) {
                                self.error = response.data['error'];
                                self.situacao = 2;
                            } else {
                                self.error = 'Não foi possível identificar a causa do problema. Por favor, entre em contato com o administrador.';
                                self.situacao = 2;
                            }
                        });
                    }
                };

                self.close = function () {
                    $uibModalInstance.dismiss('cancel');
                };
            }]);