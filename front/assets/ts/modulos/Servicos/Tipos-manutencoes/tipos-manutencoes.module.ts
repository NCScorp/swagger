
import * as angular from 'angular';

import { TiposManutencoesService } from './tipos-manutencoes.service';

import { TiposManutencoesRouting } from './tipos-manutencoes.routes';

import { TiposManutencoesIndexController } from './index/tipos-manutencoes-index.controller'
import { TiposManutencoesEditController } from './edit/tipos-manutencoes-edit.controller'
import { TiposManutencoesNewController } from './new/tipos-manutencoes-new.controller'
import { TiposManutencoesShowController } from './show/tipos-manutencoes-show.controller'
import { TiposManutencoesShowFormController } from './showform/tipos-manutencoes-show-form.controller'
import { TiposManutencoesFormController } from './form/tipos-manutencoes-form.controller'

import { TiposManutencoesShowFormComponent } from './showform/tipos-manutencoes-show-form.component'
import { TiposManutencoesFormComponent } from './form/tipos-manutencoes-form.component'

export const TiposManutencoesModule = angular.module('tiposManutencoesModule', ['ui.router.state'])
    .controller('tiposManutencoesIndexController', TiposManutencoesIndexController)
    .controller('tiposManutencoesEditController', TiposManutencoesEditController)
    .controller('tiposManutencoesNewController', TiposManutencoesNewController)
    .controller('tiposManutencoesShowController', TiposManutencoesShowController)
    .controller('tiposManutencoesShowFormController', TiposManutencoesShowFormController)
    .controller('tiposManutencoesFormController', TiposManutencoesFormController)
    .component(TiposManutencoesShowFormComponent.selector, TiposManutencoesShowFormComponent)
    .component(TiposManutencoesFormComponent.selector, TiposManutencoesFormComponent)
    .service('tiposManutencoesService', TiposManutencoesService)
    .config(TiposManutencoesRouting)
    .constant('FIELDS_Tiposmanutencoes', [
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
    .run(['$rootScope', 'FIELDS_Tiposmanutencoes', ($rootScope: any, FIELDS_Tiposmanutencoes: object) => {
        $rootScope.FIELDS_Tiposmanutencoes = FIELDS_Tiposmanutencoes;
    }]).name;
