import * as angular from 'angular';

import { ServicosTecnicosChecklistService } from './servicos-tecnicos-checklist.service';

import { ServicosTecnicosChecklistRouting } from './servicos-tecnicos-checklist.routes';

import { ServicosTecnicosChecklistIndexController } from './index/servicos-tecnicos-checklist-index.controller';
import { ServicosTecnicosChecklistNewController } from './new/servicos-tecnicos-checklist-new.controller'
import { ServicosTecnicosChecklistFormController } from './form/servicos-tecnicos-checklist-form.controller';
import { ServicosTecnicosChecklistEditController } from './edit/servicos-tecnicos-checklist-edit.controller'

import { ServicosTecnicosChecklistFormComponent } from './form/servicos-tecnicos-checklist-form.component'

export const ServicosTecnicosChecklistModule = angular.module('servicosTecnicosChecklistModule', ['ui.router.state'])
    .controller('servicosTecnicosChecklistIndexController', ServicosTecnicosChecklistIndexController)
    .controller('servicosTecnicosChecklistNewController', ServicosTecnicosChecklistNewController)
    .controller('servicosTecnicosChecklistFormController', ServicosTecnicosChecklistFormController)
    .controller('servicosTecnicosChecklistEditController', ServicosTecnicosChecklistEditController)
    .component(ServicosTecnicosChecklistFormComponent.selector, ServicosTecnicosChecklistFormComponent)
    .service('servicosTecnicosChecklistService', ServicosTecnicosChecklistService)
    .config(ServicosTecnicosChecklistRouting).constant('FIELDS_ServicostecnicosServicostecnicoschecklist', [
        {
            value: 'descricao',
            label: 'descrição',
            type: 'string',
            style: 'title',
            copy: '',
        },
        {
            value: 'obrigatorio',
            label: 'obrigatório',
            type: 'boolean',
            style: 'default',
            copy: '',
            },
        ])
    .run(['$rootScope', 'FIELDS_ServicostecnicosServicostecnicoschecklist', ($rootScope: any, FIELDS_ServicostecnicosServicostecnicoschecklist: object) => {
        $rootScope.FIELDS_ServicostecnicosServicostecnicoschecklist = FIELDS_ServicostecnicosServicostecnicoschecklist;
    }]).name;
