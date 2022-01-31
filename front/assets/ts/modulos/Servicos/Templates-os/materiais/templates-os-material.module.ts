import * as angular from 'angular';

import { TemplatesOrdemServicoMaterialFormComponent } from './form/templates-os-material-form.component';
import { TemplatesOrdemServicoMaterialFormController } from './form/templates-os-material-form.controller';

import { TemplatesOrdemServicoMaterialModalController } from './modal/templates-os-material-modal.controller';
import { TemplatesOrdemServicoMaterialModalService } from './modal/templates-os-material-modal.service';
import { TemplatesOrdemServicoMaterialService } from './templates-os-materiais.service';

export const TemplatesOrdemServicoMaterialModule = angular.module('templatesOrdemServicoMaterialModule', [])
    .service('templatesOrdemServicoMaterialService', TemplatesOrdemServicoMaterialService)
    // Modal
    .controller('templatesOrdemServicoMaterialModalController', TemplatesOrdemServicoMaterialModalController)
    .service('templatesOrdemServicoMaterialModalService', TemplatesOrdemServicoMaterialModalService)
    // Form
    .controller('templatesOrdemServicoMaterialFormController', TemplatesOrdemServicoMaterialFormController)
    .component(TemplatesOrdemServicoMaterialFormComponent.selector, TemplatesOrdemServicoMaterialFormComponent)
    .name;
