
import * as angular from 'angular';

import { TecnicosService } from './tecnicos.service';
import { TecnicosPersonalizadoService } from './tecnicos-personalizado.service';

import { TecnicosRouting } from './tecnicos.routes';

import { TecnicosIndexController } from './index/tecnicos-index.controller'
import { TecnicosNewController } from './new/tecnicos-new.controller'
import { TecnicosShowController } from './show/tecnicos-show.controller'
import { TecnicosEditController } from './edit/tecnicos-edit.controller'
import { TecnicosShowFormController } from './showform/tecnicos-show-form.controller'
import { TecnicosFormController } from './form/tecnicos-form.controller'

import { TecnicosShowFormComponent } from './showform/tecnicos-show-form.component'
import { TecnicosFormComponent } from './form/tecnicos-form.component'

export const TecnicosModule = angular.module('tecnicosModule', ['ui.router.state'])
    .controller('tecnicosIndexController', TecnicosIndexController)
    .controller('tecnicosNewController',TecnicosNewController)
    .controller('tecnicosShowController',TecnicosShowController)
    .controller('tecnicosEditController',TecnicosEditController)
    .controller('tecnicosShowFormController',TecnicosShowFormController)
    .controller('tecnicosFormController',TecnicosFormController)
    .component(TecnicosShowFormComponent.selector, TecnicosShowFormComponent)
    .component(TecnicosFormComponent.selector, TecnicosFormComponent)
    .service('tecnicosPersonalizadoService', TecnicosPersonalizadoService)
    .service('tecnicosService', TecnicosService)
    .config(TecnicosRouting)
    .constant('FIELDS_Tecnicos', [
        {
            value: 'nome',
            label: 'Nome',
            type: 'string',
            style: 'title',
            copy: '',
        },
        {
            value: 'cpf',
            label: 'CPF',
            type: 'string',
            style: 'default',
            copy: '',
        },
    ])
    .run(['$rootScope', 'FIELDS_Tecnicos',
        ($rootScope: any, FIELDS_Tecnicos: object) => {
            $rootScope.FIELDS_Tecnicos = FIELDS_Tecnicos;
    }]).name;
