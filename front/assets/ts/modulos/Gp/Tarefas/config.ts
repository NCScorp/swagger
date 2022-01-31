export const TarefasRoutes = ['$stateProvider', ($stateProvider: angular.ui.IStateProvider) => {

    let resolve = {
        'entity': ['GpTarefas', '$stateParams', '$state', (entityService: any, $stateParams: angular.ui.IStateParamsService, $state: angular.ui.IStateService) => {
            if ($stateParams.entity) {
                return $stateParams.entity;
            } else {
                if ($stateParams.tarefa) {
                    return new Promise((resolve: any, reject: any) => {
                        entityService.get($stateParams.tarefa)
                            .then((data: any) => {
                                resolve(data);
                            })
                            .catch((error: any) => {
                                if (error.status === 404) {
                                    if ($state.href('gp_tarefas_not_found', $stateParams)) {
                                        $state.go('gp_tarefas_not_found', $stateParams);
                                    } else {
                                        $state.go('not_found', $stateParams);
                                    }
                                }
                            });
                    });
                } else {
                    return {};
                }
            }
        }]
    };
    let resolveFilter = {
    };
    $stateProvider
        .state('gp_tarefas', {
            url: '/gp/tarefas?q?situacao',
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'Gp\TarefasListController',
            controllerAs: 'gp_trfs_lst_cntrllr',
            reloadOnSearch: false
        })
        ;
}]