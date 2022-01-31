import angular from "angular";
import { DashboardatendimentospendenciasController } from "./index";

export const DashboardatendimentospendenciasModule = angular
.module('DashboardatendimentospendenciasModule',[])
.controller('DashboardatendimentospendenciasController', DashboardatendimentospendenciasController)
.config(['$stateProvider', function ($stateProvider) {
    var resolveFilter = {};
    $stateProvider
        .state('crm_dashboard_atendimentos_pendencias', {
            url: "/crm/dashboard-atendimentos-pendencias?",
            resolve: resolveFilter,
            template: require('./index.html'),
            controller: 'DashboardatendimentospendenciasController',
            controllerAs: 'crm_dash_atd_pdca_cntrllr',
            reloadOnSearch: false
        })
}]).name;