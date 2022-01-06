angular.module('cliente', ['nsjRouting'])
        .directive('cliente', ['nsjRouting', function (nsjRouting) {
                return {
                    restrict: 'E',
                    templateUrl: nsjRouting.generate('template_cliente', {}, false),
                    scope: {controller: '=controller', id: '=id'},
                    controller: ['$scope', '$q', '$http', function ($scope, $q, $http) {
                            
                            function getCliente(id) {
                                return $q(function (resolve, reject) {
                                    $http({
                                        method: 'GET',
                                        url: nsjRouting.generate('ns_clientes_get', {'id': id}, false)
                                    }).then(function (response) {
                                        resolve(response.data);
                                    }).catch(function (response) {
                                        reject(response);
                                    });
                                });
                            }
                            
                            $scope.loadCliente = function (cliente) {                                
                                if (cliente.cliente && !$scope.controller.loadingCliente && (!$scope.controller.cliente.cliente || ($scope.controller.cliente.cliente !== cliente.cliente))) {
                                    $scope.loadingCliente = true;
                                    
                                    getCliente(cliente.cliente).then(function (response) {
                                        $scope.controller.cliente = response;
                                    });
                                }
                            };
                            
                            if ($scope.controller.entity.cliente != undefined || $scope.controller.entity.cliente != null) {
                                $scope.loadCliente($scope.controller.entity.cliente);
                            }
                        }
                    ]
                };
            }]);