import * as angular from 'angular';
import { TemplatesOrdemServicoItemChecklistService } from './checklist/templates-os-item-checklist.service';

import { TemplatesOrdemServicoItemFormComponent } from './form/templates-os-item-form.component';
import { TemplatesOrdemServicoItemFormController } from './form/templates-os-item-form.controller';
import { TemplatesOrdemServicoItemModalController } from './modal/templates-os-item-modal.controller';
import { TemplatesOrdemServicoItemModalService } from './modal/templates-os-item-modal.service';
import { TemplatesOrdemServicoItemService } from './templates-os-item.service';

export const TemplatesOrdemServicoItemModule = angular.module('templatesOrdemServicoItemModule', [])
    // Item
    .service('templatesOrdemServicoItemService', TemplatesOrdemServicoItemService)
    // Modal
    .controller('templatesOrdemServicoItemModalController', TemplatesOrdemServicoItemModalController)
    .service('templatesOrdemServicoItemModalService', TemplatesOrdemServicoItemModalService)
    // Form
    .controller('templatesOrdemServicoItemFormController', TemplatesOrdemServicoItemFormController)
    .component(TemplatesOrdemServicoItemFormComponent.selector, TemplatesOrdemServicoItemFormComponent)
    // Checklist
    .service('templatesOrdemServicoItemChecklistService', TemplatesOrdemServicoItemChecklistService)
    .name;
