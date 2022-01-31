import angular from "angular";
import { CrmAtcsareasListController } from ".";
import { CrmAtcsareasDefault, CrmAtcsareasDefaultShow } from "./components";
import { atcsAreasRoutes } from "./config";
import { CrmAtcsareasDefaultController } from "./default.form";
import { CrmAtcsareasDefaultShowController } from "./default.form.show";
import { CrmAtcsareasFormController } from "./edit";
import { CrmAtcsareas } from "./factory";
import { CrmAtcsareasFormNewController } from "./new";
import { CrmAtcsareasFormShowController } from "./show";
import { ModalExclusaoAtcsAreasService, ModalExclusaoAtcsAreasController } from "./modal-exclusao";

export const atcsAreasModule = angular.module('atcsAreasModule', [])
    .component('crmAtcsareasDefaultShow', CrmAtcsareasDefaultShow)
    .component('crmAtcsareasDefault', CrmAtcsareasDefault)
    .controller('CrmAtcsareasDefaultController', CrmAtcsareasDefaultController)
    .controller('CrmAtcsareasFormController', CrmAtcsareasFormController)
    .service('CrmAtcsareas', CrmAtcsareas)
    .controller('CrmAtcsareasListController', CrmAtcsareasListController)
    .controller('CrmAtcsareasFormNewController', CrmAtcsareasFormNewController)
    .controller('CrmAtcsareasFormShowController', CrmAtcsareasFormShowController)
    .controller('CrmAtcsareasDefaultShowController', CrmAtcsareasDefaultShowController)
    .service('modalExclusaoAtcsAreasService', ModalExclusaoAtcsAreasService)
    .controller('ModalExclusaoAtcsAreasController', ModalExclusaoAtcsAreasController)
    .constant('FIELDS_CrmAtcsareas', [
        {
            value: 'nome',
            label: 'Nome',
            type: 'string',
            style: 'title',
            copy: '',
        },

    ])
    .run(['$rootScope', 'FIELDS_CrmAtcsareas',
        ($rootScope: any, FIELDS_CrmAtcsareas: object) => {
            $rootScope.FIELDS_CrmAtcsareas = FIELDS_CrmAtcsareas;
        }])
    .config(atcsAreasRoutes)
    .name