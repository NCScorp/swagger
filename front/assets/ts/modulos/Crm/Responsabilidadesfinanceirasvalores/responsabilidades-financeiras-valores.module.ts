import angular from "angular";
import { CrmResponsabilidadesfinanceirasvaloresListController } from ".";
import { crmResponsabilidadesfinanceirasvaloresDefault, crmResponsabilidadesfinanceirasvaloresDefaultShow } from "./components";
import { ResponsabilidadesfinanceirasvaloresRoutes } from "./config";
import { CrmResponsabilidadesfinanceirasvaloresDefaultController } from "./default.form";
import { CrmResponsabilidadesfinanceirasvaloresDefaultShowController } from "./default.form.show";
import { CrmResponsabilidadesfinanceirasvaloresFormController } from "./edit";
import { CrmResponsabilidadesfinanceirasvalores } from "./factory";
import { CrmResponsabilidadesfinanceirasvaloresFormNewController } from "./new";
import { CrmResponsabilidadesfinanceirasvaloresFormShowController } from "./show";

export const ResponsabilidadesfinanceirasvaloresModule = angular
    .module('ResponsabilidadesfinanceirasvaloresModule', [])
    .controller('CrmResponsabilidadesfinanceirasvaloresDefaultShowController', CrmResponsabilidadesfinanceirasvaloresDefaultShowController)
    .controller('CrmResponsabilidadesfinanceirasvaloresDefaultController', CrmResponsabilidadesfinanceirasvaloresDefaultController)
    .controller('CrmResponsabilidadesfinanceirasvaloresFormController', CrmResponsabilidadesfinanceirasvaloresFormController)
    .service('CrmResponsabilidadesfinanceirasvalores', CrmResponsabilidadesfinanceirasvalores)
    .controller('CrmResponsabilidadesfinanceirasvaloresListController', CrmResponsabilidadesfinanceirasvaloresListController)
    .controller('CrmResponsabilidadesfinanceirasvaloresFormNewController', CrmResponsabilidadesfinanceirasvaloresFormNewController)
    .controller('CrmResponsabilidadesfinanceirasvaloresFormShowController', CrmResponsabilidadesfinanceirasvaloresFormShowController)
    .component('crmResponsabilidadesfinanceirasvaloresDefault', crmResponsabilidadesfinanceirasvaloresDefault)
    .component('crmResponsabilidadesfinanceirasvaloresDefaultShow', crmResponsabilidadesfinanceirasvaloresDefaultShow)
    .constant('FIELDS_CrmResponsabilidadesfinanceirasvalores', [])
    .constant('OPTIONS_CrmResponsabilidadesfinanceirasvalores', { 'responsavelfinanceiro': 'responsavelfinanceiro', })
    .constant('MAXOCCURS_CrmResponsabilidadesfinanceirasvalores', {})
    .constant('SELECTS_CrmResponsabilidadesfinanceirasvalores', {})
    .run(['$rootScope', 'FIELDS_CrmResponsabilidadesfinanceirasvalores', 'OPTIONS_CrmResponsabilidadesfinanceirasvalores', 'MAXOCCURS_CrmResponsabilidadesfinanceirasvalores', 'SELECTS_CrmResponsabilidadesfinanceirasvalores',
        ($rootScope: any, FIELDS_CrmResponsabilidadesfinanceirasvalores: object, OPTIONS_CrmResponsabilidadesfinanceirasvalores: object, MAXOCCURS_CrmResponsabilidadesfinanceirasvalores: object, SELECTS_CrmResponsabilidadesfinanceirasvalores: object) => {
            $rootScope.OPTIONS_CrmResponsabilidadesfinanceirasvalores = OPTIONS_CrmResponsabilidadesfinanceirasvalores;
            $rootScope.MAXOCCURS_CrmResponsabilidadesfinanceirasvalores = MAXOCCURS_CrmResponsabilidadesfinanceirasvalores;
            $rootScope.SELECTS_CrmResponsabilidadesfinanceirasvalores = SELECTS_CrmResponsabilidadesfinanceirasvalores;
            $rootScope.FIELDS_CrmResponsabilidadesfinanceirasvalores = FIELDS_CrmResponsabilidadesfinanceirasvalores;
        }])
    .config(ResponsabilidadesfinanceirasvaloresRoutes)
    .name