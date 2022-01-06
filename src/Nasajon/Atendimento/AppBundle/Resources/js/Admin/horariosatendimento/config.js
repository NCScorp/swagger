angular.module('mda').config(['$stateProvider', 'nsjRoutingProvider', function ($stateProvider, nsjRoutingProvider) {
    var resolve = {
        'entity': ['$q', '$http', 'nsjRouting', function ($q, $http, nsjRouting) {
                return $q(function (resolve, reject) {
                    $http({
                        method: 'GET',
                        url: nsjRouting.generate('atendimento_horariosatendimento_obter_horario_global', {}, true)
                    }).then(function (response) {
                        resolve(response.data);
                    }).catch(function (response) {
                        reject(response);
                    });
                });
            }]
    };

    $stateProvider.state('atendimento_horariosatendimento', {
        url: "/horariosatendimento",
        parent: 'atendimento_admin_configuracoes',
        templateUrl: nsjRoutingProvider.$get().generate('atendimento_horariosatendimento_template_form'),
        controllerAs: 'tndmnt_hrrstndmnt_frm_cntrllr',
        controller: 'AtendimentoHorariosatendimentoFormController',
        resolve: resolve
    });
}]);