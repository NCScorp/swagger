import * as angular from 'angular';

import { ContratosIndexController } from './index/contratos-index.controller';
import { ContratosService } from './contratos.service';
import { ContratosRouting } from './contratos.routes';

export const ContratosModule = angular.module('ContratosModule', [
        'ui.router.state',
    ])
    .controller('contratosIndexController', ContratosIndexController)
    .service('contratosService', ContratosService)
    .config(ContratosRouting)
    .name;
