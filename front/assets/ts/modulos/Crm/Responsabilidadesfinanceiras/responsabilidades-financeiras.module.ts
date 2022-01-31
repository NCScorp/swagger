import angular from "angular";
import { CrmResponsabilidadesfinanceirasListController } from ".";
import { crmResponsabilidadesfinanceirasDefaultShow, crmResponsabilidadesfinanceirasDefault } from "./components";
import { ResponsabilidadesfinanceirasRoutes } from "./config";
import { CrmResponsabilidadesfinanceirasDefaultController } from "./default.form";
import { CrmResponsabilidadesfinanceirasDefaultShowController } from "./default.form.show";
import { CrmResponsabilidadesfinanceirasFormController } from "./edit";
import { CrmResponsabilidadesfinanceiras } from "./factory";
import { CrmResponsabilidadesfinanceirasFormNewController } from "./new";
import { CrmResponsabilidadesfinanceirasFormShowController } from "./show";

export const ResponsabilidadesfinanceirasModule = angular
    .module('ResponsabilidadesfinanceirasModule', [])
    .controller('CrmResponsabilidadesfinanceirasDefaultShowController', CrmResponsabilidadesfinanceirasDefaultShowController)
    .controller('CrmResponsabilidadesfinanceirasDefaultController', CrmResponsabilidadesfinanceirasDefaultController)
    .controller('CrmResponsabilidadesfinanceirasFormController', CrmResponsabilidadesfinanceirasFormController)
    .service('CrmResponsabilidadesfinanceiras', CrmResponsabilidadesfinanceiras)
    .controller('CrmResponsabilidadesfinanceirasListController', CrmResponsabilidadesfinanceirasListController)
    .controller('CrmResponsabilidadesfinanceirasFormNewController', CrmResponsabilidadesfinanceirasFormNewController)
    .controller('CrmResponsabilidadesfinanceirasFormShowController', CrmResponsabilidadesfinanceirasFormShowController)
    .component('crmResponsabilidadesfinanceirasDefaultShow', crmResponsabilidadesfinanceirasDefaultShow)
    .component('crmResponsabilidadesfinanceirasDefault', crmResponsabilidadesfinanceirasDefault)
    .constant('FIELDS_CrmResponsabilidadesfinanceiras', [])
    .run(['$rootScope', 'FIELDS_CrmResponsabilidadesfinanceiras',
        ($rootScope: any, FIELDS_CrmResponsabilidadesfinanceiras: object) => {
            $rootScope.FIELDS_CrmResponsabilidadesfinanceiras = FIELDS_CrmResponsabilidadesfinanceiras;
        }])
    .config(ResponsabilidadesfinanceirasRoutes)
    .name