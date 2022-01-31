import * as angular from 'angular';
import { EtapasEditController } from './edit/etapas-edit.controller';
import { EtapasRouting } from './etapas.routes';
import { EtapasService } from './etapas.service';
import { EtapasFormComponent } from './form/etapas-form.component';
import { EtapasIndexController } from './index/etapas-index.controller';
import { EtapasNewController } from './new/etapas-new.controller';
import { EtapasShowController } from './show/etapas-show.controller';
import { EtapasShowFormComponent } from './showform/etapas-show-form.component';

export const EtapasModule = angular.module('etapasModule', ['ui.router.state'])
  .controller('etapasIndexController', EtapasIndexController)
  .controller('etapasNewController', EtapasNewController)
  .controller('etapasEditController', EtapasEditController)
  .controller('etapasShowController', EtapasShowController)
  .component(EtapasFormComponent.selector, EtapasFormComponent)
  .component(EtapasShowFormComponent.selector, EtapasShowFormComponent)
  .service('etapasService', EtapasService)
  .config(EtapasRouting)
  .name;
