
import * as angular from 'angular';

import { TiposServicosService } from './tipos-servicos.service';

import { TiposServicosRouting } from './tipos-servicos.routes';

import { TiposServicosEditController } from './edit/tipos-servicos-edit.controller';
import { TiposServicosIndexController } from './index/tipos-servicos-index.controller';
import { TiposServicosFormController } from './form/tipos-servicos-form.controller';
import { TiposServicosShowFormController } from './showform/tipos-servicos-show-form.controller';
import { TiposServicosShowController } from './show/tipos-servicos-show.controller';
import { TiposServicosNewController } from './new/tipos-servicos-new.controller';

import { TiposServicosFormComponent } from './form/tipos-servicos-form.component';
import { TiposServicosShowFormComponent } from './showform/tipos-servicos-show-form.component';

export const TiposServicosModule = angular.module('tiposServicosModule', ['ui.router.state'])
    .controller('tiposServicosEditController', TiposServicosEditController)
    .controller('tiposServicosIndexController', TiposServicosIndexController)
    .controller('tiposServicosFormController', TiposServicosFormController)
    .controller('tiposServicosShowFormController', TiposServicosShowFormController)
    .controller('tiposServicosShowController', TiposServicosShowController)
    .controller('tiposServicosNewController', TiposServicosNewController)
    .component(TiposServicosFormComponent.selector, TiposServicosFormComponent)
    .component(TiposServicosShowFormComponent.selector, TiposServicosShowFormComponent)
    .service('tiposServicosService', TiposServicosService)
    .config(TiposServicosRouting)
    .constant('FIELDS_Tiposservicos', [
        {
            value: 'codigo',
            label: 'codigo',
            type: 'string',
            style: 'title',
            copy: '',
        },
        {
            value: 'descricao',
            label: 'Nome',
            type: 'string',
            style: 'default',
            copy: '',
        },
    ])
    .run(['$rootScope', 'FIELDS_Tiposservicos', ($rootScope: any, FIELDS_Tiposservicos: object) => {
        $rootScope.FIELDS_Tiposservicos = FIELDS_Tiposservicos;
    }]).name;
