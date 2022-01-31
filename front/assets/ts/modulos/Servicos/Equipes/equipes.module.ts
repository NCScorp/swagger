import * as angular from 'angular';

import { EquipesService } from './equipes.service'

import { EquipesRouting } from './equipes.routes';

import { EquipesIndexController } from './index/equipes-index.controller';
import { EquipesEditController } from './edit/equipes-edit.controller';
import { EquipesNewController } from './new/equipes-new.controller';
import { EquipesShowController } from './show/equipes-show.controller';
import { EquipesShowFormController } from './showform/equipes-show-form.controller';
import { EquipesFormController } from './form/equipes-form.controller';

import { EquipesShowFormComponent } from './showform/equipes-show-form.component';
import { EquipesFormComponent } from './form/equipes-form.component'

import { EquipesTecnicosModule } from './equipes-tecnicos/equipes-tecnicos.module'

export const EquipesModule = angular.module('equipesModule', ['ui.router.state', EquipesTecnicosModule])
    .controller('equipesIndexController', EquipesIndexController)
    .controller('equipesEditController', EquipesEditController)
    .controller('equipesNewController', EquipesNewController)
    .controller('equipesShowController', EquipesShowController)
    .controller('equipesShowFormController', EquipesShowFormController)
    .controller('equipesFormController',EquipesFormController)
    .component(EquipesShowFormComponent.selector, EquipesShowFormComponent)
    .component(EquipesFormComponent.selector, EquipesFormComponent)
    .service('equipesService', EquipesService)
    .config(EquipesRouting).constant('FIELDS_Equipes', [
        {
        value: 'codigo',
        label: 'codigo',
        type: 'string',
        style: 'title',
        copy: '',
    },])
    .run(['$rootScope', 'FIELDS_Equipes', ($rootScope: any, FIELDS_Equipes: object) => {
        $rootScope.FIELDS_Equipes = FIELDS_Equipes;
    }]).name;
