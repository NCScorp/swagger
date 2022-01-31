import angular = require('angular');

export const NegociosRoutes = ['$stateProvider', 'nsjRoutingProvider', '$stateRegistryProvider', function ($stateProvider, nsjRoutingProvider, $stateRegistryProvider) {

    var resolveFilter = {
        'entity': ['CrmNegocios', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.documento) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.documento).then((negocio) => {
                            resolve(negocio);
                        }).catch((err) => {
                            $state.go('not_found', $stateParams);
                        });
                    });
                } else {
                    return null;
                }
            }
        }],
        'clientecaptadorFilter': ['NsVendedores', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.clientecaptador) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.clientecaptador)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            } else {
                return {};
            }
        }],
        'vendedorFilter': ['NsVendedores', '$stateParams', (entityService: any, $stateParams: angular.ui.IStateParamsService) => {
            entityService.after = {};
            entityService.filters = {};
            if ($stateParams.vendedor) {
                return new Promise((resolve: any, reject: any) => {
                    entityService.get($stateParams.vendedor)
                        .then((data: any) => {
                            resolve(data);
                        })
                        .catch((error: any) => {
                            resolve({});
                        });
                });
            } else {
                return {};
            }
        }]
    };
}]