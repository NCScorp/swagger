
import * as angular from 'angular';

import { TiposOrdensServicosService } from './tipos-ordens-servicos.service';

import { TiposOrdensServicosRouting } from './tipos-ordens-servicos.routes';

import { TiposOrdensServicosIndexController } from './index/tipos-ordens-servicos-index.controller';
import { TiposOrdensServicosEditController } from './edit/tipos-ordens-servicos-edit.controller';
import { TiposOrdensServicosNewController } from './new/tipos-ordens-servicos-new.controller';
import { TiposOrdensServicosShowController } from './show/tipos-ordens-servicos-show.controller'
import { TiposOrdensServicosFormController } from './form/tipos-ordens-servicos-form.controller'
import { TiposOrdensServicosShowFormController } from './showform/tipos-ordens-servicos-show-form.controller'

import { TiposOrdensServicosShowFormComponent } from './showform/tipos-ordens-servicos-show-form.component'
import { TiposOrdensServicosFormComponent } from './form/tipos-ordens-servicos-form.component'

export const TiposOrdensServicosModule = angular.module('tiposOrdensServicosModule', ['ui.router.state'])
    .controller('tiposOrdensServicosIndexController', TiposOrdensServicosIndexController)
    .controller('tiposOrdensServicosEditController', TiposOrdensServicosEditController)
    .controller('tiposOrdensServicosNewController', TiposOrdensServicosNewController)
    .controller('tiposOrdensServicosShowController', TiposOrdensServicosShowController)
    .controller('tiposOrdensServicosFormController', TiposOrdensServicosFormController)
    .controller('tiposOrdensServicosShowFormController', TiposOrdensServicosShowFormController)
    .component(TiposOrdensServicosShowFormComponent.selector, TiposOrdensServicosShowFormComponent)
    .component(TiposOrdensServicosFormComponent.selector, TiposOrdensServicosFormComponent)
    .service('tiposOrdensServicosService', TiposOrdensServicosService)
    .config(TiposOrdensServicosRouting).constant('FIELDS_Tiposordensservicos', [
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
    .run(['$rootScope', 'FIELDS_Tiposordensservicos', ($rootScope: any, FIELDS_Tiposordensservicos: object) => {
        $rootScope.FIELDS_Tiposordensservicos = FIELDS_Tiposordensservicos;
    }]).name;
