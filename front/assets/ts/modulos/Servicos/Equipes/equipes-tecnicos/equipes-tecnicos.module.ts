import * as angular from 'angular';

import { EquipesTecnicosService } from './equipes-tecnicos.service'

import { EquipesTecnicosRouting } from './equipes-tecnicos.routes';

import { EquipesTecnicosIndexController } from './index/equipes-tecnicos-index.controller';
import { EquipesTecnicosEditController } from './edit/equipes-tecnicos-edit.controller';
import { EquipesTecnicosFormController } from './form/equipes-tecnicos-form.controller'

import { EquipesTecnicosFormComponent } from './form/equipes-tecnicos-form.component';

export const EquipesTecnicosModule = angular.module('equipesTecnicosModule', ['ui.router.state'])
    .controller('equipesTecnicosIndexController', EquipesTecnicosIndexController)
    .controller('equipesTecnicosEditController',EquipesTecnicosEditController)
    .controller('equipesTecnicosFormController', EquipesTecnicosFormController)
    .component(EquipesTecnicosFormComponent.selector, EquipesTecnicosFormComponent)
    .service('equipesTecnicosService', EquipesTecnicosService)
    .config(EquipesTecnicosRouting).constant('FIELDS_EquipesEquipestecnicos', [
        {
            value: 'responsavel',
            label: 'Responsável',
            type: 'boolean',
            style: 'title',
            copy: '',
        },
        {
            value: 'tecnico.nome',
            label: 'Nome',
            type: 'string',
            style: 'title',
            copy: '',
        },])
    .run(['$rootScope', 'FIELDS_EquipesEquipestecnicos', ($rootScope: any, FIELDS_EquipesEquipestecnicos: object) => {
        $rootScope.FIELDS_EquipesEquipestecnicos = FIELDS_EquipesEquipestecnicos;
    }]).name;
