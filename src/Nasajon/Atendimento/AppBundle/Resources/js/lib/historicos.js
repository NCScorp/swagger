angular.module('historicos', ['nsjRouting'])
        .directive('historicos', ['nsjRouting', function (nsjRouting) {
                return {
                    restrict: 'E',
                    templateUrl: nsjRouting.generate('template_historicos', {}, false),
                    scope: {
                        atendimento: '=atendimento',
                        id: '=id'
                    },
                    controller: ['$scope', '$http', function ($scope, $http) {

                            if ($scope.atendimento.entity.canal === 'E-mail') {
                                $scope.atendimento.entity.historico = $scope.atendimento.entity.historico.map(function (array) {
                                    array.canal_email = true;
                                    return array;
                                });
                            }
                            
                            function verificarHistoricosCriadosPorArtigos() {
                                $scope.atendimento.entity.followups.forEach(function (followup) {
                                    $scope.atendimento.entity.historico = $scope.atendimento.entity.historico.map(function (historico) {
                                        if (historico.tipo === 6 && historico.valornovo.followup === followup.followup) {
                                            historico.criado_por_artigo = followup.artigo !== undefined && followup.artigo !== null ? true : false;                                        
                                        }

                                        return historico;
                                    });
                                });
                            }


                            $scope.filterHistoricoCamposcustomizados = function (campo) {
                                return campo['valor_antigo'] != campo['valor_novo'];
                            };

                            if (!angular.isObject($scope.atendimento)) {
                                $scope.loading = true;
                                $http
                                        .get(nsjRouting.generate('atendimento_admin_solicitacoes_get', {'id': $scope.id}, false))
                                        .then(function (response) {
                                            $scope.atendimento = response.data;

                                        })
                                        .catch(function (response) {
                                        })
                                        .finally(function () {
                                            $scope.loading = false;
                                        });
                            }
                            
                            verificarHistoricosCriadosPorArtigos();
                            
                            $scope.$on("atendimento_admin_solicitacoes_loaded", function () {
                                verificarHistoricosCriadosPorArtigos();
                            });
                        }
                    ]
                };
            }]);
        