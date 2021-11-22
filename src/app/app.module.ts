import { SharedModule } from '@shared/shared.module';
import * as angular from 'angular';

import {AppController} from './app.controller';
import {appRoutes} from './app.routes';
import {NsjRoutes} from './core/nsj-core';

/**Incluir aqui a importação dos módulos criados dentro da pasta modules. Home é um módulo de exemplo. */
import {
    HomeModule,
    ContratosModule
} from './modules';

import { nsDropzone, nsjTreeGrid} from './shared/components';

import {resizerModule} from './shared/directives';

/**Incluir aqui os módulos criados, para serem exportados */
export const app = angular.module('app',
    [
        'ui.router',
        'ui.bootstrap',
        'ui.select',
        'ngSanitize',
        'mdaUiSelect',
        'ngMessages',
        'objectList',
        'angular-file-input',
        'angularMoment',
        'angular.filter',
        'toaster',
        'convertToNumber',
        'filters',
        'ui.mask',
        'ngCpfCnpj',
        'dateInput',
        'nasajon-ui',
        'innerForm',
        'multipleDatePicker',
        'ngCookies',
        nsDropzone,
        nsjTreeGrid,
        SharedModule,
        resizerModule,
        HomeModule,
        ContratosModule
    ])
    .config(appRoutes)
    .provider('nsjRouting', NsjRoutes)
    .controller('AppController', AppController)
    .constant('angularMomentConfig', {
        timezone: 'America/Sao_Paulo'
    })
    .name;
