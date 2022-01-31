import * as angular from 'angular';

import { TemplatesOrdemServicoIndexController } from './index/templates-os-index.controller';
import { templatesOrdemServicoShowEditController } from './showedit/templates-os-showedit.controller';
import { TemplatesOrdemServicoService } from './templates-os.service';
import { TemplatesOrdemServicoRouting } from './templates-os.routes';
import { templatesOsShowFormComponent } from './showform/templates-os-show-form.component';
import { TemplatesOrdemServicoShowFormController } from './showform/templates-os-show-form.controller';
import { TemplatesOrdemServicoFormComponent } from './form/templates-os-form.component';
import { TemplatesOrdemServicoFormController } from './form/templates-os-form.controller';
import { TemplatesOrdemServicoItemModule } from './itens/templates-os-item.module';
import { TemplatesOrdemServicoMaterialModule } from './materiais/templates-os-material.module';
import { TemplatesOrdemServicoNewController } from './new/templates-os-new.controller';
import { ListagemMateriaisFormComponent } from './listagem-materiais-form/listagem-materiais-form.component';
import { ListagemServicosFormComponent } from './listagem-servicos-form/listagem-servicos-form.component';

export const TemplatesOrdemServicoModule = angular.module('TemplatesOrdemServicoModule', [
        'ui.router.state', 
        TemplatesOrdemServicoItemModule,
        TemplatesOrdemServicoMaterialModule
    ])
    .controller('templatesOrdemServicoIndexController', TemplatesOrdemServicoIndexController)
    .controller('templatesOrdemServicoShowEditController', templatesOrdemServicoShowEditController)
    .service('templatesOrdemServicoService', TemplatesOrdemServicoService)
    .controller('templatesOrdemServicoShowFormController', TemplatesOrdemServicoShowFormController)
    .component(templatesOsShowFormComponent.selector, templatesOsShowFormComponent)
    .controller('templatesOrdemServicoFormController', TemplatesOrdemServicoFormController)
    .component(TemplatesOrdemServicoFormComponent.selector, TemplatesOrdemServicoFormComponent)
    .component(ListagemMateriaisFormComponent.selector, ListagemMateriaisFormComponent)
    .component(ListagemServicosFormComponent.selector, ListagemServicosFormComponent)
    .controller('templatesOrdemServicoNewController', TemplatesOrdemServicoNewController)
    .config(TemplatesOrdemServicoRouting)
    .name;
