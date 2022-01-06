angular.module('mda')
        .config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {

                var resolve = {
                    'entity': ['ServicosOrdensServicoOperacao', '$stateParams', function (entityService, $stateParams) {
                            if ($stateParams['entity']) {
                                return $stateParams['entity'];
                            } else {
                                if ($stateParams['operacao']) {
                                    return entityService.get($stateParams['operacao']);
                                } else {
                                    return {};
                                }
                            }
                        }]
                };

                $stateProvider
                        .state('servicos_ordensservico_operacao', {
                            url: "/operacao",
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_ordensservico_operacao_template'),
                            controller: 'Servicos\OrdensServico\OperacaoListController',
                            controllerAs: 'srvcs_rdns_srvc_prc_lst_cntrllr'
                        })

                        .state('servicos_ordensservico_operacao_new', {
                            parent: 'servicos_ordensservico_operacao',
                            url: "/new",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_ordensservico_operacao_template_form'),
                            controllerAs: 'srvcs_rdns_srvc_prc_frm_cntrllr',
                            controller: 'ServicosOrdensServicoOperacaoFormController'
                        })
                        .state('servicos_ordensservico_operacao_edit', {
                            parent: 'servicos_ordensservico_operacao',
                            url: "/:operacao/edit",
                            resolve: resolve,
                            templateUrl: nsjRoutingProvider.$get().generate('servicos_ordensservico_operacao_template_form'),
                            controller: 'Servicos\OrdensServico\OperacaoFormController',
                            controllerAs: 'srvcs_rdns_srvc_prc_frm_cntrllr'
                        });
            }
        ]);