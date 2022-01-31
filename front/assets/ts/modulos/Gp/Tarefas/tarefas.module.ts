import angular from "angular";
import { GpTarefasListController } from ".";
import { TarefasRoutes } from "./config";
import { FIELDS_GpTarefas } from "./constant";
import { GpTarefas } from "./factory";

export const TarefasModule = angular
    .module('TarefasModule', [])
    .constant('FIELDS_GpTarefas', FIELDS_GpTarefas)
    .service('GpTarefas', GpTarefas)
    .controller('GpTarefasListController', GpTarefasListController)
    .run(['$rootScope', 'FIELDS_GpTarefas',
        ($rootScope: any, FIELDS_GpTarefas: object) => {
            $rootScope.FIELDS_GpTarefas = FIELDS_GpTarefas;
        }])
    .config(TarefasRoutes)
    .name