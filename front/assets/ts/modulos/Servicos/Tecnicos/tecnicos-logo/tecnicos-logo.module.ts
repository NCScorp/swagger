import angular = require("angular");
import { TecnicosLogoEditController } from "./edit/tecnicos-logo-edit.controller";
import { TecnicosLogoFormController } from "./form/tecnicos-logo-form.controller";
import { TecnicosLogoIndexController } from "./index/tecnicos-logo-index.controller";
import { TecnicosLogoModalController } from "./modal/tecnicos-logo-modal .controller";
import { TecnicosLogoModalService } from "./modal/tecnicos-logo-modal.service";
import { TecnicosLogoNewController } from "./new/tecnicos-logo-new.controller";
import { TecnicosLogoRouting } from "./tecnicos-logo.routes";
import { TecnicosLogoService } from "./tecnicos-logo.service";

export const TecnicosLogoModule = angular.module('tecnicosLogoModule', ['ui.router.state'])
    .controller('tecnicosLogoFormController', TecnicosLogoFormController)
    .service('tecnicosLogoModalService', TecnicosLogoModalService)
    .service('tecnicosLogoService', TecnicosLogoService)
    .controller('tecnicosLogoModalController', TecnicosLogoModalController)
    .controller('tecnicosLogoNewController', TecnicosLogoNewController)
    .controller('tecnicosLogoEditController',TecnicosLogoEditController)
    .controller('tecnicosLogoIndexController', TecnicosLogoIndexController)
    .constant('FIELDS_TecnicosTecnicoslogos', [
    ])
    .constant('OPTIONS_TecnicosTecnicoslogos', { 'ativo': 'Ativo', })
    .constant('MAXOCCURS_TecnicosTecnicoslogos', { 'ativo': 1, })
    .constant('SELECTS_TecnicosTecnicoslogos', {})
    .run(['$rootScope', 'FIELDS_TecnicosTecnicoslogos', 'OPTIONS_TecnicosTecnicoslogos', 'MAXOCCURS_TecnicosTecnicoslogos', 'SELECTS_TecnicosTecnicoslogos',
        ($rootScope: any, FIELDS_TecnicosTecnicoslogos: object, OPTIONS_TecnicosTecnicoslogos: object, MAXOCCURS_TecnicosTecnicoslogos: object, SELECTS_TecnicosTecnicoslogos: object) => {
            $rootScope.OPTIONS_TecnicosTecnicoslogos = OPTIONS_TecnicosTecnicoslogos;
            $rootScope.MAXOCCURS_TecnicosTecnicoslogos = MAXOCCURS_TecnicosTecnicoslogos;
            $rootScope.SELECTS_TecnicosTecnicoslogos = SELECTS_TecnicosTecnicoslogos;
            $rootScope.FIELDS_TecnicosTecnicoslogos = FIELDS_TecnicosTecnicoslogos;
        }])
    .config(TecnicosLogoRouting)
    .name