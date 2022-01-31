import * as angular from 'angular';

import { ServicosTecnicosService } from './servicos-tecnicos.service'

import { ServicosTecnicosRouting } from './servicos-tecnicos.routes';

import { ServicosTecnicosIndexController } from './index/servicos-tecnicos-index.controller';
import { ServicosTecnicosEditController } from './edit/servicos-tecnicos-edit.controller';
import { ServicosTecnicosNewController } from './new/servicos-tecnicos-new.controller';
import { ServicosTecnicosShowController } from './show/servicos-tecnicos-show.controller';
import { ServicosTecnicosShowFormController } from './showform/servicos-tecnicos-show-form.controller';
import { ServicosTecnicosFormController } from './form/servicos-tecnicos-form.controller';

import { ServicosTecnicosShowFormComponent } from './showform/servicos-tecnicos-show-form.component'
import { ServicosTecnicosFormComponent } from './form/servicos-tecnicos-form.component'

import { ServicosTecnicosChecklistModule } from './servicos-tecnicos-checklist/servicos-tecnicos-checklist.module'

export const ServicosTecnicosModule = angular.module('ServicostecnicosModule', ['ui.router.state', ServicosTecnicosChecklistModule])
    .controller('servicosTecnicosIndexController', ServicosTecnicosIndexController)
    .controller('servicosTecnicosEditController', ServicosTecnicosEditController)
    .controller('servicosTecnicosNewController', ServicosTecnicosNewController)
    .controller('servicosTecnicosShowController',ServicosTecnicosShowController)
    .controller('servicosTecnicosShowFormController',ServicosTecnicosShowFormController)
    .controller('servicosTecnicosFormController',ServicosTecnicosFormController)
    .component(ServicosTecnicosShowFormComponent.selector, ServicosTecnicosShowFormComponent)
    .component(ServicosTecnicosFormComponent.selector, ServicosTecnicosFormComponent)
    .service('servicosTecnicosService', ServicosTecnicosService)
    .config(ServicosTecnicosRouting)
    .constant('FIELDS_Servicostecnicos', [
        {
            value: 'descricao',
            label: 'Descrição',
            type: 'string',
            style: 'title',
            copy: '',
        },
        {
            value: 'codigo',
            label: 'código',
            type: 'string',
            style: 'default',
            copy: '',
        },
        {
            value: 'tipo',
            label: 'tipo',
            type: 'options',
            style: 'default',
            copy: '',
            options: {  'Padrão': 'entity.tipo == "0"',  'Transporte': 'entity.tipo == "1"',  },
        },])
    .run(
        ['$rootScope', 'FIELDS_Servicostecnicos', ($rootScope: any, FIELDS_Servicostecnicos: object) => {
        $rootScope.FIELDS_Servicostecnicos = FIELDS_Servicostecnicos;
    }]).name;
