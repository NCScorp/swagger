import * as angular from 'angular';
import { ApiService } from './api/api.service';

import { RotasService } from './rotas/rotas.service';

export const SharedModule = angular.module('sharedModule', [])
    .service('rotasService', RotasService)
    .service('apiService', ApiService)
    .name;